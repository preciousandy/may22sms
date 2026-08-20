<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FundingTransaction;
use App\Models\SystemTransaction;
use App\Notifications\SimpleDatabaseNotification;
use App\Services\ReferralService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FundingController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        $funding = FundingTransaction::with('user')
            ->when($status, function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->latest()
            ->paginate(20);

        $funding->appends($request->query());

        return view('admin.funding.index', compact('funding', 'status'));
    }

    public function approve($id, WalletService $wallet, ReferralService $referrals)
    {
        DB::beginTransaction();
        try {
            $tx = FundingTransaction::lockForUpdate()->findOrFail($id);

            if (in_array($tx->status, ['approved', 'rejected'], true)) {
                DB::rollBack();

                return back()->with('error', 'This transaction is already processed.');
            }

            $user = $tx->user()->lockForUpdate()->first();
            $wallet->credit($user, (float) $tx->amount_usd, 'funding_credit', (string) $tx->reference, [
                'funding_transaction_id' => $tx->id,
                'payment_method' => (string) $tx->payment_method,
                'provider' => (string) ($tx->provider ?? ''),
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
                'before' => [
                    'status' => $beforeStatus,
                ],
                'after' => [
                    'status' => 'approved',
                ],
                'meta' => [
                    'funding_transaction_id' => $tx->id,
                ],
            ]);

            DB::commit();

            $user->notify(new SimpleDatabaseNotification(
                'Wallet funded',
                'Your deposit has been confirmed and your wallet was credited.',
                '/wallet'
            ));

            $referrals->awardFirstFundingBonus($tx->fresh(['user']));

            return back()->with('success', 'Funding approved and wallet credited.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Failed to approve funding.');
        }
    }

    public function reject($id)
    {
        $tx = FundingTransaction::findOrFail($id);

        if (in_array($tx->status, ['approved', 'rejected'], true)) {
            return back()->with('error', 'This transaction is already processed.');
        }

        $beforeStatus = (string) ($tx->status ?? '');
        $tx->status = 'rejected';
        $tx->rejected_at = now();
        $tx->save();

        SystemTransaction::create([
            'actor_user_id' => auth()->id(),
            'user_id' => $tx->user_id,
            'type' => 'funding',
            'action' => 'reject',
            'status' => 'rejected',
            'subject_type' => FundingTransaction::class,
            'subject_id' => (string) $tx->id,
            'amount_usd' => (float) $tx->amount_usd,
            'reference' => (string) $tx->reference,
            'before' => [
                'status' => $beforeStatus,
            ],
            'after' => [
                'status' => 'rejected',
            ],
            'meta' => [
                'funding_transaction_id' => $tx->id,
            ],
        ]);

        return back()->with('success', 'Funding rejected.');
    }
}
