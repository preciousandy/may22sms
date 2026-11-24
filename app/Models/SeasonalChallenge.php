<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeasonalChallenge extends Model
{
    protected $fillable = [
        'code',
        'title',
        'description',
        'icon',
        'period_type',
        'action_type',
        'target_count',
        'reward_value_usd',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'target_count' => 'integer',
        'reward_value_usd' => 'float',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];
}
