<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeasonalMode extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'theme_type',
        'icon',
        'greeting_title',
        'greeting_subtitle',
        'badge_text',
        'primary_color',
        'secondary_color',
        'accent_color',
        'banner_gradient',
        'banner_image',
        'background_image',
        'animation_type',
        'animation_intensity',
        'animations_enabled',
        'decorations_enabled',
        'banner_enabled',
        'countdown_enabled',
        'countdown_target_at',
        'display_pages',
        'visual_effects',
        'is_active',
        'is_scheduled',
        'manual_override',
        'starts_at',
        'ends_at',
        'is_system_default',
    ];

    protected $casts = [
        'animations_enabled' => 'boolean',
        'decorations_enabled' => 'boolean',
        'banner_enabled' => 'boolean',
        'countdown_enabled' => 'boolean',
        'countdown_target_at' => 'datetime',
        'display_pages' => 'array',
        'visual_effects' => 'array',
        'is_active' => 'boolean',
        'is_scheduled' => 'boolean',
        'manual_override' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_system_default' => 'boolean',
    ];

    public function gifts()
    {
        return $this->hasMany(SeasonalGift::class, 'seasonal_mode_id');
    }

    public function announcements()
    {
        return $this->hasMany(SeasonalAnnouncement::class, 'seasonal_mode_id');
    }

    public function claims()
    {
        return $this->hasMany(SeasonalGiftClaim::class, 'seasonal_mode_id');
    }

    public function shouldDisplayOnPage(string $pageKey): bool
    {
        $pages = (array) ($this->display_pages ?? ['all']);
        if (empty($pages) || in_array('all', $pages, true)) {
            return true;
        }

        return in_array($pageKey, $pages, true);
    }
}

