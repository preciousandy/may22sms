<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceStatusAuditLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'service_id',
        'service_key',
        'admin_id',
        'admin_name',
        'admin_email',
        'previous_status',
        'new_status',
        'reason',
        'maintenance_message',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'previous_status' => 'boolean',
        'new_status' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(ServiceConfiguration::class, 'service_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
