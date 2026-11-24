<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeasonalGift extends Model
{
    protected $fillable = [
        'seasonal_mode_id',
        'name',
        'description',
        'icon_or_image',
        'gift_type',
        'reward_value_usd',
        'promo_code',
        'discount_percent',
        'distribution_mode',
        'claim_button_text',
        'max_total_claims',
        'max_claims_per_user',
        'claimed_count',
        'total_distributed_usd',
        'min_account_age_days',
        'min_wallet_funding_usd',
        'min_transactions_count',
        'eligible_user_ids',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'reward_value_usd' => 'float',
        'discount_percent' => 'float',
        'max_total_claims' => 'integer',
        'max_claims_per_user' => 'integer',
        'claimed_count' => 'integer',
        'total_distributed_usd' => 'float',
        'min_account_age_days' => 'integer',
        'min_wallet_funding_usd' => 'float',
        'min_transactions_count' => 'integer',
        'eligible_user_ids' => 'array',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function mode()
    {
        return $this->belongsTo(SeasonalMode::class, 'seasonal_mode_id');
    }

    public function claims()
    {
        return $this->hasMany(SeasonalGiftClaim::class, 'seasonal_gift_id');
    }

    public function remainingClaims(): ?int
    {
        if ($this->max_total_claims <= 0) {
            return null;
        }

        return max(0, $this->max_total_claims - $this->claimed_count);
    }

    public function isCurrentlyAvailable(): bool
    {
        if (! $this->is_active) {
            return false;
        }
        $now = now();
        if ($this->starts_at && $now->lt($this->starts_at)) {
            return false;
        }
        if ($this->expires_at && $now->gt($this->expires_at)) {
            return false;
        }
        if ($this->max_total_claims > 0 && $this->claimed_count >= $this->max_total_claims) {
            return false;
        }

        return true;
    }
}

