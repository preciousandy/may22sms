<?php

namespace App\Providers;

use App\Models\Setting;
use App\Models\PromoCode;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useTailwind();

        View::composer('*', function ($view) {
            $defaults = Cache::remember('settings:view_defaults:v2', now()->addMinutes(5), function () {
                $defaultCurrency = 'USD';
                $ngnPerUsd = 1500.0;
                $siteName = 'May22SMS';
                $siteLogoUrl = '';
                $homeHeroTitle = 'Buy SMS numbers, logs, and boosting services without stress.';
                $homeHeroSubtitle = 'Fund your wallet, apply promo codes, and place orders in seconds. Built for speed, clarity, and repeat use.';
                $announcementEnabled = false;
                $announcementText = '';
                $announcementLink = '';
                $sitePopupEnabled = false;
                $sitePopupTitle = '';
                $sitePopupMessage = '';
                $sitePopupButtonText = '';
                $sitePopupButtonLink = '';
                $pocketfiEnabled = true;
                $seoTitle = '';
                $seoDescription = '';
                $seoKeywords = '';
                $seoOgImage = '';
                $seoOgTitle = '';
                $seoOgDescription = '';
                $seoTwitterCard = 'summary_large_image';

                if (Schema::hasTable('settings')) {
                    $defaultCurrency = (string) (Setting::where('key', 'default_currency')->value('value') ?? $defaultCurrency);
                    $ngnPerUsd = (float) (Setting::where('key', 'ngn_per_usd')->value('value') ?? $ngnPerUsd);
                    $siteName = (string) (Setting::where('key', 'site_name')->value('value') ?? $siteName);
                    $siteLogoUrl = (string) (Setting::where('key', 'site_logo_url')->value('value') ?? $siteLogoUrl);
                    $homeHeroTitle = (string) (Setting::where('key', 'home_hero_title')->value('value') ?? $homeHeroTitle);
                    $homeHeroSubtitle = (string) (Setting::where('key', 'home_hero_subtitle')->value('value') ?? $homeHeroSubtitle);
                    $announcementEnabled = ((int) (Setting::where('key', 'announcement_enabled')->value('value') ?? 0)) === 1;
                    $announcementText = (string) (Setting::where('key', 'announcement_text')->value('value') ?? '');
                    $announcementLink = (string) (Setting::where('key', 'announcement_link')->value('value') ?? '');
                    $sitePopupEnabled = ((int) (Setting::where('key', 'site_popup_enabled')->value('value') ?? 0)) === 1;
                    $sitePopupTitle = (string) (Setting::where('key', 'site_popup_title')->value('value') ?? '');
                    $sitePopupMessage = (string) (Setting::where('key', 'site_popup_message')->value('value') ?? '');
                    $sitePopupButtonText = (string) (Setting::where('key', 'site_popup_button_text')->value('value') ?? '');
                    $sitePopupButtonLink = (string) (Setting::where('key', 'site_popup_button_link')->value('value') ?? '');
                    $pocketfiEnabled = ((int) (Setting::where('key', 'payment_method_pocketfi_enabled')->value('value') ?? 1)) === 1;
                    $seoTitle = (string) (Setting::where('key', 'seo_title')->value('value') ?? '');
                    $seoDescription = (string) (Setting::where('key', 'seo_description')->value('value') ?? '');
                    $seoKeywords = (string) (Setting::where('key', 'seo_keywords')->value('value') ?? '');
                    $seoOgImage = (string) (Setting::where('key', 'seo_og_image')->value('value') ?? '');
                    $seoOgTitle = (string) (Setting::where('key', 'seo_og_title')->value('value') ?? '');
                    $seoOgDescription = (string) (Setting::where('key', 'seo_og_description')->value('value') ?? '');
                    $seoTwitterCard = (string) (Setting::where('key', 'seo_twitter_card')->value('value') ?? 'summary_large_image');
                }

                $defaultCurrency = strtoupper($defaultCurrency);
                if (! in_array($defaultCurrency, ['USD', 'NGN'], true)) {
                    $defaultCurrency = 'USD';
                }

                if ($ngnPerUsd <= 0) {
                    $ngnPerUsd = 1500.0;
                }

                return [
                    'defaultCurrency' => $defaultCurrency,
                    'ngnPerUsd' => $ngnPerUsd,
                    'siteName' => $siteName,
                    'siteLogoUrl' => $siteLogoUrl,
                    'homeHeroTitle' => $homeHeroTitle,
                    'homeHeroSubtitle' => $homeHeroSubtitle,
                    'announcementEnabled' => (bool) $announcementEnabled,
                    'announcementText' => $announcementText,
                    'announcementLink' => $announcementLink,
                    'sitePopupEnabled' => (bool) $sitePopupEnabled,
                    'sitePopupTitle' => $sitePopupTitle,
                    'sitePopupMessage' => $sitePopupMessage,
                    'sitePopupButtonText' => $sitePopupButtonText,
                    'sitePopupButtonLink' => $sitePopupButtonLink,
                    'pocketfiEnabled' => (bool) $pocketfiEnabled,
                    'seoTitle' => $seoTitle,
                    'seoDescription' => $seoDescription,
                    'seoKeywords' => $seoKeywords,
                    'seoOgImage' => $seoOgImage,
                    'seoOgTitle' => $seoOgTitle,
                    'seoOgDescription' => $seoOgDescription,
                    'seoTwitterCard' => $seoTwitterCard,
                ];
            });

            $sitePopupVersion = md5(json_encode([
                'enabled' => (bool) ($defaults['sitePopupEnabled'] ?? false),
                'title' => (string) ($defaults['sitePopupTitle'] ?? ''),
                'message' => (string) ($defaults['sitePopupMessage'] ?? ''),
                'button_text' => (string) ($defaults['sitePopupButtonText'] ?? ''),
                'button_link' => (string) ($defaults['sitePopupButtonLink'] ?? ''),
            ]));
            $sitePopupHasContent = trim(strip_tags((string) ($defaults['sitePopupTitle'] ?? ''))) !== ''
                || trim(strip_tags((string) ($defaults['sitePopupMessage'] ?? ''))) !== '';
            $sitePopupStorageKey = 'site-popup-dismissed:' . $sitePopupVersion;

            if (auth()->check()) {
                $sitePopupStorageKey = 'site-popup-dismissed:user:' . (string) auth()->id() . ':' . $sitePopupVersion;
            } else {
                $sitePopupStorageKey = 'site-popup-dismissed:guest:' . $sitePopupVersion;
            }

            $currency = strtoupper((string) session('currency', (string) ($defaults['defaultCurrency'] ?? 'USD')));
            if (! in_array($currency, ['USD', 'NGN'], true)) {
                $currency = (string) ($defaults['defaultCurrency'] ?? 'USD');
            }
            $symbol = $currency === 'NGN' ? '₦' : '$';
            $fx = $currency === 'NGN' ? (float) ($defaults['ngnPerUsd'] ?? 1500.0) : 1.0;

            $fxConfig = (array) config('services.fx', []);
            $fxUrl = (string) ($fxConfig['api_url'] ?? '');
            $fxTimeout = (int) ($fxConfig['timeout_seconds'] ?? 4);
            if ($fxTimeout <= 0) {
                $fxTimeout = 4;
            }
            $fxCacheMinutes = (int) ($fxConfig['cache_minutes'] ?? 30);
            if ($fxCacheMinutes <= 0) {
                $fxCacheMinutes = 30;
            }

            $live = null;
            if ($fxUrl !== '') {
                $live = Cache::remember('fx:usd_ngn:live', now()->addMinutes($fxCacheMinutes), function () use ($fxUrl, $fxTimeout) {
                    try {
                        $response = Http::timeout($fxTimeout)
                            ->acceptJson()
                            ->get($fxUrl);

                        if (! $response->successful()) {
                            return null;
                        }

                        $json = $response->json();
                        if (! is_array($json)) {
                            return null;
                        }

                        $rate = null;
                        if (isset($json['rates']['NGN'])) {
                            $rate = $json['rates']['NGN'];
                        } elseif (isset($json['conversion_rates']['NGN'])) {
                            $rate = $json['conversion_rates']['NGN'];
                        }

                        if (! is_numeric($rate) || (float) $rate <= 0) {
                            return null;
                        }

                        return [
                            'rate' => (float) $rate,
                            'fetched_at' => now()->toIsoString(),
                        ];
                    } catch (\Throwable $e) {
                        return null;
                    }
                });
            }

            $view->with('currency', $currency);
            $view->with('currencySymbol', $symbol);
            $view->with('currencyFx', $fx);
            $view->with('ngnPerUsd', (float) ($defaults['ngnPerUsd'] ?? 1500.0));
            $view->with('siteName', (string) ($defaults['siteName'] ?? 'May22SMS'));
            $view->with('siteLogoUrl', (string) ($defaults['siteLogoUrl'] ?? ''));
            $view->with('homeHeroTitle', (string) ($defaults['homeHeroTitle'] ?? ''));
            $view->with('homeHeroSubtitle', (string) ($defaults['homeHeroSubtitle'] ?? ''));
            $view->with('announcementEnabled', (bool) ($defaults['announcementEnabled'] ?? false));
            $view->with('announcementText', (string) ($defaults['announcementText'] ?? ''));
            $view->with('announcementLink', (string) ($defaults['announcementLink'] ?? ''));
            $view->with('sitePopupEnabled', (bool) ($defaults['sitePopupEnabled'] ?? false));
            $view->with('sitePopupTitle', (string) ($defaults['sitePopupTitle'] ?? ''));
            $view->with('sitePopupMessage', (string) ($defaults['sitePopupMessage'] ?? ''));
            $view->with('sitePopupButtonText', (string) ($defaults['sitePopupButtonText'] ?? ''));
            $view->with('sitePopupButtonLink', (string) ($defaults['sitePopupButtonLink'] ?? ''));
            $view->with('sitePopupVersion', $sitePopupVersion);
            $view->with('sitePopupHasContent', $sitePopupHasContent);
            $view->with('sitePopupStorageKey', $sitePopupStorageKey);
            $view->with('liveNgnPerUsd', is_array($live) && isset($live['rate']) ? (float) $live['rate'] : null);
            $view->with('liveNgnPerUsdFetchedAt', is_array($live) && isset($live['fetched_at']) ? (string) $live['fetched_at'] : null);
            $view->with('seoTitle', (string) ($defaults['seoTitle'] ?? ''));
            $view->with('seoDescription', (string) ($defaults['seoDescription'] ?? ''));
            $view->with('seoKeywords', (string) ($defaults['seoKeywords'] ?? ''));
            $view->with('seoOgImage', (string) ($defaults['seoOgImage'] ?? ''));
            $view->with('seoOgTitle', (string) ($defaults['seoOgTitle'] ?? ''));
            $view->with('seoOgDescription', (string) ($defaults['seoOgDescription'] ?? ''));
            $view->with('seoTwitterCard', (string) ($defaults['seoTwitterCard'] ?? 'summary_large_image'));
            $rawAppUrl = (string) config('app.url');
            $isLocal = $rawAppUrl === '' || str_contains($rawAppUrl, 'localhost') || str_contains($rawAppUrl, '127.0.0.1');
            $emailPublicUrl = $isLocal ? 'https://may22sim.com' : rtrim($rawAppUrl, '/');
            $emailLogoUrl = $emailPublicUrl . '/logo/favicon-192.png';
            $view->with('emailPublicUrl', $emailPublicUrl);
            $view->with('emailLogoUrl', $emailLogoUrl);

            $view->with('formatAmount', function ($amountUsd) use ($fx) {
                return number_format(((float) $amountUsd) * $fx, 2);
            });

            $publicPromos = [];
            if (Schema::hasTable('promo_codes')) {
                $publicPromos = Cache::remember('promos:public:v2', now()->addMinutes(5), function () {
                    return PromoCode::query()
                        ->where('is_active', true)
                        ->where('is_public', true)
                        ->where(function ($q) {
                            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                        })
                        ->orderByDesc('id')
                        ->limit(3)
                        ->get()
                        ->map(function ($p) {
                            return [
                                'id' => $p->id,
                                'code' => (string) $p->code,
                                'title' => (string) ($p->title ?: 'Promo'),
                                'message' => (string) ($p->message ?: ''),
                                'type' => (string) $p->type,
                                'value' => (float) $p->value,
                                'applies_to' => (array) ($p->applies_to ?? []),
                            ];
                        })
                        ->toArray();
                });
            }
            $view->with('publicPromos', $publicPromos);

            // Inject active seasonal celebration mode & user preferences
            $activeSeasonalMode = null;
            $seasonalUserPref = null;
            $seasonalFeatureSettings = [
                'xmas_hub_button_enabled' => true,
                'xmas_claim_buttons_enabled' => true,
            ];
            try {
                if (Schema::hasTable('seasonal_modes')) {
                    $seasonalEngine = app(\App\Services\SeasonalEngineService::class);
                    $previewId = request()->query('seasonal_preview');
                    $activeSeasonalMode = $seasonalEngine->resolveActiveMode(auth()->user(), $previewId);
                    $seasonalFeatureSettings = $seasonalEngine->getInteractiveFeatureSettings();
                    if ($activeSeasonalMode && auth()->check() && Schema::hasTable('seasonal_user_preferences')) {
                        $seasonalUserPref = \App\Models\SeasonalUserPreference::where('user_id', auth()->id())->first();
                    }
                }
            } catch (\Throwable $e) {
                $activeSeasonalMode = null;
            }
            $view->with('activeSeasonalMode', $activeSeasonalMode);
            $view->with('seasonalUserPref', $seasonalUserPref);
            $view->with('seasonalFeatureSettings', $seasonalFeatureSettings);
        });
    }
}
