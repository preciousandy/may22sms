<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeasonalAdventDay;
use App\Models\SeasonalAnnouncement;
use App\Models\SeasonalAuditLog;
use App\Models\SeasonalBadge;
use App\Models\SeasonalChallenge;
use App\Models\SeasonalGift;
use App\Models\SeasonalGiftClaim;
use App\Models\SeasonalMode;
use App\Models\SeasonalSetting;
use App\Services\SeasonalEngineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SeasonalController extends Controller
{
    protected SeasonalEngineService $engine;

    public function __construct(SeasonalEngineService $engine)
    {
        $this->engine = $engine;
    }

    /**
     * Main Seasonal Mode & Gifts Management Dashboard.
     */
    public function index(Request $request)
    {
        $this->engine->ensureDefaultsSeeded();
        $this->engine->ensureInteractiveChristmasSeeded();
        $this->engine->syncScheduledModes();

        $modes = SeasonalMode::withCount(['gifts', 'claims'])
            ->orderByDesc('is_active')
            ->orderBy('id')
            ->get();

        $activeMode = $modes->firstWhere('is_active', true);

        $gifts = SeasonalGift::with('mode')
            ->orderByDesc('is_active')
            ->orderByDesc('id')
            ->get();

        $announcements = SeasonalAnnouncement::with('mode')
            ->orderByDesc('is_active')
            ->orderByDesc('id')
            ->get();

        $adventDays = Schema::hasTable('seasonal_advent_days')
            ? SeasonalAdventDay::orderBy('day_number')->get()
            : collect();

        $badges = Schema::hasTable('seasonal_badges')
            ? SeasonalBadge::orderBy('id')->get()
            : collect();

        $challenges = Schema::hasTable('seasonal_challenges')
            ? SeasonalChallenge::orderBy('id')->get()
            : collect();

        $featureSettings = $this->engine->getInteractiveFeatureSettings();

        $recentClaims = SeasonalGiftClaim::with(['user:id,name,email', 'gift:id,name,gift_type', 'mode:id,name,icon'])
            ->latest('claimed_at')
            ->limit(30)
            ->get();

        $auditLogs = Schema::hasTable('seasonal_audit_logs')
            ? SeasonalAuditLog::orderByDesc('id')->limit(25)->get()
            : collect();

        $report = $this->engine->getDistributionReport();

        return view('admin.seasonal.index', compact(
            'modes',
            'activeMode',
            'gifts',
            'announcements',
            'adventDays',
            'badges',
            'challenges',
            'featureSettings',
            'recentClaims',
            'auditLogs',
            'report'
        ));
    }

    /**
     * Create a new Seasonal Mode / Celebration Theme.
     */
    public function storeMode(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'theme_type' => 'required|string|max:40',
            'icon' => 'nullable|string|max:40',
            'greeting_title' => 'required|string|max:255',
            'greeting_subtitle' => 'nullable|string|max:1000',
            'badge_text' => 'nullable|string|max:100',
            'primary_color' => 'required|string|max:20',
            'secondary_color' => 'required|string|max:20',
            'accent_color' => 'required|string|max:20',
            'banner_gradient' => 'nullable|string|max:255',
            'animation_type' => 'required|string|max:40',
            'animation_intensity' => 'required|in:low,medium,high',
            'animations_enabled' => 'nullable|boolean',
            'decorations_enabled' => 'nullable|boolean',
            'banner_enabled' => 'nullable|boolean',
            'countdown_enabled' => 'nullable|boolean',
            'countdown_target_at' => 'nullable|date',
            'display_pages' => 'nullable|array',
            'is_scheduled' => 'nullable|boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'banner_image_file' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:3072',
            'background_image_file' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:4096',
        ]);

        $slug = Str::slug($validated['name']);
        if (SeasonalMode::where('slug', $slug)->exists()) {
            $slug .= '-' . strtolower(Str::random(4));
        }

        $bannerPath = $this->handleImageUpload($request, 'banner_image_file', 'seasonal/banners');
        $bgPath = $this->handleImageUpload($request, 'background_image_file', 'seasonal/backgrounds');

        $mode = SeasonalMode::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'theme_type' => $validated['theme_type'],
            'icon' => ($validated['icon'] ?? '') ?: '🎉',
            'greeting_title' => $validated['greeting_title'],
            'greeting_subtitle' => $validated['greeting_subtitle'] ?? '',
            'badge_text' => ($validated['badge_text'] ?? '') ?: $validated['name'],
            'primary_color' => $validated['primary_color'],
            'secondary_color' => $validated['secondary_color'],
            'accent_color' => $validated['accent_color'],
            'banner_gradient' => ($validated['banner_gradient'] ?? '') ?: "linear-gradient(135deg, {$validated['secondary_color']} 0%, {$validated['primary_color']} 100%)",
            'banner_image' => $bannerPath,
            'background_image' => $bgPath,
            'animation_type' => $validated['animation_type'],
            'animation_intensity' => $validated['animation_intensity'],
            'animations_enabled' => $request->boolean('animations_enabled', true),
            'decorations_enabled' => $request->boolean('decorations_enabled', true),
            'banner_enabled' => $request->boolean('banner_enabled', true),
            'countdown_enabled' => $request->boolean('countdown_enabled', false),
            'countdown_target_at' => $validated['countdown_target_at'] ?? null,
            'display_pages' => ! empty($validated['display_pages']) ? array_values($validated['display_pages']) : ['all'],
            'visual_effects' => [
                'header_garland' => $request->boolean('effect_header_garland', true),
                'glowing_borders' => $request->boolean('effect_glowing_borders', true),
                'floating_badges' => $request->boolean('effect_floating_badges', true),
                'snow_accumulation' => $request->boolean('effect_snow_accumulation', true),
                'icy_borders' => $request->boolean('effect_icy_borders', true),
                'christmas_ornaments' => $request->boolean('effect_christmas_ornaments', true),
                'snowflake_size' => in_array($request->input('snowflake_size'), ['small', 'medium', 'large'], true) ? $request->input('snowflake_size') : 'medium',
                'snowflake_speed' => in_array($request->input('snowflake_speed'), ['slow', 'normal', 'fast'], true) ? $request->input('snowflake_speed') : 'normal',
                'snow_cap_thickness' => in_array($request->input('snow_cap_thickness'), ['thin', 'medium', 'thick'], true) ? $request->input('snow_cap_thickness') : 'medium',
                'snow_targets' => is_array($request->input('snow_targets')) && ! empty($request->input('snow_targets'))
                    ? array_values($request->input('snow_targets'))
                    : ['cards', 'wallet', 'services', 'buttons', 'inputs', 'navbar', 'banners'],
            ],
            'is_active' => false,
            'is_scheduled' => $request->boolean('is_scheduled', false),
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
            'is_system_default' => false,
        ]);

        $admin = auth()->user();
        $this->engine->logAudit($admin?->id, $admin?->name ?? 'Admin', 'create_mode', 'seasonal_mode', $mode->id, "Created seasonal mode '{$mode->name}'");
        $this->engine->clearCache();

        return back()->with('success', "Seasonal mode '{$mode->name}' created successfully!");
    }

    /**
     * Update an existing Seasonal Mode.
     */
    public function updateMode(Request $request, int $id)
    {
        $mode = SeasonalMode::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'theme_type' => 'required|string|max:40',
            'icon' => 'nullable|string|max:40',
            'greeting_title' => 'required|string|max:255',
            'greeting_subtitle' => 'nullable|string|max:1000',
            'badge_text' => 'nullable|string|max:100',
            'primary_color' => 'required|string|max:20',
            'secondary_color' => 'required|string|max:20',
            'accent_color' => 'required|string|max:20',
            'banner_gradient' => 'nullable|string|max:255',
            'animation_type' => 'required|string|max:40',
            'animation_intensity' => 'required|in:low,medium,high',
            'countdown_target_at' => 'nullable|date',
            'display_pages' => 'nullable|array',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'banner_image_file' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:3072',
            'background_image_file' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:4096',
        ]);

        $bannerPath = $this->handleImageUpload($request, 'banner_image_file', 'seasonal/banners') ?: $mode->banner_image;
        $bgPath = $this->handleImageUpload($request, 'background_image_file', 'seasonal/backgrounds') ?: $mode->background_image;

        $mode->update([
            'name' => $validated['name'],
            'theme_type' => $validated['theme_type'],
            'icon' => ($validated['icon'] ?? '') ?: $mode->icon,
            'greeting_title' => $validated['greeting_title'],
            'greeting_subtitle' => $validated['greeting_subtitle'] ?? '',
            'badge_text' => ($validated['badge_text'] ?? '') ?: $validated['name'],
            'primary_color' => $validated['primary_color'],
            'secondary_color' => $validated['secondary_color'],
            'accent_color' => $validated['accent_color'],
            'banner_gradient' => ($validated['banner_gradient'] ?? '') ?: "linear-gradient(135deg, {$validated['secondary_color']} 0%, {$validated['primary_color']} 100%)",
            'banner_image' => $bannerPath,
            'background_image' => $bgPath,
            'animation_type' => $validated['animation_type'],
            'animation_intensity' => $validated['animation_intensity'],
            'animations_enabled' => $request->boolean('animations_enabled'),
            'decorations_enabled' => $request->boolean('decorations_enabled'),
            'banner_enabled' => $request->boolean('banner_enabled'),
            'countdown_enabled' => $request->boolean('countdown_enabled'),
            'countdown_target_at' => $validated['countdown_target_at'] ?? null,
            'display_pages' => ! empty($validated['display_pages']) ? array_values($validated['display_pages']) : ['all'],
            'visual_effects' => [
                'header_garland' => $request->boolean('effect_header_garland', true),
                'glowing_borders' => $request->boolean('effect_glowing_borders', true),
                'floating_badges' => $request->boolean('effect_floating_badges', true),
                'snow_accumulation' => $request->boolean('effect_snow_accumulation', true),
                'icy_borders' => $request->boolean('effect_icy_borders', true),
                'christmas_ornaments' => $request->boolean('effect_christmas_ornaments', true),
                'snowflake_size' => in_array($request->input('snowflake_size'), ['small', 'medium', 'large'], true) ? $request->input('snowflake_size') : 'medium',
                'snowflake_speed' => in_array($request->input('snowflake_speed'), ['slow', 'normal', 'fast'], true) ? $request->input('snowflake_speed') : 'normal',
                'snow_cap_thickness' => in_array($request->input('snow_cap_thickness'), ['thin', 'medium', 'thick'], true) ? $request->input('snow_cap_thickness') : 'medium',
                'snow_targets' => is_array($request->input('snow_targets')) && ! empty($request->input('snow_targets'))
                    ? array_values($request->input('snow_targets'))
                    : ['cards', 'wallet', 'services', 'buttons', 'inputs', 'navbar', 'banners'],
            ],
            'is_scheduled' => $request->boolean('is_scheduled'),
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
        ]);

        $admin = auth()->user();
        $this->engine->logAudit($admin?->id, $admin?->name ?? 'Admin', 'update_mode', 'seasonal_mode', $mode->id, "Updated seasonal mode '{$mode->name}'");
        $this->engine->clearCache();

        return back()->with('success', "Seasonal mode '{$mode->name}' updated successfully!");
    }

    /**
     * Activate or deactivate a Seasonal Mode (enforcing single active primary mode).
     */
    public function toggleMode(Request $request, int $id)
    {
        $mode = SeasonalMode::findOrFail($id);
        $admin = auth()->user();

        if ($mode->is_active) {
            $mode->update([
                'is_active' => false,
                'manual_override' => false,
            ]);
            $this->engine->logAudit($admin?->id, $admin?->name ?? 'Admin', 'deactivate_mode', 'seasonal_mode', $mode->id, "Manually deactivated '{$mode->name}'");
            $this->engine->clearCache();

            return back()->with('success', "'{$mode->name}' has been deactivated. Normal MAY22SMS appearance restored.");
        }

        // Deactivate all other active modes first so only one primary mode is active
        SeasonalMode::where('is_active', true)->update([
            'is_active' => false,
            'manual_override' => false,
        ]);

        // If ends_at was in the past, clear it so manual activation doesn't immediately auto-expire
        $updates = [
            'is_active' => true,
            'manual_override' => true,
        ];
        if ($mode->ends_at && $mode->ends_at->isPast()) {
            $updates['ends_at'] = null;
        }

        $mode->update($updates);

        $this->engine->logAudit($admin?->id, $admin?->name ?? 'Admin', 'activate_mode', 'seasonal_mode', $mode->id, "Manually activated '{$mode->name}'");
        $this->engine->clearCache();

        return back()->with('success', "🎉 '{$mode->name}' is now LIVE across MAY22SMS!");
    }

    /**
     * Deactivate all seasonal modes at once and restore normal site appearance.
     */
    public function deactivateAllModes()
    {
        SeasonalMode::where('is_active', true)->update([
            'is_active' => false,
            'manual_override' => false,
        ]);

        $admin = auth()->user();
        $this->engine->logAudit($admin?->id, $admin?->name ?? 'Admin', 'deactivate_all_modes', 'seasonal_mode', null, 'Deactivated all seasonal modes');
        $this->engine->clearCache();

        return back()->with('success', 'All seasonal modes deactivated. Standard MAY22SMS appearance restored.');
    }

    /**
     * Delete a custom Seasonal Mode.
     */
    public function destroyMode(int $id)
    {
        $mode = SeasonalMode::findOrFail($id);
        $name = $mode->name;
        $mode->delete();

        $admin = auth()->user();
        $this->engine->logAudit($admin?->id, $admin?->name ?? 'Admin', 'delete_mode', 'seasonal_mode', $id, "Deleted seasonal mode '{$name}'");
        $this->engine->clearCache();

        return back()->with('success', "Seasonal mode '{$name}' deleted.");
    }

    /**
     * Create a new Seasonal Gift.
     */
    public function storeGift(Request $request)
    {
        $validated = $request->validate([
            'seasonal_mode_id' => 'nullable|exists:seasonal_modes,id',
            'name' => 'required|string|max:160',
            'description' => 'nullable|string|max:1000',
            'icon_or_image' => 'nullable|string|max:255',
            'gift_type' => 'required|in:wallet_credit,sms_credit,boosting_credit,discount_coupon,promo_code,service_credit,custom_reward',
            'reward_value_usd' => 'required|numeric|min:0|max:5000',
            'promo_code' => 'nullable|string|max:60',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'distribution_mode' => 'required|in:manual_claim,auto_distribute,selected_users,all_eligible',
            'claim_button_text' => 'required|string|max:100',
            'max_total_claims' => 'required|integer|min:0|max:1000000',
            'max_claims_per_user' => 'required|integer|min:1|max:50',
            'min_account_age_days' => 'nullable|integer|min:0',
            'min_wallet_funding_usd' => 'nullable|numeric|min:0',
            'min_transactions_count' => 'nullable|integer|min:0',
            'eligible_user_ids_csv' => 'nullable|string|max:2000',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'gift_image_file' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
        ]);

        $uploadedIcon = $this->handleImageUpload($request, 'gift_image_file', 'seasonal/gifts');
        $eligibleIds = $this->parseUserIdsCsv($validated['eligible_user_ids_csv'] ?? '');

        $gift = SeasonalGift::create([
            'seasonal_mode_id' => ($validated['seasonal_mode_id'] ?? null) ?: null,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? '',
            'icon_or_image' => $uploadedIcon ?: (($validated['icon_or_image'] ?? '') ?: '🎁'),
            'gift_type' => $validated['gift_type'],
            'reward_value_usd' => round((float) $validated['reward_value_usd'], 4),
            'promo_code' => ! empty($validated['promo_code']) ? strtoupper(trim($validated['promo_code'])) : null,
            'discount_percent' => isset($validated['discount_percent']) ? (float) $validated['discount_percent'] : null,
            'distribution_mode' => $validated['distribution_mode'],
            'claim_button_text' => $validated['claim_button_text'],
            'max_total_claims' => (int) $validated['max_total_claims'],
            'max_claims_per_user' => (int) $validated['max_claims_per_user'],
            'min_account_age_days' => (int) ($validated['min_account_age_days'] ?? 0),
            'min_wallet_funding_usd' => round((float) ($validated['min_wallet_funding_usd'] ?? 0), 4),
            'min_transactions_count' => (int) ($validated['min_transactions_count'] ?? 0),
            'eligible_user_ids' => $eligibleIds ?: null,
            'starts_at' => $validated['starts_at'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        $admin = auth()->user();
        $this->engine->logAudit($admin?->id, $admin?->name ?? 'Admin', 'create_gift', 'seasonal_gift', $gift->id, "Created seasonal gift '{$gift->name}' ($" . number_format($gift->reward_value_usd, 2) . ")");

        // If admin chose immediate automatic distribution on creation
        if (in_array($gift->distribution_mode, ['auto_distribute', 'all_eligible'], true) && $request->boolean('distribute_immediately')) {
            $batch = $this->engine->distributeGiftBatch($gift, 'all_eligible', [], $admin);
            return back()->with('success', "Gift '{$gift->name}' created and automatically distributed to {$batch['distributed_count']} eligible user(s)!");
        }
        if ($gift->distribution_mode === 'selected_users' && ! empty($eligibleIds) && $request->boolean('distribute_immediately')) {
            $batch = $this->engine->distributeGiftBatch($gift, 'selected_users', $eligibleIds, $admin);
            return back()->with('success', "Gift '{$gift->name}' created and distributed to {$batch['distributed_count']} selected user(s)!");
        }

        $this->engine->clearCache();

        return back()->with('success', "Seasonal gift '{$gift->name}' created successfully!");
    }

    /**
     * Update an existing Seasonal Gift.
     */
    public function updateGift(Request $request, int $id)
    {
        $gift = SeasonalGift::findOrFail($id);

        $validated = $request->validate([
            'seasonal_mode_id' => 'nullable|exists:seasonal_modes,id',
            'name' => 'required|string|max:160',
            'description' => 'nullable|string|max:1000',
            'icon_or_image' => 'nullable|string|max:255',
            'gift_type' => 'required|in:wallet_credit,sms_credit,boosting_credit,discount_coupon,promo_code,service_credit,custom_reward',
            'reward_value_usd' => 'required|numeric|min:0|max:5000',
            'promo_code' => 'nullable|string|max:60',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'distribution_mode' => 'required|in:manual_claim,auto_distribute,selected_users,all_eligible',
            'claim_button_text' => 'required|string|max:100',
            'max_total_claims' => 'required|integer|min:0|max:1000000',
            'max_claims_per_user' => 'required|integer|min:1|max:50',
            'min_account_age_days' => 'nullable|integer|min:0',
            'min_wallet_funding_usd' => 'nullable|numeric|min:0',
            'min_transactions_count' => 'nullable|integer|min:0',
            'eligible_user_ids_csv' => 'nullable|string|max:2000',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'gift_image_file' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
        ]);

        $uploadedIcon = $this->handleImageUpload($request, 'gift_image_file', 'seasonal/gifts');
        $eligibleIds = $this->parseUserIdsCsv($validated['eligible_user_ids_csv'] ?? '');

        $gift->update([
            'seasonal_mode_id' => ($validated['seasonal_mode_id'] ?? null) ?: null,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? '',
            'icon_or_image' => $uploadedIcon ?: (($validated['icon_or_image'] ?? '') ?: $gift->icon_or_image),
            'gift_type' => $validated['gift_type'],
            'reward_value_usd' => round((float) $validated['reward_value_usd'], 4),
            'promo_code' => ! empty($validated['promo_code']) ? strtoupper(trim($validated['promo_code'])) : null,
            'discount_percent' => isset($validated['discount_percent']) ? (float) $validated['discount_percent'] : null,
            'distribution_mode' => $validated['distribution_mode'],
            'claim_button_text' => $validated['claim_button_text'],
            'max_total_claims' => (int) $validated['max_total_claims'],
            'max_claims_per_user' => (int) $validated['max_claims_per_user'],
            'min_account_age_days' => (int) ($validated['min_account_age_days'] ?? 0),
            'min_wallet_funding_usd' => round((float) ($validated['min_wallet_funding_usd'] ?? 0), 4),
            'min_transactions_count' => (int) ($validated['min_transactions_count'] ?? 0),
            'eligible_user_ids' => $eligibleIds ?: null,
            'starts_at' => $validated['starts_at'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        $admin = auth()->user();
        $this->engine->logAudit($admin?->id, $admin?->name ?? 'Admin', 'update_gift', 'seasonal_gift', $gift->id, "Updated seasonal gift '{$gift->name}'");
        $this->engine->clearCache();

        return back()->with('success', "Seasonal gift '{$gift->name}' updated!");
    }

    /**
     * Toggle active status of a Seasonal Gift.
     */
    public function toggleGift(int $id)
    {
        $gift = SeasonalGift::findOrFail($id);
        $gift->update(['is_active' => ! $gift->is_active]);

        $admin = auth()->user();
        $this->engine->logAudit($admin?->id, $admin?->name ?? 'Admin', 'toggle_gift', 'seasonal_gift', $gift->id, ($gift->is_active ? 'Enabled' : 'Disabled') . " gift '{$gift->name}'");
        $this->engine->clearCache();

        return back()->with('success', "Gift '{$gift->name}' is now " . ($gift->is_active ? 'ACTIVE' : 'INACTIVE') . '.');
    }

    /**
     * Distribute a gift immediately to selected users or all eligible users.
     */
    public function distributeGift(Request $request, int $id)
    {
        $gift = SeasonalGift::findOrFail($id);

        $validated = $request->validate([
            'target_mode' => 'required|in:selected_users,all_eligible',
            'user_ids_csv' => 'nullable|string|max:2000',
        ]);

        $userIds = $this->parseUserIdsCsv($validated['user_ids_csv'] ?? '');
        if ($validated['target_mode'] === 'selected_users' && empty($userIds)) {
            $userIds = (array) ($gift->eligible_user_ids ?? []);
        }

        if ($validated['target_mode'] === 'selected_users' && empty($userIds)) {
            return back()->with('error', 'Please enter at least one User ID or email for Selected Users distribution.');
        }

        $result = $this->engine->distributeGiftBatch($gift, $validated['target_mode'], $userIds, auth()->user());

        return back()->with(
            'success',
            "Distributed '{$gift->name}' to {$result['distributed_count']} user(s) (Total: $" . number_format($result['total_usd'], 2) . " USD, Skipped/Already Claimed: {$result['skipped_count']})."
        );
    }

    /**
     * Delete a Seasonal Gift.
     */
    public function destroyGift(int $id)
    {
        $gift = SeasonalGift::findOrFail($id);
        $name = $gift->name;
        $gift->delete();

        $admin = auth()->user();
        $this->engine->logAudit($admin?->id, $admin?->name ?? 'Admin', 'delete_gift', 'seasonal_gift', $id, "Deleted seasonal gift '{$name}'");
        $this->engine->clearCache();

        return back()->with('success', "Seasonal gift '{$name}' deleted.");
    }

    /**
     * Create a new Seasonal Announcement.
     */
    public function storeAnnouncement(Request $request)
    {
        $validated = $request->validate([
            'seasonal_mode_id' => 'nullable|exists:seasonal_modes,id',
            'title' => 'required|string|max:200',
            'message' => 'required|string|max:2000',
            'badge_text' => 'nullable|string|max:60',
            'cta_text' => 'nullable|string|max:80',
            'cta_link' => 'nullable|string|max:255',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        $ann = SeasonalAnnouncement::create([
            'seasonal_mode_id' => ($validated['seasonal_mode_id'] ?? null) ?: null,
            'title' => $validated['title'],
            'message' => $validated['message'],
            'badge_text' => ($validated['badge_text'] ?? '') ?: 'Celebration',
            'cta_text' => ($validated['cta_text'] ?? '') ?: null,
            'cta_link' => ($validated['cta_link'] ?? '') ?: null,
            'is_dismissible' => $request->boolean('is_dismissible', true),
            'show_on_dashboard' => $request->boolean('show_on_dashboard', true),
            'show_on_home' => $request->boolean('show_on_home', true),
            'send_in_app_notification' => $request->boolean('send_in_app_notification', false),
            'send_email_notification' => $request->boolean('send_email_notification', false),
            'starts_at' => $validated['starts_at'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
            'is_active' => true,
        ]);

        $admin = auth()->user();
        $this->engine->logAudit($admin?->id, $admin?->name ?? 'Admin', 'create_announcement', 'seasonal_announcement', $ann->id, "Created announcement '{$ann->title}'");

        if ($ann->send_in_app_notification || $ann->send_email_notification) {
            $sent = $this->engine->broadcastAnnouncement($ann, $ann->send_in_app_notification, $ann->send_email_notification, $admin);
            $this->engine->clearCache();
            return back()->with('success', "Seasonal announcement created and broadcasted to {$sent} active users!");
        }

        $this->engine->clearCache();

        return back()->with('success', 'Seasonal announcement created successfully!');
    }

    /**
     * Toggle active status of a Seasonal Announcement.
     */
    public function toggleAnnouncement(int $id)
    {
        $ann = SeasonalAnnouncement::findOrFail($id);
        $ann->update(['is_active' => ! $ann->is_active]);
        $this->engine->clearCache();

        return back()->with('success', "Announcement '{$ann->title}' is now " . ($ann->is_active ? 'ACTIVE' : 'INACTIVE') . '.');
    }

    /**
     * Broadcast an existing Seasonal Announcement to users.
     */
    public function broadcastAnnouncement(Request $request, int $id)
    {
        $ann = SeasonalAnnouncement::findOrFail($id);
        $count = $this->engine->broadcastAnnouncement(
            $ann,
            true,
            $request->boolean('send_email', false),
            auth()->user()
        );

        return back()->with('success', "Announcement '{$ann->title}' broadcasted to {$count} active users!");
    }

    /**
     * Delete a Seasonal Announcement.
     */
    public function destroyAnnouncement(int $id)
    {
        $ann = SeasonalAnnouncement::findOrFail($id);
        $ann->delete();
        $this->engine->clearCache();

        return back()->with('success', 'Seasonal announcement deleted.');
    }

    /**
     * Update an Advent Calendar Day (1..25).
     */
    public function updateAdventDay(Request $request, int $id)
    {
        $day = SeasonalAdventDay::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:140',
            'description' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:40',
            'reward_type' => 'required|in:wallet_credit,sms_credit,boosting_credit,discount_coupon,promo_code',
            'reward_value_ngn' => 'nullable|numeric|min:1|max:2',
            'reward_value_usd' => 'nullable|numeric|min:0|max:1000',
            'promo_code' => 'nullable|string|max:60',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'unlock_date' => 'nullable|date',
        ]);

        $rate = $this->engine->getNgnPerUsdRate();
        if (isset($validated['reward_value_ngn']) && $validated['reward_value_ngn'] !== null && $validated['reward_value_ngn'] !== '') {
            $ngnVal = max(1.0, min(2.0, (float) $validated['reward_value_ngn']));
            $rewardUsd = round($ngnVal / $rate, 4);
        } else {
            $rawUsd = (float) ($validated['reward_value_usd'] ?? $day->reward_value_usd);
            $maxTwoNairaUsd = round(2.0 / $rate, 4);
            $minOneNairaUsd = round(1.0 / $rate, 4);
            $rewardUsd = $rawUsd > 0 ? max($minOneNairaUsd, min($maxTwoNairaUsd, round($rawUsd, 4))) : 0.0;
        }

        $day->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? '',
            'icon' => ($validated['icon'] ?? '') ?: $day->icon,
            'reward_type' => $validated['reward_type'],
            'reward_value_usd' => $rewardUsd,
            'promo_code' => ! empty($validated['promo_code']) ? strtoupper(trim($validated['promo_code'])) : null,
            'discount_percent' => isset($validated['discount_percent']) ? (float) $validated['discount_percent'] : null,
            'unlock_date' => $validated['unlock_date'] ?? $day->unlock_date,
            'force_unlocked' => $request->boolean('force_unlocked'),
            'is_enabled' => $request->boolean('is_enabled', false),
        ]);

        $admin = auth()->user();
        $this->engine->logAudit($admin?->id, $admin?->name ?? 'Admin', 'update_advent_day', 'seasonal_advent_day', $day->id, "Updated Advent Calendar Day {$day->day_number}");

        return back()->with('success', "Advent Calendar Day {$day->day_number} updated!");
    }

    /**
     * Create or update a Seasonal Challenge.
     */
    public function storeChallenge(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:160',
            'description' => 'required|string|max:255',
            'icon' => 'nullable|string|max:40',
            'period_type' => 'required|in:daily,weekly,seasonal',
            'action_type' => 'required|in:visit_christmas_hub,complete_profile,claim_advent_day,decorate_tree,build_snowman,explore_village,generate_greeting_card',
            'target_count' => 'required|integer|min:1|max:100',
            'reward_value_ngn' => 'nullable|numeric|min:1|max:2',
            'reward_value_usd' => 'nullable|numeric|min:0|max:500',
            'expires_at' => 'nullable|date',
        ]);

        $rate = $this->engine->getNgnPerUsdRate();
        if (isset($validated['reward_value_ngn']) && $validated['reward_value_ngn'] !== null && $validated['reward_value_ngn'] !== '') {
            $ngnVal = max(1.0, min(2.0, (float) $validated['reward_value_ngn']));
            $rewardUsd = round($ngnVal / $rate, 4);
        } else {
            $rawUsd = (float) ($validated['reward_value_usd'] ?? (1.0 / $rate));
            $rewardUsd = max(round(1.0 / $rate, 4), min(round(2.0 / $rate, 4), round($rawUsd, 4)));
        }

        $challenge = SeasonalChallenge::create([
            'code' => Str::slug($validated['title']) . '-' . strtolower(Str::random(4)),
            'title' => $validated['title'],
            'description' => $validated['description'],
            'icon' => ($validated['icon'] ?? '') ?: '🎄',
            'period_type' => $validated['period_type'],
            'action_type' => $validated['action_type'],
            'target_count' => (int) $validated['target_count'],
            'reward_value_usd' => $rewardUsd,
            'expires_at' => $validated['expires_at'] ?? null,
            'is_active' => true,
        ]);

        $admin = auth()->user();
        $this->engine->logAudit($admin?->id, $admin?->name ?? 'Admin', 'create_challenge', 'seasonal_challenge', $challenge->id, "Created Christmas challenge '{$challenge->title}'");

        return back()->with('success', "Christmas challenge '{$challenge->title}' created!");
    }

    public function toggleChallenge(int $id)
    {
        $ch = SeasonalChallenge::findOrFail($id);
        $ch->update(['is_active' => ! $ch->is_active]);

        return back()->with('success', "Challenge '{$ch->title}' is now " . ($ch->is_active ? 'ACTIVE' : 'PAUSED') . '.');
    }

    public function destroyChallenge(int $id)
    {
        $ch = SeasonalChallenge::findOrFail($id);
        $ch->delete();

        return back()->with('success', 'Christmas challenge deleted.');
    }

    /**
     * Create a new Seasonal Badge.
     */
    public function storeBadge(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'description' => 'required|string|max:255',
            'icon' => 'nullable|string|max:40',
            'condition_type' => 'required|in:visit_hub,save_tree,save_snowman,claim_advent_1,claim_advent_5,explore_village,generate_card',
            'condition_target' => 'nullable|integer|min:1|max:100',
        ]);

        SeasonalBadge::create([
            'code' => Str::slug($validated['name']) . '-' . strtolower(Str::random(4)),
            'name' => $validated['name'],
            'description' => $validated['description'],
            'icon' => ($validated['icon'] ?? '') ?: '🏅',
            'condition_type' => $validated['condition_type'],
            'condition_target' => (int) ($validated['condition_target'] ?? 1),
            'is_active' => true,
        ]);

        return back()->with('success', "Seasonal badge '{$validated['name']}' created!");
    }

    public function toggleBadge(int $id)
    {
        $b = SeasonalBadge::findOrFail($id);
        $b->update(['is_active' => ! $b->is_active]);

        return back()->with('success', "Badge '{$b->name}' is now " . ($b->is_active ? 'ACTIVE' : 'PAUSED') . '.');
    }

    /**
     * Update Interactive Christmas Feature Switches (Advent, Tree, Snowman, Village, Cards, Music, etc.).
     */
    public function updateInteractiveFeatures(Request $request)
    {
        $keys = [
            'xmas_hub_button_enabled',
            'xmas_claim_buttons_enabled',
            'xmas_advent_enabled',
            'xmas_tree_enabled',
            'xmas_snowman_enabled',
            'xmas_mystery_gifts_enabled',
            'xmas_village_enabled',
            'xmas_greeting_cards_enabled',
            'xmas_badges_enabled',
            'xmas_challenges_enabled',
            'xmas_popups_enabled',
            'xmas_music_enabled',
        ];

        foreach ($keys as $k) {
            SeasonalSetting::setVal($k, $request->boolean($k) ? '1' : '0');
        }

        $admin = auth()->user();
        $this->engine->logAudit($admin?->id, $admin?->name ?? 'Admin', 'update_interactive_features', 'seasonal_setting', null, 'Updated interactive Christmas feature toggles');
        $this->engine->clearCache();

        return back()->with('success', 'Interactive Christmas features & button toggles updated!');
    }

    /**
     * Quick 1-click toggle for Christmas Hub Button, Gift Claim Buttons, or Resetting all Christmas Hub Gift Prices to ₦1–₦2.
     */
    public function toggleChristmasButton(Request $request)
    {
        $target = (string) $request->input('target', 'xmas_hub_button_enabled');
        $admin = auth()->user();

        if ($target === 'reset_prices_1_2_naira') {
            $rate = $this->engine->getNgnPerUsdRate();
            $oneNairaUsd = round(1.00 / $rate, 4);
            $twoNairaUsd = round(2.00 / $rate, 4);

            SeasonalAdventDay::all()->each(function ($day) use ($oneNairaUsd, $twoNairaUsd) {
                if ((float) $day->reward_value_usd > 0) {
                    $day->update([
                        'reward_value_usd' => ($day->day_number === 25 || $day->day_number % 5 === 0) ? $twoNairaUsd : $oneNairaUsd,
                    ]);
                }
            });

            SeasonalChallenge::all()->each(function ($ch) use ($oneNairaUsd, $twoNairaUsd) {
                $ch->update([
                    'reward_value_usd' => $ch->period_type === 'daily' ? $oneNairaUsd : $twoNairaUsd,
                ]);
            });

            SeasonalGift::where('reward_value_usd', '>', 0)->get()->each(function ($g) use ($twoNairaUsd) {
                $g->update([
                    'reward_value_usd' => $twoNairaUsd,
                ]);
            });

            $this->engine->logAudit($admin?->id, $admin?->name ?? 'Admin', 'reset_christmas_prices', 'seasonal_setting', null, 'Set all Christmas Hub gift, Advent & Challenge prices to ₦1.00 – ₦2.00');
            $this->engine->clearCache();

            return back()->with('success', '✅ All Christmas Hub gift boxes, Advent Calendar days, and Challenges have been set to ₦1.00 – ₦2.00 max!');
        }

        if (! in_array($target, ['xmas_hub_button_enabled', 'xmas_claim_buttons_enabled'], true)) {
            $target = 'xmas_hub_button_enabled';
        }

        $settings = $this->engine->getInteractiveFeatureSettings();
        $newState = ! ($settings[$target] ?? true);
        SeasonalSetting::setVal($target, $newState ? '1' : '0');

        $label = $target === 'xmas_hub_button_enabled' ? 'Christmas Hub Button' : 'Christmas Hub Gift Claim Buttons';
        $this->engine->logAudit($admin?->id, $admin?->name ?? 'Admin', 'toggle_christmas_button', 'seasonal_setting', null, ($newState ? 'Enabled ' : 'Disabled ') . $label);
        $this->engine->clearCache();

        return back()->with('success', "{$label} is now " . ($newState ? 'ENABLED ✅' : 'DISABLED 🔒') . '!');
    }

    /**
     * Securely upload an image to public/uploads/{folder}.
     */
    private function handleImageUpload(Request $request, string $field, string $subFolder): ?string
    {
        if (! $request->hasFile($field) || ! $request->file($field)->isValid()) {
            return null;
        }

        $file = $request->file($field);
        $ext = strtolower($file->getClientOriginalExtension() ?: 'png');
        if (! in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
            return null;
        }

        $filename = Str::random(24) . '.' . $ext;
        $targetDir = public_path('uploads/' . trim($subFolder, '/'));
        if (! is_dir($targetDir)) {
            @mkdir($targetDir, 0755, true);
        }

        $file->move($targetDir, $filename);

        return '/uploads/' . trim($subFolder, '/') . '/' . $filename;
    }

    /**
     * Parse comma-separated user IDs or email addresses into an array of integer user IDs.
     */
    private function parseUserIdsCsv(string $raw): array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return [];
        }

        $tokens = array_filter(array_map('trim', preg_split('/[\s,;]+/', $raw)));
        $ids = [];
        $emails = [];

        foreach ($tokens as $tok) {
            if (is_numeric($tok) && (int) $tok > 0) {
                $ids[] = (int) $tok;
            } elseif (str_contains($tok, '@')) {
                $emails[] = strtolower($tok);
            }
        }

        if (! empty($emails)) {
            $matchedIds = \App\Models\User::whereIn('email', $emails)->pluck('id')->map(fn ($id) => (int) $id)->toArray();
            $ids = array_merge($ids, $matchedIds);
        }

        return array_values(array_unique($ids));
    }
}

