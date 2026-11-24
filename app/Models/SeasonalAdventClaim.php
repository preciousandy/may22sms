<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeasonalAdventClaim extends Model
{
    protected $fillable = [
        'advent_day_id',
        'day_number',
        'user_id',
        'reward_type',
        'reward_value_usd',
        'reward_code',
        'reference',
        'claimed_at',
    ];

    protected $casts = [
        'day_number' => 'integer',
        'reward_value_usd' => 'float',
        'claimed_at' => 'datetime',
    ];

    public function day()
    {
        return $this->belongsTo(SeasonalAdventDay::class, 'advent_day_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

