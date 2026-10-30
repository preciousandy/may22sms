<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CryptoWebhook extends Model
{
    protected $fillable = [
        'event',
        'event_id',
        'public_id',
        'merchant_reference',
        'signature',
        'payload',
        'processed',
        'processed_at',
        'error',
    ];

    protected $casts = [
        'payload' => 'array',
        'processed' => 'boolean',
        'processed_at' => 'datetime',
    ];
}
