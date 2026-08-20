<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FootballMatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'home_team',
        'away_team',
        'competition',
        'match_time',
        'home_team_logo',
        'away_team_logo',
        'banner_image',
        'is_active',
        'home_score',
        'away_score',
    ];

    protected $casts = [
        'match_time' => 'datetime',
        'is_active' => 'boolean',
        'home_score' => 'integer',
        'away_score' => 'integer',
    ];
}
