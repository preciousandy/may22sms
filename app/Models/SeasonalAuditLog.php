<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeasonalAuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'actor_id',
        'actor_name',
        'action',
        'target_type',
        'target_id',
        'summary',
        'payload',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'created_at' => 'datetime',
    ];
}

