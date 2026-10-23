<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Mail\FundingSuccessEmail;
use App\Models\FundingTransaction;
use App\Models\Setting;
use App\Models\SystemTransaction;
use App\Notifications\SimpleDatabaseNotification;
use App\Services\MonietudeService;
use App\Services\NowPaymentsService;
use App\Services\PaymentPointService;
use App\Services\PocketFiService;
use App\Services\ReferralService;
use App\Services\StrollwalletService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class FundingController extends Controller
{
    private function enabledFundingMethods(): array
    {
        $paymentpointEnabled = (int) (Setting::where('key', 'payment_method_paymentpoint_enabled')->value('value') ?? 1) === 1;
        $strollwalletEnabled = (int) (Setting::where('key', 'payment_method_strollwallet_enabled')->value('value') ?? 1) === 1;
        $pocketfiEnabled = (int) (Setting::where('key', 'payment_method_pocketfi_enabled')->value('value') ?? 1) === 1;
        $monietudeEnabled = (int) (Setting::where('key', 'payment_method_monietude_enabled')->value('value') ?? 1) === 1;
        $cryptoEnabled = (int) (Setting::where('key', 'payment_method_crypto_enabled')->value('value') ?? 1) === 1;

        $methods = [];
        if ($monietudeEnabled) {
            $methods[] = 'monietude';
        }
        if ($paymentpointEnabled) {
            $methods[] = 'paymentpoint';
        }
        if ($pocketfiEnabled) {
            $methods[] = 'pocketfi';
        }
        if ($strollwalletEnabled) {
            $methods[] = 'strollwallet';
        }
        if ($cryptoEnabled) {
            $methods[] = 'crypto';
        }

        return $methods;
    }

    public function index()
    {
        $userId = auth()->id();

        $pendingPocketFi = FundingTransaction::where('user_id', $userId)
            ->whereIn('status', ['pending', 'waiting_payment', 'initiated'])
            ->where('payment_method', 'pocketfi')
            ->whereNotNull('provider_payment_id')
            ->orderByDesc('created_at')
            ->limit(3)
            ->get();

        $pocketfi = app(PocketFiService::class);
        $wallet = app(WalletService::class);
        $referrals = app(ReferralService::class);
        $checkedAny = false;

        foreach ($pendingPocketFi as $pendingTx) {
            try {
                $confirmResult = $pocketfi->confirmPayment((string) $pendingTx->provider_payment_id);
                $data = is_array($confirmResult['data'] ?? null) ? $confirmResult['data'] : [];
                $status = strtolower((string) ($data['status'] ?? ''));
                $isSuccess = in_array($status, ['success', 'successful', 'completed', 'paid', 'confirmed'], true)
                    || ($status !== '' && str_contains($status, 'success'));
                Log::info('PocketFi auto-check on funding index', ['tx_id' => $pendingTx->id, 'payment_id' => $pendingTx->provider_payment_id, 'status' => $status, 'ok' => $confirmResult['ok'] ?? false]);

                if (($confirmResult['ok'] ?? false) && $isSuccess) {
                    DB::beginTransaction();
                    try {
                        $lockedTx = FundingTransaction::lockForUpdate()->find($pendingTx->id);
                        if ($lockedTx && $lockedTx->status !== 'approved') {
                            $ngnPerUsd = (float) (Setting::where('key', 'ngn_per_usd')->value('value') ?? 1500);
                            $amount = (float) ($data['amount'] ?? $data['amount_paid'] ?? $data['totalPaymentAmount'] ?? $lockedTx->amount);
                            if ($amount > 0) {
                                $calc = FundingTransaction::calculateNetAndFee($amount, (string) ($lockedTx->payment_method ?: 'pocketfi'), $ngnPerUsd);
                                $lockedTx->amount = round($amount, 2);
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
                                'gross_ngn' => $amount,
                                'charge_percent' => FundingTransaction::getChargePercent((string) ($lockedTx->payment_method ?: 'pocketfi')),
                            ]);
                            $lockedTx->status = 'approved';
                            $lockedTx->approved_at = now();
                            $lockedTx->save();
                            DB::commit();
                            $checkedAny = true;
                            try {
                                $user->notify(new SimpleDatabaseNotification(
                                    'Wallet funded',
                                    'Your PocketFi payment was confirmed and your wallet was credited.',
                                    '/wallet'
                                ));
                                Mail::to($user->email)->send(new FundingSuccessEmail($user, $lockedTx, $lockedTx->amount_usd));
                            } catch (\Exception $e) {
                                Log::error('Funding notify/email failed (auto-check): '.$e->getMessage());
                            }
                            $referrals->awardFirstFundingBonus($lockedTx->fresh(['user']));
                        } else {
                            DB::commit();
                        }
                    } catch (\Throwable $e) {
                        DB::rollBack();
                        Log::warning('PocketFi auto-check credit failed', ['tx_id' => $pendingTx->id, 'error' => $e->getMessage()]);
                    }
                } elseif (($confirmResult['ok'] ?? false) && $status !== '' && ! in_array($pendingTx->status, ['approved', 'rejected'], true)) {
                    $pendingTx->status = $status;
                    $pendingTx->save();
                }
            } catch (\Throwable $e) {
                Log::warning('PocketFi auto-check error', ['tx_id' => $pendingTx->id, 'error' => $e->getMessage()]);
            }
        }

        $funding = FundingTransaction::where('user_id', $userId)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $ngnPerUsd = (float) (Setting::where('key', 'ngn_per_usd')->value('value') ?? 1500);
        $currency = session('currency', (string) (Setting::where('key', 'default_currency')->value('value') ?? 'USD'));

        $enabledFundingMethods = $this->enabledFundingMethods();
        $defaultFundingMethod = $enabledFundingMethods[0] ?? 'monietude';

        $user = auth()->user();
        $userVirtualAccounts = [];
        if ($user->paymentpoint_account_number) {
            $userVirtualAccounts['paymentpoint'] = [
                'account_number' => $user->paymentpoint_account_number,
                'bank_name' => $user->paymentpoint_bank_name,
                'account_name' => $user->paymentpoint_account_name,
            ];
        }
        if ($user->strollwallet_account_number) {
            $userVirtualAccounts['strollwallet'] = [
                'account_number' => $user->strollwallet_account_number,
                'bank_name' => $user->strollwallet_bank_name,
                'account_name' => $user->strollwallet_account_name,
            ];
        }

        $monietudeService = app(MonietudeService::class);
        if (in_array('monietude', $enabledFundingMethods, true) && ! $user->monietude_account_number && $monietudeService->isConfigured()) {
            try {
                $phone = $monietudeService->formatPhone($user->phone_number, $user->id);
                $provider = config('services.monietude.provider', 'palmpay');
                $result = $monietudeService->createVirtualAccount([
                    'name' => $user->name ?: ('User ' . $user->id),
                    'phone' => $phone,
                    'email' => $user->email,
                    'provider' => $provider,
                ]);

                if ($result['ok'] ?? false) {
                    $vaData = $result['data']['data'] ?? $result['data'] ?? [];
                    if (! empty($vaData['account_number'])) {
                        $user->monietude_account_number = $vaData['account_number'];
                        $user->monietude_bank_name = $vaData['bank_name'] ?? 'PalmPay';
                        $user->monietude_account_name = $vaData['account_name'] ?? $user->name;
                        $user->monietude_provider = $vaData['provider'] ?? $provider;
                        $user->save();
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Auto-generate Monietude virtual account error: ' . $e->getMessage());
            }
        }

        if ($user->monietude_account_number) {
            $userVirtualAccounts['monietude'] = [
                'account_number' => $user->monietude_account_number,
                'bank_name' => $user->monietude_bank_name ?: 'PalmPay',
                'account_name' => $user->monietude_account_name,
                'provider' => $user->monietude_provider ?: 'palmpay',
            ];
        }

        if ($checkedAny) {
            return redirect('/funding')->with('success', 'Pending PocketFi payments were checked. If confirmed, your wallet has been credited.');
        }

        return view('user.funding', compact('funding', 'ngnPerUsd', 'currency', 'enabledFundingMethods', 'defaultFundingMethod', 'userVirtualAccounts'));
    }

    public function store(Request $request)
    {
        $userId = auth()->id();
        
        $method = (string) $request->payment_method;
            
        $rules = [
            'payment_method' => 'required|string',
            'amount' => 'nullable|numeric|min:1',
            'pay_currency' => 'nullable|string|max:20',
            'paymentpoint_nin' => 'nullable|string|min:11|max:11',
        ];

        $request->validate($rules);

        $allowedMethods = $this->enabledFundingMethods();
        if (empty($allowedMethods)) {
            return back()->with('error', 'Funding is temporarily unavailable. Please try again later.')->withInput();
        }

        $ngnPerUsd = (float) (Setting::where('key', 'ngn_per_usd')->value('value') ?? 1500);
        if ($ngnPerUsd <= 0) {
            $ngnPerUsd = 1500;
        }
        $currency = session('currency', (string) (Setting::where('key', 'default_currency')->value('value') ?? 'USD'));
        $method = (string) $request->payment_method;
        if (! in_array($method, $allowedMethods, true)) {
            return back()->with('error', 'This payment method is currently disabled.')->withInput();
        }

        $reference = 'FUND-'.strtoupper(Str::random(10));

        $tx = null;
        $amount = 0;
        $amountUsdExact = 0;

        if ($method === 'crypto') {
            if (is_null($request->amount)) {
                return back()->with('error', 'Amount is required for crypto funding.')->withInput();
            }
            $amount = (float) $request->amount;

            if ($currency === 'NGN') {
                $amountUsd = $ngnPerUsd > 0 ? ($amount / $ngnPerUsd) : 0;
            } else {
                $amountUsd = $amount;
            }
            $amountUsdExact = (float) $amountUsd;

            $minUsd = 1.0;
            $minNgn = 1.0 * $ngnPerUsd;

            $belowMinimum = false;
            if ($currency === 'NGN') {
                $belowMinimum = $amount < $minNgn;
            } else {
                $belowMinimum = $amountUsd < $minUsd;
            }

            if ($belowMinimum) {
                if ($currency === 'NGN') {
                    return back()
                        ->with('error', 'Minimum deposit is ₦'.number_format($minNgn, 2).' (≈ $'.number_format($minUsd, 2).')')
                        ->withInput();
                }

                return back()
                    ->with('error', 'Minimum deposit is $'.number_format($minUsd, 2).' (≈ ₦'.number_format($minNgn, 2).')')
                    ->withInput();
            }

            $calc = FundingTransaction::calculateNetAndFee($currency === 'NGN' ? $amount : $amount * $ngnPerUsd, (string) $request->payment_method, $ngnPerUsd);

            $tx = FundingTransaction::create([
                'user_id' => auth()->id(),
                'promo_code_id' => null,
                'payment_method' => $request->payment_method,
                'currency' => $currency,
                'amount' => round($amount, 2),
                'amount_usd' => $calc['net_usd'],
                'rate_ngn_per_usd' => $currency === 'NGN' ? $ngnPerUsd : null,
                'reference' => $reference,
                'status' => 'pending',
            ]);

            SystemTransaction::create([
                'actor_user_id' => auth()->id(),
                'user_id' => auth()->id(),
                'type' => 'funding',
                'action' => 'create',
                'status' => (string) $tx->status,
                'subject_type' => FundingTransaction::class,
                'subject_id' => (string) $tx->id,
                'amount_usd' => (float) $tx->amount_usd,
                'reference' => (string) $tx->reference,
                'meta' => [
                    'payment_method' => (string) $tx->payment_method,
                    'currency' => (string) $tx->currency,
                    'amount' => (float) $tx->amount,
                ],
            ]);

            $payCurrency = strtolower((string) ($request->pay_currency ?: 'usdttrc20'));

            $now = app(NowPaymentsService::class);
            if (! $now->isConfigured()) {
                return back()->with('error', 'Crypto payments are not configured yet. Add NOWPAYMENTS_API_KEY and NOWPAYMENTS_IPN_SECRET.')->withInput();
            }

            $tx->provider = 'nowpayments';
            $tx->pay_currency = $payCurrency;
            $tx->status = 'initiated';
            $tx->save();

            $appUrl = rtrim((string) (request()->root() ?: 'https://may22sim.com'), '/');
            if (str_contains($appUrl, '127.0.0.1') || str_contains($appUrl, 'localhost')) {
                $appUrl = 'https://may22sim.com';
            }
            $isLocalUrl = str_contains($appUrl, '127.0.0.1') || str_contains($appUrl, 'localhost');

            $invoicePayload = [
                'price_amount' => (string) number_format((float) $tx->amount_usd, 2, '.', ''),
                'price_currency' => 'usd',
                'pay_currency' => $payCurrency,
                'order_id' => $tx->reference,
                'order_description' => 'Wallet funding',
            ];

            if (! $isLocalUrl && $appUrl !== '') {
                $invoicePayload['ipn_callback_url'] = $appUrl.'/api/nowpayments/ipn';
                $invoicePayload['success_url'] = $appUrl.'/funding';
                $invoicePayload['cancel_url'] = $appUrl.'/funding';
            }

            $result = $now->createInvoice($invoicePayload);
            $data = is_array($result['data'] ?? null) ? $result['data'] : [];

            $invoiceId = $data['id'] ?? $data['invoice_id'] ?? null;
            $invoiceUrl = $data['invoice_url'] ?? $data['url'] ?? null;

            if (! ($result['ok'] ?? false) || ! $invoiceUrl) {
                $tx->status = 'failed';
                $tx->save();
                $message = $data['message'] ?? $data['error'] ?? $data['errors'] ?? null;
                $status = (int) ($result['status'] ?? 0);
                Log::warning('NOWPayments invoice create failed', [
                    'status' => $status,
                    'reference' => $tx->reference,
                    'response' => $data,
                ]);

                $suffix = $message ? (' ('.(is_string($message) ? $message : json_encode($message)).')') : '';
                $hint = $isLocalUrl ? ' If you are on localhost, set APP_URL to your public domain for webhooks.' : '';

                return back()->with('error', 'Failed to create crypto invoice. '.($status ? ('HTTP '.$status.'.') : '').$suffix.$hint)->withInput();
            }

            $tx->provider_invoice_id = $invoiceId ? (string) $invoiceId : null;
            $tx->payment_url = (string) $invoiceUrl;
            $tx->status = 'waiting_payment';
            $tx->save();

            return redirect()->away($tx->payment_url);
        }

        if ($method === 'pocketfi') {
            if (is_null($request->amount)) {
                return back()->with('error', 'Amount is required for PocketFi funding.')->withInput();
            }
            $amount = (float) $request->amount;

            if ($currency === 'NGN') {
                $amountUsd = $ngnPerUsd > 0 ? ($amount / $ngnPerUsd) : 0;
            } else {
                $amountUsd = $amount;
            }
            $amountUsdExact = (float) $amountUsd;

            $minUsd = 0.06;
            $minNgn = 0.06 * $ngnPerUsd;

            $belowMinimum = false;
            if ($currency === 'NGN') {
                $belowMinimum = $amount < $minNgn;
            } else {
                $belowMinimum = $amountUsd < $minUsd;
            }

            if ($belowMinimum) {
                if ($currency === 'NGN') {
                    return back()
                        ->with('error', 'Minimum deposit is ₦'.number_format($minNgn, 2).' (≈ $'.number_format($minUsd, 2).')')
                        ->withInput();
                }

                return back()
                    ->with('error', 'Minimum deposit is $'.number_format($minUsd, 2).' (≈ ₦'.number_format($minNgn, 2).')')
                    ->withInput();
            }

            $service = app(PocketFiService::class);
            if (! $service->isConfigured()) {
                return back()->with('error', 'PocketFi is not configured yet.')->withInput();
            }

            $calc = FundingTransaction::calculateNetAndFee($currency === 'NGN' ? $amount : $amount * $ngnPerUsd, $method, $ngnPerUsd);

            $tx = FundingTransaction::create([
                'user_id' => auth()->id(),
                'promo_code_id' => null,
                'payment_method' => $method,
                'currency' => $currency,
                'amount' => round($amount, 2),
                'amount_usd' => $calc['net_usd'],
                'rate_ngn_per_usd' => $currency === 'NGN' ? $ngnPerUsd : null,
                'reference' => $reference,
                'status' => 'pending',
            ]);

            SystemTransaction::create([
                'actor_user_id' => auth()->id(),
                'user_id' => auth()->id(),
                'type' => 'funding',
                'action' => 'create',
                'status' => (string) $tx->status,
                'subject_type' => FundingTransaction::class,
                'subject_id' => (string) $tx->id,
                'amount_usd' => (float) $tx->amount_usd,
                'reference' => (string) $tx->reference,
                'meta' => [
                    'payment_method' => (string) $tx->payment_method,
                    'currency' => (string) $tx->currency,
                    'amount' => (float) $tx->amount,
                ],
            ]);

            $appUrl = rtrim((string) (request()->root() ?: 'https://may22sim.com'), '/');
            if (str_contains($appUrl, '127.0.0.1') || str_contains($appUrl, 'localhost')) {
                $appUrl = 'https://may22sim.com';
            }
            $user = auth()->user();

            $fullNameParts = explode(' ', $user->name, 2);
            $firstName = trim($fullNameParts[0] ?? 'User');
            $lastName = trim($fullNameParts[1] ?? 'User');

            $redirectUrl = trim($appUrl.'/funding/pocketfi/callback');

            $checkoutPayload = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => trim($user->phone ?? $user->phone_number ?? '08000000000'),
                'business_id' => config('services.pocketfi.business_id'),
                'email' => trim($user->email),
                'reference' => $reference,
                'redirect_link' => $redirectUrl,
                'redirect_url' => $redirectUrl,
                'webhook_url' => trim($appUrl.'/api/pocketfi/webhook'),
                'callback_url' => trim($appUrl.'/api/pocketfi/webhook'),
                'amount' => (string) ($currency === 'NGN' ? $amount : $amount * $ngnPerUsd),
            ];

            Log::info('PocketFi checkout request payload', ['payload' => $checkoutPayload]);

            $result = $service->createCheckout($checkoutPayload);
            $data = is_array($result['data'] ?? null) ? $result['data'] : [];

            Log::info('PocketFi checkout response', ['result' => $result, 'data' => $data]);

            if (! ($result['ok'] ?? false)) {
                $tx->status = 'failed';
                $tx->save();
                $message = $data['message'] ?? $data['error'] ?? $data['errors'] ?? json_encode($data);
                $status = (int) ($result['status'] ?? 0);
                Log::warning('PocketFi checkout create failed', [
                    'status' => $status,
                    'reference' => $tx->reference,
                    'response' => $data,
                ]);

                $suffix = $message ? (' ('.(is_string($message) ? $message : json_encode($message)).')') : '';
                return back()->with('error', 'Failed to create PocketFi payment. '.($status ? ('HTTP '.$status.'.') : '').$suffix)->withInput();
            }

            $paymentId = $data['payment_id'] ?? null;
            $paymentUrl = $data['payment_link'] ?? null;

            if (! $paymentUrl) {
                $tx->status = 'failed';
                $tx->save();
                Log::warning('PocketFi payment link missing', ['data' => $data]);
                return back()->with('error', 'PocketFi payment link not returned. Response: '.json_encode($data))->withInput();
            }

            $tx->provider = 'pocketfi';
            $tx->provider_payment_id = $paymentId ? (string) $paymentId : null;
            $tx->payment_url = (string) $paymentUrl;
            $tx->status = 'waiting_payment';
            $tx->save();

            return redirect()->away($tx->payment_url);
        }

        if ($method === 'monietude') {
            if (is_null($request->amount)) {
                return back()->with('error', 'Amount is required.')->withInput();
            }
            $amount = (float) $request->amount;

            if ($currency === 'NGN') {
                $amountUsd = $ngnPerUsd > 0 ? ($amount / $ngnPerUsd) : 0;
            } else {
                $amountUsd = $amount;
            }

            $minUsd = 0.06;
            $minNgn = 0.06 * $ngnPerUsd;

            $belowMinimum = $currency === 'NGN' ? ($amount < $minNgn) : ($amountUsd < $minUsd);
            if ($belowMinimum) {
                if ($currency === 'NGN') {
                    return back()
                        ->with('error', 'Minimum deposit is ₦'.number_format($minNgn, 2).' (≈ $'.number_format($minUsd, 2).')')
                        ->withInput();
                }

                return back()
                    ->with('error', 'Minimum deposit is $'.number_format($minUsd, 2).' (≈ ₦'.number_format($minNgn, 2).')')
                    ->withInput();
            }

            $service = app(MonietudeService::class);
            if (! $service->isConfigured()) {
                return back()->with('error', 'Monietude is not configured yet.')->withInput();
            }

            $user = auth()->user();
            if (! $user->monietude_account_number) {
                $phone = $service->formatPhone($user->phone_number, $user->id);
                $provider = config('services.monietude.provider', 'palmpay');
                $result = $service->createVirtualAccount([
                    'name' => $user->name ?: ('User ' . $user->id),
                    'phone' => $phone,
                    'email' => $user->email,
                    'provider' => $provider,
                ]);

                if (! ($result['ok'] ?? false)) {
                    $msg = $result['data']['message'] ?? 'Failed to create Monietude virtual account.';
                    return back()->with('error', $msg)->withInput();
                }

                $vaData = $result['data']['data'] ?? $result['data'] ?? [];
                $user->monietude_account_number = $vaData['account_number'] ?? null;
                $user->monietude_bank_name = $vaData['bank_name'] ?? 'PalmPay';
                $user->monietude_account_name = $vaData['account_name'] ?? $user->name;
                $user->monietude_provider = $vaData['provider'] ?? $provider;
                $user->save();
            }

            $calc = FundingTransaction::calculateNetAndFee($currency === 'NGN' ? $amount : $amount * $ngnPerUsd, 'monietude', $ngnPerUsd);
            $reference = 'MN-' . strtoupper(Str::random(10));

            $tx = FundingTransaction::create([
                'user_id' => $user->id,
                'promo_code_id' => null,
                'payment_method' => 'monietude',
                'provider' => 'monietude',
                'provider_payment_id' => $user->monietude_account_number,
                'currency' => $currency,
                'amount' => round($amount, 2),
                'amount_usd' => $calc['net_usd'],
                'rate_ngn_per_usd' => $currency === 'NGN' ? $ngnPerUsd : null,
                'reference' => $reference,
                'status' => 'waiting_payment',
            ]);

            SystemTransaction::create([
                'actor_user_id' => $user->id,
                'user_id' => $user->id,
                'type' => 'funding',
                'action' => 'create',
                'status' => (string) $tx->status,
                'subject_type' => FundingTransaction::class,
                'subject_id' => (string) $tx->id,
                'amount_usd' => (float) $tx->amount_usd,
                'reference' => (string) $tx->reference,
                'meta' => [
                    'payment_method' => 'monietude',
                    'currency' => (string) $tx->currency,
                    'amount' => (float) $tx->amount,
                ],
            ]);

            return redirect()->route('funding')->with('virtual_account', [
                'method' => 'monietude',
                'bank_name' => $user->monietude_bank_name ?: 'PalmPay',
                'account_name' => $user->monietude_account_name,
                'account_number' => $user->monietude_account_number,
                'amount' => $amount,
                'currency' => $currency,
                'reference' => $tx->reference,
            ]);
        }

        if ($method === 'paymentpoint' || $method === 'strollwallet') {
            if (is_null($request->amount)) {
                return back()->with('error', 'Amount is required.')->withInput();
            }
            $amount = (float) $request->amount;

            if ($currency === 'NGN') {
                $amountUsd = $ngnPerUsd > 0 ? ($amount / $ngnPerUsd) : 0;
            } else {
                $amountUsd = $amount;
            }
            $amountUsdExact = (float) $amountUsd;

            $minUsd = 0.06;
            $minNgn = 0.06 * $ngnPerUsd;

            $belowMinimum = false;
            if ($currency === 'NGN') {
                $belowMinimum = $amount < $minNgn;
            } else {
                $belowMinimum = $amountUsd < $minUsd;
            }

            if ($belowMinimum) {
                if ($currency === 'NGN') {
                    return back()
                        ->with('error', 'Minimum deposit is ₦'.number_format($minNgn, 2).' (≈ $'.number_format($minUsd, 2).')')
                        ->withInput();
                }

                return back()
                    ->with('error', 'Minimum deposit is $'.number_format($minUsd, 2).' (≈ ₦'.number_format($minNgn, 2).')')
                    ->withInput();
            }

            $service = $method === 'paymentpoint' ? app(PaymentPointService::class) : app(StrollwalletService::class);
            if (! $service->isConfigured()) {
                $serviceName = $method === 'paymentpoint' ? 'PaymentPoint' : 'Strollwallet';
                return back()->with('error', $serviceName.' is not configured yet.')->withInput();
            }

            $user = auth()->user();
            $accountNumberField = $method.'_account_number';
            $accountNameField = $method.'_account_name';
            $bankNameField = $method.'_bank_name';

            if (! $user->$accountNumberField) {
                $businessId = config('services.'.$method.'.business_id', '');

                if ($method === 'paymentpoint' && $request->paymentpoint_nin) {
                    $user->paymentpoint_nin = $request->paymentpoint_nin;
                    $user->save();
                }

                $payload = [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phoneNumber' => $user->phone ?? $user->phone_number ?? '08000000000',
                    'bankCode' => ['20946', '20897'],
                    'businessId' => $businessId,
                ];

                if ($method === 'paymentpoint' && $user->paymentpoint_nin) {
                    $payload['nin'] = $user->paymentpoint_nin;
                }

                $result = $service->initializePayment($payload);
                $data = is_array($result['data'] ?? null) ? $result['data'] : [];

                if (! ($result['ok'] ?? false)) {
                    $message = $data['message'] ?? $data['error'] ?? $data['errors'] ?? null;
                    $status = (int) ($result['status'] ?? 0);
                    Log::warning(ucfirst($method).' virtual account create failed', [
                        'status' => $status,
                        'user_id' => $user->id,
                        'response' => $data,
                    ]);

                    $suffix = $message ? (' ('.(is_string($message) ? $message : json_encode($message)).')') : '';

                    return back()->with('error', 'Failed to create virtual account. '.($status ? ('HTTP '.$status.'.') : '').$suffix)->withInput();
                }

                $bankAccounts = $data['bankAccounts'] ?? $data['data']['bankAccounts'] ?? [];
                $firstAccount = is_array($bankAccounts) && count($bankAccounts) > 0 ? $bankAccounts[0] : [];
                $accountNumber = $firstAccount['accountNumber'] ?? null;
                $bankName = $firstAccount['bankName'] ?? 'Palmpay';
                $accountName = $firstAccount['accountName'] ?? $user->name;

                if (! $accountNumber) {
                    return back()->with('error', 'Virtual account created but no account number returned.')->withInput();
                }

                $user->$accountNumberField = $accountNumber;
                $user->$accountNameField = $accountName;
                $user->$bankNameField = $bankName;
                $user->save();
            }

            if (is_null($tx)) {
                $calc = FundingTransaction::calculateNetAndFee($currency === 'NGN' ? $amount : $amount * $ngnPerUsd, $method, $ngnPerUsd);

                $tx = FundingTransaction::create([
                    'user_id' => auth()->id(),
                    'promo_code_id' => null,
                    'payment_method' => $method,
                    'currency' => $currency,
                    'amount' => round($amount, 2),
                    'amount_usd' => $calc['net_usd'],
                    'rate_ngn_per_usd' => $currency === 'NGN' ? $ngnPerUsd : null,
                    'reference' => $reference,
                    'status' => 'pending',
                ]);

                SystemTransaction::create([
                    'actor_user_id' => auth()->id(),
                    'user_id' => auth()->id(),
                    'type' => 'funding',
                    'action' => 'create',
                    'status' => (string) $tx->status,
                    'subject_type' => FundingTransaction::class,
                    'subject_id' => (string) $tx->id,
                    'amount_usd' => (float) $tx->amount_usd,
                    'reference' => (string) $tx->reference,
                    'meta' => [
                        'payment_method' => (string) $tx->payment_method,
                        'currency' => (string) $tx->currency,
                        'amount' => (float) $tx->amount,
                    ],
                ]);
            }

            $tx->provider = $method;
            $tx->status = 'waiting_payment';
            $tx->provider_payment_id = $user->$accountNumberField;
            $tx->save();

            SystemTransaction::create([
                'actor_user_id' => auth()->id(),
                'user_id' => auth()->id(),
                'type' => 'funding',
                'action' => 'create',
                'status' => (string) $tx->status,
                'subject_type' => FundingTransaction::class,
                'subject_id' => (string) $tx->id,
                'amount_usd' => (float) $tx->amount_usd,
                'reference' => (string) $tx->reference,
                'meta' => [
                    'payment_method' => (string) $tx->payment_method,
                    'currency' => (string) $tx->currency,
                    'amount' => (float) $tx->amount,
                ],
            ]);

            return redirect()->route('funding')->with('virtual_account', [
                'method' => $method,
                'account_number' => $user->$accountNumberField,
                'bank_name' => $user->$bankNameField,
                'account_name' => $user->$accountNameField,
                'amount' => $amount,
                'currency' => $currency,
                'reference' => $tx->reference,
            ]);
        }

        return back()->with('success', "Funding request created. Reference: {$reference}");
    }

    public function paymentPointCallback(Request $request, PaymentPointService $paymentPoint, WalletService $wallet, ReferralService $referrals)
    {
        $reference = $request->query('reference')
            ?? $request->query('trxref')
            ?? $request->query('tx_ref')
            ?? $request->query('order_id');

        if (! $reference) {
            if (auth()->check()) {
                return redirect('/funding')->with('error', 'PaymentPoint callback received without a reference.');
            }
            return redirect('/login')->with('error', 'PaymentPoint callback received without a reference. Please log in and check your funding history.');
        }

        $tx = FundingTransaction::where('reference', (string) $reference)
            ->first();

        if (! $tx) {
            if (auth()->check()) {
                return redirect('/funding')->with('error', 'Funding transaction not found for this reference.');
            }
            return redirect('/login')->with('error', 'Funding transaction not found for this reference. Please log in and check your funding history.');
        }

        if (! auth()->check()) {
            try {
                auth()->loginUsingId($tx->user_id, true);
            } catch (\Throwable $e) {
                Log::warning('PaymentPoint callback auto-login failed', ['tx_id' => $tx->id, 'error' => $e->getMessage()]);
            }
        }

        if ($tx->status === 'approved') {
            return redirect('/funding')->with('success', 'Deposit confirmed and wallet credited.');
        }

        $verifyResult = $paymentPoint->verifyTransaction((string) $tx->reference);
        $data = is_array($verifyResult['data'] ?? null) ? $verifyResult['data'] : [];
        $inner = is_array($data['data'] ?? null) ? $data['data'] : $data;

        $status = strtolower((string) ($inner['status'] ?? $inner['payment_status'] ?? ''));
        $providerPaymentId = $inner['id'] ?? $inner['transaction_id'] ?? $inner['payment_id'] ?? null;

        if ($providerPaymentId && ! $tx->provider_payment_id) {
            $tx->provider_payment_id = (string) $providerPaymentId;
            $tx->save();
        }

        $isSuccess = in_array($status, ['success', 'successful', 'completed', 'paid'], true)
            || ($status !== '' && str_contains($status, 'success'));

        if (! ($verifyResult['ok'] ?? false)) {
            $tx->status = $tx->status === 'initiated' ? 'waiting_payment' : $tx->status;
            $tx->save();

            return redirect('/funding')->with('success', 'Payment received. Waiting for confirmation.');
        }

        if (! $isSuccess) {
            if ($status !== '') {
                $tx->status = $status;
                $tx->save();
            }

            return redirect('/funding')->with('success', 'Payment is processing. Wallet will be credited once confirmed.');
        }

        DB::beginTransaction();
        try {
            $lockedTx = FundingTransaction::lockForUpdate()->find($tx->id);
            if (! $lockedTx) {
                DB::rollBack();

                return redirect('/funding')->with('success', 'Payment is processing. Wallet will be credited once confirmed.');
            }

            if ($lockedTx->status === 'approved') {
                DB::commit();

                return redirect('/funding')->with('success', 'Deposit confirmed and wallet credited.');
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

            return redirect('/funding')->with('success', 'Deposit confirmed and wallet credited.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::warning('PaymentPoint callback credit failed', [
                'reference' => $tx->reference,
                'error' => $e->getMessage(),
            ]);

            return redirect('/funding')->with('error', 'Could not finalize this payment yet. If you were charged, contact support with your reference.');
        }
    }

    public function strollwalletCallback(Request $request, StrollwalletService $strollwallet, WalletService $wallet, ReferralService $referrals)
    {
        $reference = $request->query('reference')
            ?? $request->query('trxref')
            ?? $request->query('tx_ref')
            ?? $request->query('order_id');

        if (! $reference) {
            if (auth()->check()) {
                return redirect('/funding')->with('error', 'Strollwallet callback received without a reference.');
            }
            return redirect('/login')->with('error', 'Strollwallet callback received without a reference. Please log in and check your funding history.');
        }

        $tx = FundingTransaction::where('reference', (string) $reference)
            ->first();

        if (! $tx) {
            if (auth()->check()) {
                return redirect('/funding')->with('error', 'Funding transaction not found for this reference.');
            }
            return redirect('/login')->with('error', 'Funding transaction not found for this reference. Please log in and check your funding history.');
        }

        if (! auth()->check()) {
            try {
                auth()->loginUsingId($tx->user_id, true);
            } catch (\Throwable $e) {
                Log::warning('Strollwallet callback auto-login failed', ['tx_id' => $tx->id, 'error' => $e->getMessage()]);
            }
        }

        if ($tx->status === 'approved') {
            return redirect('/funding')->with('success', 'Deposit confirmed and wallet credited.');
        }

        $verifyResult = $strollwallet->verifyTransaction((string) $tx->reference);
        $data = is_array($verifyResult['data'] ?? null) ? $verifyResult['data'] : [];
        $inner = is_array($data['data'] ?? null) ? $data['data'] : $data;

        $status = strtolower((string) ($inner['status'] ?? $inner['payment_status'] ?? ''));
        $providerPaymentId = $inner['id'] ?? $inner['transaction_id'] ?? $inner['payment_id'] ?? null;

        if ($providerPaymentId && ! $tx->provider_payment_id) {
            $tx->provider_payment_id = (string) $providerPaymentId;
            $tx->save();
        }

        $isSuccess = in_array($status, ['success', 'successful', 'completed', 'paid'], true)
            || ($status !== '' && str_contains($status, 'success'));

        if (! ($verifyResult['ok'] ?? false)) {
            $tx->status = $tx->status === 'initiated' ? 'waiting_payment' : $tx->status;
            $tx->save();

            return redirect('/funding')->with('success', 'Payment received. Waiting for confirmation.');
        }

        if (! $isSuccess) {
            if ($status !== '') {
                $tx->status = $status;
                $tx->save();
            }

            return redirect('/funding')->with('success', 'Payment is processing. Wallet will be credited once confirmed.');
        }

        DB::beginTransaction();
        try {
            $lockedTx = FundingTransaction::lockForUpdate()->find($tx->id);
            if (! $lockedTx) {
                DB::rollBack();

                return redirect('/funding')->with('success', 'Payment is processing. Wallet will be credited once confirmed.');
            }

            if ($lockedTx->status === 'approved') {
                DB::commit();

                return redirect('/funding')->with('success', 'Deposit confirmed and wallet credited.');
            }

            $user = $lockedTx->user()->lockForUpdate()->first();
            $wallet->credit($user, (float) $lockedTx->amount_usd, 'funding_credit', (string) $lockedTx->reference, [
                'funding_transaction_id' => $lockedTx->id,
                'payment_method' => (string) $lockedTx->payment_method,
                'provider' => 'strollwallet',
                'provider_payment_id' => (string) ($lockedTx->provider_payment_id ?? ''),
            ]);

            $lockedTx->status = 'approved';
            $lockedTx->approved_at = now();
            $lockedTx->save();

            DB::commit();

            $user->notify(new SimpleDatabaseNotification(
                'Wallet funded',
                'Your Strollwallet payment was confirmed and your wallet was credited.',
                '/wallet'
            ));

            try {
                Mail::to($user->email)->send(new FundingSuccessEmail($user, $lockedTx, $lockedTx->amount_usd));
            } catch (\Exception $e) {
                Log::error('Funding Success Email failed to send: '.$e->getMessage());
            }

            $referrals->awardFirstFundingBonus($lockedTx->fresh(['user']));

            return redirect('/funding')->with('success', 'Deposit confirmed and wallet credited.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::warning('Strollwallet callback credit failed', [
                'reference' => $tx->reference,
                'error' => $e->getMessage(),
            ]);

            return redirect('/funding')->with('error', 'Could not finalize this payment yet. If you were charged, contact support with your reference.');
        }
    }

    public function pocketfiCallback(Request $request, PocketFiService $pocketfi, WalletService $wallet, ReferralService $referrals)
    {
        $paymentId  = $request->query('payment_id');
        $reference  = $request->query('reference') ?? $request->query('tx_ref') ?? $request->query('trxref');

        // Try to find transaction by payment_id first, then by reference
        $tx = null;
        if ($paymentId) {
            $tx = FundingTransaction::where('provider_payment_id', (string) $paymentId)->first();
        }
        if (! $tx && $reference) {
            $tx = FundingTransaction::where('reference', (string) $reference)
                ->where('payment_method', 'pocketfi')
                ->first();
            // If found by reference, grab the stored payment_id
            if ($tx && $tx->provider_payment_id) {
                $paymentId = $tx->provider_payment_id;
            }
        }

        // Last fallback: if logged in, find their latest pending pocketfi tx
        if (! $tx && ! $paymentId && auth()->check()) {
            $tx = FundingTransaction::where('user_id', auth()->id())
                ->whereIn('status', ['waiting_payment', 'initiated', 'pending'])
                ->where('payment_method', 'pocketfi')
                ->latest()
                ->first();
            if ($tx && $tx->provider_payment_id) {
                $paymentId = $tx->provider_payment_id;
            }
        }

        if (! $paymentId) {
            if (auth()->check()) {
                return redirect('/funding')->with('error', 'PocketFi callback received without a payment reference. Please check your funding history or contact support.');
            }
            return redirect('/login')->with('error', 'PocketFi payment received. Please log in and check your funding history.');
        }

        if (! $tx) {
            $tx = FundingTransaction::where('provider_payment_id', (string) $paymentId)->first();
        }


        if (! $tx) {
            if (auth()->check()) {
                return redirect('/funding')->with('error', 'Funding transaction not found for this payment.');
            }
            return redirect('/login')->with('error', 'Funding transaction not found for this payment. Please log in and check your funding history.');
        }

        if (! auth()->check()) {
            try {
                auth()->loginUsingId($tx->user_id, true);
            } catch (\Throwable $e) {
                Log::warning('PocketFi callback auto-login failed', ['tx_id' => $tx->id, 'error' => $e->getMessage()]);
            }
        }

        if ($tx->status === 'approved') {
            return redirect('/funding')->with('success', 'Deposit confirmed and wallet credited.');
        }

        $confirmResult = $pocketfi->confirmPayment((string) $paymentId);
        $data = is_array($confirmResult['data'] ?? null) ? $confirmResult['data'] : [];
        Log::info('PocketFi callback confirm', ['payment_id' => $paymentId, 'confirm_ok' => $confirmResult['ok'] ?? false, 'confirm_data' => $data]);

        $status = strtolower((string) ($data['status'] ?? ''));
        $amount = (float) ($data['amount'] ?? 0);

        $isSuccess = in_array($status, ['success', 'successful', 'completed', 'paid', 'confirmed'], true)
            || ($status !== '' && str_contains($status, 'success'));

        if (! ($confirmResult['ok'] ?? false) || ! $isSuccess) {
            if ($status !== '' && ! in_array($tx->status, ['approved', 'rejected'], true)) {
                $tx->status = $status;
                $tx->save();
            }

            if ($isSuccess === false && ($confirmResult['ok'] ?? false)) {
                return redirect('/funding')->with('info', 'Payment is '.$status.'. Wallet will be credited once fully confirmed by PocketFi.');
            }
            return redirect('/funding')->with('success', 'Payment is processing. Wallet will be credited once confirmed.');
        }

        DB::beginTransaction();
        try {
            $lockedTx = FundingTransaction::lockForUpdate()->find($tx->id);
            if (! $lockedTx) {
                DB::rollBack();

                return redirect('/funding')->with('success', 'Payment is processing. Wallet will be credited once confirmed.');
            }

            if ($lockedTx->status === 'approved') {
                DB::commit();

                return redirect('/funding')->with('success', 'Deposit confirmed and wallet credited.');
            }

            $ngnPerUsd = (float) (Setting::where('key', 'ngn_per_usd')->value('value') ?? 1500);
            if ($amount === 0.0) {
                $amount = (float) ($data['amount_paid'] ?? $data['totalPaymentAmount'] ?? $lockedTx->amount);
            }
            if ($amount > 0) {
                $calc = FundingTransaction::calculateNetAndFee($amount, (string) ($lockedTx->payment_method ?: 'pocketfi'), $ngnPerUsd);
                $lockedTx->amount = round($amount, 2);
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
                'gross_ngn' => $amount,
                'charge_percent' => FundingTransaction::getChargePercent((string) ($lockedTx->payment_method ?: 'pocketfi')),
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

            return redirect('/funding')->with('success', 'Deposit confirmed and wallet credited.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::warning('PocketFi callback credit failed', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);

            return redirect('/funding')->with('error', 'Could not finalize this payment yet. If you were charged, contact support with your payment ID.');
        }
    }

    public function cancel($id)
    {
        $tx = FundingTransaction::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (! $tx) {
            return redirect('/funding')->with('error', 'Funding transaction not found.');
        }

        if (! in_array($tx->status, ['pending', 'initiated', 'waiting_payment'], true)) {
            return redirect('/funding')->with('error', 'Only pending transactions can be cancelled.');
        }

        $tx->status = 'cancelled';
        $tx->save();

        return redirect('/funding')->with('success', 'Funding request cancelled successfully.');
    }

    public function checkStatus($id, PocketFiService $pocketfi, WalletService $wallet, ReferralService $referrals)
    {
        $tx = FundingTransaction::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (! $tx) {
            return redirect('/funding')->with('error', 'Funding transaction not found.');
        }

        if ($tx->status === 'approved') {
            return redirect('/funding')->with('success', 'This deposit was already confirmed and your wallet was credited.');
        }

        if (! in_array($tx->status, ['pending', 'initiated', 'waiting_payment'], true)) {
            return redirect('/funding')->with('error', 'Only pending transactions can have their status checked.');
        }

        $provider = (string) ($tx->payment_method ?? $tx->provider ?? '');
        $providerPaymentId = $tx->provider_payment_id;

        if ($provider === 'pocketfi' && $providerPaymentId) {
            $confirmResult = $pocketfi->confirmPayment((string) $providerPaymentId);
            $data = is_array($confirmResult['data'] ?? null) ? $confirmResult['data'] : [];
            $status = strtolower((string) ($data['status'] ?? ''));
            $isSuccess = in_array($status, ['success', 'successful', 'completed', 'paid', 'confirmed'], true)
                || ($status !== '' && str_contains($status, 'success'));
            Log::info('PocketFi manual status check', ['tx_id' => $tx->id, 'payment_id' => $providerPaymentId, 'status' => $status, 'ok' => $confirmResult['ok'] ?? false]);

            if (($confirmResult['ok'] ?? false) && $isSuccess) {
                DB::beginTransaction();
                try {
                    $lockedTx = FundingTransaction::lockForUpdate()->find($tx->id);
                    if (! $lockedTx || $lockedTx->status === 'approved') {
                        DB::commit();
                        return redirect('/funding')->with('success', 'Deposit confirmed and wallet credited.');
                    }
                    $ngnPerUsd = (float) (Setting::where('key', 'ngn_per_usd')->value('value') ?? 1500);
                    $amount = (float) ($data['amount'] ?? $data['amount_paid'] ?? $data['totalPaymentAmount'] ?? $lockedTx->amount);
                    if ($amount > 0) {
                        $calc = FundingTransaction::calculateNetAndFee($amount, (string) ($lockedTx->payment_method ?: 'pocketfi'), $ngnPerUsd);
                        $lockedTx->amount = round($amount, 2);
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
                        'gross_ngn' => $amount,
                        'charge_percent' => FundingTransaction::getChargePercent((string) ($lockedTx->payment_method ?: 'pocketfi')),
                    ]);
                    $lockedTx->status = 'approved';
                    $lockedTx->approved_at = now();
                    $lockedTx->save();
                    DB::commit();
                    try {
                        $user->notify(new SimpleDatabaseNotification(
                            'Wallet funded',
                            'Your PocketFi payment was confirmed and your wallet was credited.',
                            '/wallet'
                        ));
                        Mail::to($user->email)->send(new FundingSuccessEmail($user, $lockedTx, $lockedTx->amount_usd));
                    } catch (\Exception $e) {
                        Log::error('Funding notify/email failed (manual check): '.$e->getMessage());
                    }
                    $referrals->awardFirstFundingBonus($lockedTx->fresh(['user']));
                    return redirect('/funding')->with('success', 'Deposit confirmed and wallet credited.');
                } catch (\Throwable $e) {
                    DB::rollBack();
                    Log::warning('PocketFi manual check credit failed', ['tx_id' => $tx->id, 'error' => $e->getMessage()]);
                    return redirect('/funding')->with('error', 'Could not finalize this payment yet. If you were charged, contact support with your payment ID.');
                }
            }

            if (($confirmResult['ok'] ?? false) && $status !== '') {
                if (! in_array($tx->status, ['approved', 'rejected'], true)) {
                    $tx->status = $status;
                    $tx->save();
                }
                if (in_array($status, ['failed', 'cancelled', 'canceled', 'reversed', 'abandoned', 'expired'], true)) {
                    return redirect('/funding')->with('error', 'Payment status: '.$status.'. This payment has been marked as failed.');
                }
                return redirect('/funding')->with('info', 'Payment status: '.$status.'. Still waiting on PocketFi confirmation. Wallet will be credited once fully confirmed.');
            }

            return redirect('/funding')->with('info', 'Still waiting on PocketFi confirmation. Wallet will be credited once confirmed.');
        }

        if (! $providerPaymentId) {
            return redirect('/funding')->with('info', 'No provider reference for this transaction yet. Please wait a moment and try again.');
        }

        return redirect('/funding')->with('info', 'Status check for this payment method is not currently available. If already paid, please wait for confirmation or contact support.');
    }
}
