<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FundingTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'promo_code_id',
        'payment_method',
        'provider',
        'provider_invoice_id',
        'provider_payment_id',
        'payment_url',
        'pay_currency',
        'currency',
        'amount',
        'amount_usd',
        'rate_ngn_per_usd',
        'reference',
        'status',
        'approved_at',
        'rejected_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function getChargePercent(string $paymentMethod): float
    {
        $chargeEnabled = (int) (Setting::where('key', 'funding_charge_enabled')->value('value') ?? 0) === 1;
        if (! $chargeEnabled) {
            return 0.0;
        }

        $methodKey = 'payment_method_' . strtolower($paymentMethod) . '_charge_percent';
        $specificCharge = Setting::where('key', $methodKey)->value('value');

        if ($specificCharge !== null && $specificCharge !== '' && (float) $specificCharge > 0) {
            return (float) $specificCharge;
        }

        $globalCharge = Setting::where('key', 'funding_charge_percentage')->value('value');
        if ($globalCharge !== null && $globalCharge !== '' && (float) $globalCharge > 0) {
            return (float) $globalCharge;
        }

        return 0.0;
    }

    public static function calculateNetAndFee(float $amountNgn, string $paymentMethod, float $ngnPerUsd): array
    {
        $chargePercent = static::getChargePercent($paymentMethod);
        $feeNgn = round($amountNgn * ($chargePercent / 100), 2);
        $netNgn = max(0.0, $amountNgn - $feeNgn);
        $netUsd = $ngnPerUsd > 0 ? round($netNgn / $ngnPerUsd, 8) : 0.0;

        return [
            'gross_ngn' => $amountNgn,
            'fee_percent' => $chargePercent,
            'fee_ngn' => $feeNgn,
            'net_ngn' => $netNgn,
            'net_usd' => $netUsd,
        ];
    }
}
