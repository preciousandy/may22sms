<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavingsAuditLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'savings_plan_id',
        'user_id',
        'actor_id',
        'event',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    public function plan()
    {
        return $this->belongsTo(SavingsPlan::class, 'savings_plan_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public static function record(?int $planId, ?int $userId, ?int $actorId, string $event, ?array $old = null, ?array $new = null): self
    {
        return self::create([
            'savings_plan_id' => $planId,
            'user_id' => $userId,
            'actor_id' => $actorId,
            'event' => $event,
            'old_values' => $old,
            'new_values' => $new,
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'user_agent' => request()->userAgent() ?? 'CLI/System',
            'created_at' => now(),
        ]);
    }
}
