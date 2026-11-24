<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeasonalGiftClaim extends Model
{
    protected $fillable = [
        'seasonal_gift_id',
        'seasonal_mode_id',
        'user_id',
        'gift_type',
        'reward_value_usd',
        'reward_code',
        'distribution_method',
        'reference',
        'ip_address',
        'user_agent',
        'meta',
        'claimed_at',
    ];

    protected $casts = [
        'reward_value_usd' => 'float',
        'meta' => 'array',
        'claimed_at' => 'datetime',
    ];

    public function gift()
    {
        return $this->belongsTo(SeasonalGift::class, 'seasonal_gift_id');
    }

    public function mode()
    {
        return $this->belongsTo(SeasonalMode::class, 'seasonal_mode_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

