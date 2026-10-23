<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class DailyLoginGift extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount_usd',
        'streak',
        'claimed_date',
    ];

    protected $casts = [
        'amount_usd' => 'decimal:4',
        'streak' => 'integer',
        'claimed_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if a user has already claimed their gift today.
     */
    public static function hasClaimedToday(int $userId): bool
    {
        $today = Carbon::today()->toDateString();

        return static::where('user_id', $userId)
            ->whereDate('claimed_date', $today)
            ->exists();
    }

    /**
     * Get the total count of users who have claimed today.
     */
    public static function getTodayClaimsCount(): int
    {
        $today = Carbon::today()->toDateString();

        return static::whereDate('claimed_date', $today)->count();
    }

    /**
     * Determine next streak level (1 through 7) based on yesterday's check-in.
     */
    public static function calculateNextStreak(int $userId): int
    {
        $yesterday = Carbon::yesterday()->toDateString();

        $lastClaim = static::where('user_id', $userId)
            ->orderByDesc('claimed_date')
            ->first();

        if (! $lastClaim) {
            return 1;
        }

        $lastDate = Carbon::parse($lastClaim->claimed_date)->toDateString();

        if ($lastDate === $yesterday) {
            // Consecutive day: advance streak (cycle 1-7)
            $next = ((int) $lastClaim->streak) + 1;
            return $next > 7 ? 1 : $next;
        }

        // Streak broken: reset to 1
        return 1;
    }
}
