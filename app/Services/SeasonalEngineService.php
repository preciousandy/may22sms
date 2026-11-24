<?php

namespace App\Services;

use App\Models\BoostOrder;
use App\Models\FundingTransaction;
use App\Models\LogOrder;
use App\Models\PromoCode;
use App\Models\SeasonalAnnouncement;
use App\Models\SeasonalAuditLog;
use App\Models\SeasonalGift;
use App\Models\SeasonalGiftClaim;
use App\Models\SeasonalMode;
use App\Models\SeasonalSetting;
use App\Models\SeasonalUserPreference;
use App\Models\SmsOrder;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;

class SeasonalEngineService
{
    public const CACHE_ACTIVE_MODE_KEY = 'seasonal:active_mode:v1';

    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Return the 10 default preset definitions for MAY22SMS seasonal modes.
     */
    public function getDefaultPresets(): array
    {
        return [
            [
                'name' => 'Christmas Mode',
                'slug' => 'christmas-mode',
                'theme_type' => 'christmas',
                'icon' => '🎄',
                'greeting_title' => 'Merry Christmas from MAY22SMS! 🎄✨',
                'greeting_subtitle' => 'Celebrate the festive season with instant SMS verifications, holiday cheer, and a special Christmas gift waiting for you below!',
                'badge_text' => 'Christmas Celebration',
                'primary_color' => '#dc2626',
                'secondary_color' => '#15803d',
                'accent_color' => '#facc15',
                'banner_gradient' => 'linear-gradient(135deg, #7f1d1d 0%, #991b1b 45%, #14532d 100%)',
                'animation_type' => 'snow',
                'animation_intensity' => 'medium',
                'animations_enabled' => true,
                'decorations_enabled' => true,
                'banner_enabled' => true,
                'countdown_enabled' => true,
                'countdown_target_at' => Carbon::create(null, 12, 25, 0, 0, 0),
                'display_pages' => ['all'],
                'visual_effects' => ['header_garland' => true, 'glowing_borders' => true, 'floating_badges' => true],
                'is_system_default' => true,
                'default_gift' => [
                    'name' => 'May22SMS Christmas Bonus Gift',
                    'description' => 'Unwrap your festive Christmas wallet credit from the May22SMS team!',
                    'icon_or_image' => '🎁',
                    'gift_type' => 'wallet_credit',
                    'reward_value_usd' => 0.15,
                    'claim_button_text' => 'Claim Your Christmas Gift 🎄',
                ],
                'default_announcement' => [
                    'title' => '🎄 Merry Christmas from MAY22SMS!',
                    'message' => 'Celebrate with a special Christmas gift from us and enjoy 24/7 instant SMS verifications & social boosting throughout the holidays.',
                    'badge_text' => 'Festive Gift',
                    'cta_text' => 'Claim Gift on Dashboard',
                    'cta_link' => '/dashboard#seasonal-celebration-hub',
                ],
            ],
            [
                'name' => 'New Year Mode',
                'slug' => 'new-year-mode',
                'theme_type' => 'new_year',
                'icon' => '🎆',
                'greeting_title' => 'Happy New Year from MAY22SMS! 🎆🥂',
                'greeting_subtitle' => 'Welcome to a bigger, faster year of digital growth! Claim your New Year celebration gift and launch into the year ahead.',
                'badge_text' => 'New Year Celebration',
                'primary_color' => '#4f46e5',
                'secondary_color' => '#0f172a',
                'accent_color' => '#fbbf24',
                'banner_gradient' => 'linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #78350f 100%)',
                'animation_type' => 'fireworks_confetti',
                'animation_intensity' => 'high',
                'animations_enabled' => true,
                'decorations_enabled' => true,
                'banner_enabled' => true,
                'countdown_enabled' => true,
                'countdown_target_at' => Carbon::now()->endOfYear(),
                'display_pages' => ['all'],
                'visual_effects' => ['header_garland' => true, 'glowing_borders' => true, 'floating_badges' => true],
                'is_system_default' => true,
                'default_gift' => [
                    'name' => 'New Year Prosperity Reward',
                    'description' => 'Start the New Year with free bonus credits for SMS verifications and social boosting!',
                    'icon_or_image' => '🥂',
                    'gift_type' => 'wallet_credit',
                    'reward_value_usd' => 0.20,
                    'claim_button_text' => 'Claim New Year Gift 🎆',
                ],
                'default_announcement' => [
                    'title' => '🎆 Happy New Year! Special Promotional Offers Live',
                    'message' => 'Enjoy our New Year promotional offers and claim your special celebration reward on your dashboard.',
                    'badge_text' => 'New Year',
                    'cta_text' => 'Claim New Year Gift',
                    'cta_link' => '/dashboard#seasonal-celebration-hub',
                ],
            ],
            [
                'name' => "Valentine's Day Mode",
                'slug' => 'valentines-day-mode',
                'theme_type' => 'valentines',
                'icon' => '💖',
                'greeting_title' => "Happy Valentine's Day from MAY22SMS! 💖🌹",
                'greeting_subtitle' => 'We love our community! Enjoy floating hearts, sweet discounts, and a special Valentine gift from us.',
                'badge_text' => 'Valentine Special',
                'primary_color' => '#e11d48',
                'secondary_color' => '#9f1239',
                'accent_color' => '#fda4af',
                'banner_gradient' => 'linear-gradient(135deg, #881337 0%, #be123c 55%, #db2777 100%)',
                'animation_type' => 'hearts',
                'animation_intensity' => 'medium',
                'animations_enabled' => true,
                'decorations_enabled' => true,
                'banner_enabled' => true,
                'countdown_enabled' => false,
                'countdown_target_at' => null,
                'display_pages' => ['all'],
                'visual_effects' => ['header_garland' => true, 'glowing_borders' => true, 'floating_badges' => true],
                'is_system_default' => true,
                'default_gift' => [
                    'name' => "Valentine's Love Bonus",
                    'description' => 'A sweet token of appreciation for choosing MAY22SMS!',
                    'icon_or_image' => '💝',
                    'gift_type' => 'wallet_credit',
                    'reward_value_usd' => 0.12,
                    'claim_button_text' => 'Claim Valentine Gift 💖',
                ],
            ],
            [
                'name' => 'Easter Mode',
                'slug' => 'easter-mode',
                'theme_type' => 'easter',
                'icon' => '🥚',
                'greeting_title' => 'Happy Easter Celebration from MAY22SMS! 🐣🌷',
                'greeting_subtitle' => 'Crack open your special Easter surprise gift and enjoy seamless verifications all weekend long.',
                'badge_text' => 'Easter Celebration',
                'primary_color' => '#8b5cf6',
                'secondary_color' => '#0d9488',
                'accent_color' => '#fde047',
                'banner_gradient' => 'linear-gradient(135deg, #4c1d95 0%, #6d28d9 50%, #0d9488 100%)',
                'animation_type' => 'easter_eggs',
                'animation_intensity' => 'medium',
                'animations_enabled' => true,
                'decorations_enabled' => true,
                'banner_enabled' => true,
                'countdown_enabled' => false,
                'countdown_target_at' => null,
                'display_pages' => ['all'],
                'visual_effects' => ['header_garland' => true, 'glowing_borders' => true, 'floating_badges' => true],
                'is_system_default' => true,
                'default_gift' => [
                    'name' => 'Golden Easter Egg Reward',
                    'description' => 'You found the May22SMS Golden Easter Egg! Claim your free bonus credit now.',
                    'icon_or_image' => '🥚',
                    'gift_type' => 'wallet_credit',
                    'reward_value_usd' => 0.10,
                    'claim_button_text' => 'Crack Open Easter Gift 🐣',
                ],
            ],
            [
                'name' => 'Halloween Mode',
                'slug' => 'halloween-mode',
                'theme_type' => 'halloween',
                'icon' => '🎃',
                'greeting_title' => 'Spooky Halloween Treats at MAY22SMS! 🎃👻',
                'greeting_subtitle' => 'No tricks — only instant OTP codes, scary-fast social boosting, and a Halloween treat bag for your wallet!',
                'badge_text' => 'Halloween Spooktacular',
                'primary_color' => '#ea580c',
                'secondary_color' => '#1e1b4b',
                'accent_color' => '#f97316',
                'banner_gradient' => 'linear-gradient(135deg, #090d16 0%, #3b0764 50%, #c2410c 100%)',
                'animation_type' => 'pumpkins',
                'animation_intensity' => 'medium',
                'animations_enabled' => true,
                'decorations_enabled' => true,
                'banner_enabled' => true,
                'countdown_enabled' => false,
                'countdown_target_at' => null,
                'display_pages' => ['all'],
                'visual_effects' => ['header_garland' => true, 'glowing_borders' => true, 'floating_badges' => true],
                'is_system_default' => true,
                'default_gift' => [
                    'name' => 'Halloween Treat Bag Credit',
                    'description' => 'Grab your spooky Halloween treat credit before midnight!',
                    'icon_or_image' => '🎃',
                    'gift_type' => 'wallet_credit',
                    'reward_value_usd' => 0.10,
                    'claim_button_text' => 'Claim Halloween Treat 🎃',
                ],
            ],
            [
                'name' => 'Nigeria Independence Day Mode',
                'slug' => 'nigeria-independence-day-mode',
                'theme_type' => 'independence',
                'icon' => '🇳🇬',
                'greeting_title' => 'Happy Nigeria Independence Day! 🇳🇬💚🤍💚',
                'greeting_subtitle' => 'Celebrating Nigerian excellence, resilience, and digital innovation with special green-and-white rewards for our users!',
                'badge_text' => 'October 1st Celebration',
                'primary_color' => '#008751',
                'secondary_color' => '#064e3b',
                'accent_color' => '#ffffff',
                'banner_gradient' => 'linear-gradient(135deg, #022c22 0%, #008751 55%, #059669 100%)',
                'animation_type' => 'green_white_confetti',
                'animation_intensity' => 'medium',
                'animations_enabled' => true,
                'decorations_enabled' => true,
                'banner_enabled' => true,
                'countdown_enabled' => false,
                'countdown_target_at' => null,
                'display_pages' => ['all'],
                'visual_effects' => ['header_garland' => true, 'glowing_borders' => true, 'floating_badges' => true],
                'is_system_default' => true,
                'default_gift' => [
                    'name' => 'Naija Independence Celebration Gift',
                    'description' => 'Celebrate October 1st with free wallet credit from MAY22SMS!',
                    'icon_or_image' => '🇳🇬',
                    'gift_type' => 'wallet_credit',
                    'reward_value_usd' => 0.15,
                    'claim_button_text' => 'Claim Independence Gift 🇳🇬',
                ],
            ],
            [
                'name' => 'Black Friday Mode',
                'slug' => 'black-friday-mode',
                'theme_type' => 'black_friday',
                'icon' => '🛍️',
                'greeting_title' => 'MAY22SMS Black Friday Mega Sale is LIVE! ⚡🖤',
                'greeting_subtitle' => 'Massive discounts on SMS verifications, digital logs, and social media boosting! Claim your Black Friday voucher now.',
                'badge_text' => 'Black Friday Mega Deals',
                'primary_color' => '#eab308',
                'secondary_color' => '#090d16',
                'accent_color' => '#facc15',
                'banner_gradient' => 'linear-gradient(135deg, #000000 0%, #1e293b 55%, #854d0e 100%)',
                'animation_type' => 'gold_sparkles',
                'animation_intensity' => 'high',
                'animations_enabled' => true,
                'decorations_enabled' => true,
                'banner_enabled' => true,
                'countdown_enabled' => true,
                'countdown_target_at' => Carbon::now()->addDays(3),
                'display_pages' => ['all'],
                'visual_effects' => ['header_garland' => true, 'glowing_borders' => true, 'floating_badges' => true],
                'is_system_default' => true,
                'default_gift' => [
                    'name' => 'Black Friday Flash Credit',
                    'description' => 'Instant Black Friday bonus credit for your next SMS verification or log order!',
                    'icon_or_image' => '⚡',
                    'gift_type' => 'wallet_credit',
                    'reward_value_usd' => 0.15,
                    'claim_button_text' => 'Claim Black Friday Bonus ⚡',
                ],
                'default_announcement' => [
                    'title' => '🖤 Black Friday Discounts Are Now Available!',
                    'message' => 'Claim your Black Friday bonus credit and enjoy slashed prices across SMS verifications, logs, and boosting.',
                    'badge_text' => 'Black Friday',
                    'cta_text' => 'Claim Bonus Now',
                    'cta_link' => '/dashboard#seasonal-celebration-hub',
                ],
            ],
            [
                'name' => 'May22SMS Anniversary Mode',
                'slug' => 'may22sms-anniversary-mode',
                'theme_type' => 'anniversary',
                'icon' => '👑',
                'greeting_title' => 'Happy MAY22SMS Anniversary! Thank You for Being With Us 👑🎉',
                'greeting_subtitle' => 'Celebrating another milestone of powering tens of thousands of verifications and digital orders. Claim your customer appreciation gift!',
                'badge_text' => 'May22SMS Anniversary',
                'primary_color' => '#0d9488',
                'secondary_color' => '#0f766e',
                'accent_color' => '#facc15',
                'banner_gradient' => 'linear-gradient(135deg, #042f2e 0%, #0d9488 50%, #b45309 100%)',
                'animation_type' => 'fireworks_confetti',
                'animation_intensity' => 'high',
                'animations_enabled' => true,
                'decorations_enabled' => true,
                'banner_enabled' => true,
                'countdown_enabled' => false,
                'countdown_target_at' => null,
                'display_pages' => ['all'],
                'visual_effects' => ['header_garland' => true, 'glowing_borders' => true, 'floating_badges' => true],
                'is_system_default' => true,
                'default_gift' => [
                    'name' => 'Customer Appreciation Anniversary Gift',
                    'description' => 'Thank you for being part of the MAY22SMS community! Enjoy this special anniversary credit.',
                    'icon_or_image' => '👑',
                    'gift_type' => 'wallet_credit',
                    'reward_value_usd' => 0.25,
                    'claim_button_text' => 'Claim Anniversary Gift 👑',
                ],
            ],
            [
                'name' => 'Ramadan / Eid Mode',
                'slug' => 'ramadan-eid-mode',
                'theme_type' => 'ramadan',
                'icon' => '🌙',
                'greeting_title' => 'Ramadan Kareem & Eid Mubarak from MAY22SMS! 🌙✨',
                'greeting_subtitle' => 'Wishing you peace, blessings, and prosperity. Claim your special Eid celebration gift from our team.',
                'badge_text' => 'Blessed Celebration',
                'primary_color' => '#059669',
                'secondary_color' => '#064e3b',
                'accent_color' => '#fbbf24',
                'banner_gradient' => 'linear-gradient(135deg, #022c22 0%, #065f46 50%, #78350f 100%)',
                'animation_type' => 'lanterns_stars',
                'animation_intensity' => 'medium',
                'animations_enabled' => true,
                'decorations_enabled' => true,
                'banner_enabled' => true,
                'countdown_enabled' => false,
                'countdown_target_at' => null,
                'display_pages' => ['all'],
                'visual_effects' => ['header_garland' => true, 'glowing_borders' => true, 'floating_badges' => true],
                'is_system_default' => true,
                'default_gift' => [
                    'name' => 'Eid Mubarak Blessing Gift',
                    'description' => 'Celebrate with a special Eid reward credit from MAY22SMS!',
                    'icon_or_image' => '🌙',
                    'gift_type' => 'wallet_credit',
                    'reward_value_usd' => 0.15,
                    'claim_button_text' => 'Claim Eid Gift 🌙',
                ],
            ],
            [
                'name' => 'Custom Seasonal Mode',
                'slug' => 'custom-seasonal-mode',
                'theme_type' => 'custom',
                'icon' => '✨',
                'greeting_title' => 'Special Celebration Event at MAY22SMS! ✨🎉',
                'greeting_subtitle' => 'Enjoy limited-time seasonal rewards, exclusive promotions, and instant digital services.',
                'badge_text' => 'Special Event',
                'primary_color' => '#0d9488',
                'secondary_color' => '#0f172a',
                'accent_color' => '#facc15',
                'banner_gradient' => 'linear-gradient(135deg, #0f172a 0%, #0d9488 60%, #0f766e 100%)',
                'animation_type' => 'gold_sparkles',
                'animation_intensity' => 'medium',
                'animations_enabled' => true,
                'decorations_enabled' => true,
                'banner_enabled' => true,
                'countdown_enabled' => false,
                'countdown_target_at' => null,
                'display_pages' => ['all'],
                'visual_effects' => ['header_garland' => true, 'glowing_borders' => true, 'floating_badges' => true],
                'is_system_default' => true,
                'default_gift' => [
                    'name' => 'Special Event Celebration Gift',
                    'description' => 'Claim your limited-time celebration bonus reward!',
                    'icon_or_image' => '🎁',
                    'gift_type' => 'wallet_credit',
                    'reward_value_usd' => 0.10,
                    'claim_button_text' => 'Claim Special Gift 🎁',
                ],
            ],
        ];
    }

