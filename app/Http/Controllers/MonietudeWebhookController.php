<?php

namespace App\Http\Controllers;

use App\Mail\FundingSuccessEmail;
use App\Models\FundingTransaction;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\SimpleDatabaseNotification;
use App\Services\MonietudeService;
use App\Services\ReferralService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class MonietudeWebhookController extends Controller
{
    public function handle(
        Request $request,
        MonietudeService $monietude,
        WalletService $wallet,
        ReferralService $referrals
    ) {
        $raw = $request->getContent();
        Log::info('Monietude Webhook received', [
            'raw' => $raw,
            'headers' => $request->headers->all(),
        ]);

        $payload = $request->json()->all();
        if (! is_array($payload) || empty($payload)) {
            $payload = $request->all();
        }

        $data = is_array($payload['data'] ?? null) ? $payload['data'] : $payload;

        $event = strtolower((string) ($payload['event'] ?? $data['event'] ?? ''));
        $type = strtolower((string) ($payload['type'] ?? $data['type'] ?? ''));
        $status = strtolower((string) ($data['status'] ?? $payload['status'] ?? ''));
        $reference = (string) ($data['reference'] ?? $payload['reference'] ?? '');
        $providerPaymentId = $data['provider_reference'] ?? $data['id'] ?? $reference;

        $virtualAccount = is_array($data['virtual_account'] ?? null) 
            ? $data['virtual_account'] 
            : (is_array($payload['virtual_account'] ?? null) ? $payload['virtual_account'] : []);

        $accountNumber = $virtualAccount['account_number'] ?? $data['account_number'] ?? null;
        $customerPhone = $virtualAccount['customer_phone'] ?? $virtualAccount['phone'] ?? $data['customer_phone'] ?? null;
        $customerEmail = $virtualAccount['customer_email'] ?? $virtualAccount['email'] ?? $data['customer_email'] ?? ($data['customer']['email'] ?? null);

        // Optional payment verification with Monietude
        if (! empty($reference)) {
            $verifyRes = $monietude->verifyPayment($reference);
            if ($verifyRes['ok'] ?? false) {
                $verifyData = $verifyRes['data']['data'] ?? $verifyRes['data'] ?? [];
                if (isset($verifyData['status'])) {
                    $status = strtolower((string) $verifyData['status']);
                }
            }
        }

        // Parse amount (Monietude virtual account funding amount is represented in kobo)
        $rawAmount = (float) ($data['amount'] ?? $payload['amount'] ?? 0);
        $amountNgn = $rawAmount;
        if ($rawAmount >= 10000 && (int) $rawAmount == $rawAmount && ! str_contains((string) ($data['amount'] ?? ''), '.')) {
            // E.g. 500000 kobo = 5,000 NGN; 10000 kobo = 100 NGN
            $amountNgn = $rawAmount / 100;
        } elseif (isset($data['amount_kobo']) || isset($data['amountKobo'])) {
            $amountNgn = (float) ($data['amount_kobo'] ?? $data['amountKobo']) / 100;
        }

        // Locate transaction by reference first
        $tx = null;
        if (! empty($reference)) {
            $tx = FundingTransaction::where('reference', $reference)->first();
        }

        // If not found, match user by virtual account number, phone, or email
        if (! $tx) {
            $user = null;
            if ($accountNumber) {
                $user = User::where('monietude_account_number', (string) $accountNumber)->first();
            }

            if (! $user && $customerPhone) {
                $cleanPhone = $monietude->formatPhone((string) $customerPhone);
                $user = User::where('phone_number', (string) $customerPhone)
                    ->orWhere('phone_number', $cleanPhone)
                    ->first();
            }

            if (! $user && $customerEmail) {
                $user = User::where('email', (string) $customerEmail)->first();
            }

            if ($user) {
                $ngnPerUsd = (float) (Setting::where('key', 'ngn_per_usd')->value('value') ?? 1500);
                $calc = FundingTransaction::calculateNetAndFee($amountNgn, 'monietude', $ngnPerUsd);
                $ref = $reference ?: ('MN-' . strtoupper(Str::random(10)));

                $tx = FundingTransaction::create([
                    'user_id' => $user->id,
                    'payment_method' => 'monietude',
                    'provider' => 'monietude',
                    'provider_payment_id' => (string) ($providerPaymentId ?: $accountNumber),
                    'currency' => 'NGN',
                    'amount' => round($amountNgn, 2),
                    'amount_usd' => $calc['net_usd'],
                    'rate_ngn_per_usd' => $ngnPerUsd,
                    'reference' => $ref,
                    'status' => 'pending',
                ]);
            }
        }

        if (! $tx) {
            Log::warning('Monietude Webhook: No matching transaction or user found', [
                'reference' => $reference,
                'account_number' => $accountNumber,
                'phone' => $customerPhone,
                'email' => $customerEmail,
            ]);

            return response()->json(['ok' => true]);
        }

        if ($providerPaymentId && ! $tx->provider_payment_id) {
            $tx->provider_payment_id = (string) $providerPaymentId;
            $tx->save();
        }

        // Check for failure
        $isFailed = in_array($status, ['failed', 'cancelled', 'canceled', 'reversed', 'abandoned'], true)
            || ($status !== '' && str_contains($status, 'fail'))
            || ($event !== '' && (str_contains($event, 'fail') || str_contains($event, 'cancel')));

        if ($isFailed) {
            if (! in_array($tx->status, ['approved', 'rejected'], true)) {
                $tx->status = $status !== '' ? $status : 'failed';
                $tx->rejected_at = now();
                $tx->save();
            }

            return response()->json(['ok' => true]);
        }

        // Check for success
        $isSuccess = in_array($status, ['success', 'successful', 'completed', 'paid'], true)
            || ($status !== '' && str_contains($status, 'success'))
            || ($event !== '' && str_contains($event, 'success'))
            || ($type !== '' && str_contains($type, 'funding'));

        if (! $isSuccess) {
            $tx->status = $status !== '' ? $status : $tx->status;
            $tx->save();

            return response()->json(['ok' => true]);
        }

        // Atomically process and credit user
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
            if ($amountNgn > 0) {
                $calc = FundingTransaction::calculateNetAndFee($amountNgn, 'monietude', $ngnPerUsd);
                $lockedTx->amount = round($amountNgn, 2);
                $lockedTx->amount_usd = $calc['net_usd'];
                $lockedTx->currency = 'NGN';
                $lockedTx->rate_ngn_per_usd = $ngnPerUsd;
                $lockedTx->save();
            }

            $user = $lockedTx->user()->lockForUpdate()->first();
            $wallet->credit($user, (float) $lockedTx->amount_usd, 'funding_credit', (string) $lockedTx->reference, [
                'funding_transaction_id' => $lockedTx->id,
                'payment_method' => (string) $lockedTx->payment_method,
                'provider' => 'monietude',
                'provider_payment_id' => (string) ($lockedTx->provider_payment_id ?? ''),
                'gross_ngn' => $amountNgn,
                'charge_percent' => FundingTransaction::getChargePercent('monietude'),
            ]);

            $lockedTx->status = 'approved';
            $lockedTx->approved_at = now();
            $lockedTx->save();

            DB::commit();

            $user->notify(new SimpleDatabaseNotification(
                'Wallet funded',
                'Your PalmPay (Monietude) transfer of ₦' . number_format($amountNgn, 2) . ' was received and credited to your wallet.',
                '/wallet'
            ));

            try {
                Mail::to($user->email)->send(new FundingSuccessEmail($user, $lockedTx, $lockedTx->amount_usd));
            } catch (\Exception $e) {
                Log::error('Monietude funding email failed: ' . $e->getMessage());
            }

            $referrals->awardFirstFundingBonus($lockedTx->fresh(['user']));

            return response()->json(['ok' => true]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Monietude Webhook transaction credit failed: ' . $e->getMessage(), [
                'tx_id' => $tx->id,
                'exception' => $e,
            ]);

            return response()->json(['error' => 'server_error'], 500);
        }
    }
}
