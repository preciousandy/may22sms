<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyLoginGift;
use App\Models\Setting;
use App\Models\SmsCountry;
use App\Services\FiveSimService;
use App\Services\DaisySmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    public function index()
    {
        $smsMarkup = Setting::where('key', 'sms_markup_percent')->value('value') ?? 20;
        $totalCountries = SmsCountry::count();
        $defaultCurrency = Setting::where('key', 'default_currency')->value('value') ?? 'USD';
        $ngnPerUsd = Setting::where('key', 'ngn_per_usd')->value('value') ?? 1500;
        $smsPremiumEnabled = (int) (Setting::where('key', 'sms_premium_enabled')->value('value') ?? 1) === 1;
        $smsPremiumMarkupPercent = (float) (Setting::where('key', 'sms_premium_markup_percent')->value('value') ?? 40);
        $paymentpointEnabled = (int) (Setting::where('key', 'payment_method_paymentpoint_enabled')->value('value') ?? 1) === 1;
        $strollwalletEnabled = (int) (Setting::where('key', 'payment_method_strollwallet_enabled')->value('value') ?? 1) === 1;
        $cryptoEnabled = (int) (Setting::where('key', 'payment_method_crypto_enabled')->value('value') ?? 1) === 1;
        $pocketfiEnabled = (int) (Setting::where('key', 'payment_method_pocketfi_enabled')->value('value') ?? 1) === 1;
        $monietudeEnabled = (int) (Setting::where('key', 'payment_method_monietude_enabled')->value('value') ?? 1) === 1;
        $monietudeApiKey = (string) (Setting::where('key', 'monietude_api_key')->value('value') ?? config('services.monietude.api_key', ''));
        $monietudeProvider = (string) (Setting::where('key', 'monietude_provider')->value('value') ?? config('services.monietude.provider', 'palmpay'));
        $smsProvider = (string) (Setting::where('key', 'sms_provider')->value('value') ?? 'herosms');
        $herosmsEnabled = (int) (Setting::where('key', 'sms_provider_herosms_enabled')->value('value') ?? 1) === 1;
        $daisysmsEnabled = (int) (Setting::where('key', 'sms_provider_daisysms_enabled')->value('value') ?? 1) === 1;
        $fivesimEnabled = (int) (Setting::where('key', 'sms_provider_5sim_enabled')->value('value') ?? 1) === 1;
        $herosmsApiKey = (string) (Setting::where('key', 'herosms_api_key')->value('value') ?? config('services.herosms.api_key', ''));
        $daisysmsApiKey = (string) (Setting::where('key', 'daisysms_api_key')->value('value') ?? config('services.daisysms.api_key', ''));
        $siteName = (string) (Setting::where('key', 'site_name')->value('value') ?? 'May22SMS');
        $siteLogoUrl = (string) (Setting::where('key', 'site_logo_url')->value('value') ?? '');
        $homeHeroTitle = (string) (Setting::where('key', 'home_hero_title')->value('value') ?? 'Buy SMS numbers, logs, and boosting services without stress.');
        $homeHeroSubtitle = (string) (Setting::where('key', 'home_hero_subtitle')->value('value') ?? 'Fund your wallet, apply promo codes, and place orders in seconds. Built for speed, clarity, and repeat use.');
        $announcementEnabled = (int) (Setting::where('key', 'announcement_enabled')->value('value') ?? 0) === 1;
        $announcementText = (string) (Setting::where('key', 'announcement_text')->value('value') ?? '');
        $announcementLink = (string) (Setting::where('key', 'announcement_link')->value('value') ?? '');
        $popupEnabled = (int) (Setting::where('key', 'site_popup_enabled')->value('value') ?? 0) === 1;
        $popupTitle = (string) (Setting::where('key', 'site_popup_title')->value('value') ?? '');
        $popupMessage = (string) (Setting::where('key', 'site_popup_message')->value('value') ?? '');
        $popupButtonText = (string) (Setting::where('key', 'site_popup_button_text')->value('value') ?? '');
        $popupButtonLink = (string) (Setting::where('key', 'site_popup_button_link')->value('value') ?? '');
        $requireStaff2fa = (int) (Setting::where('key', 'require_staff_2fa')->value('value') ?? 0) === 1;

        // SEO Settings
        $seoTitle = (string) (Setting::where('key', 'seo_title')->value('value') ?? '');
        $seoDescription = (string) (Setting::where('key', 'seo_description')->value('value') ?? '');
        $seoKeywords = (string) (Setting::where('key', 'seo_keywords')->value('value') ?? '');
        $seoOgImage = (string) (Setting::where('key', 'seo_og_image')->value('value') ?? '');
        $seoOgTitle = (string) (Setting::where('key', 'seo_og_title')->value('value') ?? '');
        $seoOgDescription = (string) (Setting::where('key', 'seo_og_description')->value('value') ?? '');
        $seoTwitterCard = (string) (Setting::where('key', 'seo_twitter_card')->value('value') ?? 'summary_large_image');

        $blockedIpsRaw = (string) (Setting::where('key', 'blocked_ips')->value('value') ?? '');
        $blockedIpsList = [];
        if ($blockedIpsRaw !== '') {
            $decoded = json_decode($blockedIpsRaw, true);
            if (is_array($decoded)) {
                $blockedIpsList = array_values(array_filter(array_map(fn ($v) => is_string($v) ? trim($v) : null, $decoded)));
            }
        }
        $blockedIpsText = implode("\n", $blockedIpsList);

        $smsMarkupByCountry = (string) (Setting::where('key', 'sms_markup_by_country')->value('value') ?? '');
        $smsMarkupByService = (string) (Setting::where('key', 'sms_markup_by_service')->value('value') ?? '');

        $smsCountryLines = [];
        $decodedCountry = $smsMarkupByCountry !== '' ? json_decode($smsMarkupByCountry, true) : null;
        if (is_array($decodedCountry)) {
            foreach ($decodedCountry as $k => $v) {
                if (is_string($k) && is_numeric($v)) {
                    $smsCountryLines[] = $k . '=' . (string) $v;
                }
            }
        }

        $smsServiceLines = [];
        $decodedService = $smsMarkupByService !== '' ? json_decode($smsMarkupByService, true) : null;
        if (is_array($decodedService)) {
            foreach ($decodedService as $k => $v) {
                if (is_string($k) && is_numeric($v)) {
                    $smsServiceLines[] = $k . '=' . (string) $v;
                }
            }
        }

        // Daily Login Gift Settings & Stats
        $dailyGiftEnabled = (int) (Setting::where('key', 'daily_gift_enabled')->value('value') ?? 1) === 1;
        $dailyGiftMaxClaimsPerDay = (int) (Setting::where('key', 'daily_gift_max_claims_per_day')->value('value') ?? 0);
        $dailyGiftType = (string) (Setting::where('key', 'daily_gift_type')->value('value') ?? 'streak');
        $dailyGiftFixedAmount = (float) (Setting::where('key', 'daily_gift_fixed_amount')->value('value') ?? 0.05);
        $dailyGiftMinAmount = (float) (Setting::where('key', 'daily_gift_min_amount')->value('value') ?? 0.02);
        $dailyGiftMaxAmount = (float) (Setting::where('key', 'daily_gift_max_amount')->value('value') ?? 0.10);
        
        $rawStreakAmounts = Setting::where('key', 'daily_gift_streak_amounts')->value('value');
        $defaultStreakAmounts = [1 => 0.02, 2 => 0.04, 3 => 0.06, 4 => 0.08, 5 => 0.10, 6 => 0.15, 7 => 0.25];
        $dailyGiftStreakAmounts = $defaultStreakAmounts;
        if ($rawStreakAmounts) {
            $decoded = json_decode($rawStreakAmounts, true);
            if (is_array($decoded)) {
                for ($i = 1; $i <= 7; $i++) {
                    $dailyGiftStreakAmounts[$i] = isset($decoded[$i]) ? (float) $decoded[$i] : $defaultStreakAmounts[$i];
                }
            }
        }

        $dailyGiftTotalClaims = DailyLoginGift::count();
        $dailyGiftTotalClaimedAmount = (float) DailyLoginGift::sum('amount_usd');
        $dailyGiftTodayClaims = DailyLoginGift::getTodayClaimsCount();

        $maintenanceEnabled = app()->isDownForMaintenance();
        $health = [
            'app_env' => (string) config('app.env'),
            'app_debug' => (bool) config('app.debug'),
            'queue_connection' => (string) config('queue.default'),
            'cache_driver' => (string) config('cache.default'),
            'storage_writable' => is_writable(storage_path()),
            'storage_linked' => file_exists(public_path('storage')) || is_link(public_path('storage')),
        ];

        return view('admin.settings', compact(
            'smsMarkup',
            'totalCountries',
            'defaultCurrency',
            'ngnPerUsd',
            'smsPremiumEnabled',
            'smsPremiumMarkupPercent',
            'paymentpointEnabled',
            'strollwalletEnabled',
            'cryptoEnabled',
            'pocketfiEnabled',
            'monietudeEnabled',
            'monietudeApiKey',
            'monietudeProvider',
            'smsProvider',
            'herosmsEnabled',
            'daisysmsEnabled',
            'fivesimEnabled',
            'herosmsApiKey',
            'daisysmsApiKey',
            'siteName',
            'siteLogoUrl',
            'homeHeroTitle',
            'homeHeroSubtitle',
            'announcementEnabled',
            'announcementText',
            'announcementLink',
            'popupEnabled',
            'popupTitle',
            'popupMessage',
            'popupButtonText',
            'popupButtonLink',
            'requireStaff2fa',
            'blockedIpsText',
            'maintenanceEnabled',
            'health',
            'smsCountryLines',
            'smsServiceLines',
            'seoTitle',
            'seoDescription',
            'seoKeywords',
            'seoOgImage',
            'seoOgTitle',
            'seoOgDescription',
            'seoTwitterCard',
            'dailyGiftEnabled',
            'dailyGiftMaxClaimsPerDay',
            'dailyGiftType',
            'dailyGiftFixedAmount',
            'dailyGiftMinAmount',
            'dailyGiftMaxAmount',
            'dailyGiftStreakAmounts',
            'dailyGiftTotalClaims',
            'dailyGiftTotalClaimedAmount',
            'dailyGiftTodayClaims'
        ));
    }

    public function update(Request $request)
    {
        $request->validate([
            'sms_markup_percent' => 'nullable|numeric|min:0',
            'sms_premium_enabled' => 'nullable|in:0,1',
            'sms_premium_markup_percent' => 'nullable|numeric|min:0',
            'sms_provider' => 'nullable|in:herosms,daisysms,5sim',
            'sms_provider_herosms_enabled' => 'nullable|in:0,1',
            'sms_provider_daisysms_enabled' => 'nullable|in:0,1',
            'sms_provider_5sim_enabled' => 'nullable|in:0,1',
            'herosms_api_key' => 'nullable|string|max:255',
            'daisysms_api_key' => 'nullable|string|max:255',
            'default_currency' => 'nullable|in:USD,NGN',
            'ngn_per_usd' => 'nullable|numeric|min:1',
            'payment_method_paymentpoint_enabled' => 'nullable|in:0,1',
            'payment_method_strollwallet_enabled' => 'nullable|in:0,1',
            'payment_method_crypto_enabled' => 'nullable|in:0,1',
            'payment_method_pocketfi_enabled' => 'nullable|in:0,1',
            'payment_method_monietude_enabled' => 'nullable|in:0,1',
            'monietude_api_key' => 'nullable|string|max:255',
            'monietude_provider' => 'nullable|string|max:50',
            'site_name' => 'nullable|string|max:80',
            'site_logo_url' => 'nullable|string|max:500',
            'home_hero_title' => 'nullable|string|max:200',
            'home_hero_subtitle' => 'nullable|string|max:300',
            'announcement_enabled' => 'nullable|in:0,1',
            'announcement_text' => 'nullable|string|max:180',
            'announcement_link' => 'nullable|string|max:500',
            'site_popup_enabled' => 'nullable|in:0,1',
            'site_popup_title' => 'nullable|string|max:120',
            'site_popup_message' => 'nullable|string|max:1000',
            'site_popup_button_text' => 'nullable|string|max:40',
            'site_popup_button_link' => 'nullable|string|max:500',
            'require_staff_2fa' => 'nullable|in:0,1',
            'blocked_ips_text' => 'nullable|string|max:5000',
            'sms_markup_by_country_text' => 'nullable|string|max:8000',
            'sms_markup_by_service_text' => 'nullable|string|max:8000',
            // SEO
            'seo_title' => 'nullable|string|max:60',
            'seo_description' => 'nullable|string|max:160',
            'seo_keywords' => 'nullable|string|max:500',
            'seo_og_image' => 'nullable|url|max:500',
            'seo_og_title' => 'nullable|string|max:60',
            'seo_og_description' => 'nullable|string|max:160',
            'seo_twitter_card' => 'nullable|in:summary,summary_large_image,app,player',
            // Daily Login Gift
            'daily_gift_enabled' => 'nullable|in:0,1',
            'daily_gift_max_claims_per_day' => 'nullable|integer|min:0',
            'daily_gift_type' => 'nullable|in:streak,fixed,random',
            'daily_gift_fixed_amount' => 'nullable|numeric|min:0.001',
            'daily_gift_min_amount' => 'nullable|numeric|min:0.001',
            'daily_gift_max_amount' => 'nullable|numeric|min:0.001',
            'daily_gift_streak_amounts' => 'nullable|array',
            'daily_gift_streak_amounts.*' => 'nullable|numeric|min:0.001',
        ]);

        if ($request->filled('sms_provider')) {
            Setting::updateOrCreate(
                ['key' => 'sms_provider'],
                ['value' => $request->sms_provider]
            );
        }

        if ($request->has('sms_provider_herosms_enabled')) {
            Setting::updateOrCreate(
                ['key' => 'sms_provider_herosms_enabled'],
                ['value' => (string) ((int) $request->sms_provider_herosms_enabled)]
            );
        }

        if ($request->has('sms_provider_daisysms_enabled')) {
            Setting::updateOrCreate(
                ['key' => 'sms_provider_daisysms_enabled'],
                ['value' => (string) ((int) $request->sms_provider_daisysms_enabled)]
            );
        }

        if ($request->has('sms_provider_5sim_enabled')) {
            Setting::updateOrCreate(
                ['key' => 'sms_provider_5sim_enabled'],
                ['value' => (string) ((int) $request->sms_provider_5sim_enabled)]
            );
        }

        if ($request->has('herosms_api_key')) {
            Setting::updateOrCreate(
                ['key' => 'herosms_api_key'],
                ['value' => (string) $request->herosms_api_key]
            );
        }

        if ($request->has('daisysms_api_key')) {
            Setting::updateOrCreate(
                ['key' => 'daisysms_api_key'],
                ['value' => (string) $request->daisysms_api_key]
            );
        }

        if ($request->filled('sms_markup_percent')) {
            Setting::updateOrCreate(
                ['key' => 'sms_markup_percent'],
                ['value' => $request->sms_markup_percent]
            );
        }

        if ($request->has('sms_premium_enabled')) {
            Setting::updateOrCreate(
                ['key' => 'sms_premium_enabled'],
                ['value' => (string) ((int) $request->sms_premium_enabled)]
            );
        }

        if ($request->has('sms_premium_markup_percent')) {
            $v = (float) ($request->sms_premium_markup_percent ?? 0);
            Setting::updateOrCreate(
                ['key' => 'sms_premium_markup_percent'],
                ['value' => (string) ($v < 0 ? 0 : $v)]
            );
        }

        if ($request->filled('default_currency')) {
            Setting::updateOrCreate(
                ['key' => 'default_currency'],
                ['value' => $request->default_currency]
            );
        }

        if ($request->filled('ngn_per_usd')) {
            Setting::updateOrCreate(
                ['key' => 'ngn_per_usd'],
                ['value' => $request->ngn_per_usd]
            );
        }

        if ($request->has('payment_method_paymentpoint_enabled')) {
            Setting::updateOrCreate(
                ['key' => 'payment_method_paymentpoint_enabled'],
                ['value' => (string) ((int) $request->payment_method_paymentpoint_enabled)]
            );
        }
        if ($request->has('payment_method_strollwallet_enabled')) {
            Setting::updateOrCreate(
                ['key' => 'payment_method_strollwallet_enabled'],
                ['value' => (string) ((int) $request->payment_method_strollwallet_enabled)]
            );
        }
        if ($request->has('payment_method_crypto_enabled')) {
            Setting::updateOrCreate(
                ['key' => 'payment_method_crypto_enabled'],
                ['value' => (string) ((int) $request->payment_method_crypto_enabled)]
            );
        }

        if ($request->has('payment_method_pocketfi_enabled')) {
            Setting::updateOrCreate(
                ['key' => 'payment_method_pocketfi_enabled'],
                ['value' => (string) ((int) $request->payment_method_pocketfi_enabled)]
            );
        }

        if ($request->has('payment_method_monietude_enabled')) {
            Setting::updateOrCreate(
                ['key' => 'payment_method_monietude_enabled'],
                ['value' => (string) ((int) $request->payment_method_monietude_enabled)]
            );
        }

        if ($request->has('monietude_api_key')) {
            Setting::updateOrCreate(
                ['key' => 'monietude_api_key'],
                ['value' => (string) trim($request->monietude_api_key)]
            );
        }

        if ($request->has('monietude_provider')) {
            Setting::updateOrCreate(
                ['key' => 'monietude_provider'],
                ['value' => (string) trim($request->monietude_provider)]
            );
        }

        if ($request->filled('site_name')) {
            Setting::updateOrCreate(
                ['key' => 'site_name'],
                ['value' => (string) $request->site_name]
            );
        }

        if ($request->has('site_logo_url')) {
            Setting::updateOrCreate(
                ['key' => 'site_logo_url'],
                ['value' => (string) ($request->site_logo_url ?? '')]
            );
        }

        if ($request->has('home_hero_title')) {
            Setting::updateOrCreate(
                ['key' => 'home_hero_title'],
                ['value' => (string) ($request->home_hero_title ?? '')]
            );
        }

        if ($request->has('home_hero_subtitle')) {
            Setting::updateOrCreate(
                ['key' => 'home_hero_subtitle'],
                ['value' => (string) ($request->home_hero_subtitle ?? '')]
            );
        }

        if ($request->has('announcement_enabled')) {
            Setting::updateOrCreate(
                ['key' => 'announcement_enabled'],
                ['value' => (string) ((int) $request->announcement_enabled)]
            );
        }

        if ($request->has('announcement_text')) {
            Setting::updateOrCreate(
                ['key' => 'announcement_text'],
                ['value' => (string) ($request->announcement_text ?? '')]
            );
        }

        if ($request->has('announcement_link')) {
            Setting::updateOrCreate(
                ['key' => 'announcement_link'],
                ['value' => (string) ($request->announcement_link ?? '')]
            );
        }

        if ($request->has('site_popup_enabled')) {
            Setting::updateOrCreate(
                ['key' => 'site_popup_enabled'],
                ['value' => (string) ((int) $request->site_popup_enabled)]
            );
        }

        if ($request->has('site_popup_title')) {
            Setting::updateOrCreate(
                ['key' => 'site_popup_title'],
                ['value' => (string) ($request->site_popup_title ?? '')]
            );
        }

        if ($request->has('site_popup_message')) {
            Setting::updateOrCreate(
                ['key' => 'site_popup_message'],
                ['value' => (string) ($request->site_popup_message ?? '')]
            );
        }

        if ($request->has('site_popup_button_text')) {
            Setting::updateOrCreate(
                ['key' => 'site_popup_button_text'],
                ['value' => (string) ($request->site_popup_button_text ?? '')]
            );
        }

        if ($request->has('site_popup_button_link')) {
            Setting::updateOrCreate(
                ['key' => 'site_popup_button_link'],
                ['value' => (string) ($request->site_popup_button_link ?? '')]
            );
        }

        if ($request->has('require_staff_2fa')) {
            Setting::updateOrCreate(
                ['key' => 'require_staff_2fa'],
                ['value' => (string) ((int) $request->require_staff_2fa)]
            );
        }

        if ($request->has('blocked_ips_text')) {
            $raw = (string) ($request->blocked_ips_text ?? '');
            $parts = preg_split('/[\s,]+/', str_replace("\r", "\n", $raw));
            $ips = [];
            if (is_array($parts)) {
                foreach ($parts as $p) {
                    $p = trim((string) $p);
                    if ($p === '') {
                        continue;
                    }
                    if (filter_var($p, FILTER_VALIDATE_IP)) {
                        $ips[] = $p;
                    }
                }
            }
            $ips = array_values(array_unique($ips));
            Setting::updateOrCreate(
                ['key' => 'blocked_ips'],
                ['value' => json_encode($ips)]
            );
            Cache::forget('security:blocked_ips:v1');
        }

        if ($request->has('sms_markup_by_country_text')) {
            $raw = (string) ($request->sms_markup_by_country_text ?? '');
            $lines = explode("\n", str_replace("\r", '', $raw));
            $map = [];
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }
                $parts = array_map('trim', explode('=', $line, 2));
                if (count($parts) !== 2) {
                    continue;
                }
                $key = strtolower(trim((string) ($parts[0] ?? '')));
                $val = (string) ($parts[1] ?? '');
                if ($key === '' || ! is_numeric($val)) {
                    continue;
                }
                $num = (float) $val;
                if ($num < 0) {
                    $num = 0;
                }
                $map[$key] = $num;
            }
            Setting::updateOrCreate(
                ['key' => 'sms_markup_by_country'],
                ['value' => json_encode($map)]
            );
        }

        if ($request->has('sms_markup_by_service_text')) {
            $raw = (string) ($request->sms_markup_by_service_text ?? '');
            $lines = explode("\n", str_replace("\r", '', $raw));
            $map = [];
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }
                $parts = array_map('trim', explode('=', $line, 2));
                if (count($parts) !== 2) {
                    continue;
                }
                $key = strtolower(trim((string) ($parts[0] ?? '')));
                $val = (string) ($parts[1] ?? '');
                if ($key === '' || ! is_numeric($val)) {
                    continue;
                }
                $num = (float) $val;
                if ($num < 0) {
                    $num = 0;
                }
                $map[$key] = $num;
            }
            Setting::updateOrCreate(
                ['key' => 'sms_markup_by_service'],
                ['value' => json_encode($map)]
            );
        }

        // SEO Settings
        if ($request->has('seo_title')) {
            Setting::updateOrCreate(
                ['key' => 'seo_title'],
                ['value' => (string) ($request->seo_title ?? '')]
            );
        }
        if ($request->has('seo_description')) {
            Setting::updateOrCreate(
                ['key' => 'seo_description'],
                ['value' => (string) ($request->seo_description ?? '')]
            );
        }
        if ($request->has('seo_keywords')) {
            Setting::updateOrCreate(
                ['key' => 'seo_keywords'],
                ['value' => (string) ($request->seo_keywords ?? '')]
            );
        }
        if ($request->has('seo_og_image')) {
            Setting::updateOrCreate(
                ['key' => 'seo_og_image'],
                ['value' => (string) ($request->seo_og_image ?? '')]
            );
        }
        if ($request->has('seo_og_title')) {
            Setting::updateOrCreate(
                ['key' => 'seo_og_title'],
                ['value' => (string) ($request->seo_og_title ?? '')]
            );
        }
        if ($request->has('seo_og_description')) {
            Setting::updateOrCreate(
                ['key' => 'seo_og_description'],
                ['value' => (string) ($request->seo_og_description ?? '')]
            );
        }
        if ($request->has('seo_twitter_card')) {
            Setting::updateOrCreate(
                ['key' => 'seo_twitter_card'],
                ['value' => (string) ($request->seo_twitter_card ?? 'summary_large_image')]
            );
        }

        // Save Daily Login Gift Settings
        if ($request->has('daily_gift_enabled')) {
            Setting::updateOrCreate(
                ['key' => 'daily_gift_enabled'],
                ['value' => (string) ((int) $request->daily_gift_enabled)]
            );
        }

        if ($request->has('daily_gift_max_claims_per_day')) {
            Setting::updateOrCreate(
                ['key' => 'daily_gift_max_claims_per_day'],
                ['value' => (string) max(0, (int) $request->daily_gift_max_claims_per_day)]
            );
        }

        if ($request->filled('daily_gift_type')) {
            Setting::updateOrCreate(
                ['key' => 'daily_gift_type'],
                ['value' => (string) $request->daily_gift_type]
            );
        }

        if ($request->filled('daily_gift_fixed_amount')) {
            Setting::updateOrCreate(
                ['key' => 'daily_gift_fixed_amount'],
                ['value' => (string) max(0.001, (float) $request->daily_gift_fixed_amount)]
            );
        }

        if ($request->filled('daily_gift_min_amount')) {
            Setting::updateOrCreate(
                ['key' => 'daily_gift_min_amount'],
                ['value' => (string) max(0.001, (float) $request->daily_gift_min_amount)]
            );
        }

        if ($request->filled('daily_gift_max_amount')) {
            Setting::updateOrCreate(
                ['key' => 'daily_gift_max_amount'],
                ['value' => (string) max(0.001, (float) $request->daily_gift_max_amount)]
            );
        }

        if ($request->has('daily_gift_streak_amounts') && is_array($request->daily_gift_streak_amounts)) {
            $cleaned = [];
            for ($i = 1; $i <= 7; $i++) {
                $val = $request->daily_gift_streak_amounts[$i] ?? null;
                $cleaned[$i] = ($val !== null && is_numeric($val)) ? max(0.001, (float) $val) : 0.05;
            }
            Setting::updateOrCreate(
                ['key' => 'daily_gift_streak_amounts'],
                ['value' => json_encode($cleaned)]
            );
        }

        Cache::forget('settings:view_defaults:v1');
        Cache::forget('settings:view_defaults:v2');

        return back()->with('success', 'Settings updated successfully.');
    }

    public function maintenance(Request $request)
    {
        $request->validate([
            'action' => 'required|in:up,down,optimize_clear,config_cache,route_cache,view_cache,storage_link',
        ]);

        $action = (string) $request->action;

        try {
            if ($action === 'down') {
                $secret = Str::random(32);
                Artisan::call('down', [
                    '--secret' => $secret,
                ]);
                $bypassUrl = url('/' . $secret);

                return response(
                    '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Maintenance Enabled</title></head><body style="font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial; padding: 24px; color: #111827;"><div style="max-width: 720px; margin: 0 auto;"><h1 style="font-size: 22px; margin: 0 0 12px; font-weight: 800;">Maintenance mode enabled</h1><p style="margin: 0 0 16px; color: #374151; font-weight: 600;">The site will show &quot;Service Unavailable&quot; to normal visitors. Use this bypass link to keep admin access:</p><p style="margin: 0 0 18px; padding: 12px 14px; background: #F9FAFB; border: 1px solid #E5E7EB; border-radius: 12px; font-weight: 800; word-break: break-all;">' . e($bypassUrl) . '</p><a href="' . e($bypassUrl) . '" style="display:inline-block; background:#0d9488; color:#fff; text-decoration:none; padding: 10px 14px; border-radius: 10px; font-weight: 800;">Open bypass link</a><p style="margin: 18px 0 0; color: #6B7280; font-weight: 600;">After opening it once, you can go back to /admin/settings and click &quot;Up&quot; to disable maintenance mode.</p></div></body></html>',
                    200,
                    ['Content-Type' => 'text/html; charset=UTF-8']
                );
            } elseif ($action === 'up') {
                Artisan::call('up');
            } elseif ($action === 'optimize_clear') {
                Artisan::call('optimize:clear');
            } elseif ($action === 'config_cache') {
                Artisan::call('config:cache');
            } elseif ($action === 'route_cache') {
                Artisan::call('route:cache');
            } elseif ($action === 'view_cache') {
                Artisan::call('view:cache');
            } elseif ($action === 'storage_link') {
                Artisan::call('storage:link');
                return back()->with('success', 'Storage link created successfully (public/storage linked to storage/app/public).');
            }
        } catch (\Throwable $e) {
            return back()->with('error', 'Maintenance action failed: ' . $e->getMessage());
        }

        return back()->with('success', 'Maintenance action completed.');
    }

    public function syncCountries(FiveSimService $fiveSim, DaisySmsService $daisySms)
    {
        $smsProvider = (string) (Setting::where('key', 'sms_provider')->value('value') ?? '5sim');
        $countriesData = [];

        if ($smsProvider === 'daisysms') {
            $countriesData = $daisySms->getCountries();
        } else {
            $countriesData = $fiveSim->getCountries();
        }

        if (! is_array($countriesData) || empty($countriesData)) {
            return back()->withErrors(['sync' => "Failed to fetch countries from {$smsProvider}. Check your API key."]);
        }

        $count = 0;
        foreach ($countriesData as $slug => $data) {
            SmsCountry::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $data['text_en'] ?? ucfirst($slug),
                    'iso' => $data['iso'] ?? null,
                ]
            );
            $count++;
        }

        return back()->with('success', "Successfully synced {$count} countries from {$smsProvider}.");
    }
}
