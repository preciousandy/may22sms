<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoostService extends Model
{
    const AUTO_DEACTIVATE_THRESHOLD = 3;

    protected $fillable = [
        'jap_service_id',
        'name',
        'category',
        'type',
        'rate',
        'min',
        'max',
        'is_active',
        'price_override',
        'fail_count',
        'last_error',
        'last_failed_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'fail_count' => 'integer',
        'last_failed_at' => 'datetime',
    ];

    public function orders()
    {
        return $this->hasMany(BoostOrder::class);
    }

    public function finalRate(float $markupPercent): float
    {
        if ($this->price_override !== null) {
            return (float) $this->price_override;
        }

        $rate = (float) $this->rate;
        $multiplier = 1 + ($markupPercent / 100);

        return $rate * $multiplier;
    }

    public function recordSuccess(): void
    {
        $this->fail_count = 0;
        $this->last_error = null;
        $this->last_failed_at = null;
        $this->save();
    }

    public function recordFailure(string $error, bool $isServiceError = false): bool
    {
        $this->fail_count = (int) $this->fail_count + 1;
        $this->last_error = $error;
        $this->last_failed_at = now();

        $deactivated = false;
        if ($isServiceError || $this->fail_count >= self::AUTO_DEACTIVATE_THRESHOLD) {
            $this->is_active = false;
            $deactivated = true;
        }

        $this->save();

        return $deactivated;
    }

    public function resetFailureStats(): void
    {
        $this->fail_count = 0;
        $this->last_error = null;
        $this->last_failed_at = null;
        $this->save();
    }

    public function hasRecentFailures(int $minutes = 60): bool
    {
        if ($this->fail_count === 0 || $this->last_failed_at === null) {
            return false;
        }

        return $this->last_failed_at->diffInMinutes(now()) <= $minutes;
    }
}
