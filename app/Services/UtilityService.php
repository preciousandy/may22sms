<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use App\Models\UtilityTransaction;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class UtilityService
{
    protected BigiSubService $bigiSubService;
    protected WalletService $walletService;
    protected UtilityPricingService $pricingService;

    public function __construct(
        BigiSubService $bigiSubService,
        WalletService $walletService,
        ?UtilityPricingService $pricingService = null
    ) {
        $this->bigiSubService = $bigiSubService;
        $this->walletService = $walletService;
        $this->pricingService = $pricingService ?: app(UtilityPricingService::class);
    }

    public function getPricingService(): UtilityPricingService
    {
        return $this->pricingService;
    }

    /**
     * Get live or configured NGN to USD rate.
     */
    public function getExchangeRate(): float
    {
        return $this->pricingService->getExchangeRate();
    }

    /**
     * Convert NGN amount to USD with 8 decimal precision.
     */
    public function convertNgnToUsd(float $amountNgn): float
    {
        $rate = $this->getExchangeRate();
        if ($rate <= 0) {
            $rate = 1500.0;
        }

        return round($amountNgn / $rate, 8);
    }

    /**
     * Convert USD amount to NGN rounded to 2 decimals.
     */
    public function convertUsdToNgn(float $amountUsd): float
    {
        $rate = $this->getExchangeRate();
        return round($amountUsd * $rate, 2);
    }

    /**
     * Check if user account is eligible.
     */
    protected function validateUserEligibility(User $user): void
    {
        if ($user->account_status !== 'active') {
            throw new RuntimeException('Your account is currently inactive or restricted.');
        }
    }

    /**
     * 1. Process Airtime Purchase.
     */
    public function processAirtimePurchase(User $user, array $data, ?string $ip = null, ?string $userAgent = null): UtilityTransaction
    {
        app(\App\Services\ServiceManager::class)->ensureAvailable('airtime');

        $this->validateUserEligibility($user);

        $network = strtolower(trim($data['network'] ?? ''));
        if (!in_array($network, ['mtn', 'airtel', 'glo', '9mobile'], true)) {
            throw new InvalidArgumentException('Invalid telecom network selected.');
        }

        $phone = preg_replace('/[^0-9]/', '', (string) ($data['phone_number'] ?? ''));
        if (strlen($phone) < 10 || strlen($phone) > 14) {
            throw new InvalidArgumentException('Invalid Nigerian phone number provided.');
        }

        $amountNgn = (float) ($data['amount'] ?? 0);
        
        // Calculate authoritative server-side pricing & profit
        $pricing = $this->pricingService->calculateAirtimePricing($amountNgn, $network);
        if (!$pricing['is_enabled']) {
            throw new RuntimeException('Airtime recharge service is currently disabled.');
        }

        $amountUsd = $pricing['total_customer_usd'];
        $discountNgn = $pricing['discount_ngn'];
        $feeNgn = $pricing['fee_ngn'];
        $sellingPriceNgn = $pricing['selling_price_ngn'];
        $providerCostNgn = $pricing['wholesale_cost_ngn'];
        $providerCostUsd = $pricing['wholesale_cost_usd'];
        $profitNgn = $pricing['profit_ngn'];
        $profitUsd = $pricing['profit_usd'];
        $rate = $pricing['exchange_rate'];
        $snapshot = $pricing['snapshot'];

        $reference = 'UTL-AIR-' . strtoupper(Str::random(12));

        // 1. Atomically debit user wallet and record transaction in pending state
        $tx = DB::transaction(function () use (
            $user, $reference, $network, $phone, $amountNgn, $amountUsd, 
            $discountNgn, $feeNgn, $sellingPriceNgn, $providerCostNgn, $providerCostUsd, 
            $profitNgn, $profitUsd, $rate, $snapshot, $ip, $userAgent
        ) {
            $lockedUser = User::lockForUpdate()->findOrFail($user->id);

            if ((float) $lockedUser->walletBalance < $amountUsd) {
                throw new RuntimeException('Insufficient wallet balance to purchase this airtime.');
            }

            // Debit wallet
            $this->walletService->debit(
                $lockedUser,
                $amountUsd,
                'utility_airtime',
                $reference,
                [
                    'service' => 'airtime',
                    'network' => strtoupper($network),
                    'phone_number' => $phone,
                    'amount_ngn' => $amountNgn,
                    'total_charged_ngn' => $sellingPriceNgn + $feeNgn,
                ]
            );

            return UtilityTransaction::create([
                'user_id' => $lockedUser->id,
                'reference' => $reference,
                'service_type' => 'airtime',
                'provider' => 'bigisub',
                'network_or_disco' => $network,
                'customer_target' => $phone,
                'amount_ngn' => $amountNgn,
                'amount_usd' => $amountUsd,
                'provider_cost_ngn' => $providerCostNgn,
                'provider_cost_usd' => $providerCostUsd,
                'selling_price_ngn' => $sellingPriceNgn,
                'fee_ngn' => $feeNgn,
                'discount_ngn' => $discountNgn,
                'total_charged_usd' => $amountUsd,
                'profit_ngn' => $profitNgn,
                'profit_usd' => $profitUsd,
                'pricing_snapshot' => $snapshot,
                'exchange_rate' => $rate,
                'status' => 'pending',
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'request_payload' => [
                    'network' => $network,
                    'phone' => $phone,
                    'amount' => $amountNgn,
                    'amount_ngn' => $amountNgn,
                ],
            ]);
        });

        // 2. Dispatch to BigiSub (Face value $amountNgn is recharged)
        $res = $this->bigiSubService->purchaseAirtime($network, $phone, $amountNgn, $reference);

        return $this->finalizeTransaction($tx, $res, $amountUsd, 'Airtime top-up');
    }

    /**
     * 2. Process Data Bundle Purchase.
     */
    public function processDataPurchase(User $user, array $data, ?string $ip = null, ?string $userAgent = null): UtilityTransaction
    {
        app(\App\Services\ServiceManager::class)->ensureAvailable('data');

        $this->validateUserEligibility($user);

        $network = strtolower(trim($data['network'] ?? ''));
        if (!in_array($network, ['mtn', 'airtel', 'glo', '9mobile'], true)) {
            throw new InvalidArgumentException('Invalid telecom network selected.');
        }

        $phone = preg_replace('/[^0-9]/', '', (string) ($data['phone_number'] ?? ''));
        if (strlen($phone) < 10 || strlen($phone) > 14) {
            throw new InvalidArgumentException('Invalid Nigerian phone number provided.');
        }

        $planId = $data['plan_id'] ?? null;
        if (empty($planId)) {
            throw new InvalidArgumentException('Please select a valid data bundle plan.');
        }

        // Fetch verified raw wholesale catalog from BigiSub
        $rawPlans = $this->bigiSubService->getRawDataPlans($network);
        $selectedPlan = null;
        foreach ($rawPlans as $p) {
            if ((string) ($p['id'] ?? '') === (string) $planId) {
                $selectedPlan = $p;
                break;
            }
        }

        if (!$selectedPlan) {
            throw new InvalidArgumentException('The selected data plan is no longer available. Please select another plan.');
        }

        $planName = $selectedPlan['name'] ?? ($selectedPlan['size'] ?? 'Data Bundle');
        $wholesaleNgn = (float) ($selectedPlan['plan_amount'] ?? ($selectedPlan['price_ngn'] ?? ($selectedPlan['price'] ?? 0)));
        if ($wholesaleNgn <= 0) {
            throw new InvalidArgumentException('Invalid provider plan price detected.');
        }

        // Calculate authoritative server-side pricing & markup
        $pricing = $this->pricingService->calculateDataPricing($wholesaleNgn, $network);
        if (!$pricing['is_enabled']) {
            throw new RuntimeException('Data bundle service is currently disabled.');
        }

        $amountNgn = $pricing['selling_price_ngn']; // Customer face value / selling price
        $amountUsd = $pricing['total_customer_usd'];
        $providerCostNgn = $pricing['wholesale_cost_ngn'];
        $providerCostUsd = $pricing['wholesale_cost_usd'];
        $profitNgn = $pricing['profit_ngn'];
        $profitUsd = $pricing['profit_usd'];
        $rate = $pricing['exchange_rate'];
        $snapshot = $pricing['snapshot'];

        $reference = 'UTL-DAT-' . strtoupper(Str::random(12));

        // Debit wallet
        $tx = DB::transaction(function () use (
            $user, $reference, $network, $planId, $planName, $phone, 
            $amountNgn, $amountUsd, $providerCostNgn, $providerCostUsd, 
            $profitNgn, $profitUsd, $rate, $snapshot, $ip, $userAgent
        ) {
            $lockedUser = User::lockForUpdate()->findOrFail($user->id);

            if ((float) $lockedUser->walletBalance < $amountUsd) {
                throw new RuntimeException('Insufficient wallet balance to purchase this data bundle.');
            }

            $this->walletService->debit(
                $lockedUser,
                $amountUsd,
                'utility_data',
                $reference,
                [
                    'service' => 'data',
                    'network' => strtoupper($network),
                    'plan_name' => $planName,
                    'phone_number' => $phone,
                    'amount_ngn' => $amountNgn,
                ]
            );

            return UtilityTransaction::create([
                'user_id' => $lockedUser->id,
                'reference' => $reference,
                'service_type' => 'data',
                'provider' => 'bigisub',
                'network_or_disco' => $network,
                'product_code' => (string) $planId,
                'product_name' => $planName,
                'customer_target' => $phone,
                'amount_ngn' => $amountNgn,
                'amount_usd' => $amountUsd,
                'provider_cost_ngn' => $providerCostNgn,
                'provider_cost_usd' => $providerCostUsd,
                'selling_price_ngn' => $amountNgn,
                'fee_ngn' => 0.0,
                'discount_ngn' => 0.0,
                'total_charged_usd' => $amountUsd,
                'profit_ngn' => $profitNgn,
                'profit_usd' => $profitUsd,
                'pricing_snapshot' => $snapshot,
                'exchange_rate' => $rate,
                'status' => 'pending',
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'request_payload' => [
                    'network' => $network,
                    'plan_id' => $planId,
                    'plan_name' => $planName,
                    'phone' => $phone,
                ],
            ]);
        });

        // Dispatch to provider with exact plan ID
        $res = $this->bigiSubService->purchaseData($network, $planId, $phone, $reference);

        return $this->finalizeTransaction($tx, $res, $amountUsd, 'Data bundle delivery');
    }

    /**
     * 3. Process Electricity Bill Payment.
     */
    public function processElectricityPayment(User $user, array $data, ?string $ip = null, ?string $userAgent = null): UtilityTransaction
    {
        app(\App\Services\ServiceManager::class)->ensureAvailable('electricity');

        $this->validateUserEligibility($user);

        $disco = $this->bigiSubService->normalizeDiscoSlug($data['disco'] ?? '');
        $supportedDiscos = $this->bigiSubService->getSupportedDiscos();
        if (!array_key_exists($disco, $supportedDiscos)) {
            throw new InvalidArgumentException('Invalid electricity distribution company (DisCo) selected.');
        }

        $meterNumber = preg_replace('/[^0-9]/', '', (string) ($data['meter_number'] ?? ''));
        if (strlen($meterNumber) < 6 || strlen($meterNumber) > 20) {
            throw new InvalidArgumentException('Invalid meter number provided.');
        }

        $meterType = strtolower(trim($data['meter_type'] ?? 'prepaid'));
        if (!in_array($meterType, ['prepaid', 'postpaid'], true)) {
            throw new InvalidArgumentException('Meter type must be either prepaid or postpaid.');
        }

        $customerName = trim((string) ($data['customer_name'] ?? ''));
        if (empty($customerName)) {
            // Run on-the-fly verification if name wasn't pre-filled
            $verifyRes = $this->bigiSubService->verifyMeter($disco, $meterNumber, $meterType);
            if (!$verifyRes['success']) {
                throw new InvalidArgumentException($verifyRes['message'] ?? 'Could not verify meter number. Please re-check.');
            }
            $customerName = $verifyRes['customer_name'] ?? 'Verified Customer';
        }

        $phone = preg_replace('/[^0-9]/', '', (string) ($data['phone_number'] ?? $user->phone_number ?? '08000000000'));
        $amountNgn = (float) ($data['amount'] ?? 0); // Meter recharge face value

        // Calculate authoritative server-side pricing & convenience fee
        $pricing = $this->pricingService->calculateElectricityPricing($amountNgn);
        if (!$pricing['is_enabled']) {
            throw new RuntimeException('Electricity bill payment service is currently disabled.');
        }

        $feeNgn = $pricing['fee_ngn']; // Platform convenience fee
        $amountUsd = $pricing['total_customer_usd'];
        $sellingPriceNgn = $pricing['selling_price_ngn'];
        $providerCostNgn = $pricing['wholesale_cost_ngn']; // Exactly $amountNgn
        $providerCostUsd = $pricing['wholesale_cost_usd'];
        $profitNgn = $pricing['profit_ngn'];
        $profitUsd = $pricing['profit_usd'];
        $rate = $pricing['exchange_rate'];
        $snapshot = $pricing['snapshot'];

        $reference = 'UTL-ELC-' . strtoupper(Str::random(12));

        $tx = DB::transaction(function () use (
            $user, $reference, $disco, $supportedDiscos, $meterNumber, $meterType, 
            $customerName, $phone, $amountNgn, $amountUsd, $feeNgn, $sellingPriceNgn,
            $providerCostNgn, $providerCostUsd, $profitNgn, $profitUsd, $rate, $snapshot, 
            $ip, $userAgent
        ) {
            $lockedUser = User::lockForUpdate()->findOrFail($user->id);

            if ((float) $lockedUser->walletBalance < $amountUsd) {
                throw new RuntimeException('Insufficient wallet balance to pay this electricity bill.');
            }

            $this->walletService->debit(
                $lockedUser,
                $amountUsd,
                'utility_electricity',
                $reference,
                [
                    'service' => 'electricity',
                    'disco' => $supportedDiscos[$disco]['name'],
                    'meter_number' => $meterNumber,
                    'meter_type' => $meterType,
                    'customer_name' => $customerName,
                    'amount_ngn' => $amountNgn,
                    'fee_ngn' => $feeNgn,
                    'total_charged_ngn' => $amountNgn + $feeNgn,
                ]
            );

            return UtilityTransaction::create([
                'user_id' => $lockedUser->id,
                'reference' => $reference,
                'service_type' => 'electricity',
                'provider' => 'bigisub',
                'network_or_disco' => $disco,
                'product_name' => $supportedDiscos[$disco]['name'] . " (" . ucfirst($meterType) . ")",
                'customer_target' => $meterNumber,
                'customer_name' => $customerName,
                'meter_type' => $meterType,
                'amount_ngn' => $amountNgn,
                'amount_usd' => $amountUsd,
                'provider_cost_ngn' => $providerCostNgn,
                'provider_cost_usd' => $providerCostUsd,
                'selling_price_ngn' => $sellingPriceNgn,
                'fee_ngn' => $feeNgn,
                'discount_ngn' => 0.0,
                'total_charged_usd' => $amountUsd,
                'profit_ngn' => $profitNgn,
                'profit_usd' => $profitUsd,
                'pricing_snapshot' => $snapshot,
                'exchange_rate' => $rate,
                'status' => 'pending',
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'request_payload' => [
                    'disco' => $disco,
                    'meter_number' => $meterNumber,
                    'meter_type' => $meterType,
                    'amount' => $amountNgn,
                    'customer_name' => $customerName,
                    'phone' => $phone,
                ],
            ]);
        });

        // Dispatch to provider with ONLY the meter face value $amountNgn (NOT including the convenience fee)
        $res = $this->bigiSubService->payElectricity($disco, $meterNumber, $meterType, $amountNgn, $customerName, $phone, $reference);

        return $this->finalizeTransaction($tx, $res, $amountUsd, 'Electricity bill payment');
    }

    /**
     * 4. Process Betting Wallet Funding.
     */
    public function processBettingFunding(User $user, array $data, ?string $ip = null, ?string $userAgent = null): UtilityTransaction
    {
        app(\App\Services\ServiceManager::class)->ensureAvailable('betting');

        $this->validateUserEligibility($user);

        $platform = strtolower(trim($data['platform'] ?? ''));
        $supportedPlatforms = $this->bigiSubService->getSupportedBettingPlatforms();
        if (!array_key_exists($platform, $supportedPlatforms)) {
            throw new InvalidArgumentException('Invalid betting platform selected.');
        }

        $customerId = trim((string) ($data['customer_id'] ?? ''));
        if (strlen($customerId) < 3 || strlen($customerId) > 50) {
            throw new InvalidArgumentException('Please provide a valid betting customer user ID.');
        }

        $customerName = trim((string) ($data['customer_name'] ?? ''));
        if (empty($customerName)) {
            $verifyRes = $this->bigiSubService->verifyBettingCustomer($platform, $customerId);
            if (!$verifyRes['success']) {
                throw new InvalidArgumentException($verifyRes['message'] ?? 'Could not verify betting account ID.');
            }
            $customerName = $verifyRes['customer_name'] ?? 'Verified Bettor';
        }

        $amountNgn = (float) ($data['amount'] ?? 0); // Requested funding amount

        // Calculate authoritative server-side pricing & processing fee
        $pricing = $this->pricingService->calculateBettingPricing($amountNgn);
        if (!$pricing['is_enabled']) {
            throw new RuntimeException('Betting wallet top-up is currently disabled.');
        }

        $feeNgn = $pricing['fee_ngn']; // Processing fee
        $amountUsd = $pricing['total_customer_usd'];
        $sellingPriceNgn = $pricing['selling_price_ngn'];
        $providerCostNgn = $pricing['wholesale_cost_ngn']; // Exactly $amountNgn
        $providerCostUsd = $pricing['wholesale_cost_usd'];
        $profitNgn = $pricing['profit_ngn'];
        $profitUsd = $pricing['profit_usd'];
        $rate = $pricing['exchange_rate'];
        $snapshot = $pricing['snapshot'];

        $reference = 'UTL-BET-' . strtoupper(Str::random(12));

        $tx = DB::transaction(function () use (
            $user, $reference, $platform, $supportedPlatforms, $customerId, 
            $customerName, $amountNgn, $amountUsd, $feeNgn, $sellingPriceNgn, 
            $providerCostNgn, $providerCostUsd, $profitNgn, $profitUsd, $rate, 
            $snapshot, $ip, $userAgent
        ) {
            $lockedUser = User::lockForUpdate()->findOrFail($user->id);

            if ((float) $lockedUser->walletBalance < $amountUsd) {
                throw new RuntimeException('Insufficient wallet balance to fund this betting wallet.');
            }

            $this->walletService->debit(
                $lockedUser,
                $amountUsd,
                'utility_betting',
                $reference,
                [
                    'service' => 'betting',
                    'platform' => $supportedPlatforms[$platform]['name'],
                    'customer_id' => $customerId,
                    'customer_name' => $customerName,
                    'amount_ngn' => $amountNgn,
                    'fee_ngn' => $feeNgn,
                    'total_charged_ngn' => $amountNgn + $feeNgn,
                ]
            );

            return UtilityTransaction::create([
                'user_id' => $lockedUser->id,
                'reference' => $reference,
                'service_type' => 'betting',
                'provider' => 'bigisub',
                'network_or_disco' => $platform,
                'product_name' => $supportedPlatforms[$platform]['name'] . ' Wallet Funding',
                'customer_target' => $customerId,
                'customer_name' => $customerName,
                'amount_ngn' => $amountNgn,
                'amount_usd' => $amountUsd,
                'provider_cost_ngn' => $providerCostNgn,
                'provider_cost_usd' => $providerCostUsd,
                'selling_price_ngn' => $sellingPriceNgn,
                'fee_ngn' => $feeNgn,
                'discount_ngn' => 0.0,
                'total_charged_usd' => $amountUsd,
                'profit_ngn' => $profitNgn,
                'profit_usd' => $profitUsd,
                'pricing_snapshot' => $snapshot,
                'exchange_rate' => $rate,
                'status' => 'pending',
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'request_payload' => [
                    'platform' => $platform,
                    'customer_id' => $customerId,
                    'customer_name' => $customerName,
                    'amount' => $amountNgn,
                    'amount_ngn' => $amountNgn,
                ],
            ]);
        });

        // Dispatch to provider with ONLY the funding amount $amountNgn (NOT including the processing fee)
        $res = $this->bigiSubService->fundBetting($platform, $customerId, $amountNgn, $reference);

        return $this->finalizeTransaction($tx, $res, $amountUsd, 'Betting wallet top-up');
    }

    /**
     * Finalize transaction state based on provider response.
     * Enforces automatic refund if failure is confirmed.
     */
    protected function finalizeTransaction(UtilityTransaction $tx, array $res, float $amountUsd, string $serviceLabel): UtilityTransaction
    {
        $tx->response_payload = $res['raw'] ?? $res;
        $tx->provider_reference = $res['data']['ref'] ?? ($res['data']['ident'] ?? ($res['data']['id'] ?? null));
        $tx->provider_status = $res['status'] ?? null;

        if (!empty($res['token'])) {
            $tx->token = (string) $res['token'];
        }
        if (!empty($res['units'])) {
            $tx->units = (string) $res['units'];
        }

        // 1. Success
        if (!empty($res['success']) && $res['success'] === true) {
            $tx->status = 'completed';
            $tx->error_message = null;
            $tx->save();
            return $tx;
        }

        // 2. Uncertain / Timeout -> Do NOT immediately refund, keep in pending/uncertain state
        if (($res['status'] ?? '') === 'uncertain') {
            $tx->status = 'uncertain';
            $tx->error_message = $res['message'] ?? 'Transaction is currently processing with the provider.';
            $tx->save();
            return $tx;
        }

        // 3. Definite Failure -> Atomically refund the user exactly once
        $this->refundTransaction($tx, null, $res['message'] ?? "{$serviceLabel} failed at provider.");

        return $tx;
    }

    /**
     * Atomically refund a failed or uncertain transaction.
     */
    public function refundTransaction(UtilityTransaction $tx, ?int $adminId = null, string $reason = 'Provider rejected'): UtilityTransaction
    {
        return DB::transaction(function () use ($tx, $adminId, $reason) {
            $lockedTx = UtilityTransaction::lockForUpdate()->findOrFail($tx->id);

            if ($lockedTx->refunded_at !== null || $lockedTx->status === 'completed') {
                return $lockedTx; // Already refunded or completed
            }

            $lockedUser = User::lockForUpdate()->findOrFail($lockedTx->user_id);
            $refundRef = 'REF-' . $lockedTx->reference;

            // Credit wallet back
            $this->walletService->credit(
                $lockedUser,
                (float) $lockedTx->total_charged_usd,
                'utility_refund',
                $refundRef,
                [
                    'original_reference' => $lockedTx->reference,
                    'service_type' => $lockedTx->service_type,
                    'amount_ngn' => (float) $lockedTx->amount_ngn,
                    'admin_id' => $adminId,
                    'reason' => $reason,
                ]
            );

            $lockedTx->status = 'refunded';
            $lockedTx->error_message = $reason;
            $lockedTx->refunded_at = now();
            $lockedTx->save();

            return $lockedTx;
        });
    }

    /**
     * Reconcile an uncertain or pending transaction against BigiSub.
     */
    public function reconcileTransaction(UtilityTransaction $tx): UtilityTransaction
    {
        if ($tx->status === 'completed' || $tx->refunded_at !== null) {
            return $tx;
        }

        $queryRes = $this->bigiSubService->queryTransaction($tx->reference);

        if (!empty($queryRes['success']) && ($queryRes['status'] ?? '') === 'completed') {
            $tx->status = 'completed';
            $tx->provider_reference = $queryRes['data']['ref'] ?? $tx->provider_reference;
            $tx->save();
            return $tx;
        }

        if (($queryRes['status'] ?? '') === 'failed') {
            return $this->refundTransaction($tx, null, 'Reconciliation confirmed provider failure.');
        }

        return $tx;
    }
}
