<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class UtilityPricingService
{
    /**
     * Default Fallback Configurations.
     */
    public const DEFAULTS = [
        // Data Bundles
        'utility_data_enabled' => '1',
        'utility_data_pricing_mode' => 'fixed', // 'fixed' or 'percent'
        'utility_data_markup_value' => '40.0', // ₦40 per plan or 10%
        'utility_data_min_amount' => '50.0',
        'utility_data_max_amount' => '100000.0',

        // Airtime
        'utility_airtime_enabled' => '1',
        'utility_airtime_discount_percent' => '0.0', // 0% default recommended
        'utility_airtime_fee_mode' => 'none', // 'none', 'fixed', 'percent'
        'utility_airtime_fee_value' => '0.0',
        'utility_airtime_provider_discount_percent' => '2.0', // Standard ~2% wholesale discount from telco/provider
        'utility_airtime_min_amount' => '50.0',
        'utility_airtime_max_amount' => '50000.0',

        // Electricity
        'utility_electricity_enabled' => '1',
        'utility_electricity_fee_mode' => 'fixed', // 'fixed', 'percent', 'none'
        'utility_electricity_fee_value' => '100.0', // ₦100 flat convenience fee
        'utility_electricity_min_amount' => '500.0',
        'utility_electricity_max_amount' => '500000.0',

        // Betting
        'utility_betting_enabled' => '0', // Keep disabled until verified
        'utility_betting_fee_mode' => 'percent', // 'percent', 'fixed', 'none'
        'utility_betting_fee_value' => '1.5', // 1.5% processing fee
        'utility_betting_min_amount' => '100.0',
        'utility_betting_max_amount' => '500000.0',

        // Safety
        'utility_allow_negative_profit' => '0',
    ];

    /**
     * Get live or configured NGN per USD exchange rate.
     */
    public function getExchangeRate(): float
    {
        $rate = (float) (Setting::where('key', 'ngn_per_usd')->value('value') ?? 1500.0);
        return $rate > 0 ? $rate : 1500.0;
    }

    /**
     * Get a specific setting with default fallback.
     */
    public function getSetting(string $key, ?string $default = null): string
    {
        $val = Setting::where('key', $key)->value('value');
        if ($val !== null && $val !== '') {
            return (string) $val;
        }

        return $default !== null ? $default : (self::DEFAULTS[$key] ?? '');
    }

    /**
     * Get full configuration map for all utility pricing.
     */
    public function getAllSettings(): array
    {
        $keys = array_keys(self::DEFAULTS);
        $settings = Setting::whereIn('key', $keys)->pluck('value', 'key')->all();

        $merged = [];
        foreach (self::DEFAULTS as $k => $def) {
            $merged[$k] = isset($settings[$k]) && $settings[$k] !== '' ? $settings[$k] : $def;
        }

        return $merged;
    }

    /**
     * Update settings and log audit records for changed values.
     */
    public function updateSettings(array $inputs, ?int $adminId = null, ?string $adminName = null, ?string $adminEmail = null, ?string $ip = null, ?string $userAgent = null): array
    {
        $currentSettings = $this->getAllSettings();
        $updated = [];

        foreach (self::DEFAULTS as $key => $defaultVal) {
            if (!array_key_exists($key, $inputs)) {
                continue;
            }

            $newVal = (string) $inputs[$key];
            $prevVal = (string) ($currentSettings[$key] ?? $defaultVal);

            if ($newVal !== $prevVal) {
                Setting::updateOrCreate(['key' => $key], ['value' => $newVal]);

                // Determine service category
                $service = 'global';
                if (str_contains($key, 'data')) $service = 'data';
                elseif (str_contains($key, 'airtime')) $service = 'airtime';
                elseif (str_contains($key, 'electricity')) $service = 'electricity';
                elseif (str_contains($key, 'betting')) $service = 'betting';

                DB::table('utility_pricing_audit_logs')->insert([
                    'admin_id' => $adminId,
                    'admin_name' => $adminName,
                    'admin_email' => $adminEmail,
                    'service_type' => $service,
                    'setting_key' => $key,
                    'previous_value' => $prevVal,
                    'new_value' => $newVal,
                    'ip_address' => $ip,
                    'user_agent' => $userAgent,
                    'created_at' => now(),
                ]);

                $updated[$key] = ['old' => $prevVal, 'new' => $newVal];
            }
        }

        return $updated;
    }

    /**
     * 1. Calculate Data Bundle Pricing & Profit.
     */
    public function calculateDataPricing(float $wholesaleNgn, string $network = ''): array
    {
        $rate = $this->getExchangeRate();
        $enabled = (int) $this->getSetting('utility_data_enabled', '1') === 1;
        $mode = $this->getSetting('utility_data_pricing_mode', 'fixed');
        $markupVal = (float) $this->getSetting('utility_data_markup_value', '40.0');
        $min = (float) $this->getSetting('utility_data_min_amount', '50.0');
        $max = (float) $this->getSetting('utility_data_max_amount', '100000.0');

        if ($wholesaleNgn <= 0) {
            throw new InvalidArgumentException('Invalid wholesale price for data plan.');
        }

        $markupNgn = match ($mode) {
            'percent' => round($wholesaleNgn * ($markupVal / 100.0), 2),
            default => round($markupVal, 2),
        };

        if ($markupNgn < 0) {
            $markupNgn = 0.0;
        }

        $sellingPriceNgn = round($wholesaleNgn + $markupNgn, 2);

        if ($sellingPriceNgn < $min || $sellingPriceNgn > $max) {
            throw new InvalidArgumentException("Data plan price (₦{$sellingPriceNgn}) is outside allowable limits (₦{$min} - ₦{$max}).");
        }

        $totalCustomerNgn = $sellingPriceNgn;
        $totalCustomerUsd = round($totalCustomerNgn / $rate, 8);
        $providerCostNgn = round($wholesaleNgn, 2);
        $providerCostUsd = round($providerCostNgn / $rate, 8);
        $profitNgn = round($totalCustomerNgn - $providerCostNgn, 2);
        $profitUsd = round($profitNgn / $rate, 8);

        return [
            'service_type' => 'data',
            'is_enabled' => $enabled,
            'face_value_ngn' => $wholesaleNgn,
            'wholesale_cost_ngn' => $providerCostNgn,
            'wholesale_cost_usd' => $providerCostUsd,
            'markup_mode' => $mode,
            'markup_value' => $markupVal,
            'markup_ngn' => $markupNgn,
            'fee_ngn' => 0.0,
            'discount_ngn' => 0.0,
            'selling_price_ngn' => $sellingPriceNgn,
            'total_customer_ngn' => $totalCustomerNgn,
            'total_customer_usd' => $totalCustomerUsd,
            'profit_ngn' => $profitNgn,
            'profit_usd' => $profitUsd,
            'exchange_rate' => $rate,
            'snapshot' => [
                'service' => 'data',
                'network' => strtolower($network),
                'pricing_mode' => $mode,
                'markup_value' => $markupVal,
                'wholesale_ngn' => $providerCostNgn,
                'markup_ngn' => $markupNgn,
                'selling_price_ngn' => $sellingPriceNgn,
                'exchange_rate' => $rate,
                'calculated_at' => now()->toIso8601String(),
            ],
        ];
    }

    /**
     * Apply configured markup to a list of raw BigiSub data plans.
     */
    public function applyDataMarkupToPlans(array $plans, string $network = ''): array
    {
        $rate = $this->getExchangeRate();
        $markedPlans = [];

        foreach ($plans as $p) {
            $rawPrice = (float) ($p['plan_amount'] ?? ($p['price_ngn'] ?? ($p['price'] ?? 0)));
            if ($rawPrice <= 0) {
                continue;
            }

            try {
                $calc = $this->calculateDataPricing($rawPrice, $network);
                $p['wholesale_price_ngn'] = $calc['wholesale_cost_ngn'];
                $p['markup_ngn'] = $calc['markup_ngn'];
                $p['price_ngn'] = $calc['selling_price_ngn'];
                $p['price'] = $calc['selling_price_ngn'];
                $p['price_usd'] = round($calc['selling_price_ngn'] / $rate, 2);
                $markedPlans[] = $p;
            } catch (\Throwable $e) {
                // If pricing fails validation, retain original safely
                $p['wholesale_price_ngn'] = $rawPrice;
                $p['price_ngn'] = $rawPrice;
                $p['price'] = $rawPrice;
                $p['price_usd'] = round($rawPrice / $rate, 2);
                $markedPlans[] = $p;
            }
        }

        return $markedPlans;
    }

    /**
     * 2. Calculate Airtime Pricing & Profit.
     */
    public function calculateAirtimePricing(float $faceValueNgn, string $network = ''): array
    {
        $rate = $this->getExchangeRate();
        $enabled = (int) $this->getSetting('utility_airtime_enabled', '1') === 1;
        $discountPercent = (float) $this->getSetting('utility_airtime_discount_percent', '0.0');
        $feeMode = $this->getSetting('utility_airtime_fee_mode', 'none');
        $feeVal = (float) $this->getSetting('utility_airtime_fee_value', '0.0');
        $providerDiscountPercent = (float) $this->getSetting('utility_airtime_provider_discount_percent', '2.0');
        $min = (float) $this->getSetting('utility_airtime_min_amount', '50.0');
        $max = (float) $this->getSetting('utility_airtime_max_amount', '50000.0');
        $allowNegative = (int) $this->getSetting('utility_allow_negative_profit', '0') === 1;

        if ($faceValueNgn < $min || $faceValueNgn > $max) {
            throw new InvalidArgumentException("Airtime amount (₦{$faceValueNgn}) must be between ₦{$min} and ₦{$max}.");
        }

        // Customer discount (cashback)
        $discountNgn = $discountPercent > 0 ? round($faceValueNgn * ($discountPercent / 100.0), 2) : 0.0;

        // Customer service fee (if configured)
        $feeNgn = match ($feeMode) {
            'percent' => round($faceValueNgn * ($feeVal / 100.0), 2),
            'fixed' => round($feeVal, 2),
            default => 0.0,
        };

        $totalCustomerNgn = round(max(1.0, $faceValueNgn - $discountNgn + $feeNgn), 2);
        $sellingPriceNgn = round(max(1.0, $faceValueNgn - $discountNgn), 2);

        // Actual provider cost: What BigiSub debits (face value minus wholesale vendor discount)
        $providerCostNgn = round($faceValueNgn * (1.0 - ($providerDiscountPercent / 100.0)), 2);

        // Platform Gross Profit = Total Customer Charge - Actual Provider Cost
        $profitNgn = round($totalCustomerNgn - $providerCostNgn, 2);

        if ($profitNgn < 0 && !$allowNegative) {
            throw new InvalidArgumentException(
                "Configured airtime discount ({$discountPercent}%) causes negative profit (Loss of ₦" . abs($profitNgn) . "). Please adjust discount."
            );
        }

        $totalCustomerUsd = round($totalCustomerNgn / $rate, 8);
        $providerCostUsd = round($providerCostNgn / $rate, 8);
        $profitUsd = round($profitNgn / $rate, 8);

        return [
            'service_type' => 'airtime',
            'is_enabled' => $enabled,
            'face_value_ngn' => $faceValueNgn,
            'provider_discount_percent' => $providerDiscountPercent,
            'wholesale_cost_ngn' => $providerCostNgn,
            'wholesale_cost_usd' => $providerCostUsd,
            'discount_percent' => $discountPercent,
            'discount_ngn' => $discountNgn,
            'fee_mode' => $feeMode,
            'fee_value' => $feeVal,
            'fee_ngn' => $feeNgn,
            'selling_price_ngn' => $sellingPriceNgn,
            'total_customer_ngn' => $totalCustomerNgn,
            'total_customer_usd' => $totalCustomerUsd,
            'profit_ngn' => $profitNgn,
            'profit_usd' => $profitUsd,
            'exchange_rate' => $rate,
            'snapshot' => [
                'service' => 'airtime',
                'network' => strtolower($network),
                'face_value_ngn' => $faceValueNgn,
                'discount_percent' => $discountPercent,
                'discount_ngn' => $discountNgn,
                'fee_mode' => $feeMode,
                'fee_ngn' => $feeNgn,
                'provider_discount_percent' => $providerDiscountPercent,
                'provider_cost_ngn' => $providerCostNgn,
                'total_customer_ngn' => $totalCustomerNgn,
                'profit_ngn' => $profitNgn,
                'exchange_rate' => $rate,
                'calculated_at' => now()->toIso8601String(),
            ],
        ];
    }

    /**
     * 3. Calculate Electricity Bill Payment Pricing & Profit.
     */
    public function calculateElectricityPricing(float $amountNgn): array
    {
        $rate = $this->getExchangeRate();
        $enabled = (int) $this->getSetting('utility_electricity_enabled', '1') === 1;
        $feeMode = $this->getSetting('utility_electricity_fee_mode', 'fixed');
        $feeVal = (float) $this->getSetting('utility_electricity_fee_value', '100.0');
        $min = (float) $this->getSetting('utility_electricity_min_amount', '500.0');
        $max = (float) $this->getSetting('utility_electricity_max_amount', '500000.0');

        if ($amountNgn < $min || $amountNgn > $max) {
            throw new InvalidArgumentException("Electricity payment (₦{$amountNgn}) must be between ₦{$min} and ₦{$max}.");
        }

        $feeNgn = match ($feeMode) {
            'percent' => round($amountNgn * ($feeVal / 100.0), 2),
            'fixed' => round($feeVal, 2),
            default => 0.0,
        };

        // Customer pays: Face Value + Convenience Fee
        $totalCustomerNgn = round($amountNgn + $feeNgn, 2);

        // Provider is sent ONLY the Face Value ($amountNgn)
        $providerCostNgn = round($amountNgn, 2);

        // Platform Gross Profit = Customer Convenience Fee
        $profitNgn = round($totalCustomerNgn - $providerCostNgn, 2);

        $totalCustomerUsd = round($totalCustomerNgn / $rate, 8);
        $providerCostUsd = round($providerCostNgn / $rate, 8);
        $profitUsd = round($profitNgn / $rate, 8);

        return [
            'service_type' => 'electricity',
            'is_enabled' => $enabled,
            'face_value_ngn' => $amountNgn,
            'wholesale_cost_ngn' => $providerCostNgn,
            'wholesale_cost_usd' => $providerCostUsd,
            'fee_mode' => $feeMode,
            'fee_value' => $feeVal,
            'fee_ngn' => $feeNgn,
            'discount_ngn' => 0.0,
            'selling_price_ngn' => $totalCustomerNgn,
            'total_customer_ngn' => $totalCustomerNgn,
            'total_customer_usd' => $totalCustomerUsd,
            'profit_ngn' => $profitNgn,
            'profit_usd' => $profitUsd,
            'exchange_rate' => $rate,
            'snapshot' => [
                'service' => 'electricity',
                'meter_amount_ngn' => $amountNgn,
                'fee_mode' => $feeMode,
                'fee_value' => $feeVal,
                'convenience_fee_ngn' => $feeNgn,
                'provider_cost_ngn' => $providerCostNgn,
                'total_customer_ngn' => $totalCustomerNgn,
                'profit_ngn' => $profitNgn,
                'exchange_rate' => $rate,
                'calculated_at' => now()->toIso8601String(),
            ],
        ];
    }

    /**
     * 4. Calculate Betting Wallet Funding Pricing & Profit.
     */
    public function calculateBettingPricing(float $amountNgn): array
    {
        $rate = $this->getExchangeRate();
        $enabled = (int) $this->getSetting('utility_betting_enabled', '0') === 1;
        $feeMode = $this->getSetting('utility_betting_fee_mode', 'percent');
        $feeVal = (float) $this->getSetting('utility_betting_fee_value', '1.5');
        $min = (float) $this->getSetting('utility_betting_min_amount', '100.0');
        $max = (float) $this->getSetting('utility_betting_max_amount', '500000.0');

        if ($amountNgn < $min || $amountNgn > $max) {
            throw new InvalidArgumentException("Betting wallet funding (₦{$amountNgn}) must be between ₦{$min} and ₦{$max}.");
        }

        $feeNgn = match ($feeMode) {
            'percent' => round($amountNgn * ($feeVal / 100.0), 2),
            'fixed' => round($feeVal, 2),
            default => 0.0,
        };

        // Customer pays: Funding amount + Processing fee
        $totalCustomerNgn = round($amountNgn + $feeNgn, 2);

        // Provider is sent ONLY the punter's funding amount ($amountNgn)
        $providerCostNgn = round($amountNgn, 2);

        // Platform Gross Profit = Processing Fee
        $profitNgn = round($totalCustomerNgn - $providerCostNgn, 2);

        $totalCustomerUsd = round($totalCustomerNgn / $rate, 8);
        $providerCostUsd = round($providerCostNgn / $rate, 8);
        $profitUsd = round($profitNgn / $rate, 8);

        return [
            'service_type' => 'betting',
            'is_enabled' => $enabled,
            'face_value_ngn' => $amountNgn,
            'wholesale_cost_ngn' => $providerCostNgn,
            'wholesale_cost_usd' => $providerCostUsd,
            'fee_mode' => $feeMode,
            'fee_value' => $feeVal,
            'fee_ngn' => $feeNgn,
            'discount_ngn' => 0.0,
            'selling_price_ngn' => $totalCustomerNgn,
            'total_customer_ngn' => $totalCustomerNgn,
            'total_customer_usd' => $totalCustomerUsd,
            'profit_ngn' => $profitNgn,
            'profit_usd' => $profitUsd,
            'exchange_rate' => $rate,
            'snapshot' => [
                'service' => 'betting',
                'deposit_amount_ngn' => $amountNgn,
                'fee_mode' => $feeMode,
                'fee_value' => $feeVal,
                'processing_fee_ngn' => $feeNgn,
                'provider_cost_ngn' => $providerCostNgn,
                'total_customer_ngn' => $totalCustomerNgn,
                'profit_ngn' => $profitNgn,
                'exchange_rate' => $rate,
                'calculated_at' => now()->toIso8601String(),
            ],
        ];
    }
}
