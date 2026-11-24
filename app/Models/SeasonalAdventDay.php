<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeasonalAdventDay extends Model
{
    protected $fillable = [
        'day_number',
        'title',
        'description',
        'icon',
        'reward_type',
        'reward_value_usd',
        'promo_code',
        'discount_percent',
        'unlock_date',
        'force_unlocked',
        'is_enabled',
        'claimed_count',
    ];

    protected $casts = [
        'day_number' => 'integer',
        'reward_value_usd' => 'float',
        'discount_percent' => 'float',
        'unlock_date' => 'date',
        'force_unlocked' => 'boolean',
        'is_enabled' => 'boolean',
        'claimed_count' => 'integer',
    ];

    public function claims()
    {
        return $this->hasMany(SeasonalAdventClaim::class, 'advent_day_id');
    }

    public function isUnlockedNow(): bool
    {
        if (! $this->is_enabled) {
            return false;
        }
        if ($this->force_unlocked) {
            return true;
        }
        if ($this->unlock_date) {
            return now()->toDateString() >= $this->unlock_date->toDateString();
        }
        // Default: Day 1 is always unlocked for immediate festive engagement, or if in December up to current day
        if ($this->day_number === 1) {
            return true;
        }
        if ((int) now()->month === 12 && (int) now()->day >= $this->day_number) {
            return true;
        }

        return false;
    }
}
