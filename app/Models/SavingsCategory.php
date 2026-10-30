<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavingsCategory extends Model
{
    use HasFactory;

    protected $attributes = [
        'early_withdrawal_fee_percent' => 2.50,
        'early_withdrawal_interest_forfeited_percent' => 100.00,
    ];

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'description',
        'default_annual_rate',
        'is_interest_enabled',
        'min_deposit_ngn',
        'min_deposit_usd',
        'min_duration_days',
        'max_duration_days',
        'allow_early_withdrawal',
        'allow_partial_withdrawal',
        'early_withdrawal_fee_percent',
        'early_withdrawal_interest_forfeited_percent',
        'maturity_fee_percent',
        'is_enabled',
        'order_index',
    ];

    protected $casts = [
        'default_annual_rate' => 'float',
        'is_interest_enabled' => 'boolean',
        'min_deposit_ngn' => 'float',
        'min_deposit_usd' => 'float',
        'min_duration_days' => 'integer',
        'max_duration_days' => 'integer',
        'allow_early_withdrawal' => 'boolean',
        'allow_partial_withdrawal' => 'boolean',
        'early_withdrawal_fee_percent' => 'float',
        'early_withdrawal_interest_forfeited_percent' => 'float',
        'maturity_fee_percent' => 'float',
        'is_enabled' => 'boolean',
        'order_index' => 'integer',
    ];

    protected $appends = [
        'effective_rate',
    ];

    /**
     * Check if interest is globally and category-wise enabled.
     */
    public function isInterestActive(): bool
    {
        $globalEnabled = Setting::where('key', 'savings_global_interest_enabled')->value('value');
        if ($globalEnabled !== null && (string)$globalEnabled === '0') {
            return false;
        }

        return (bool) ($this->is_interest_enabled ?? true);
    }

    /**
     * Effective rate taking global and category interest toggles into account.
     */
    public function getEffectiveRateAttribute(): float
    {
        if (! $this->isInterestActive()) {
            return 0.00;
        }

        return (float) ($this->default_annual_rate ?? 0.00);
    }

    public function plans()
    {
        return $this->hasMany(SavingsPlan::class, 'category_id');
    }
}
