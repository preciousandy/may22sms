<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeasonalAnnouncement extends Model
{
    protected $fillable = [
        'seasonal_mode_id',
        'title',
        'message',
        'badge_text',
        'cta_text',
        'cta_link',
        'is_dismissible',
        'show_on_dashboard',
        'show_on_home',
        'send_in_app_notification',
        'send_email_notification',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'is_dismissible' => 'boolean',
        'show_on_dashboard' => 'boolean',
        'show_on_home' => 'boolean',
        'send_in_app_notification' => 'boolean',
        'send_email_notification' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function mode()
    {
        return $this->belongsTo(SeasonalMode::class, 'seasonal_mode_id');
    }

    public function isCurrentlyVisible(): bool
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

        return true;
    }
}