    /**
     * Seed the 10 default seasonal modes, sample gifts, and announcements if not yet seeded.
     */
    public function ensureDefaultsSeeded(): void
    {
        if (! Schema::hasTable('seasonal_modes')) {
            return;
        }

        if (SeasonalMode::count() > 0) {
            return;
        }

        foreach ($this->getDefaultPresets() as $preset) {
            $giftData = $preset['default_gift'] ?? null;
            $annData = $preset['default_announcement'] ?? null;
            unset($preset['default_gift'], $preset['default_announcement']);

            $mode = SeasonalMode::create(array_merge($preset, [
                'is_active' => false,
                'is_scheduled' => false,
                'manual_override' => false,
            ]));

            if (is_array($giftData) && Schema::hasTable('seasonal_gifts')) {
                SeasonalGift::create(array_merge([
                    'seasonal_mode_id' => $mode->id,
                    'distribution_mode' => 'manual_claim',
                    'max_total_claims' => 500,
                    'max_claims_per_user' => 1,
                    'is_active' => true,
                ], $giftData));
            }

            if (is_array($annData) && Schema::hasTable('seasonal_announcements')) {
                SeasonalAnnouncement::create(array_merge([
                    'seasonal_mode_id' => $mode->id,
                    'is_dismissible' => true,
                    'show_on_dashboard' => true,
                    'show_on_home' => true,
                    'is_active' => true,
                ], $annData));
            }
        }
    }

