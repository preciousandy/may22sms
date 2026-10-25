<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'category',
        'icon',
        'description',
        'is_enabled',
        'maintenance_message',
        'updated_by',
        'last_status_changed_at',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'last_status_changed_at' => 'datetime',
    ];

    public function updatedByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(ServiceStatusAuditLog::class, 'service_id')->latest();
    }

    public function isAvailable(): bool
    {
        return (bool) $this->is_enabled;
    }
}
