<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeasonalUserBadge extends Model
{
    protected $fillable = [
        'seasonal_badge_id',
        'user_id',
        'earned_at',
    ];

    protected $casts = [
        'earned_at' => 'datetime',
    ];

    public function badge()
    {
        return $this->belongsTo(SeasonalBadge::class, 'seasonal_badge_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