    /**
     * Synchronize scheduled seasonal modes based on current time and starts_at / ends_at.
     */
    public function syncScheduledModes(): array
    {
        if (! Schema::hasTable('seasonal_modes')) {
            return ['activated' => [], 'deactivated' => []];
        }

        $now = now();
        $activated = [];
        $deactivated = [];

        // 1. Deactivate any active mode whose ends_at has passed
        $expiredModes = SeasonalMode::where('is_active', true)
            ->whereNotNull('ends_at')
            ->where('ends_at', '<=', $now)
            ->get();

        foreach ($expiredModes as $expired) {
            $expired->update([
                'is_active' => false,
                'manual_override' => false,
            ]);
            $deactivated[] = $expired->name;
            $this->logAudit(null, 'System Scheduler', 'auto_deactivate_mode', 'seasonal_mode', $expired->id, "Auto-deactivated expired seasonal mode: {$expired->name}");
        }

        // 2. If no manual override mode is currently active, check if a scheduled mode should be active now
        $currentManualActive = SeasonalMode::where('is_active', true)
            ->where('manual_override', true)
            ->first();

        if (! $currentManualActive) {
            $dueMode = SeasonalMode::where('is_scheduled', true)
                ->whereNotNull('starts_at')
                ->where('starts_at', '<=', $now)
                ->where(function ($q) use ($now) {
                    $q->whereNull('ends_at')->orWhere('ends_at', '>', $now);
                })
                ->orderByDesc('starts_at')
                ->first();

            if ($dueMode && ! $dueMode->is_active) {
                // Deactivate other primary modes first
                SeasonalMode::where('is_active', true)->where('id', '!=', $dueMode->id)->update(['is_active' => false]);
                $dueMode->update(['is_active' => true]);
                $activated[] = $dueMode->name;
                $this->logAudit(null, 'System Scheduler', 'auto_activate_mode', 'seasonal_mode', $dueMode->id, "Auto-activated scheduled seasonal mode: {$dueMode->name}");
            }
        }

        if (! empty($activated) || ! empty($deactivated)) {
            $this->clearCache();
        }

        return ['activated' => $activated, 'deactivated' => $deactivated];
    }

