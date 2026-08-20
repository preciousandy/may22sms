<?php

namespace App\Http\Controllers;

use App\Mail\FundingSuccessEmail;
use App\Models\FundingTransaction;
use App\Notifications\SimpleDatabaseNotification;
use App\Services\NowPaymentsService;
use App\Services\ReferralService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NowPaymentsWebhookController extends Controller
{
    public function handle(Request $request, NowPaymentsService $now, WalletService $wallet, ReferralService $referrals)
    {
        $raw = $request->getContent();
        $sig = $request->header('x-nowpayments-sig');

        if (! $now->verifyIpnSignature($raw, $sig)) {
            return response()->json(['error' => 'invalid_signature'], 401);
        }

        $payload = $request->json()->all();
        if (! is_array($payload) || $payload === []) {
            $payload = $request->all();
        }
        $orderId = $payload['order_id'] ?? null;
        $paymentId = $payload['payment_id'] ?? null;
        $status = strtolower((string) ($payload['payment_status'] ?? $payload['status'] ?? ''));

        if (! $orderId && ! $paymentId) {
            return response()->json(['error' => 'missing_identifiers'], 400);
        }

        $txQuery = FundingTransaction::query();
        if ($orderId) {
            $txQuery->where('reference', (string) $orderId);
        } else {
            $txQuery->where('provider_payment_id', (string) $paymentId);
        }

        $tx = $txQuery->first();
        if (! $tx) {
            return response()->json(['ok' => true]);
        }

        if ($paymentId && ! $tx->provider_payment_id) {
            $tx->provider_payment_id = (string) $paymentId;
            $tx->save();
        }

        if (in_array($status, ['failed', 'expired', 'refunded'], true)) {
            if (! in_array($tx->status, ['approved', 'rejected'], true)) {
                $tx->status = $status;
                $tx->rejected_at = now();
                $tx->save();
            }

            return response()->json(['ok' => true]);
        }

        if (! in_array($status, ['finished', 'confirmed'], true)) {
            $tx->status = $status !== '' ? $status : $tx->status;
            $tx->save();

            return response()->json(['ok' => true]);
        }

        DB::beginTransaction();
        try {
            $lockedTx = FundingTransaction::lockForUpdate()->find($tx->id);
            if (! $lockedTx) {
                DB::rollBack();

                return response()->json(['ok' => true]);
            }

            if ($lockedTx->status === 'approved') {
                DB::commit();

                return response()->json(['ok' => true]);
            }

            $user = $lockedTx->user()->lockForUpdate()->first();
            $wallet->credit($user, (float) $lockedTx->amount_usd, 'funding_credit', (string) $lockedTx->reference, [
                'funding_transaction_id' => $lockedTx->id,
                'payment_method' => (string) $lockedTx->payment_method,
                'provider' => 'nowpayments',
                'provider_payment_id' => (string) ($lockedTx->provider_payment_id ?? ''),
            ]);

            $lockedTx->status = 'approved';
            $lockedTx->approved_at = now();
            $lockedTx->save();

            DB::commit();

            $user->notify(new SimpleDatabaseNotification(
                'Wallet funded',
                'Your crypto payment was confirmed and your wallet was credited.',
                '/wallet'
            ));

            try {
                Mail::to($user->email)->send(new FundingSuccessEmail($user, $lockedTx, $lockedTx->amount_usd));
            } catch (\Exception $e) {
                Log::error('Funding Success Email failed to send: '.$e->getMessage());
            }

            $referrals->awardFirstFundingBonus($lockedTx->fresh(['user']));

            return response()->json(['ok' => true]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json(['error' => 'server_error'], 500);
        }
    }
}
