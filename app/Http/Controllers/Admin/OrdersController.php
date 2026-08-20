<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BoostOrder;
use App\Models\FundingTransaction;
use App\Models\LogOrder;
use App\Models\SmsOrder;
use App\Models\WalletTransaction;
use App\Notifications\SimpleDatabaseNotification;
use App\Services\DaisySmsService;
use App\Services\FiveSimService;
use App\Services\WalletService;
use Illuminate\Http\Request;
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
            $statusOptions = ['all', 'PENDING', 'pending', 'completed', 'RECEIVED', 'canceled', 'CANCELED', 'TIMEOUT'];
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
            $statusOptions = ['all', 'pending', 'initiated', 'waiting_payment', 'approved', 'rejected', 'failed'];
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
            $order = FundingTransaction::with('user')->findOrFail($id);
            $order->status = 'rejected';
            $order->save();

            if ($order->user) {
                $order->user->notify(new SimpleDatabaseNotification(
                    'Funding Request Rejected by Admin',
                    "Your funding request #{$order->id} (\${$order->amount_usd}) was rejected.",
                    '/funding'
                ));
            }

            return back()->with('success', 'Funding transaction #' . $order->id . ' updated to rejected.');
        }

        return back()->with('error', 'Invalid order type.');
    }
}