    /**
     * Resolve the active seasonal mode (or admin preview mode if admin requests ?seasonal_preview={slug|id}).
     */
    public function resolveActiveMode(?User $user = null, $previewIdentifier = null): ?SeasonalMode
    {
        if (! Schema::hasTable('seasonal_modes')) {
            return null;
        }

        // Support Admin Preview without affecting ordinary users
        if ($previewIdentifier && $user && ($user->is_admin || $user->is_editor || $user->is_support)) {
            $previewMode = is_numeric($previewIdentifier)
                ? SeasonalMode::with(['gifts', 'announcements'])->find((int) $previewIdentifier)
                : SeasonalMode::with(['gifts', 'announcements'])->where('slug', (string) $previewIdentifier)->first();

            if ($previewMode) {
                $previewMode->setAttribute('is_preview_mode', true);
                return $previewMode;
            }
        }

        return Cache::remember(self::CACHE_ACTIVE_MODE_KEY, 60, function () {
            $this->syncScheduledModes();

            return SeasonalMode::with([
                'gifts' => fn ($q) => $q->where('is_active', true),
                'announcements' => fn ($q) => $q->where('is_active', true),
            ])->where('is_active', true)->first();
        });
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_ACTIVE_MODE_KEY);
    }

    /**
     * Check whether a user meets all eligibility rules for a given SeasonalGift.
     *
     * @return array{eligible: bool, reason: string|null}
     */
    public function checkUserEligibility(User $user, SeasonalGift $gift): array
    {
        if (! $gift->is_active) {
            return ['eligible' => false, 'reason' => 'This seasonal gift is no longer active.'];
        }

        $now = now();
        if ($gift->starts_at && $now->lt($gift->starts_at)) {
            return ['eligible' => false, 'reason' => 'This seasonal gift is not yet open for claiming.'];
        }

        if ($gift->expires_at && $now->gt($gift->expires_at)) {
            return ['eligible' => false, 'reason' => 'This seasonal gift has expired.'];
        }

        if ($gift->max_total_claims > 0 && $gift->claimed_count >= $gift->max_total_claims) {
            return ['eligible' => false, 'reason' => 'All available claims for this seasonal gift have been exhausted.'];
        }

        // Check selected users whitelist if configured
        $selectedUserIds = array_values(array_filter(array_map('intval', (array) ($gift->eligible_user_ids ?? []))));
        if ($gift->distribution_mode === 'selected_users' || ! empty($selectedUserIds)) {
            if (! in_array((int) $user->id, $selectedUserIds, true)) {
                return ['eligible' => false, 'reason' => 'This seasonal gift is reserved for selected accounts.'];
            }
        }

        // Minimum account age check
        if ($gift->min_account_age_days > 0 && $user->created_at) {
            $accountAgeDays = $user->created_at->diffInDays($now);
            if ($accountAgeDays < $gift->min_account_age_days) {
                return [
                    'eligible' => false,
                    'reason' => "Your account must be at least {$gift->min_account_age_days} days old to claim this gift.",
                ];
            }
        }

        // Minimum wallet funding check
        if ($gift->min_wallet_funding_usd > 0) {
            $totalFundedUsd = (float) FundingTransaction::where('user_id', $user->id)
                ->whereIn('status', ['approved', 'completed', 'paid', 'successful'])
                ->sum('amount_usd');

            if ($totalFundedUsd < $gift->min_wallet_funding_usd) {
                return [
                    'eligible' => false,
                    'reason' => 'A minimum wallet funding history of $' . number_format($gift->min_wallet_funding_usd, 2) . ' USD is required to claim this gift.',
                ];
            }
        }

        // Minimum successful transactions check
        if ($gift->min_transactions_count > 0) {
            $txCount = SmsOrder::where('user_id', $user->id)->whereIn('status', ['RECEIVED', 'COMPLETED', 'FINISHED', 'received', 'completed'])->count()
                + LogOrder::where('user_id', $user->id)->whereIn('status', ['completed', 'paid', 'delivered'])->count()
                + BoostOrder::where('user_id', $user->id)->whereIn('status', ['completed', 'processing', 'in_progress'])->count();

            if ($txCount < $gift->min_transactions_count) {
                return [
                    'eligible' => false,
                    'reason' => "You need at least {$gift->min_transactions_count} completed order(s) on MAY22SMS to claim this gift.",
                ];
            }
        }

        return ['eligible' => true, 'reason' => null];
    }

    /**
     * Atomically process a user claiming a seasonal gift.
     */
    public function claimGift(
        User $user,
        SeasonalGift $gift,
        string $distributionMethod = 'manual_claim',
        ?string $ip = null,
        ?string $userAgent = null,
        ?int $adminActorId = null
    ): array {
        return DB::transaction(function () use ($user, $gift, $distributionMethod, $ip, $userAgent, $adminActorId) {
            $lockedGift = SeasonalGift::lockForUpdate()->findOrFail($gift->id);
            $lockedUser = User::lockForUpdate()->findOrFail($user->id);

            // 1. Check duplicate claim
            $existingClaimCount = SeasonalGiftClaim::where('seasonal_gift_id', $lockedGift->id)
                ->where('user_id', $lockedUser->id)
                ->lockForUpdate()
                ->count();

            $maxPerUser = max(1, (int) $lockedGift->max_claims_per_user);
            if ($existingClaimCount >= $maxPerUser) {
                throw new RuntimeException('You have already claimed this seasonal gift!');
            }

            // 2. Check eligibility (unless forced by admin for selected_users)
            if ($distributionMethod === 'manual_claim') {
                $eligibility = $this->checkUserEligibility($lockedUser, $lockedGift);
                if (! $eligibility['eligible']) {
                    throw new RuntimeException($eligibility['reason'] ?? 'You are not eligible for this gift.');
                }
            } else {
                if (! $lockedGift->is_active) {
                    throw new RuntimeException('This seasonal gift is currently inactive.');
                }
                if ($lockedGift->max_total_claims > 0 && $lockedGift->claimed_count >= $lockedGift->max_total_claims) {
                    throw new RuntimeException('This seasonal gift has reached its maximum claim limit.');
                }
            }

            $rewardUsd = max(0.0, round((float) $lockedGift->reward_value_usd, 4));
            $rewardCode = $lockedGift->promo_code;
            $reference = 'SEAS-GIFT-' . $lockedGift->id . '-U' . $lockedUser->id . '-' . ($existingClaimCount + 1);

            // 3. Process reward by gift type
            $creditWalletTypes = ['wallet_credit', 'sms_credit', 'boosting_credit', 'service_credit'];
            if (in_array($lockedGift->gift_type, $creditWalletTypes, true) && $rewardUsd > 0) {
                $this->walletService->credit(
                    $lockedUser,
                    $rewardUsd,
                    'seasonal_gift',
                    $reference,
                    [
                        'seasonal_gift_id' => $lockedGift->id,
                        'seasonal_mode_id' => $lockedGift->seasonal_mode_id,
                        'gift_name' => $lockedGift->name,
                        'gift_type' => $lockedGift->gift_type,
                        'distribution_method' => $distributionMethod,
                        'admin_id' => $adminActorId,
                    ]
                );
            } elseif (in_array($lockedGift->gift_type, ['discount_coupon', 'promo_code'], true)) {
                // Ensure or generate a PromoCode in promo_codes table so user can redeem it at checkout
                if (empty($rewardCode)) {
                    $rewardCode = strtoupper('GIFT' . $lockedGift->id . Str::random(5));
                }
                if (Schema::hasTable('promo_codes')) {
                    PromoCode::firstOrCreate(
                        ['code' => strtoupper($rewardCode)],
                        [
                            'title' => $lockedGift->name,
                            'message' => $lockedGift->description ?: 'Seasonal Celebration Discount Coupon',
                            'applies_to' => ['sms', 'logs', 'boost', 'funding'],
                            'type' => $lockedGift->discount_percent > 0 ? 'percent' : 'fixed',
                            'value' => $lockedGift->discount_percent > 0 ? (float) $lockedGift->discount_percent : max(0.5, $rewardUsd),
                            'max_uses' => max(100, (int) $lockedGift->max_total_claims),
                            'used_count' => 0,
                            'is_active' => true,
                            'is_public' => false,
                            'expires_at' => $lockedGift->expires_at ?? now()->addDays(14),
                        ]
                    );
                }
            } elseif ($lockedGift->gift_type === 'custom_reward' && $rewardUsd > 0) {
                $this->walletService->credit(
                    $lockedUser,
                    $rewardUsd,
                    'seasonal_gift',
                    $reference,
                    [
                        'seasonal_gift_id' => $lockedGift->id,
                        'gift_name' => $lockedGift->name,
                        'gift_type' => 'custom_reward',
                    ]
                );
            }

            // 4. Record claim
            $claim = SeasonalGiftClaim::create([
                'seasonal_gift_id' => $lockedGift->id,
                'seasonal_mode_id' => $lockedGift->seasonal_mode_id,
                'user_id' => $lockedUser->id,
                'gift_type' => $lockedGift->gift_type,
                'reward_value_usd' => $rewardUsd,
                'reward_code' => $rewardCode,
                'distribution_method' => $distributionMethod,
                'reference' => $reference,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'meta' => [
                    'gift_name' => $lockedGift->name,
                    'discount_percent' => $lockedGift->discount_percent,
                ],
                'claimed_at' => now(),
            ]);

            // 5. Increment counters on SeasonalGift
            $lockedGift->increment('claimed_count');
            if ($rewardUsd > 0) {
                $lockedGift->increment('total_distributed_usd', $rewardUsd);
            }

            // 6. Send in-app notification to user
            $this->sendUserInAppNotification(
                $lockedUser,
                "🎁 Seasonal Gift Claimed: {$lockedGift->name}",
                $rewardUsd > 0
                    ? "Congratulations! $" . number_format($rewardUsd, 2) . " USD has been credited to your MAY22SMS wallet from {$lockedGift->name}."
                    : "Congratulations! You received seasonal promo code {$rewardCode} from {$lockedGift->name}."
            );

            $freshUser = User::find($lockedUser->id);

            return [
                'claim' => $claim,
                'reward_usd' => $rewardUsd,
                'reward_code' => $rewardCode,
                'new_balance_usd' => (float) ($freshUser->walletBalance ?? 0),
                'message' => $rewardUsd > 0
                    ? "🎉 Congratulations! You claimed {$lockedGift->name} ($" . number_format($rewardUsd, 2) . " USD credited to your wallet)!"
                    : "🎉 Congratulations! You claimed {$lockedGift->name}! Use code: {$rewardCode}",
            ];
        });
    }

    /**
     * Distribute a gift automatically to selected users or all eligible users.
     */
    public function distributeGiftBatch(SeasonalGift $gift, string $mode, array $selectedUserIds = [], ?User $admin = null): array
    {
        $query = User::where('account_status', 'active')->where('is_admin', false);

        if ($mode === 'selected_users') {
            $ids = array_values(array_filter(array_map('intval', $selectedUserIds)));
            if (empty($ids)) {
                return ['distributed_count' => 0, 'total_usd' => 0.0, 'skipped_count' => 0];
            }
            $query->whereIn('id', $ids);
        }

        $alreadyClaimedIds = SeasonalGiftClaim::where('seasonal_gift_id', $gift->id)
            ->pluck('user_id')
            ->toArray();

        if (! empty($alreadyClaimedIds)) {
            $query->whereNotIn('id', $alreadyClaimedIds);
        }

        $remainingCap = $gift->remainingClaims();
        if ($remainingCap !== null && $remainingCap <= 0) {
            return ['distributed_count' => 0, 'total_usd' => 0.0, 'skipped_count' => 0];
        }

        $users = $query->orderBy('id')->limit($remainingCap ?? 500)->get();

        $distributed = 0;
        $skipped = 0;
        $totalUsd = 0.0;

        foreach ($users as $targetUser) {
            if ($mode !== 'selected_users') {
                $elig = $this->checkUserEligibility($targetUser, $gift);
                if (! $elig['eligible']) {
                    $skipped++;
                    continue;
                }
            }

            try {
                $res = $this->claimGift(
                    $targetUser,
                    $gift,
                    $mode === 'selected_users' ? 'admin_selected' : 'admin_bulk',
                    request()?->ip(),
                    request()?->userAgent(),
                    $admin?->id
                );
                $distributed++;
                $totalUsd += (float) ($res['reward_usd'] ?? 0);
            } catch (\Throwable $e) {
                $skipped++;
            }
        }

        $this->logAudit(
            $admin?->id,
            $admin?->name ?? 'Admin',
            'distribute_gift_batch',
            'seasonal_gift',
            $gift->id,
            "Distributed gift '{$gift->name}' ({$mode}) to {$distributed} user(s), total $" . number_format($totalUsd, 2),
            ['mode' => $mode, 'distributed' => $distributed, 'skipped' => $skipped, 'total_usd' => $totalUsd]
        );

        $this->clearCache();

        return [
            'distributed_count' => $distributed,
            'total_usd' => round($totalUsd, 4),
            'skipped_count' => $skipped,
        ];
    }

    /**
     * Broadcast a seasonal announcement to users via in-app notification and/or email.
     */
    public function broadcastAnnouncement(SeasonalAnnouncement $announcement, bool $sendInApp = true, bool $sendEmail = false, ?User $admin = null): int
    {
        $users = User::where('account_status', 'active')->select(['id', 'name', 'email'])->get();
        $count = 0;

        if ($sendInApp && Schema::hasTable('notifications')) {
            $now = now();
            $rows = [];
            foreach ($users as $u) {
                $rows[] = [
                    'id' => (string) Str::uuid(),
                    'type' => 'App\\Notifications\\SystemBroadcastNotification',
                    'notifiable_type' => User::class,
                    'notifiable_id' => $u->id,
                    'data' => json_encode([
                        'title' => $announcement->title,
                        'message' => $announcement->message,
                        'action_url' => $announcement->cta_link ?: '/dashboard',
                        'action_text' => $announcement->cta_text ?: 'View Celebration',
                        'seasonal_announcement_id' => $announcement->id,
                    ]),
                    'read_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $count++;

                if (count($rows) >= 250) {
                    DB::table('notifications')->insert($rows);
                    $rows = [];
                }
            }
            if (! empty($rows)) {
                DB::table('notifications')->insert($rows);
            }
        }

        if ($sendEmail) {
            foreach ($users->take(100) as $u) {
                try {
                    Mail::raw("{$announcement->title}\n\n{$announcement->message}\n\nVisit MAY22SMS: " . url($announcement->cta_link ?: '/dashboard'), function ($msg) use ($u, $announcement) {
                        $msg->to($u->email)->subject($announcement->title);
                    });
                } catch (\Throwable $e) {
                    Log::warning('Seasonal announcement email failed: ' . $e->getMessage());
                }
            }
        }

        $this->logAudit(
            $admin?->id,
            $admin?->name ?? 'Admin',
            'broadcast_announcement',
            'seasonal_announcement',
            $announcement->id,
            "Broadcasted seasonal announcement '{$announcement->title}' to {$count} users."
        );

        return $count;
    }

    /**
     * Send an in-app database notification to a single user.
     */
    public function sendUserInAppNotification(User $user, string $title, string $message, string $actionUrl = '/dashboard'): void
    {
        if (! Schema::hasTable('notifications')) {
            return;
        }

        try {
            DB::table('notifications')->insert([
                'id' => (string) Str::uuid(),
                'type' => 'App\\Notifications\\SystemBroadcastNotification',
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
                'data' => json_encode([
                    'title' => $title,
                    'message' => $message,
                    'action_url' => $actionUrl,
                ]),
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to insert seasonal notification: ' . $e->getMessage());
        }
    }

    /**
     * Record an audit log entry.
     */
    public function logAudit(?int $actorId, string $actorName, string $action, ?string $targetType, ?int $targetId, string $summary, array $payload = []): void
    {
        if (! Schema::hasTable('seasonal_audit_logs')) {
            return;
        }

        try {
            SeasonalAuditLog::create([
                'actor_id' => $actorId,
                'actor_name' => $actorName,
                'action' => $action,
                'target_type' => $targetType,
                'target_id' => $targetId,
                'summary' => $summary,
                'payload' => $payload ?: null,
                'ip_address' => request()?->ip(),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to write seasonal audit log: ' . $e->getMessage());
        }
    }

    /**
     * Compute aggregate gift distribution report statistics.
     */
    public function getDistributionReport(): array
    {
        if (! Schema::hasTable('seasonal_gifts')) {
            return [
                'total_gifts_created' => 0,
                'total_eligible_users' => 0,
                'total_gifts_claimed' => 0,
                'total_gifts_distributed' => 0,
                'total_reward_value_usd' => 0.0,
                'remaining_available_claims' => 0,
            ];
        }

        $gifts = SeasonalGift::all();
        $totalGiftsCreated = $gifts->count();
        $totalEligibleUsers = User::where('account_status', 'active')->where('is_admin', false)->count();
        $totalClaims = SeasonalGiftClaim::count();
        $totalDistributed = SeasonalGiftClaim::whereIn('distribution_method', ['auto_distribute', 'admin_selected', 'admin_bulk'])->count();
        $totalRewardValueUsd = (float) SeasonalGiftClaim::sum('reward_value_usd');

        $remainingClaims = 0;
        foreach ($gifts->where('is_active', true) as $g) {
            if ($g->max_total_claims > 0) {
                $remainingClaims += max(0, $g->max_total_claims - $g->claimed_count);
            }
        }

        return [
            'total_gifts_created' => $totalGiftsCreated,
            'total_eligible_users' => $totalEligibleUsers,
            'total_gifts_claimed' => $totalClaims,
            'total_gifts_distributed' => $totalDistributed,
            'total_reward_value_usd' => round($totalRewardValueUsd, 4),
            'remaining_available_claims' => $remainingClaims,
        ];
    }
}

