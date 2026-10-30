<?php

namespace App\Services;

use App\Models\CryptoBankCache;
use App\Models\CryptoTransaction;
use App\Models\CryptoWebhook;
use App\Models\Setting;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Notifications\SimpleDatabaseNotification;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class QuidaxService
{
    protected string $env;
    protected string $rampBaseUrl;
    protected string $openapiBaseUrl;
    protected ?string $privateKey;
    protected ?string $apiKey;
    protected ?string $webhookSecret;
    protected int $timeout;

    public function __construct()
    {
        $this->env = config('quidax.env', 'test');
        $this->rampBaseUrl = rtrim(config('quidax.ramp_base_url', 'https://ramp-be.quidax.io'), '/');
        $this->openapiBaseUrl = rtrim(config('quidax.openapi_base_url', 'https://openapi.quidax.io/exchange-open-api/api/v1'), '/');
        $this->timeout = (int) config('quidax.request_timeout_seconds', 15);

        if ($this->isTestMode()) {
            $this->privateKey = config('quidax.test_private_key');
            $this->apiKey = config('quidax.test_api_key');
            $this->webhookSecret = config('quidax.test_webhook_secret');
        } else {
            $this->privateKey = config('quidax.live_private_key');
            $this->apiKey = config('quidax.live_api_key');
            $this->webhookSecret = config('quidax.live_webhook_secret');
        }
    }

    public function isTestMode(): bool
    {
        return $this->env === 'test';
    }

    public function isAvailable(): bool
    {
        return app(ServiceManager::class)->isAvailable('crypto_to_naira');
    }

    /**
     * Get live or cached market ticker for a trading pair.
     */
    public function getMarketTicker(string $pair = 'usdtngn'): float
    {
        $cacheKey = 'quidax_ticker_' . strtolower($pair);
        
        return cache()->remember($cacheKey, 20, function () use ($pair) {
            try {
                $response = Http::timeout(8)
                    ->acceptJson()
                    ->get($this->openapiBaseUrl . '/markets/tickers');

                if ($response->successful()) {
                    $json = $response->json();
                    $targetPair = strtolower($pair);
                    if (isset($json['data'][$targetPair]['ticker']['last'])) {
                        return (float) $json['data'][$targetPair]['ticker']['last'];
                    }
                    if (isset($json['data'][$targetPair]['ticker']['buy'])) {
                        return (float) $json['data'][$targetPair]['ticker']['buy'];
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Quidax ticker fetch failed: ' . $e->getMessage());
            }

            // Fallback to setting rate
            return (float) (Setting::where('key', 'ngn_per_usd')->value('value') ?? 1500.0);
        });
    }

    /**
     * Calculate conversion quote for crypto to NGN.
     */
    public function calculateQuote(string $currency, float $amount, string $network = 'bep20'): array
    {
        $currency = strtolower($currency);
        $supported = config("quidax.supported_currencies.{$currency}");

        if (! $supported) {
            throw new Exception("Unsupported cryptocurrency: {$currency}");
        }

        if ($amount < $supported['min_amount']) {
            throw new Exception("Minimum amount for {$supported['symbol']} is {$supported['min_amount']}");
        }

        if ($amount > $supported['max_amount']) {
            throw new Exception("Maximum amount for {$supported['symbol']} is {$supported['max_amount']}");
        }

        if (! in_array(strtolower($network), $supported['networks'], true)) {
            throw new Exception("Unsupported network {$network} for {$supported['symbol']}. Supported: " . implode(', ', $supported['networks']));
        }

        // Ticker rate (NGN per 1 Crypto)
        $pair = ($currency === 'btc') ? 'btcngn' : ($currency === 'usdc' ? 'usdcngn' : 'usdtngn');
        $rate = $this->getMarketTicker($pair);

        $grossNgn = round($amount * $rate, 2);
        $platformFeePercent = (float) config('quidax.platform_fee_percent', 0.5);
        $platformFeeNgn = round(($grossNgn * $platformFeePercent) / 100, 2);
        $estimatedPayoutNgn = max(0, $grossNgn - $platformFeeNgn);

        return [
            'from_currency' => $currency,
            'symbol' => $supported['symbol'],
            'from_amount' => $amount,
            'network' => strtolower($network),
            'to_currency' => 'ngn',
            'exchange_rate' => $rate,
            'gross_ngn' => $grossNgn,
            'platform_fee_percent' => $platformFeePercent,
            'platform_fee_ngn' => $platformFeeNgn,
            'estimated_payout_ngn' => $estimatedPayoutNgn,
            'min_amount' => $supported['min_amount'],
            'max_amount' => $supported['max_amount'],
            'expires_in_seconds' => 60,
            'timestamp' => now()->timestamp,
        ];
    }

    /**
     * Get Nigerian banks supported for off-ramp payout.
     */
    public function getSupportedBanks(): array
    {
        // 1. Check local cache table
        $cached = CryptoBankCache::orderBy('name')->get(['code', 'name', 'public_id'])->toArray();
        if (! empty($cached)) {
            return $cached;
        }

        // 2. Fetch from Quidax API if private key is present
        if ($this->privateKey) {
            try {
                $response = Http::timeout($this->timeout)
                    ->withHeaders([
                        'accept' => 'application/json',
                        'x-private-key' => $this->privateKey,
                    ])
                    ->get($this->rampBaseUrl . '/api/v1/merchants/custodial/banks?country=NG');

                if ($response->successful() && isset($response->json()['data'])) {
                    $banks = $response->json()['data'];
                    if (is_array($banks) && count($banks) > 0) {
                        foreach ($banks as $b) {
                            if (! empty($b['code']) && ! empty($b['name'])) {
                                CryptoBankCache::updateOrCreate(
                                    ['code' => $b['code']],
                                    [
                                        'name' => $b['name'],
                                        'public_id' => $b['public_id'] ?? null,
                                        'country' => 'NG',
                                    ]
                                );
                            }
                        }
                        return CryptoBankCache::orderBy('name')->get(['code', 'name', 'public_id'])->toArray();
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Quidax bank list fetch failed: ' . $e->getMessage());
            }
        }

        // 3. Fallback bank list (standard Nigerian commercial & fintech banks)
        $standardBanks = [
            ['code' => '044', 'name' => 'Access Bank'],
            ['code' => '023', 'name' => 'Citibank Nigeria'],
            ['code' => '050', 'name' => 'Ecobank Nigeria'],
            ['code' => '070', 'name' => 'Fidelity Bank'],
            ['code' => '011', 'name' => 'First Bank of Nigeria'],
            ['code' => '214', 'name' => 'First City Monument Bank (FCMB)'],
            ['code' => '058', 'name' => 'Guaranty Trust Bank (GTBank)'],
            ['code' => '030', 'name' => 'Heritage Bank'],
            ['code' => '301', 'name' => 'Jaiz Bank'],
            ['code' => '082', 'name' => 'Keystone Bank'],
            ['code' => '50211', 'name' => 'Kuda Microfinance Bank'],
            ['code' => '50515', 'name' => 'Moniepoint MFB'],
            ['code' => '999992', 'name' => 'OPay (PayCom)'],
            ['code' => '999991', 'name' => 'PalmPay'],
            ['code' => '076', 'name' => 'Polaris Bank'],
            ['code' => '101', 'name' => 'Providus Bank'],
            ['code' => '221', 'name' => 'Stanbic IBTC Bank'],
            ['code' => '068', 'name' => 'Standard Chartered Bank'],
            ['code' => '232', 'name' => 'Sterling Bank'],
            ['code' => '100', 'name' => 'Suntrust Bank'],
            ['code' => '032', 'name' => 'Union Bank of Nigeria'],
            ['code' => '033', 'name' => 'United Bank for Africa (UBA)'],
            ['code' => '215', 'name' => 'Unity Bank'],
            ['code' => '035', 'name' => 'Wema Bank'],
            ['code' => '057', 'name' => 'Zenith Bank'],
        ];

        foreach ($standardBanks as $sb) {
            CryptoBankCache::updateOrCreate(
                ['code' => $sb['code']],
                ['name' => $sb['name'], 'country' => 'NG']
            );
        }

        return CryptoBankCache::orderBy('name')->get(['code', 'name', 'public_id'])->toArray();
    }

    /**
     * Create and initiate an off-ramp transaction.
     */
    public function initiateOffRamp(
        User $user,
        string $fromCurrency,
        float $fromAmount,
        string $network,
        string $payoutDestination = 'bank_account',
        ?string $bankCode = null,
        ?string $accountNumber = null,
        ?string $accountName = null
    ): CryptoTransaction {
        // Enforce availability
        app(ServiceManager::class)->ensureAvailable('crypto_to_naira');

        // Quote validation
        $quote = $this->calculateQuote($fromCurrency, $fromAmount, $network);

        $merchantReference = 'ngn-sell-' . strtolower(Str::random(12));

        if (! $this->isTestMode() && empty($this->privateKey)) {
            throw new Exception('Quidax live credentials are not configured. Please contact support.');
        }

        $providerPublicId = null;
        $providerReference = null;
        $actualToAmount = $quote['estimated_payout_ngn'];

        // Live Quidax API Call when not in test mode or when live key is active
        if (! $this->isTestMode() && $this->privateKey) {
            $nameParts = explode(' ', trim($user->name ?: 'Customer User'), 2);
            $firstName = $nameParts[0] ?: 'Customer';
            $lastName = $nameParts[1] ?? 'User';

            $payload = [
                'from_currency' => strtolower($fromCurrency),
                'to_currency' => 'ngn',
                'from_amount' => (string) $fromAmount,
                'network' => strtolower($network),
                'customer' => [
                    'email' => $user->email,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                ],
                'merchant_reference' => $merchantReference,
            ];

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                    'x-private-key' => $this->privateKey,
                ])
                ->post($this->rampBaseUrl . '/api/v1/merchants/custodial/off_ramp_transactions/initiate', $payload);

            if (! $response->successful()) {
                $err = $response->json()['message'] ?? 'Failed to initiate Quidax transaction';
                Log::error('Quidax initiate off-ramp failed', ['response' => $response->body()]);
                throw new Exception($err);
            }

            $data = $response->json()['data'] ?? [];
            $providerPublicId = $data['public_id'] ?? null;
            $providerReference = $data['reference'] ?? null;
            if (isset($data['to_amount'])) {
                $actualToAmount = (float) $data['to_amount'];
            }
        } else {
            // Test Mode: Generate simulated Quidax identifiers
            $providerPublicId = (string) Str::uuid();
            $providerReference = 'TRX-' . strtoupper(Str::random(10));
        }

        // Find bank name if bankCode provided
        $bankName = null;
        if ($bankCode) {
            $bankName = CryptoBankCache::where('code', $bankCode)->value('name');
        }

        return CryptoTransaction::create([
            'user_id' => $user->id,
            'merchant_reference' => $merchantReference,
            'provider_reference' => $providerReference,
            'provider_public_id' => $providerPublicId,
            'from_currency' => strtolower($fromCurrency),
            'to_currency' => 'ngn',
            'network' => strtolower($network),
            'from_amount' => $fromAmount,
            'to_amount' => $actualToAmount,
            'exchange_rate' => $quote['exchange_rate'],
            'platform_fee' => $quote['platform_fee_ngn'],
            'payout_destination' => $payoutDestination,
            'bank_code' => $bankCode,
            'bank_name' => $bankName,
            'account_number' => $accountNumber,
            'account_name' => $accountName,
            'status' => 'pending',
            'is_test_mode' => $this->isTestMode(),
            'metadata' => [
                'user_email' => $user->email,
                'quote' => $quote,
            ],
        ]);
    }

    /**
     * Attach customer Nigerian bank account to the transaction.
     */
    public function attachBankAccount(CryptoTransaction $tx, string $bankCode, string $accountNumber, ?string $accountName = null): array
    {
        if ($tx->status !== 'pending') {
            throw new Exception("Transaction cannot be modified in state: {$tx->status}");
        }

        $bankName = CryptoBankCache::where('code', $bankCode)->value('name') ?? 'Bank (' . $bankCode . ')';

        if (! $this->isTestMode() && $this->privateKey) {
            $payload = [
                'currency_code' => 'ngn',
                'bank_code' => $bankCode,
                'account_number' => $accountNumber,
            ];

            $url = "{$this->rampBaseUrl}/api/v1/merchants/custodial/off_ramp_transactions/{$tx->merchant_reference}/bank_account";
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                    'x-private-key' => $this->privateKey,
                ])
                ->post($url, $payload);

            if (! $response->successful()) {
                $msg = $response->json()['message'] ?? 'Failed to attach bank account';
                Log::error('Quidax attach bank account failed', ['response' => $response->body()]);
                throw new Exception($msg);
            }

            $resolvedName = $response->json()['data']['metadata']['account_name'] ?? $accountName;
            if ($resolvedName) {
                $accountName = $resolvedName;
            }
        }

        $tx->update([
            'bank_code' => $bankCode,
            'bank_name' => $bankName,
            'account_number' => $accountNumber,
            'account_name' => $accountName,
        ]);

        return [
            'status' => 'ok',
            'bank_code' => $bankCode,
            'bank_name' => $bankName,
            'account_number' => $accountNumber,
            'account_name' => $accountName,
        ];
    }

    /**
     * Confirm off-ramp transaction to generate crypto deposit address.
     */
    public function confirmOffRamp(CryptoTransaction $tx): array
    {
        if ($tx->deposit_address) {
            return [
                'address' => $tx->deposit_address,
                'tag' => $tx->deposit_tag,
                'network' => $tx->network,
                'currency' => $tx->from_currency,
                'status' => $tx->status,
            ];
        }

        $address = null;
        $tag = null;
        $assignedAt = now();

        if (! $this->isTestMode() && $this->privateKey) {
            $url = "{$this->rampBaseUrl}/api/v1/merchants/custodial/off_ramp_transactions/{$tx->merchant_reference}/confirm";
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'accept' => 'application/json',
                    'x-private-key' => $this->privateKey,
                ])
                ->post($url);

            if (! $response->successful()) {
                $msg = $response->json()['message'] ?? 'Failed to confirm off-ramp transaction';
                Log::error('Quidax confirm failed', ['response' => $response->body()]);
                throw new Exception($msg);
            }

            $data = $response->json()['data'] ?? [];
            $address = $data['address'] ?? null;
            $tag = $data['tag'] ?? null;
            if (isset($data['assigned_at'])) {
                $assignedAt = \Carbon\Carbon::parse($data['assigned_at']);
            }
        } else {
            // Test Mode: Generate deterministic mock deposit address per network
            $prefix = match (strtolower($tx->network)) {
                'trc20' => 'T' . Str::random(33),
                'btc' => 'bc1q' . strtolower(Str::random(38)),
                default => '0x' . strtolower(Str::random(40)),
            };
            $address = $prefix;
            $tag = ($tx->from_currency === 'xrp' || $tx->from_currency === 'ton') ? (string) rand(100000, 999999) : null;
        }

        if (! $address) {
            throw new Exception('Quidax did not return a valid deposit address.');
        }

        $tx->update([
            'deposit_address' => $address,
            'deposit_tag' => $tag,
            'deposit_assigned_at' => $assignedAt,
            'status' => 'address_assigned',
        ]);

        return [
            'address' => $address,
            'tag' => $tag,
            'network' => $tx->network,
            'currency' => $tx->from_currency,
            'status' => 'address_assigned',
        ];
    }

    /**
     * Requery status of an off-ramp transaction from Quidax.
     */
    public function requeryOffRamp(CryptoTransaction $tx): array
    {
        if (! $this->isTestMode() && $this->privateKey) {
            $url = "{$this->rampBaseUrl}/api/v1/merchants/off_ramp_transaction/{$tx->merchant_reference}";
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'accept' => 'application/json',
                    'x-private-key' => $this->privateKey,
                ])
                ->get($url);

            if ($response->successful()) {
                $data = $response->json()['data'] ?? [];
                $this->applyProviderUpdate($tx, $data);
            }
        }

        return [
            'merchant_reference' => $tx->merchant_reference,
            'status' => $tx->status,
            'from_amount' => $tx->from_amount,
            'to_amount' => $tx->to_amount,
            'deposit_address' => $tx->deposit_address,
            'crypto_received_amount' => $tx->crypto_received_amount,
            'crypto_txid' => $tx->crypto_txid,
            'fiat_payout_amount' => $tx->fiat_payout_amount,
            'settled_at' => $tx->settled_at?->toIso8601String(),
            'wallet_credited' => $tx->wallet_credited,
        ];
    }

    /**
     * Verify HMAC-SHA256 signature for Quidax webhooks.
     */
    public function verifyWebhookSignature(string $rawPayload, ?string $signatureHeader): bool
    {
        if (empty($signatureHeader) || empty($this->webhookSecret)) {
            // If in test mode with empty secret, allow test verification
            if ($this->isTestMode() && empty($this->webhookSecret)) {
                return true;
            }
            return false;
        }

        $expected = hash_hmac('sha256', $rawPayload, $this->webhookSecret);

        return hash_equals($expected, $signatureHeader);
    }

    /**
     * Handle incoming Quidax webhook event idempotently.
     */
    public function handleWebhook(array $payload, string $rawPayload, ?string $signatureHeader): array
    {
        $event = $payload['event'] ?? 'unknown';
        $data = $payload['data'] ?? [];
        $publicId = $data['public_id'] ?? null;
        $merchantRef = $data['merchant_reference'] ?? null;

        $eventId = $event . ':' . ($publicId ?: $merchantRef ?: Str::random(10));

        // Deduplicate
        $existing = CryptoWebhook::where('event_id', $eventId)->first();
        if ($existing && $existing->processed) {
            return [
                'status' => 'duplicate',
                'message' => 'Event already processed',
            ];
        }

        $webhookLog = CryptoWebhook::create([
            'event' => $event,
            'event_id' => $eventId,
            'public_id' => $publicId,
            'merchant_reference' => $merchantRef,
            'signature' => $signatureHeader,
            'payload' => $payload,
            'processed' => false,
        ]);

        // Find associated transaction
        $tx = null;
        if ($merchantRef) {
            $tx = CryptoTransaction::where('merchant_reference', $merchantRef)->first();
        }
        if (! $tx && $publicId) {
            $tx = CryptoTransaction::where('provider_public_id', $publicId)->first();
        }

        if (! $tx) {
            $webhookLog->update(['error' => 'Associated transaction not found']);
            return ['status' => 'not_found', 'message' => 'Transaction not found'];
        }

        // Apply state updates
        $this->applyProviderUpdate($tx, $data, $event);

        $webhookLog->update([
            'processed' => true,
            'processed_at' => now(),
        ]);

        return [
            'status' => 'success',
            'event' => $event,
            'merchant_reference' => $tx->merchant_reference,
            'new_status' => $tx->status,
        ];
    }

    /**
     * Update transaction state and perform safe settlement.
     */
    public function applyProviderUpdate(CryptoTransaction $tx, array $data, ?string $event = null): void
    {
        $status = strtolower($data['status'] ?? $tx->status);

        // Crypto deposit details
        $cryptoDeposit = $data['crypto_deposit'] ?? [];
        $receivedAmount = isset($cryptoDeposit['amount']) ? (float) $cryptoDeposit['amount'] : $tx->crypto_received_amount;
        $txid = $cryptoDeposit['txid'] ?? $tx->crypto_txid;

        // Fiat payout details
        $fiatPayout = $data['fiat_payout'] ?? [];
        $payoutAmount = isset($fiatPayout['amount']) ? (float) $fiatPayout['amount'] : $tx->fiat_payout_amount;
        $payoutId = $fiatPayout['public_id'] ?? $tx->fiat_payout_id;
        $processorFee = isset($fiatPayout['processor_fee']) ? (float) $fiatPayout['processor_fee'] : $tx->processor_fee;

        $errorMessage = $data['error_message'] ?? $tx->error_message;

        // Status mapping
        if ($event === 'sell_transaction.successful' || $status === 'completed') {
            $newStatus = 'completed';
        } elseif ($event === 'sell_transaction.failed' || in_array($status, ['failed', 'needs_attention'], true)) {
            $newStatus = ($status === 'needs_attention') ? 'needs_attention' : 'failed';
        } elseif ($event === 'sell_transaction.processing' || $status === 'processing') {
            $newStatus = 'processing';
        } else {
            $newStatus = $tx->status;
        }

        DB::transaction(function () use ($tx, $newStatus, $receivedAmount, $txid, $payoutAmount, $payoutId, $processorFee, $errorMessage) {
            $lockedTx = CryptoTransaction::where('id', $tx->id)->lockForUpdate()->first();

            $lockedTx->status = $newStatus;
            $lockedTx->crypto_received_amount = $receivedAmount;
            $lockedTx->crypto_txid = $txid;
            $lockedTx->fiat_payout_amount = $payoutAmount;
            $lockedTx->fiat_payout_id = $payoutId;
            $lockedTx->processor_fee = $processorFee;
            if ($errorMessage) {
                $lockedTx->error_message = $errorMessage;
            }

            // If completed and payout destination is May22SMS wallet
            if ($newStatus === 'completed' && $lockedTx->payout_destination === 'wallet' && ! $lockedTx->wallet_credited) {
                $user = User::where('id', $lockedTx->user_id)->first();
                if ($user) {
                    // Convert NGN amount to USD wallet base currency
                    $rate = (float) (Setting::where('key', 'ngn_per_usd')->value('value') ?? 1500.0);
                    $ngnAmount = (float) ($payoutAmount ?: $lockedTx->to_amount);
                    $usdToCredit = round($ngnAmount / max(1, $rate), 4);

                    app(WalletService::class)->credit(
                        $user,
                        $usdToCredit,
                        'crypto_deposit',
                        $lockedTx->merchant_reference,
                        [
                            'crypto_currency' => $lockedTx->from_currency,
                            'crypto_amount' => $lockedTx->from_amount,
                            'ngn_amount' => $ngnAmount,
                            'network' => $lockedTx->network,
                            'rate' => $lockedTx->quoted_rate,
                        ]
                    );

                    $walletTx = WalletTransaction::where('user_id', $user->id)
                        ->where('reference', $lockedTx->merchant_reference)
                        ->first();

                    $lockedTx->wallet_credited = true;
                    $lockedTx->wallet_transaction_id = $walletTx?->id;
                    $lockedTx->settled_at = now();

                    // Notify user
                    $user->notify(new SimpleDatabaseNotification(
                        'Crypto Deposit Converted & Credited',
                        "Your deposit of {$lockedTx->from_amount} " . strtoupper($lockedTx->from_currency) . " has been converted to ₦" . number_format($ngnAmount, 2) . " (\${$usdToCredit}) and credited to your wallet balance.",
                        '/services/crypto-to-naira'
                    ));
                }
            } elseif ($newStatus === 'completed' && ! $lockedTx->settled_at) {
                $lockedTx->settled_at = now();
                $user = User::find($lockedTx->user_id);
                if ($user) {
                    $user->notify(new SimpleDatabaseNotification(
                        'Crypto Payout Sent to Bank',
                        "Your {$lockedTx->from_amount} " . strtoupper($lockedTx->from_currency) . " conversion has completed. ₦" . number_format($lockedTx->to_amount, 2) . " was sent to your {$lockedTx->bank_name} account ({$lockedTx->account_number}).",
                        '/services/crypto-to-naira'
                    ));
                }
            }

            $lockedTx->save();
        });
    }
}
