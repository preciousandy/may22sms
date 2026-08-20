<?php

namespace App\Http\Controllers;

use App\Mail\FundingSuccessEmail;
use App\Models\FundingTransaction;
use App\Models\Setting;
use App\Notifications\SimpleDatabaseNotification;
use App\Services\PaymentPointService;
use App\Services\ReferralService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PaymentPointWebhookController extends Controller
{
    public function handle(Request $request, PaymentPointService $paymentPoint, WalletService $wallet, ReferralService $referrals)
    {
        $raw = $request->getContent();
        Log::info('PaymentPoint Webhook received (raw)', ['raw' => $raw, 'headers' => $request->headers->all()]);
        $configuredHeader = (string) config('services.paymentpoint.signature_header', '');
        $sig = ($configuredHeader !== '' ? $request->header($configuredHeader) : null)
            ?? $request->header('paymentpoint-signature')
            ?? $request->header('x-paymentpoint-signature')
            ?? $request->header('x-paystack-signature')
            ?? $request->header('x-signature');
        Log::info('PaymentPoint Webhook signature check', [
            'sig' => $sig,
            'configured_header' => $configuredHeader,
            'webhook_secret_configured' => config('services.paymentpoint.webhook_secret') !== '',
        ]);

        if ($paymentPoint->verifyWebhookSignature($raw, $sig) === false) {
            Log::warning('PaymentPoint Webhook invalid signature');
            return response()->json(['error' => 'invalid_signature'], 401);
        }

        $payload = $request->json()->all();
        if (! is_array($payload) || $payload === []) {
            $payload = $request->all();
        }
        $data = is_array($payload['data'] ?? null) ? $payload['data'] : $payload;
        Log::info('PaymentPoint Webhook parsed', ['payload' => $payload, 'data' => $data]);

        $reference = $data['reference'] ?? $data['tx_ref'] ?? $data['order_id'] ?? null;
        $notificationStatus = strtolower((string) ($payload['notification_status'] ?? ''));
        $status = strtolower((string) ($data['status'] ?? $data['transaction_status'] ?? $notificationStatus ?? ''));
        $providerPaymentId = $data['id'] ?? $data['transaction_id'] ?? $data['payment_id'] ?? null;
        $receiverAccountNumber = $data['receiver']['account_number'] ?? null;

        // Try to find transaction by reference first, then by receiver account number
        $tx = null;
        if ($reference) {
            $tx = FundingTransaction::where('reference', (string) $reference)->first();
        }
        if (!$tx && $receiverAccountNumber) {
            $tx = FundingTransaction::where('provider', 'paymentpoint')
                ->where('provider_payment_id', (string) $receiverAccountNumber)
                ->whereIn('status', ['pending', 'waiting_payment'])
                ->latest()
                ->first();
        }

        if (! $tx) {
            Log::warning('PaymentPoint Webhook: no matching transaction found', ['reference' => $reference, 'receiver_account_number' => $receiverAccountNumber]);
            return response()->json(['ok' => true]);
        }

        if ($providerPaymentId && ! $tx->provider_payment_id) {
            $tx->provider_payment_id = (string) $providerPaymentId;
            $tx->save();
        }

        $isFailed = in_array($status, ['failed', 'cancelled', 'canceled', 'reversed', 'abandoned'], true)
            || ($status !== '' && str_contains($status, 'fail'))
            || ($notificationStatus !== '' && (str_contains($notificationStatus, 'fail') || str_contains($notificationStatus, 'cancel')));

        if ($isFailed) {
            if (! in_array($tx->status, ['approved', 'rejected'], true)) {
                $tx->status = $status !== '' ? $status : 'failed';
                $tx->rejected_at = now();
                $tx->save();
            }

            return response()->json(['ok' => true]);
        }

        $isSuccess = in_array($status, ['success', 'successful', 'completed', 'paid', 'payment_successful'], true)
            || ($status !== '' && str_contains($status, 'success'))
            || ($notificationStatus !== '' && str_contains($notificationStatus, 'success'));
        if (! $isSuccess) {
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

            $ngnPerUsd = (float) (Setting::where('key', 'ngn_per_usd')->value('value') ?? 1500);
            $amountNgn = (float) ($data['amount'] ?? $data['amountNgn'] ?? $data['ngnAmount'] ?? $data['amount_paid'] ?? 0);
            if ($amountNgn === 0) {
                $amountKobo = (float) ($data['amountKobo'] ?? $data['koboAmount'] ?? $data['kobo'] ?? 0);
                $amountNgn = $amountKobo / 100;
            }
            $amountUsd = $ngnPerUsd > 0 ? ($amountNgn / $ngnPerUsd) : 0;

            if ($amountUsd > 0) {
                $lockedTx->amount = round($amountNgn, 2);
                $lockedTx->amount_usd = round($amountUsd, 2);
                $lockedTx->currency = 'NGN';
                $lockedTx->rate_ngn_per_usd = $ngnPerUsd;
                $lockedTx->save();
            }

            $user = $lockedTx->user()->lockForUpdate()->first();
            $wallet->credit($user, (float) $lockedTx->amount_usd, 'funding_credit', (string) $lockedTx->reference, [
                'funding_transaction_id' => $lockedTx->id,
                'payment_method' => (string) $lockedTx->payment_method,
                'provider' => 'paymentpoint',
                'provider_payment_id' => (string) ($lockedTx->provider_payment_id ?? ''),
            ]);

            $lockedTx->status = 'approved';
            $lockedTx->approved_at = now();
            $lockedTx->save();

            DB::commit();

            $user->notify(new SimpleDatabaseNotification(
                'Wallet funded',
                'Your PaymentPoint payment was confirmed and your wallet was credited.',
                '/wallet'
            ));

            try {
                Mail::to($user->email)->send(new FundingSuccessEmail($user, $lockedTx, $lockedTx->amount_usd));
            } catch (\Exception $e) {
                Log::error('Funding Success Email failed to send: '.$e->getMessage());
            }

            $referrals->awardFirstFundingBonus($lockedTx->fresh(['user']));

            Log::info('PaymentPoint Webhook: transaction approved', ['transaction_id' => $lockedTx->id]);
            return response()->json(['ok' => true]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('PaymentPoint Webhook: server error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'server_error'], 500);
        }
    }
}
