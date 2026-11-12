<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UtilityPricingAuditLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'utility_pricing_audit_logs';

    protected $fillable = [
        'admin_id',
        'admin_name',
        'admin_email',
        'service_type',
        'setting_key',
        'previous_value',
        'new_value',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
