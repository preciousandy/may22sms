<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BoostOrder;
use App\Models\FundingTransaction;
use App\Models\LogOrder;
use App\Models\SmsOrder;
use App\Models\SystemTransaction;
use App\Models\WalletTransaction;
use App\Notifications\SimpleDatabaseNotification;
use App\Services\DaisySmsService;
use App\Services\FiveSimService;
use App\Services\ReferralService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrdersController extends Controller
{
    private function allowedTabs(): array
    {
        return ['logs', 'sms', 'boost', 'funding'];
    }

    private function applyUserSearch($query, string $q)
    {
        if ($q === '') {
            return $query;
        }

        $like = '%' . $q . '%';

        return $query->where(function ($w) use ($q, $like) {
            $w->whereHas('user', function ($u) use ($q, $like) {
                $u->where('email', 'like', $like)->orWhere('name', 'like', $like);
                if (is_numeric($q)) {
                    $u->orWhere('id', (int) $q);
                }
            });

            if (is_numeric($q)) {
                $w->orWhere('id', (int) $q);
            }
        });
    }

    public function index(Request $request)
    {
        $tab = (string) $request->query('tab', 'logs');
        if (! in_array($tab, $this->allowedTabs(), true)) {
            $tab = 'logs';
        }

        $q = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', 'all');

        $orders = null;
        $statusOptions = [];

        if ($tab === 'logs') {
            $statusOptions = ['all', 'completed', 'pending', 'failed', 'canceled'];
            $query = LogOrder::query()->with('user')->latest();
            $query = $this->applyUserSearch($query, $q);
            if ($status !== 'all') {
                $query->where('status', $status);
            }
            $orders = $query->paginate(25, ['*'], 'page')->appends($request->query());
        } elseif ($tab === 'sms') {
            $statusOptions = ['all', 'PENDING', 'pending', 'WAITING', 'completed', 'RECEIVED', 'canceled', 'CANCELED', 'TIMEOUT'];
            $query = SmsOrder::query()->with('user')->latest();
            $query = $this->applyUserSearch($query, $q);
            if ($status !== 'all') {
                $query->where('status', $status);
            }
            $orders = $query->paginate(25, ['*'], 'page')->appends($request->query());
        } elseif ($tab === 'boost') {
            $statusOptions = ['all', 'processing', 'completed', 'canceled', 'partial', 'pending'];
            $query = BoostOrder::query()->with(['user', 'service'])->latest();
            $query = $this->applyUserSearch($query, $q);
            if ($status !== 'all') {
                $query->where('status', $status);
            }
            $orders = $query->paginate(25, ['*'], 'page')->appends($request->query());
        } elseif ($tab === 'funding') {
            $statusOptions = ['all', 'pending', 'waiting_payment', 'initiated', 'approved', 'rejected', 'canceled', 'failed', 'expired'];
            $query = FundingTransaction::query()->with('user')->latest();
            $query = $this->applyUserSearch($query, $q);
            if ($status !== 'all') {
                $query->where('status', $status);
            }
            $orders = $query->paginate(25, ['*'], 'page')->appends($request->query());
        }

        $counts = [
            'logs' => LogOrder::count(),
            'sms' => SmsOrder::count(),
            'boost' => BoostOrder::count(),
            'funding' => FundingTransaction::count(),
        ];

        $pendingCounts = [
            'logs' => LogOrder::where('status', 'pending')->count(),
            'sms' => SmsOrder::whereIn('status', ['PENDING', 'WAITING', 'pending'])->count(),
            'boost' => BoostOrder::whereIn('status', ['pending', 'processing'])->count(),
            'funding' => FundingTransaction::whereIn('status', ['pending', 'initiated', 'waiting_payment'])->count(),
        ];

        return view('admin.orders.index', compact('tab', 'q', 'status', 'orders', 'counts', 'pendingCounts', 'statusOptions'));
    }

    public function refundOrder(Request $request)
    {
        $request->validate([
            'order_type' => 'required|string|in:logs,sms,boost,funding',
            'order_id' => 'required|integer',
            'reason' => 'nullable|string|max:255',
        ]);

        $type = $request->input('order_type');
        $id = (int) $request->input('order_id');
        $reason = $request->input('reason') ?: 'Admin initiated refund';
        $walletService = app(WalletService::class);

        if ($type === 'sms') {
            $order = SmsOrder::with('user')->findOrFail($id);
            if (in_array(strtoupper($order->status), ['CANCELED', 'CANCELLED', 'REFUNDED'], true)) {
                return back()->with('error', 'SMS Order #' . $order->id . ' is already canceled/refunded.');
            }

            // Attempt provider cancellation
            try {
                $provider = strtolower((string) ($order->provider ?: '5sim'));
                $smsService = $provider === 'daisysms' ? app(DaisySmsService::class) : app(FiveSimService::class);
                if (method_exists($smsService, 'cancelOrder')) {
                    $smsService->cancelOrder($order->order_id);
                }
            } catch (\Throwable $e) {
                Log::warning("Admin refund SMS order {$order->id} provider cancel warning: " . $e->getMessage());
            }

            $order->status = 'CANCELED';
            $order->save();

            $refundRef = 'SMSREFUND-' . $order->id;
            $alreadyRefunded = WalletTransaction::where('type', 'sms_refund')->where('reference', $refundRef)->exists();

            if (! $alreadyRefunded && (float) $order->price > 0 && $order->user) {
                $walletService->credit($order->user, (float) $order->price, 'sms_refund', $refundRef, [
                    'admin_refund' => true,
                    'reason' => $reason,
                    'order_id' => $order->id,
                ]);

                $order->user->notify(new SimpleDatabaseNotification(
                    'SMS Order Refunded by Admin',
                    "Your SMS order #{$order->id} has been refunded (\${$order->price}). Reason: {$reason}",
                    '/orders?type=sms'
                ));
            }

            return back()->with('success', 'SMS Order #' . $order->id . ' refunded and credited to user wallet.');
        } elseif ($type === 'logs') {
            $order = LogOrder::with('user')->findOrFail($id);
            if (in_array(strtolower($order->status), ['canceled', 'cancelled', 'refunded'], true)) {
                return back()->with('error', 'Log Order #' . $order->id . ' is already canceled/refunded.');
            }

            $order->status = 'canceled';
            $order->save();

            $refundRef = 'LOGREFUND-' . $order->id;
            $alreadyRefunded = WalletTransaction::where('type', 'log_refund')->where('reference', $refundRef)->exists();

            if (! $alreadyRefunded && (float) $order->total_amount > 0 && $order->user) {
                $walletService->credit($order->user, (float) $order->total_amount, 'log_refund', $refundRef, [
                    'admin_refund' => true,
                    'reason' => $reason,
                    'order_id' => $order->id,
                ]);

                $order->user->notify(new SimpleDatabaseNotification(
                    'Log Order Refunded by Admin',
                    "Your log order #{$order->id} has been refunded (\${$order->total_amount}). Reason: {$reason}",
                    '/orders?type=logs'
                ));
            }

            return back()->with('success', 'Log Order #' . $order->id . ' refunded and credited to user wallet.');
        } elseif ($type === 'boost') {
            $order = BoostOrder::with('user')->findOrFail($id);
            if (in_array(strtolower($order->status), ['canceled', 'cancelled', 'refunded'], true)) {
                return back()->with('error', 'Boost Order #' . $order->id . ' is already canceled/refunded.');
            }

            $order->status = 'canceled';
            $order->save();

            $refundRef = 'BOOSTREFUND-' . $order->id;
            $alreadyRefunded = WalletTransaction::where('type', 'boost_refund')->where('reference', $refundRef)->exists();

            if (! $alreadyRefunded && (float) $order->charge > 0 && $order->user) {
                $walletService->credit($order->user, (float) $order->charge, 'boost_refund', $refundRef, [
                    'admin_refund' => true,
                    'reason' => $reason,
                    'order_id' => $order->id,
                ]);

                $order->user->notify(new SimpleDatabaseNotification(
                    'Boost Order Refunded by Admin',
                    "Your social boosting order #{$order->id} has been refunded (\${$order->charge}). Reason: {$reason}",
                    '/orders?type=boost'
                ));
            }

            return back()->with('success', 'Boost Order #' . $order->id . ' refunded and credited to user wallet.');
        } elseif ($type === 'funding') {
            // For funding, refunding is only valid if it was approved (reversal)
            $order = FundingTransaction::with('user')->findOrFail($id);
            if (strtolower((string) $order->status) !== 'approved') {
                return back()->with('error', 'Cannot refund an unpaid funding request. Use the Cancel button instead.');
            }

            return $this->reverseFunding($request);
        }

        return back()->with('error', 'Invalid order type.');
    }

    public function cancelOrder(Request $request)
    {
        $request->validate([
            'order_type' => 'required|string|in:logs,sms,boost,funding',
            'order_id' => 'required|integer',
            'reason' => 'nullable|string|max:255',
        ]);

        $type = $request->input('order_type');
        $id = (int) $request->input('order_id');
        $reason = $request->input('reason') ?: 'Admin initiated cancellation (no refund)';

        if ($type === 'funding') {
            $order = FundingTransaction::with('user')->findOrFail($id);
            if (strtolower((string) $order->status) === 'approved') {
                return back()->with('error', 'Approved funding transactions cannot simply be cancelled. Use Reverse to debit wallet if needed.');
            }

            if (in_array(strtolower((string) $order->status), ['canceled', 'cancelled', 'rejected', 'failed', 'expired'], true)) {
                return back()->with('error', 'Funding transaction #' . $order->id . ' is already canceled/rejected.');
            }

            $beforeStatus = (string) ($order->status ?? '');
            $order->status = 'canceled';
            $order->rejected_at = now();
            $order->save();

            SystemTransaction::create([
                'actor_user_id' => auth()->id(),
                'user_id' => $order->user_id,
                'type' => 'funding',
                'action' => 'cancel',
                'status' => 'canceled',
                'subject_type' => FundingTransaction::class,
                'subject_id' => (string) $order->id,
                'amount_usd' => (float) $order->amount_usd,
                'reference' => (string) $order->reference,
                'before' => ['status' => $beforeStatus],
                'after' => ['status' => 'canceled'],
                'meta' => [
                    'funding_transaction_id' => $order->id,
                    'reason' => $reason,
                    'refunded' => false,
                ],
            ]);

            if ($order->user) {
                $order->user->notify(new SimpleDatabaseNotification(
                    'Funding Request Canceled',
                    "Your funding request #{$order->id} (\${$order->amount_usd}) has been canceled. No payment was recorded.",
                    '/funding'
                ));
            }

            return back()->with('success', 'Funding transaction #' . $order->id . ' canceled. No funds were refunded or credited.');
        } elseif ($type === 'sms') {
            $order = SmsOrder::with('user')->findOrFail($id);
            if (in_array(strtoupper($order->status), ['CANCELED', 'CANCELLED', 'REFUNDED'], true)) {
                return back()->with('error', 'SMS Order #' . $order->id . ' is already canceled.');
            }

            try {
                $provider = strtolower((string) ($order->provider ?: '5sim'));
                $smsService = $provider === 'daisysms' ? app(DaisySmsService::class) : app(FiveSimService::class);
                if (method_exists($smsService, 'cancelOrder')) {
                    $smsService->cancelOrder($order->order_id);
                }
            } catch (\Throwable $e) {
                Log::warning("Admin cancel SMS order {$order->id} provider cancel warning: " . $e->getMessage());
            }

            $order->status = 'CANCELED';
            $order->save();

            SystemTransaction::create([
                'actor_user_id' => auth()->id(),
                'user_id' => $order->user_id,
                'type' => 'sms_order',
                'action' => 'cancel_no_refund',
                'status' => 'canceled',
                'subject_type' => SmsOrder::class,
                'subject_id' => (string) $order->id,
                'amount_usd' => (float) $order->price,
                'reference' => 'SMSORDER-' . $order->id,
                'meta' => [
                    'reason' => $reason,
                    'refunded' => false,
                ],
            ]);

            if ($order->user) {
                $order->user->notify(new SimpleDatabaseNotification(
                    'SMS Order Canceled',
                    "Your SMS order #{$order->id} was canceled by admin without refund. Reason: {$reason}",
                    '/orders?type=sms'
                ));
            }

            return back()->with('success', 'SMS Order #' . $order->id . ' canceled without refund.');
        } elseif ($type === 'logs') {
            $order = LogOrder::with('user')->findOrFail($id);
            if (in_array(strtolower($order->status), ['canceled', 'cancelled', 'refunded'], true)) {
                return back()->with('error', 'Log Order #' . $order->id . ' is already canceled.');
            }

            $order->status = 'canceled';
            $order->save();

            SystemTransaction::create([
                'actor_user_id' => auth()->id(),
                'user_id' => $order->user_id,
                'type' => 'log_order',
                'action' => 'cancel_no_refund',
                'status' => 'canceled',
                'subject_type' => LogOrder::class,
                'subject_id' => (string) $order->id,
                'amount_usd' => (float) $order->total_amount,
                'reference' => 'LOGORDER-' . $order->id,
                'meta' => [
                    'reason' => $reason,
                    'refunded' => false,
                ],
            ]);

            if ($order->user) {
                $order->user->notify(new SimpleDatabaseNotification(
                    'Log Order Canceled',
                    "Your log order #{$order->id} was canceled by admin without refund. Reason: {$reason}",
                    '/orders?type=logs'
                ));
            }

            return back()->with('success', 'Log Order #' . $order->id . ' canceled without refund.');
        } elseif ($type === 'boost') {
            $order = BoostOrder::with('user')->findOrFail($id);
            if (in_array(strtolower($order->status), ['canceled', 'cancelled', 'refunded'], true)) {
                return back()->with('error', 'Boost Order #' . $order->id . ' is already canceled.');
            }

            $order->status = 'canceled';
            $order->save();

            SystemTransaction::create([
                'actor_user_id' => auth()->id(),
                'user_id' => $order->user_id,
                'type' => 'boost_order',
                'action' => 'cancel_no_refund',
                'status' => 'canceled',
                'subject_type' => BoostOrder::class,
                'subject_id' => (string) $order->id,
                'amount_usd' => (float) $order->charge,
                'reference' => 'BOOSTORDER-' . $order->id,
                'meta' => [
                    'reason' => $reason,
                    'refunded' => false,
                ],
            ]);

            if ($order->user) {
                $order->user->notify(new SimpleDatabaseNotification(
                    'Boost Order Canceled',
                    "Your social boosting order #{$order->id} was canceled by admin without refund. Reason: {$reason}",
                    '/orders?type=boost'
                ));
            }

            return back()->with('success', 'Boost Order #' . $order->id . ' canceled without refund.');
        }

        return back()->with('error', 'Invalid order type.');
    }

    public function approveFunding(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer',
        ]);

        $id = (int) $request->input('order_id');
        $walletService = app(WalletService::class);
        $referralService = app(ReferralService::class);

        DB::beginTransaction();
        try {
            $tx = FundingTransaction::lockForUpdate()->findOrFail($id);

            if (in_array(strtolower((string) $tx->status), ['approved'], true)) {
                DB::rollBack();
                return back()->with('error', 'This funding transaction is already approved.');
            }

            $user = $tx->user()->lockForUpdate()->first();
            if (! $user) {
                DB::rollBack();
                return back()->with('error', 'Associated user not found.');
            }

            $walletService->credit($user, (float) $tx->amount_usd, 'funding_credit', (string) $tx->reference, [
                'funding_transaction_id' => $tx->id,
                'payment_method' => (string) $tx->payment_method,
                'provider' => (string) ($tx->provider ?? ''),
                'admin_id' => auth()->id(),
            ]);

            $beforeStatus = (string) ($tx->status ?? '');
            $tx->status = 'approved';
            $tx->approved_at = now();
            $tx->save();

            SystemTransaction::create([
                'actor_user_id' => auth()->id(),
                'user_id' => $tx->user_id,
                'type' => 'funding',
                'action' => 'approve',
                'status' => 'approved',
                'subject_type' => FundingTransaction::class,
                'subject_id' => (string) $tx->id,
                'amount_usd' => (float) $tx->amount_usd,
                'reference' => (string) $tx->reference,
                'before' => ['status' => $beforeStatus],
                'after' => ['status' => 'approved'],
                'meta' => [
                    'funding_transaction_id' => $tx->id,
                ],
            ]);

            DB::commit();

            $user->notify(new SimpleDatabaseNotification(
                'Wallet funded',
                'Your deposit of $' . number_format($tx->amount_usd, 2) . ' has been approved and credited.',
                '/wallet'
            ));

            $referralService->awardFirstFundingBonus($tx->fresh(['user']));

            return back()->with('success', 'Funding transaction #' . $tx->id . ' approved and $' . number_format($tx->amount_usd, 2) . ' credited to user wallet.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Admin approve funding error: ' . $e->getMessage());
            return back()->with('error', 'Failed to approve funding: ' . $e->getMessage());
        }
    }

    public function reverseFunding(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer',
            'reason' => 'nullable|string|max:255',
        ]);

        $id = (int) $request->input('order_id');
        $reason = $request->input('reason') ?: 'Admin initiated funding reversal';
        $walletService = app(WalletService::class);

        DB::beginTransaction();
        try {
            $tx = FundingTransaction::lockForUpdate()->findOrFail($id);

            if (strtolower((string) $tx->status) !== 'approved') {
                DB::rollBack();
                return back()->with('error', 'Only approved funding transactions can be reversed.');
            }

            $user = $tx->user()->lockForUpdate()->first();
            if (! $user) {
                DB::rollBack();
                return back()->with('error', 'Associated user not found.');
            }

            $revRef = 'REVERT-FUNDING-' . $tx->id;
            $alreadyReverted = WalletTransaction::where('type', 'funding_reversal')->where('reference', $revRef)->exists();
            if ($alreadyReverted) {
                DB::rollBack();
                return back()->with('error', 'This funding transaction has already been reversed.');
            }

            $walletService->debit($user, (float) $tx->amount_usd, 'funding_reversal', $revRef, [
                'funding_transaction_id' => $tx->id,
                'admin_id' => auth()->id(),
                'reason' => $reason,
            ]);

            $beforeStatus = (string) ($tx->status ?? '');
            $tx->status = 'rejected';
            $tx->rejected_at = now();
            $tx->save();

            SystemTransaction::create([
                'actor_user_id' => auth()->id(),
                'user_id' => $tx->user_id,
                'type' => 'funding',
                'action' => 'reverse',
                'status' => 'rejected',
                'subject_type' => FundingTransaction::class,
                'subject_id' => (string) $tx->id,
                'amount_usd' => (float) $tx->amount_usd,
                'reference' => (string) $tx->reference,
                'before' => ['status' => $beforeStatus],
                'after' => ['status' => 'rejected'],
                'meta' => [
                    'funding_transaction_id' => $tx->id,
                    'reason' => $reason,
                    'reverted_at' => now()->toDateTimeString(),
                ],
            ]);

            DB::commit();

            $user->notify(new SimpleDatabaseNotification(
                'Funding Reversal',
                "Your funding #{$tx->id} (\${$tx->amount_usd}) was reversed by admin. Reason: {$reason}",
                '/wallet'
            ));

            return back()->with('success', 'Funding transaction #' . $tx->id . ' has been reversed and $' . number_format($tx->amount_usd, 2) . ' debited from user wallet.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Admin reverse funding error: ' . $e->getMessage());
            return back()->with('error', 'Failed to reverse funding: ' . $e->getMessage());
        }
    }
}
