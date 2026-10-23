<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\DailyLoginGift;
use App\Models\Setting;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DailyGiftController extends Controller
{
    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Get the current user's daily gift status, streak, and daily quota info.
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();
        $today = Carbon::today()->toDateString();

        $enabled = (int) (Setting::where('key', 'daily_gift_enabled')->value('value') ?? 1) === 1;
        $maxClaimsPerDay = (int) (Setting::where('key', 'daily_gift_max_claims_per_day')->value('value') ?? 0);
        $todayClaimsCount = DailyLoginGift::getTodayClaimsCount();
        $isPoolExhausted = ($maxClaimsPerDay > 0 && $todayClaimsCount >= $maxClaimsPerDay);

        $todayClaim = DailyLoginGift::where('user_id', $user->id)
            ->whereDate('claimed_date', $today)
            ->first();

        $hasClaimedToday = (bool) $todayClaim;
        $currentStreak = $hasClaimedToday 
            ? (int) $todayClaim->streak 
            : DailyLoginGift::calculateNextStreak($user->id);

        $type = (string) (Setting::where('key', 'daily_gift_type')->value('value') ?? 'streak');
        $fixedAmount = (float) (Setting::where('key', 'daily_gift_fixed_amount')->value('value') ?? 0.05);
        $minAmount = (float) (Setting::where('key', 'daily_gift_min_amount')->value('value') ?? 0.02);
        $maxAmount = (float) (Setting::where('key', 'daily_gift_max_amount')->value('value') ?? 0.10);

        $streakAmounts = $this->getStreakAmounts();

        // Seconds until midnight UTC reset
        $secondsUntilReset = max(0, Carbon::tomorrow('UTC')->diffInSeconds(Carbon::now('UTC')));

        return response()->json([
            'success' => true,
            'enabled' => $enabled,
            'has_claimed_today' => $hasClaimedToday,
            'streak' => $currentStreak,
            'today_claimed_amount' => $todayClaim ? (float) $todayClaim->amount_usd : null,
            'max_claims_per_day' => $maxClaimsPerDay,
            'today_claims_count' => $todayClaimsCount,
            'pool_exhausted' => $isPoolExhausted && ! $hasClaimedToday,
            'remaining_pool' => $maxClaimsPerDay > 0 ? max(0, $maxClaimsPerDay - $todayClaimsCount) : null,
            'type' => $type,
            'fixed_amount' => $fixedAmount,
            'min_amount' => $minAmount,
            'max_amount' => $maxAmount,
            'streak_amounts' => $streakAmounts,
            'seconds_until_reset' => $secondsUntilReset,
        ]);
    }

    /**
     * Claim today's daily login gift.
     */
    public function claim(Request $request): JsonResponse
    {
        $user = $request->user();

        // 1. Verify feature is enabled
        $enabled = (int) (Setting::where('key', 'daily_gift_enabled')->value('value') ?? 1) === 1;
        if (! $enabled) {
            return response()->json([
                'success' => false,
                'message' => 'Daily login gifts are currently disabled.',
            ], 403);
        }

        $today = Carbon::today()->toDateString();
        $reference = 'GIFT-' . $user->id . '-' . $today;

        // 2. Perform atomic claim transaction
        try {
            $result = DB::transaction(function () use ($user, $today, $reference) {
                // Lock and verify user has not claimed today
                $alreadyClaimed = DailyLoginGift::where('user_id', $user->id)
                    ->whereDate('claimed_date', $today)
                    ->lockForUpdate()
                    ->exists();

                if ($alreadyClaimed) {
                    return [
                        'status' => 'already_claimed',
                        'message' => 'You have already claimed your daily gift today! Check back tomorrow.',
                    ];
                }

                // Check daily limit cap
                $maxClaimsPerDay = (int) (Setting::where('key', 'daily_gift_max_claims_per_day')->value('value') ?? 0);
                if ($maxClaimsPerDay > 0) {
                    $todayCount = DailyLoginGift::whereDate('claimed_date', $today)->lockForUpdate()->count();
                    if ($todayCount >= $maxClaimsPerDay) {
                        return [
                            'status' => 'pool_exhausted',
                            'message' => "Today's gift pool limit ({$maxClaimsPerDay} users) has been reached! Please check back tomorrow at midnight UTC.",
                        ];
                    }
                }

                $streak = DailyLoginGift::calculateNextStreak($user->id);
                $amount = $this->calculateRewardAmount($streak);

                // Credit user wallet atomically
                $this->walletService->credit(
                    $user,
                    $amount,
                    'daily_gift',
                    $reference,
                    [
                        'streak' => $streak,
                        'claimed_date' => $today,
                    ]
                );

                // Record daily claim
                $gift = DailyLoginGift::create([
                    'user_id' => $user->id,
                    'amount_usd' => $amount,
                    'streak' => $streak,
                    'claimed_date' => $today,
                ]);

                $freshUser = User::find($user->id);

                return [
                    'status' => 'success',
                    'amount' => $amount,
                    'streak' => $streak,
                    'new_balance' => (float) ($freshUser->walletBalance ?? 0),
                    'message' => "🎁 Congratulations! You claimed $" . number_format($amount, 2) . " (Day {$streak} Streak)!",
                ];
            });

            if ($result['status'] === 'already_claimed' || $result['status'] === 'pool_exhausted') {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 400);
            }

            return response()->json([
                'success' => true,
                'amount' => $result['amount'],
                'streak' => $result['streak'],
                'new_balance' => $result['new_balance'],
                'message' => $result['message'],
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to claim daily gift at this time: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate reward amount based on admin settings.
     */
    protected function calculateRewardAmount(int $streak): float
    {
        $type = (string) (Setting::where('key', 'daily_gift_type')->value('value') ?? 'streak');

        if ($type === 'fixed') {
            $amt = (float) (Setting::where('key', 'daily_gift_fixed_amount')->value('value') ?? 0.05);
            return max(0.01, round($amt, 4));
        }

        if ($type === 'random') {
            $min = (float) (Setting::where('key', 'daily_gift_min_amount')->value('value') ?? 0.02);
            $max = (float) (Setting::where('key', 'daily_gift_max_amount')->value('value') ?? 0.10);
            if ($max < $min) {
                $max = $min;
            }
            $cents = mt_rand((int) round($min * 100), (int) round($max * 100));
            return max(0.01, round($cents / 100, 4));
        }

        // Streak mode
        $streakAmounts = $this->getStreakAmounts();
        $streakKey = min(7, max(1, $streak));

        return (float) ($streakAmounts[$streakKey] ?? 0.05);
    }

    /**
     * Get streak amount configuration for Day 1 through 7.
     */
    protected function getStreakAmounts(): array
    {
        $default = [
            1 => 0.02,
            2 => 0.04,
            3 => 0.06,
            4 => 0.08,
            5 => 0.10,
            6 => 0.15,
            7 => 0.25,
        ];

        $raw = Setting::where('key', 'daily_gift_streak_amounts')->value('value');
        if (! $raw) {
            return $default;
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return $default;
        }

        $result = [];
        for ($i = 1; $i <= 7; $i++) {
            $result[$i] = isset($decoded[$i]) ? max(0.001, (float) $decoded[$i]) : $default[$i];
        }

        return $result;
    }
}
