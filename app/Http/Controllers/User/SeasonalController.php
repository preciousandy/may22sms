<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\SeasonalGift;
use App\Models\SeasonalGiftClaim;
use App\Models\SeasonalUserPreference;
use App\Services\SeasonalEngineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SeasonalController extends Controller
{
    protected SeasonalEngineService $engine;

    public function __construct(SeasonalEngineService $engine)
    {
        $this->engine = $engine;
    }

    /**
     * Claim an active seasonal gift for the authenticated user.
     */
    public function claimGift(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Please sign in to claim this gift.'], 401);
        }

        // Prevent rapid double-clicking race conditions with an atomic lock per user+gift
        $lock = Cache::lock("seasonal_claim:u{$user->id}:g{$id}", 15);
        if (! $lock->get()) {
            return response()->json([
                'success' => false,
                'message' => 'Your gift claim is already being processed. Please wait a moment.',
            ], 429);
        }

        try {
            $gift = SeasonalGift::findOrFail($id);
            $result = $this->engine->claimGift(
                $user,
                $gift,
                'manual_claim',
                (string) $request->ip(),
                (string) $request->userAgent()
            );

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'reward_usd' => $result['reward_usd'],
                'reward_code' => $result['reward_code'],
                'new_balance_usd' => $result['new_balance_usd'],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Could not claim this seasonal gift.',
            ], 400);
        } finally {
            optional($lock)->release();
        }
    }

    /**
     * Toggle user's personal seasonal animation preference (accessibility / performance control).
     */
    public function toggleAnimations(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user || ! Schema::hasTable('seasonal_user_preferences')) {
            return response()->json(['success' => false], 400);
        }

        $pref = SeasonalUserPreference::firstOrCreate(
            ['user_id' => $user->id],
            ['animations_disabled' => false, 'dismissed_announcements' => []]
        );

        $disabled = $request->has('disabled')
            ? $request->boolean('disabled')
            : ! $pref->animations_disabled;

        $pref->update(['animations_disabled' => $disabled]);

        return response()->json([
            'success' => true,
            'animations_disabled' => $disabled,
        ]);
    }

    /**
     * Dismiss a seasonal announcement for the current user.
     */
    public function dismissAnnouncement(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (! $user || ! Schema::hasTable('seasonal_user_preferences')) {
            return response()->json(['success' => true]);
        }

        $pref = SeasonalUserPreference::firstOrCreate(
            ['user_id' => $user->id],
            ['animations_disabled' => false, 'dismissed_announcements' => []]
        );

        $dismissed = array_values(array_unique(array_merge(
            array_map('intval', (array) ($pref->dismissed_announcements ?? [])),
            [$id]
        )));

        $pref->update(['dismissed_announcements' => $dismissed]);

        return response()->json([
            'success' => true,
            'dismissed_id' => $id,
        ]);
    }

    /**
     * Return user's seasonal gift claim history.
     */
    public function history(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user || ! Schema::hasTable('seasonal_gift_claims')) {
            return response()->json(['success' => true, 'claims' => []]);
        }

        $claims = SeasonalGiftClaim::with(['gift:id,name,gift_type,icon_or_image', 'mode:id,name,icon'])
            ->where('user_id', $user->id)
            ->orderByDesc('claimed_at')
            ->limit(25)
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'gift_name' => $c->gift->name ?? ($c->meta['gift_name'] ?? 'Seasonal Gift'),
                    'mode_name' => $c->mode->name ?? 'Celebration',
                    'mode_icon' => $c->mode->icon ?? '🎉',
                    'gift_type' => $c->gift_type,
                    'reward_value_usd' => (float) $c->reward_value_usd,
                    'reward_code' => $c->reward_code,
                    'reference' => $c->reference,
                    'claimed_at' => optional($c->claimed_at)->format('M d, Y H:i'),
                ];
            });

        return response()->json([
            'success' => true,
            'claims' => $claims,
        ]);
    }
}

