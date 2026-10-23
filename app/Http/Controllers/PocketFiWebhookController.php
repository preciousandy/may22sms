<?php

namespace App\Http\Controllers;

use App\Mail\FundingSuccessEmail;
use App\Models\FundingTransaction;
use App\Models\Setting;
use App\Notifications\SimpleDatabaseNotification;
use App\Services\PocketFiService;
use App\Services\ReferralService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PocketFiWebhookController extends Controller
{
    public function handle(Request $request, PocketFiService $pocketfi, WalletService $wallet, ReferralService $referrals)
    {
        $raw = $request->getContent();

        $payload = $request->json()->all();
        if (! is_array($payload) || $payload === []) {
            $payload = $request->all();
        }

        $order = is_array($payload['order'] ?? null) ? $payload['order'] : [];
        $transaction = is_array($payload['transaction'] ?? null) ? $payload['transaction'] : [];
        $accountNumber = (string) ($payload['account_number'] ?? $payload['accountNumber'] ?? '');
        $providerPaymentId = (string) ($transaction['reference'] ?? $payload['transaction_reference'] ?? $payload['payment_id'] ?? $payload['id'] ?? '');
        $amountStr = (string) ($order['amount'] ?? $payload['amount'] ?? $order['totalPaymentAmount'] ?? '');
        $amountPaidStr = (string) ($order['amount_paid'] ?? $order['paid_amount'] ?? $amountStr);

        Log::info('PocketFi Webhook received (raw)', [
            'raw' => $raw,
            'headers' => $request->headers->all(),
            'parsed' => [
                'provider_payment_id' => $providerPaymentId,
                'account_number' => $accountNumber,
                'order_amount' => $amountStr,
                'order_amount_paid' => $amountPaidStr,
            ],
        ]);

        $configuredHeader = (string) config('services.pocketfi.signature_header', '');
        $sig = null;
        $headerNames = array_values(array_unique(array_filter([
            $configuredHeader,
            'x-pocketfi-signature',
            'pocketfi-signature',
            'x-signature',
        ])));
        foreach ($headerNames as $h) {
            if ($h === '') {
                continue;
            }
            $val = $request->header($h);
            if ($val !== null && $val !== '') {
                $sig = (string) $val;
                break;
            }
        }

        $signatureValid = $pocketfi->verifyWebhookSignature($raw, $sig, $payload);
        Log::info('PocketFi Webhook signature check', [
            'sig_header_used' => $sig ? 'present (len:'.strlen($sig).')' : 'absent',
            'signature_valid' => $signatureValid,
            'webhook_secret_configured' => config('services.pocketfi.webhook_secret') !== '' && config('services.pocketfi.webhook_secret') !== null,
        ]);

        $tx = null;
        if ($providerPaymentId !== '') {
            $tx = FundingTransaction::where(function ($q) use ($providerPaymentId) {
                $q->where('provider_payment_id', $providerPaymentId)
                    ->orWhere('reference', $providerPaymentId);
            })
            ->where(function ($q) {
                $q->where('provider', 'pocketfi')
                    ->orWhere('payment_method', 'pocketfi');
            })
            ->first();
        }
        if (! $tx && $accountNumber !== '') {
            $tx = FundingTransaction::where('provider_payment_id', $accountNumber)
                ->where(function ($q) {
                    $q->where('provider', 'pocketfi')
                        ->orWhere('payment_method', 'pocketfi');
                })
                ->first();
        }

        if (! $tx && $signatureValid === false) {
            Log::warning('PocketFi Webhook: invalid signature and no tx matched', [
                'provider_payment_id' => $providerPaymentId,
                'account_number' => $accountNumber,
            ]);
            return response()->json(['error' => 'invalid_signature'], 401);
        }

        $statusFromConfirm = '';
        $confirmOk = false;
        $confirmData = [];
        if (! $signatureValid) {
            if ($providerPaymentId !== '' && str_starts_with($providerPaymentId, 'PFI|')) {
                $confirm = $pocketfi->confirmPayment($providerPaymentId);
                $confirmOk = (bool) ($confirm['ok'] ?? false);
                $confirmData = is_array($confirm['data'] ?? null) ? $confirm['data'] : [];
                $statusFromConfirm = strtolower((string) ($confirmData['status'] ?? ''));
                Log::info('PocketFi Webhook: signature invalid; ran confirmPayment fallback', [
                    'provider_payment_id' => $providerPaymentId,
                    'confirm_ok' => $confirmOk,
                    'confirm_status' => $statusFromConfirm,
                    'confirm_data_keys' => array_keys($confirmData),
                ]);
                if (! $tx && isset($confirmData['reference']) && is_string($confirmData['reference']) && $confirmData['reference'] !== '') {
                    $tx = FundingTransaction::where('reference', $confirmData['reference'])
                        ->where(function ($q) {
                            $q->where('provider', 'pocketfi')
                                ->orWhere('payment_method', 'pocketfi');
                        })
                        ->first();
                }
            }
        }

        if (! $tx) {
            Log::warning('PocketFi Webhook: no matching transaction found', [
                'provider_payment_id' => $providerPaymentId,
                'account_number' => $accountNumber,
            ]);
            return response()->json(['ok' => true]);
        }

        if ($providerPaymentId !== '' && (! $tx->provider_payment_id || $tx->provider_payment_id === '' || str_starts_with($providerPaymentId, 'PFI|'))) {
            $tx->provider_payment_id = $providerPaymentId;
            $tx->provider = 'pocketfi';
            $tx->save();
        }

        $reportedStatus = strtolower((string) ($order['status'] ?? $transaction['status'] ?? $payload['status'] ?? ''));
        $event = strtolower((string) ($payload['event'] ?? ''));
        if ($reportedStatus === '' && $confirmOk && $statusFromConfirm !== '') {
            $reportedStatus = $statusFromConfirm;
        }

        $isFailed = in_array($reportedStatus, ['failed', 'cancelled', 'canceled', 'reversed', 'abandoned', 'expired', 'rejected'], true)
            || ($reportedStatus !== '' && str_contains($reportedStatus, 'fail'))
            || ($event !== '' && (str_contains($event, 'fail') || str_contains($event, 'cancel')));

        if ($isFailed) {
            if (! in_array($tx->status, ['approved', 'rejected'], true)) {
                $tx->status = $reportedStatus !== '' ? $reportedStatus : 'failed';
                $tx->rejected_at = now();
                $tx->save();
            }

            return response()->json(['ok' => true]);
        }

        $isSuccess = in_array($reportedStatus, ['success', 'successful', 'completed', 'paid', 'payment_successful', 'confirmed', 'settled'], true)
            || ($reportedStatus !== '' && str_contains($reportedStatus, 'success'))
            || ($reportedStatus !== '' && str_contains($reportedStatus, 'paid'))
            || ($event !== '' && str_contains($event, 'success'))
            || ($confirmOk && in_array($statusFromConfirm, ['success', 'successful', 'completed', 'paid', 'confirmed', 'settled'], true));

        if (! $isSuccess) {
            if ($reportedStatus !== '' && ! in_array($tx->status, ['approved', 'rejected'], true)) {
                $tx->status = $reportedStatus;
                $tx->save();
            } elseif ($confirmOk && $statusFromConfirm !== '' && ! in_array($tx->status, ['approved', 'rejected'], true)) {
                $tx->status = $statusFromConfirm;
                $tx->save();
            }

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
            $amountNgn = (float) ($confirmData['amount'] ?? $confirmData['amount_paid'] ?? $confirmData['totalPaymentAmount'] ?? null);
            if ($amountNgn === 0.0 || $amountNgn === null) {
                $amountNgn = (float) ($amountPaidStr !== '' ? $amountPaidStr : ($amountStr !== '' ? $amountStr : 0));
            }
            if ($amountNgn === 0.0) {
                $amountKobo = (float) ($order['amountKobo'] ?? $order['koboAmount'] ?? $order['kobo'] ?? 0);
                $amountNgn = $amountKobo > 0 ? ($amountKobo / 100) : 0;
            }
            if ($amountNgn === 0.0 && isset($confirmData['checkout_info']) && is_string($confirmData['checkout_info'])) {
                $decodedCheckout = json_decode($confirmData['checkout_info'], true);
                if (is_array($decodedCheckout)) {
                    $co = $decodedCheckout['checkout'] ?? [];
                    $coTotal = 0;
                    foreach (['account_1', 'account_2', 'account_3'] as $accKey) {
                        $acc = is_array($co[$accKey] ?? null) ? $co[$accKey] : [];
                        $candidate = (float) ($acc['totalPaymentAmount'] ?? 0);
                        if ($candidate > 0) {
                            $coTotal = $candidate;
                            break;
                        }
                    }
                    $amountNgn = $coTotal > 0 ? $coTotal : $amountNgn;
                }
            }
            if ($amountNgn === 0.0) {
                $amountNgn = (float) $lockedTx->amount;
            }
            if ($amountNgn > 0) {
                $calc = FundingTransaction::calculateNetAndFee($amountNgn, 'pocketfi', $ngnPerUsd);
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
                'provider' => 'pocketfi',
                'provider_payment_id' => (string) ($lockedTx->provider_payment_id ?? ''),
                'gross_ngn' => $amountNgn,
                'charge_percent' => FundingTransaction::getChargePercent('pocketfi'),
            ]);

            $lockedTx->status = 'approved';
            $lockedTx->approved_at = now();
            $lockedTx->save();

            DB::commit();

            $user->notify(new SimpleDatabaseNotification(
                'Wallet funded',
                'Your PocketFi payment was confirmed and your wallet was credited.',
                '/wallet'
            ));

            try {
                Mail::to($user->email)->send(new FundingSuccessEmail($user, $lockedTx, $lockedTx->amount_usd));
            } catch (\Exception $e) {
                Log::error('Funding Success Email failed to send: '.$e->getMessage());
            }

            $referrals->awardFirstFundingBonus($lockedTx->fresh(['user']));

            Log::info('PocketFi Webhook: transaction approved', [
                'transaction_id' => $lockedTx->id,
                'reference' => $lockedTx->reference,
                'amount_usd' => $lockedTx->amount_usd,
                'via_signature' => $signatureValid ? 'yes' : 'confirmPayment-fallback',
            ]);
            return response()->json(['ok' => true]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('PocketFi Webhook: server error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'server_error'], 500);
        }
    }
}
