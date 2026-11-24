<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeasonalUserChallenge extends Model
{
    protected $fillable = [
        'seasonal_challenge_id',
        'user_id',
        'progress_count',
        'is_completed',
        'is_reward_claimed',
        'claim_reference',
        'completed_at',
        'claimed_at',
    ];

    protected $casts = [
        'progress_count' => 'integer',
        'is_completed' => 'boolean',
        'is_reward_claimed' => 'boolean',
        'completed_at' => 'datetime',
        'claimed_at' => 'datetime',
    ];

    public function challenge()
    {
        return $this->belongsTo(SeasonalChallenge::class, 'seasonal_challenge_id');
    }
}
