<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeasonalUserPreference extends Model
{
    protected $fillable = [
        'user_id',
        'animations_disabled',
        'dismissed_announcements',
    ];

    protected $casts = [
        'animations_disabled' => 'boolean',
        'dismissed_announcements' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

