<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    protected $fillable = [
        'code',
        'title',
        'message',
        'applies_to',
        'type',
        'value',
        'max_uses',
        'used_count',
        'is_active',
        'is_public',
        'expires_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'expires_at' => 'datetime',
        'applies_to' => 'array',
    ];

    public function redemptions()
    {
        return $this->hasMany(PromoRedemption::class);
    }
}
