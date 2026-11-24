<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\SeasonalAdventClaim;
use App\Models\SeasonalAdventDay;
use App\Models\SeasonalBadge;
use App\Models\SeasonalChallenge;
use App\Models\SeasonalGift;
use App\Models\SeasonalGiftClaim;
use App\Models\SeasonalMode;
use App\Models\SeasonalUserBadge;
use App\Models\SeasonalUserChallenge;
use App\Models\SeasonalUserCreation;
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

    /**
     * Render the dedicated MAY22SMS Interactive Christmas Wonderland Hub.
     */
    public function christmasHub(Request $request)
    {
        $user = $request->user();
        $this->engine->ensureDefaultsSeeded();
        $this->engine->ensureInteractiveChristmasSeeded();

        $featureSettings = $this->engine->getInteractiveFeatureSettings();

        // Record visit activity (auto-completes "Visit Christmas Wonderland" challenge & "Christmas Star" badge)
        if ($user) {
            $this->engine->recordActivityAndEvaluate($user, 'visit_christmas_hub');
            if (! empty(trim((string) $user->phone_number))) {
                $this->engine->recordActivityAndEvaluate($user, 'complete_profile');
            }
        }

        $christmasMode = SeasonalMode::where('slug', 'christmas-mode')->first()
            ?: SeasonalMode::where('theme_type', 'christmas')->first();

        $adventDays = Schema::hasTable('seasonal_advent_days')
            ? SeasonalAdventDay::orderBy('day_number')->get()
            : collect();

        $claimedAdventDays = ($user && Schema::hasTable('seasonal_advent_claims'))
            ? SeasonalAdventClaim::where('user_id', $user->id)->pluck('day_number')->map(fn ($d) => (int) $d)->toArray()
            : [];

        $mysteryGifts = Schema::hasTable('seasonal_gifts')
            ? SeasonalGift::where('is_active', true)->orderBy('id')->get()
            : collect();

        $claimedGiftIds = ($user && Schema::hasTable('seasonal_gift_claims'))
            ? SeasonalGiftClaim::where('user_id', $user->id)->pluck('seasonal_gift_id')->map(fn ($id) => (int) $id)->toArray()
            : [];

        $badges = Schema::hasTable('seasonal_badges')
            ? SeasonalBadge::where('is_active', true)->orderBy('id')->get()
            : collect();

        $earnedBadgeIds = ($user && Schema::hasTable('seasonal_user_badges'))
            ? SeasonalUserBadge::where('user_id', $user->id)->pluck('seasonal_badge_id')->map(fn ($id) => (int) $id)->toArray()
            : [];

        $challenges = Schema::hasTable('seasonal_challenges')
            ? SeasonalChallenge::where('is_active', true)->orderBy('id')->get()
            : collect();

        $userChallengesMap = ($user && Schema::hasTable('seasonal_user_challenges'))
            ? SeasonalUserChallenge::where('user_id', $user->id)->get()->keyBy('seasonal_challenge_id')
            : collect();

        $creation = ($user && Schema::hasTable('seasonal_user_creations'))
            ? SeasonalUserCreation::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'tree_config' => [
                        'topper' => '⭐',
                        'lights' => 'warm_gold',
                        'garland' => 'gold_tinsel',
                        'slots' => ['🔴', '🔔', '🟡', '🎁', '🔵', '❄️', '🦌', '🧦'],
                    ],
                    'snowman_config' => [
                        'name' => explode(' ', $user->name ?? 'Frosty')[0] . "'s Frosty",
                        'hat' => '🎩',
                        'scarf' => '#dc2626',
                        'buttons' => '⚫',
                        'eyes' => '😊',
                        'nose' => '🥕',
                        'accessory' => '🧹',
                    ],
                    'greeting_card_config' => [
                        'template' => 'royal_red',
                        'recipient' => 'My Dear Friend',
                        'sender' => $user->name ?? 'May22SMS User',
                        'message' => 'Wishing you joy, peace, prosperity, and instant digital success this Christmas season and throughout the New Year!',
                    ],
                ]
            )
            : null;

        return view('user.christmas', compact(
            'christmasMode',
            'featureSettings',
            'adventDays',
            'claimedAdventDays',
            'mysteryGifts',
            'claimedGiftIds',
            'badges',
            'earnedBadgeIds',
            'challenges',
            'userChallengesMap',
            'creation'
        ));
    }

    /**
     * Claim a day in the 25-Day Christmas Advent Calendar.
     */
    public function claimAdventDay(Request $request, int $day): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Please sign in first.'], 401);
        }

        $lock = Cache::lock("advent_claim:u{$user->id}:d{$day}", 10);
        if (! $lock->get()) {
            return response()->json(['success' => false, 'message' => 'Claim in progress...'], 429);
        }

        try {
            $res = $this->engine->claimAdventDay($user, $day);
            return response()->json([
                'success' => true,
                'message' => $res['message'],
                'reward_usd' => $res['reward_usd'],
                'reward_code' => $res['reward_code'],
                'new_balance_usd' => $res['new_balance_usd'],
                'earned_badges' => $res['earned_badges'],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Unable to unwrap this Advent day.',
            ], 400);
        } finally {
            optional($lock)->release();
        }
    }

    /**
     * Save the user's decorated Christmas Tree, Snowman Builder, or Greeting Card creation.
     */
    public function saveCreation(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user || ! Schema::hasTable('seasonal_user_creations')) {
            return response()->json(['success' => false, 'message' => 'Unable to save creation.'], 400);
        }

        $validated = $request->validate([
            'type' => 'required|in:tree,snowman,greeting_card,village_visit',
            'config' => 'required|array',
        ]);

        $creation = SeasonalUserCreation::firstOrCreate(['user_id' => $user->id]);
        $type = $validated['type'];
        $cfg = $validated['config'];

        $actionType = 'visit_christmas_hub';
        $msg = 'Saved!';

        if ($type === 'tree') {
            $creation->tree_config = $cfg;
            $actionType = 'decorate_tree';
            $msg = '🎄 Your decorated Christmas Tree has been saved!';
        } elseif ($type === 'snowman') {
            $creation->snowman_config = $cfg;
            $actionType = 'build_snowman';
            $msg = '⛄ Your custom Snowman has been saved to your Christmas Profile Card!';
        } elseif ($type === 'greeting_card') {
            $creation->greeting_card_config = $cfg;
            $creation->cards_generated_count = ((int) $creation->cards_generated_count) + 1;
            $actionType = 'generate_greeting_card';
            $msg = '💌 Your Christmas Greeting Card has been saved!';
        } elseif ($type === 'village_visit') {
            $spots = array_values(array_unique(array_merge(
                (array) ($creation->village_Visited_spots ?? []),
                [(string) ($cfg['spot'] ?? 'cottage')]
            )));
            $creation->village_Visited_spots = $spots;
            $actionType = 'explore_village';
            $msg = '🏘️ Village spot discovered!';
        }

        $creation->save();

        $eval = $this->engine->recordActivityAndEvaluate($user, $actionType);

        return response()->json([
            'success' => true,
            'message' => $msg,
            'earned_badges' => $eval['earned_badges'],
            'completed_challenges' => $eval['completed_challenges'],
        ]);
    }

    /**
     * Claim wallet reward for a completed Christmas challenge.
     */
    public function claimChallenge(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Please sign in first.'], 401);
        }

        $lock = Cache::lock("challenge_claim:u{$user->id}:c{$id}", 10);
        if (! $lock->get()) {
            return response()->json(['success' => false, 'message' => 'Claim in progress...'], 429);
        }

        try {
            $res = $this->engine->claimChallengeReward($user, $id);
            return response()->json([
                'success' => true,
                'message' => $res['message'],
                'reward_usd' => $res['reward_usd'],
                'new_balance_usd' => $res['new_balance_usd'],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Could not claim challenge reward.',
            ], 400);
        } finally {
            optional($lock)->release();
        }
    }
}

