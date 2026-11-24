<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeasonalBadge extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'icon',
        'condition_type',
        'condition_target',
        'is_active',
    ];

    protected $casts = [
        'condition_target' => 'integer',
        'is_active' => 'boolean',
    ];
}

