<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HeroSmsService
{
    protected string $apiKey;
    protected string $baseUrl;
    protected array $backupBaseUrls = [
        'https://hero-sms.com/api/stubs/handler_api.php',
    ];

    // Supported countries list
    protected array $countries = [
        'usa' => ['text_en' => 'United States', 'iso' => 'US', 'id' => 187],
        'russia' => ['text_en' => 'Russia', 'iso' => 'RU', 'id' => 0],
        'uk' => ['text_en' => 'United Kingdom', 'iso' => 'GB', 'id' => 16],
        'germany' => ['text_en' => 'Germany', 'iso' => 'DE', 'id' => 43],
        'france' => ['text_en' => 'France', 'iso' => 'FR', 'id' => 78],
        'brazil' => ['text_en' => 'Brazil', 'iso' => 'BR', 'id' => 73],
        'india' => ['text_en' => 'India', 'iso' => 'IN', 'id' => 22],
        'canada' => ['text_en' => 'Canada', 'iso' => 'CA', 'id' => 36],
        'nigeria' => ['text_en' => 'Nigeria', 'iso' => 'NG', 'id' => 19],
        'vietnam' => ['text_en' => 'Vietnam', 'iso' => 'VN', 'id' => 10],
        'indonesia' => ['text_en' => 'Indonesia', 'iso' => 'ID', 'id' => 6],
        'philippines' => ['text_en' => 'Philippines', 'iso' => 'PH', 'id' => 4],
        'thailand' => ['text_en' => 'Thailand', 'iso' => 'TH', 'id' => 52],
        'malaysia' => ['text_en' => 'Malaysia', 'iso' => 'MY', 'id' => 7],
        'mexico' => ['text_en' => 'Mexico', 'iso' => 'MX', 'id' => 54],
        'argentina' => ['text_en' => 'Argentina', 'iso' => 'AR', 'id' => 39],
        'colombia' => ['text_en' => 'Colombia', 'iso' => 'CO', 'id' => 33],
        'chile' => ['text_en' => 'Chile', 'iso' => 'CL', 'id' => 151],
        'peru' => ['text_en' => 'Peru', 'iso' => 'PE', 'id' => 65],
        'spain' => ['text_en' => 'Spain', 'iso' => 'ES', 'id' => 56],
        'italy' => ['text_en' => 'Italy', 'iso' => 'IT', 'id' => 86],
        'poland' => ['text_en' => 'Poland', 'iso' => 'PL', 'id' => 15],
        'netherlands' => ['text_en' => 'Netherlands', 'iso' => 'NL', 'id' => 48],
        'belgium' => ['text_en' => 'Belgium', 'iso' => 'BE', 'id' => 82],
        'sweden' => ['text_en' => 'Sweden', 'iso' => 'SE', 'id' => 46],
        'norway' => ['text_en' => 'Norway', 'iso' => 'NO', 'id' => 174],
        'denmark' => ['text_en' => 'Denmark', 'iso' => 'DK', 'id' => 172],
        'finland' => ['text_en' => 'Finland', 'iso' => 'FI', 'id' => 163],
        'switzerland' => ['text_en' => 'Switzerland', 'iso' => 'CH', 'id' => 173],
        'austria' => ['text_en' => 'Austria', 'iso' => 'AT', 'id' => 50],
        'portugal' => ['text_en' => 'Portugal', 'iso' => 'PT', 'id' => 117],
        'greece' => ['text_en' => 'Greece', 'iso' => 'GR', 'id' => 129],
        'czech' => ['text_en' => 'Czech Republic', 'iso' => 'CZ', 'id' => 63],
        'romania' => ['text_en' => 'Romania', 'iso' => 'RO', 'id' => 32],
        'bulgaria' => ['text_en' => 'Bulgaria', 'iso' => 'BG', 'id' => 83],
        'hungary' => ['text_en' => 'Hungary', 'iso' => 'HU', 'id' => 84],
        'ukraine' => ['text_en' => 'Ukraine', 'iso' => 'UA', 'id' => 1],
        'kazakhstan' => ['text_en' => 'Kazakhstan', 'iso' => 'KZ', 'id' => 2],
        'uzbekistan' => ['text_en' => 'Uzbekistan', 'iso' => 'UZ', 'id' => 40],
        'singapore' => ['text_en' => 'Singapore', 'iso' => 'SG', 'id' => 196],
        'hongkong' => ['text_en' => 'Hong Kong', 'iso' => 'HK', 'id' => 14],
        'taiwan' => ['text_en' => 'Taiwan', 'iso' => 'TW', 'id' => 55],
        'southkorea' => ['text_en' => 'South Korea', 'iso' => 'KR', 'id' => 190],
        'saudiarabia' => ['text_en' => 'Saudi Arabia', 'iso' => 'SA', 'id' => 53],
        'turkey' => ['text_en' => 'Turkey', 'iso' => 'TR', 'id' => 62],
        'egypt' => ['text_en' => 'Egypt', 'iso' => 'EG', 'id' => 21],
        'southafrica' => ['text_en' => 'South Africa', 'iso' => 'ZA', 'id' => 31],
    ];

    // Supported service code mappings (SMS-Activate API standard)
    protected array $serviceMap = [
        'wa' => 'whatsapp',
        'tg' => 'telegram',
        'lf' => 'tiktok',
        'fb' => 'facebook',
        'ig' => 'instagram',
        'go' => 'google',
        'tw' => 'twitter',
        'ds' => 'discord',
        'am' => 'amazon',
        'nf' => 'netflix',
        'ot' => 'other',
        'mb' => 'yahoo',
        'mm' => 'microsoft',
        'dr' => 'openai',
        'ub' => 'uber',
        'ts' => 'paypal',
        'sn' => 'snapchat',
        'oi' => 'tinder',
        'vi' => 'viber',
        'wb' => 'wechat',
        'me' => 'line',
        'kt' => 'kakaotalk',
        'bl' => 'bigolive',
        'bp' => 'badoo',
        'bw' => 'signal',
        'bz' => 'blizzard',
        'ac' => 'doordash',
        'cn' => 'nike',
        'pf' => 'steam',
        'yw' => 'lemon8',
        'pm' => 'shopee',
        'sg' => 'airbnb',
        'fx' => 'paxful',
        'fd' => 'foodpanda',
        'gr' => 'grab',
        'lq' => 'lazada',
        'ls' => 'alipay',
        'kx' => 'binance',
        'cq' => 'mercari',
        'dh' => 'ebay',
        're' => 'coinbase',
        'dp' => 'protonmail',
        'df' => 'bolt',
        'xk' => 'temu',
        'mc' => 'mailru',
        'ok' => 'okru',
        'vk' => 'vkontakte',
        'av' => 'avito',
        'ya' => 'yandex',
        'oz' => 'ozon',
    ];

    public function resolveServiceName(string $code): string
    {
        $c = strtolower(trim($code));
        return $this->serviceMap[$c] ?? $c;
    }

    public function resolveServiceCode(string $name): string
    {
        $p = strtolower(trim($name));
        if (isset($this->serviceMap[$p])) {
            return $p;
        }

        foreach ($this->serviceMap as $code => $mappedName) {
            if ($mappedName === $p) {
                return $code;
            }
        }

        return $p;
    }

    public function __construct()
    {
        $dbKey = '';
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                $dbKey = (string) (\App\Models\Setting::where('key', 'herosms_api_key')->value('value') ?? '');
            }
        } catch (\Throwable $e) {}

        $this->apiKey = $dbKey !== '' ? $dbKey : (string) (config('services.herosms.api_key') ?: '');
        $this->baseUrl = (string) (config('services.herosms.base_url') ?: 'https://hero-sms.com/stubs/handler_api.php');
    }

    public function getCountries(): array
    {
        return $this->countries;
    }

    private function resolveCountryId(string $countryKey): ?int
    {
        $raw = strtolower(trim($countryKey));
        if ($raw === '') {
            return null;
        }
        if (is_numeric($raw)) {
            return (int) $raw;
        }

        if (isset($this->countries[$raw]['id'])) {
            return (int) $this->countries[$raw]['id'];
        }

        $key = preg_replace('/[^a-z0-9]/', '', $raw);

        if (isset($this->countries[$key]['id'])) {
            return (int) $this->countries[$key]['id'];
        }

        $aliases = [
            // Direct & common aliases
            'usa' => 187, 'us' => 187, 'unitedstates' => 187,
            'russia' => 0, 'ru' => 0,
            'uk' => 16, 'unitedkingdom' => 16, 'england' => 16, 'gb' => 16, 'greatbritain' => 16,
            'ukraine' => 1, 'ua' => 1,
            'kazakhstan' => 2, 'kz' => 2,
            'china' => 3, 'cn' => 3,
            'philippines' => 4, 'ph' => 4,
            'myanmar' => 5, 'mm' => 5,
            'indonesia' => 6, 'id' => 6,
            'malaysia' => 7, 'my' => 7,
            'kenya' => 8, 'ke' => 8,
            'tanzania' => 9, 'tz' => 9,
            'vietnam' => 10, 'vn' => 10,
            'kyrgyzstan' => 11, 'kg' => 11,
            'israel' => 13, 'il' => 13,
            'hongkong' => 14, 'hk' => 14,
            'poland' => 15, 'pl' => 15,
            'madagascar' => 17, 'mg' => 17,
            'drcongo' => 18, 'drc' => 18, 'cd' => 18,
            'nigeria' => 19, 'ng' => 19,
            'macao' => 20, 'macau' => 20, 'mo' => 20,
            'egypt' => 21, 'eg' => 21,
            'india' => 22, 'in' => 22,
            'ireland' => 23, 'ie' => 23,
            'cambodia' => 24, 'kh' => 24,
            'laos' => 25, 'la' => 25,
            'haiti' => 26, 'ht' => 26,
            'ivorycoast' => 27, 'cotedivoire' => 27, 'ci' => 27,
            'gambia' => 28, 'gm' => 28,
            'serbia' => 29, 'rs' => 29,
            'yemen' => 30, 'ye' => 30,
            'southafrica' => 31, 'za' => 31,
            'romania' => 32, 'ro' => 32,
            'colombia' => 33, 'co' => 33,
            'estonia' => 34, 'ee' => 34,
            'azerbaijan' => 35, 'az' => 35,
            'canada' => 36, 'ca' => 36,
            'morocco' => 37, 'ma' => 37,
            'ghana' => 38, 'gh' => 38,
            'argentina' => 39, 'ar' => 39,
            'uzbekistan' => 40, 'uz' => 40,
            'cameroon' => 41, 'cm' => 41,
            'chad' => 42, 'td' => 42,
            'germany' => 43, 'de' => 43,
            'lithuania' => 44, 'lt' => 44,
            'croatia' => 45, 'hr' => 45,
            'sweden' => 46, 'se' => 46,
            'iraq' => 47, 'iq' => 47,
            'netherlands' => 48, 'nl' => 48,
            'latvia' => 49, 'lv' => 49,
            'austria' => 50, 'at' => 50,
            'belarus' => 51, 'by' => 51,
            'thailand' => 52, 'th' => 52,
            'saudiarabia' => 53, 'sa' => 53,
            'mexico' => 54, 'mx' => 54,
            'taiwan' => 55, 'tw' => 55,
            'spain' => 56, 'es' => 56,
            'iran' => 57, 'ir' => 57,
            'algeria' => 58, 'dz' => 58,
            'slovenia' => 59, 'si' => 59,
            'bangladesh' => 60, 'bd' => 60,
            'senegal' => 61, 'sn' => 61,
            'turkey' => 62, 'tr' => 62,
            'czech' => 63, 'czechia' => 63, 'czechrepublic' => 63, 'cz' => 63,
            'srilanka' => 64, 'lk' => 64,
            'peru' => 65, 'pe' => 65,
            'pakistan' => 66, 'pk' => 66,
            'newzealand' => 67, 'nz' => 67,
            'guinea' => 68, 'gn' => 68,
            'mali' => 69, 'ml' => 69,
            'venezuela' => 70, 've' => 70,
            'ethiopia' => 71, 'et' => 71,
            'mongolia' => 72, 'mn' => 72,
            'brazil' => 73, 'br' => 73,
            'afghanistan' => 74, 'af' => 74,
            'uganda' => 75, 'ug' => 75,
            'angola' => 76, 'ao' => 76,
            'cyprus' => 77, 'cy' => 77,
            'france' => 78, 'fr' => 78,
            'papua' => 79, 'papuanewguinea' => 79, 'pg' => 79,
            'mozambique' => 80, 'mz' => 80,
            'nepal' => 81, 'np' => 81,
            'belgium' => 82, 'be' => 82,
            'bulgaria' => 83, 'bg' => 83,
            'hungary' => 84, 'hu' => 84,
            'moldova' => 85, 'md' => 85,
            'italy' => 86, 'it' => 86,
            'paraguay' => 87, 'py' => 87,
            'honduras' => 88, 'hn' => 88,
            'tunisia' => 89, 'tn' => 89,
            'nicaragua' => 90, 'ni' => 90,
            'timorleste' => 91, 'easttimor' => 91, 'tl' => 91,
            'bolivia' => 92, 'bo' => 92,
            'costarica' => 93, 'cr' => 93,
            'guatemala' => 94, 'gt' => 94,
            'uae' => 95, 'unitedarabemirates' => 95, 'ae' => 95,
            'zimbabwe' => 96, 'zw' => 96,
            'puertorico' => 97, 'pr' => 97,
            'sudan' => 98, 'sd' => 98,
            'togo' => 99, 'tg' => 99,
            'kuwait' => 100, 'kw' => 100,
            'salvador' => 101, 'elsalvador' => 101, 'sv' => 101,
            'libya' => 102, 'ly' => 102,
            'jamaica' => 103, 'jm' => 103,
            'trinidadandtobago' => 104, 'tit' => 104, 'tt' => 104,
            'ecuador' => 105, 'ec' => 105,
            'swaziland' => 106, 'eswatini' => 106, 'sz' => 106,
            'oman' => 107, 'om' => 107,
            'bosnia' => 108, 'bosniaandherzegovina' => 108, 'bih' => 108, 'ba' => 108,
            'dominicanrepublic' => 109, 'dominicana' => 109, 'do' => 109,
            'syria' => 110, 'sy' => 110,
            'qatar' => 111, 'qa' => 111,
            'panama' => 112, 'pa' => 112,
            'cuba' => 113, 'cu' => 113,
            'mauritania' => 114, 'mr' => 114,
            'sierraleone' => 115, 'sl' => 115,
            'jordan' => 116, 'jo' => 116,
            'portugal' => 117, 'pt' => 117,
            'barbados' => 118, 'bb' => 118,
            'burundi' => 119, 'bi' => 119,
            'benin' => 120, 'bj' => 120,
            'brunei' => 121, 'bn' => 121,
            'bahamas' => 122, 'bs' => 122,
            'botswana' => 123, 'bw' => 123,
            'belize' => 124, 'bz' => 124,
            'centralafricanrepublic' => 125, 'cf' => 125,
            'dominica' => 126, 'dm' => 126,
            'grenada' => 127, 'gd' => 127,
            'georgia' => 128, 'ge' => 128,
            'greece' => 129, 'gr' => 129,
            'guineabissau' => 130, 'gw' => 130,
            'guyana' => 131, 'gy' => 131,
            'iceland' => 132, 'is' => 132,
            'comoros' => 133, 'km' => 133,
            'saintkittsandnevis' => 134, 'kn' => 134,
            'liberia' => 135, 'lr' => 135,
            'lesotho' => 136, 'ls' => 136,
            'malawi' => 137, 'mw' => 137,
            'namibia' => 138, 'na' => 138,
            'niger' => 139, 'ne' => 139,
            'rwanda' => 140, 'rw' => 140,
            'slovakia' => 141, 'sk' => 141,
            'suriname' => 142, 'sr' => 142,
            'tajikistan' => 143, 'tj' => 143,
            'monaco' => 144, 'mc' => 144,
            'bahrain' => 145, 'bh' => 145,
            'reunion' => 146, 're' => 146,
            'zambia' => 147, 'zm' => 147,
            'armenia' => 148, 'am' => 148,
            'somalia' => 149, 'so' => 149,
            'congo' => 150, 'cg' => 150,
            'chile' => 151, 'cl' => 151,
            'burkinafaso' => 152, 'bf' => 152,
            'lebanon' => 153, 'lb' => 153,
            'gabon' => 154, 'ga' => 154,
            'albania' => 155, 'al' => 155,
            'uruguay' => 156, 'uy' => 156,
            'mauritius' => 157, 'mu' => 157,
            'bhutan' => 158, 'bhutane' => 158, 'bt' => 158,
            'maldives' => 159, 'mv' => 159,
            'guadeloupe' => 160, 'gp' => 160,
            'turkmenistan' => 161, 'tm' => 161,
            'frenchguiana' => 162, 'gf' => 162,
            'finland' => 163, 'fi' => 163,
            'saintlucia' => 164, 'lc' => 164,
            'luxembourg' => 165, 'lu' => 165,
            'saintvincentandthegrenadines' => 166, 'saintvincentandgrenadines' => 166, 'vc' => 166,
            'equatorialguinea' => 167, 'gq' => 167,
            'djibouti' => 168, 'dj' => 168,
            'antiguaandbarbuda' => 169, 'ag' => 169,
            'caymanislands' => 170, 'ky' => 170,
            'montenegro' => 171, 'me' => 171,
            'denmark' => 172, 'dk' => 172,
            'switzerland' => 173, 'ch' => 173,
            'norway' => 174, 'no' => 174,
            'australia' => 175, 'au' => 175,
            'eritrea' => 176, 'er' => 176,
            'southsudan' => 177, 'ss' => 177,
            'saotomeandprincipe' => 178, 'st' => 178,
            'aruba' => 179, 'aw' => 179,
            'montserrat' => 180, 'ms' => 180,
            'anguilla' => 181, 'ai' => 181,
            'japan' => 182, 'jp' => 182,
            'northmacedonia' => 183, 'mk' => 183,
            'seychelles' => 184, 'republicofseychelles' => 184, 'sc' => 184,
            'newcaledonia' => 185, 'nc' => 185,
            'capeverde' => 186, 'cv' => 186,
            'palestine' => 188, 'ps' => 188,
            'fiji' => 189, 'fj' => 189,
            'southkorea' => 190, 'korea' => 190, 'kr' => 190,
            'solomonislands' => 191, 'sb' => 191,
            'singapore' => 196, 'sg' => 196,
            'samoa' => 198, 'ws' => 198,
            'malta' => 199, 'mt' => 199,
            'liechtenstein' => 200, 'li' => 200,
            'gibraltar' => 201, 'gi' => 201,
            'kosovo' => 203, 'xk' => 203,
            'niue' => 204, 'nu' => 204,
        ];

        return $aliases[$key] ?? null;
    }

    public function getPrices(string $country): array
    {
        $countryKey = strtolower($country);

        if (empty($this->apiKey)) {
            Log::warning("HeroSMS getPrices: API key is not set in database settings.");
            return [];
        }

        return Cache::remember('herosms:prices:' . $countryKey, 60, function () use ($countryKey) {
            $urlsToTry = array_filter([$this->baseUrl, ...$this->backupBaseUrls]);
            $countryId = $this->resolveCountryId($countryKey);

            foreach ($urlsToTry as $url) {
                try {
                    $query = [
                        'api_key' => $this->apiKey,
                        'action' => 'getPrices',
                    ];
                    if ($countryId !== null) {
                        $query['country'] = $countryId;
                    }

                    $response = Http::timeout(15)->get($url, $query);
                    $data = trim((string) $response->body());

                    Log::info("HeroSMS getPrices response", [
                        'url_tried' => $url,
                        'country' => $countryKey,
                        'country_id' => $countryId,
                        'body_snippet' => substr($data, 0, 300),
                    ]);

                    if (!str_starts_with($data, 'ERROR') && !str_starts_with($data, 'WRONG_') && !str_starts_with($data, 'BAD_') && !str_contains($data, 'UNPROCESSABLE_ENTITY')) {
                        $prices = json_decode($data, true);

                        if (is_array($prices)) {
                            $result = [];
                            $targetData = $prices;
                            if ($countryId !== null && isset($prices[$countryId])) {
                                $targetData = $prices[$countryId];
                            } elseif ($countryId !== null && isset($prices[(string) $countryId])) {
                                $targetData = $prices[(string) $countryId];
                            } elseif (isset($prices[$countryKey])) {
                                $targetData = $prices[$countryKey];
                            }

                            $result[$countryKey] = [];
                            foreach ($targetData as $service => $details) {
                                $cost = 0.0;
                                $count = 999;

                                if (is_array($details)) {
                                    if (isset($details['cost'])) {
                                        $cost = (float) $details['cost'];
                                        $count = (int) ($details['count'] ?? 999);
                                    } else {
                                        // Support nested per-operator pricing if provided
                                        $serviceName = $this->resolveServiceName((string) $service);
                                        $opMap = [];
                                        foreach ($details as $opKey => $opVal) {
                                            if (is_array($opVal) && (isset($opVal['cost']) || isset($opVal['price']))) {
                                                $opCost = (float) ($opVal['cost'] ?? $opVal['price'] ?? 0.0);
                                                if ($opCost > 0) {
                                                    $opMap[(string) $opKey] = [
                                                        'cost' => $opCost,
                                                        'count' => (int) ($opVal['count'] ?? 999),
                                                    ];
                                                }
                                            }
                                        }
                                        if (!empty($opMap)) {
                                            $result[$countryKey][$serviceName] = $opMap;
                                            continue;
                                        }

                                        $first = reset($details);
                                        if (is_array($first)) {
                                            $cost = (float) ($first['cost'] ?? $first['price'] ?? 0.0);
                                            $count = (int) ($first['count'] ?? 999);
                                        } elseif (is_numeric($first)) {
                                            $cost = (float) $first;
                                        }
                                    }
                                } elseif (is_numeric($details)) {
                                    $cost = (float) $details;
                                }

                                if ($cost > 0) {
                                    $serviceName = $this->resolveServiceName((string) $service);

                                    $result[$countryKey][$serviceName] = [
                                        'any' => [
                                            'cost' => $cost,
                                            'count' => $count,
                                        ]
                                    ];
                                }
                            }

                            return $result;
                        }
                    }

                    if ($data !== '') {
                        break;
                    }
                } catch (\Throwable $e) {
                    Log::warning("HeroSMS getPrices exception for {$url}: " . $e->getMessage());
                }
            }

            return [];
        });
    }

    /**
     * Fetch available mobile network operators for a given country from HeroSMS.
     */
    public function getOperators(string $country): array
    {
        $countryKey = strtolower(trim($country));

        if (empty($this->apiKey)) {
            return [];
        }

        $countryId = $this->resolveCountryId($countryKey);
        if ($countryId === null) {
            return [];
        }

        return Cache::remember('herosms:operators:' . $countryKey, 300, function () use ($countryKey, $countryId) {
            $urlsToTry = array_filter([$this->baseUrl, ...$this->backupBaseUrls]);

            foreach ($urlsToTry as $url) {
                try {
                    $response = Http::timeout(15)->get($url, [
                        'api_key' => $this->apiKey,
                        'action' => 'getOperators',
                        'country' => $countryId,
                    ]);

                    $body = trim((string) $response->body());
                    $decoded = json_decode($body, true);

                    if (is_array($decoded) && isset($decoded['countryOperators']) && is_array($decoded['countryOperators'])) {
                        $rawList = $decoded['countryOperators'][$countryId]
                            ?? $decoded['countryOperators'][(string) $countryId]
                            ?? [];

                        if (is_array($rawList)) {
                            $operators = [];
                            foreach ($rawList as $op) {
                                $opStr = trim((string) $op);
                                if ($opStr !== '' && strtolower($opStr) !== 'any') {
                                    $operators[] = $opStr;
                                }
                            }

                            return array_values(array_unique($operators));
                        }
                    }

                    if ($body !== '') {
                        break;
                    }
                } catch (\Throwable $e) {
                    Log::warning("HeroSMS getOperators exception for {$url} ({$countryKey}): " . $e->getMessage());
                }
            }

            return [];
        });
    }

    /**
     * Fetch dynamic price tiers (freePriceMap / maxPrice) for a country and service from HeroSMS.
     *
     * @return array<int, array{price: float, count: int}>
     */
    public function getPriceTiers(string $country, string $product): array
    {
        $countryKey = strtolower(trim($country));
        $serviceCode = $this->resolveServiceCode($product);
        $countryId = $this->resolveCountryId($countryKey);

        if (empty($this->apiKey) || $countryId === null || $serviceCode === '') {
            return [];
        }

        $countriesData = Cache::remember('herosms:free_price:' . $serviceCode, 300, function () use ($serviceCode) {
            $urlsToTry = array_filter([$this->baseUrl, ...$this->backupBaseUrls]);

            foreach ($urlsToTry as $url) {
                try {
                    $response = Http::timeout(15)->get($url, [
                        'api_key' => $this->apiKey,
                        'action' => 'getTopCountriesByService',
                        'service' => $serviceCode,
                        'freePrice' => 'true',
                    ]);

                    $body = trim((string) $response->body());
                    $decoded = json_decode($body, true);

                    if (is_array($decoded) && !isset($decoded['title'])) {
                        return $decoded;
                    }

                    if ($body !== '') {
                        break;
                    }
                } catch (\Throwable $e) {
                    Log::warning("HeroSMS getPriceTiers exception for {$url} ({$serviceCode}): " . $e->getMessage());
                }
            }

            return [];
        });

        if (!is_array($countriesData)) {
            return [];
        }

        $targetEntry = null;
        foreach ($countriesData as $entry) {
            if (is_array($entry) && isset($entry['country']) && (int) $entry['country'] === $countryId) {
                $targetEntry = $entry;
                break;
            }
        }

        if (!$targetEntry || empty($targetEntry['freePriceMap']) || !is_array($targetEntry['freePriceMap'])) {
            return [];
        }

        $tiers = [];
        foreach ($targetEntry['freePriceMap'] as $priceStr => $countVal) {
            $p = round((float) $priceStr, 4);
            $c = (int) $countVal;
            if ($p > 0 && $c > 0) {
                $tiers[(string) $p] = [
                    'price' => $p,
                    'count' => $c,
                ];
            }
        }

        usort($tiers, fn ($a, $b) => $a['price'] <=> $b['price']);

        return $tiers;
    }

    public function getBalance(): array
    {
        $urlsToTry = array_filter([$this->baseUrl, ...$this->backupBaseUrls]);

        foreach ($urlsToTry as $url) {
            try {
                $response = Http::timeout(15)->get($url, [
                    'api_key' => $this->apiKey,
                    'action' => 'getBalance',
                ]);

                $body = trim((string) $response->body());
                Log::info("HeroSMS getBalance response", [
                    'url_tried' => $url,
                    'body' => $body,
                ]);

                if (str_starts_with($body, 'ACCESS_BALANCE:')) {
                    $balance = substr($body, 15);
                    return ['balance' => (float) $balance];
                }

                if ($body !== '') {
                    return ['error' => $body];
                }
            } catch (\Throwable $e) {
                Log::warning("HeroSMS getBalance exception for {$url}: " . $e->getMessage());
            }
        }

        return ['error' => 'Failed to get balance from provider.'];
    }

    public function buyNumber(string $country, string $operator, string $product, ?float $maxPrice = null): array
    {
        $countryKey = strtolower($country);
        $prices = $this->getPrices($countryKey);
        $fallbackPrice = 0.0;
        if (isset($prices[$countryKey][$product][$operator]['cost'])) {
            $fallbackPrice = (float) $prices[$countryKey][$product][$operator]['cost'];
        } elseif (isset($prices[$countryKey][$product]['any']['cost'])) {
            $fallbackPrice = (float) $prices[$countryKey][$product]['any']['cost'];
        }
        if ($maxPrice !== null && $maxPrice > 0) {
            $fallbackPrice = max($fallbackPrice, $maxPrice);
        }

        $urlsToTry = array_filter([$this->baseUrl, ...$this->backupBaseUrls]);
        $countryId = $this->resolveCountryId($countryKey);
        $serviceCode = $this->resolveServiceCode($product);
        $lastError = null;

        foreach ($urlsToTry as $url) {
            try {
                $params = [
                    'api_key' => $this->apiKey,
                    'action' => 'getNumber',
                    'service' => $serviceCode,
                ];

                if ($countryId !== null) {
                    $params['country'] = $countryId;
                }

                if (!empty($operator) && $operator !== 'any') {
                    $params['operator'] = $operator;
                }

                if ($maxPrice !== null && $maxPrice > 0) {
                    $params['maxPrice'] = round($maxPrice, 4);
                }

                $response = Http::timeout(45)->get($url, $params);
                $body = trim((string) $response->body());

                Log::info("HeroSMS buyNumber response", [
                    'url_tried' => $url,
                    'country' => $countryKey,
                    'product' => $product,
                    'body' => $body,
                ]);

                if (str_starts_with($body, 'ACCESS_NUMBER:')) {
                    $parts = explode(':', $body);
                    $id = $parts[1] ?? '';
                    $phone = $parts[2] ?? '';

                    $price = $response->header('X-Price', 0);
                    if (empty($price) || !is_numeric($price)) {
                        $price = $fallbackPrice;
                    }

                    return [
                        'id' => $id,
                        'phone' => $phone,
                        'price' => (float) $price,
                        'operator' => $operator ?: 'any',
                        'product' => $product,
                    ];
                }

                if ($body === 'NO_NUMBERS' || str_contains(strtolower($body), 'try again')) {
                    $lastError = 'No numbers available right now for this service. Please try again later.';
                } elseif ($body === 'WRONG_MAX_PRICE' || str_contains($body, 'WRONG_MAX_PRICE')) {
                    $lastError = 'Selected price tier is too low for available numbers. Please select a higher price tier.';
                } elseif ($body === 'NO_BALANCE') {
                    $lastError = 'Provider balance is low. Please contact support.';
                } elseif ($body === 'WRONG_KEY' || $body === 'BAD_KEY' || $body === 'WRONG_API_KEY') {
                    $lastError = 'Invalid HeroSMS API key.';
                } elseif ($body === 'TOO_MANY_ACTIVATIONS' || $body === 'MAX_REQS') {
                    $lastError = 'Too many requests. Please wait a few seconds and try again.';
                } elseif ($body !== '') {
                    $lastError = $body;
                }

                if ($body !== '') {
                    break;
                }
            } catch (\Throwable $e) {
                Log::warning("HeroSMS buyNumber exception for {$url}: " . $e->getMessage());
                $lastError = 'Connection to HeroSMS failed.';
            }
        }

        return ['error' => $lastError ?? 'Failed to buy number from provider.'];
    }

    public function checkSms(string $orderId): array
    {
        $urlsToTry = array_filter([$this->baseUrl, ...$this->backupBaseUrls]);
        $result = null;

        foreach ($urlsToTry as $url) {
            try {
                $response = Http::timeout(20)->get($url, [
                    'api_key' => $this->apiKey,
                    'action' => 'getStatus',
                    'id' => $orderId,
                    'text' => 1,
                ]);

                $body = trim((string) $response->body());
                $text = (string) $response->header('X-Text', '');

                Log::info("HeroSMS checkSms response", [
                    'url_tried' => $url,
                    'order_id' => $orderId,
                    'body' => $body,
                    'x-text' => $text,
                ]);

                if (str_starts_with($body, 'STATUS_OK')) {
                    $rest = str_contains($body, ':') ? explode(':', $body, 2)[1] : '';
                    $rest = trim($rest);

                    $extractedCode = $this->extractOtpFromText($rest);
                    if ($extractedCode === null && $text !== '') {
                        $extractedCode = $this->extractOtpFromText($text);
                    }
                    // If $rest only gave 3 digits (e.g. truncated before hyphen) and $text has full 6 digits, prefer $text
                    if ($extractedCode !== null && strlen($extractedCode) <= 3 && $text !== '') {
                        $fromText = $this->extractOtpFromText($text);
                        if ($fromText !== null && strlen($fromText) > strlen($extractedCode)) {
                            $extractedCode = $fromText;
                        }
                    }

                    $result = [
                        'status' => 'RECEIVED',
                        'sms' => [
                            ['code' => $extractedCode ?? $rest, 'text' => $text !== '' ? $text : $rest]
                        ]
                    ];
                    break;
                } elseif (str_starts_with($body, 'STATUS_WAIT_CODE') || str_starts_with($body, 'STATUS_WAIT_RESEND')) {
                    $result = ['status' => 'PENDING'];
                    break;
                } elseif (str_starts_with($body, 'STATUS_CANCEL') || str_starts_with($body, 'NO_ACTIVATION') || str_starts_with($body, 'BAD_KEY') || str_starts_with($body, 'WRONG_KEY')) {
                    $result = ['status' => 'CANCELED'];
                    break;
                }

                if ($body !== '') {
                    break;
                }
            } catch (\Throwable $e) {
                Log::warning("HeroSMS checkSms exception: {$e->getMessage()}");
                continue;
            }
        }

        if (!$result) {
            return ['status' => 'PENDING'];
        }

        return $result;
    }

    /**
     * Extract full OTP code from raw provider response or SMS body.
     * Supports hyphenated/spaced codes like WhatsApp "780-456" -> "780456" as well as standard 4-8 digit codes.
     */
    public function extractOtpFromText(string $raw): ?string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        // 1. Match hyphenated or space-separated 6-8 digit OTP codes (e.g. WhatsApp "780-456" or "123 456")
        if (preg_match('/(?<!\d)(\d{3,4})[-\s](\d{3,4})(?!\d)/', $raw, $m)) {
            return $m[1] . $m[2];
        }

        // 2. Match contiguous 4-8 digit OTP codes first (so 6-digit codes are preferred over 3-digit prefixes)
        if (preg_match('/(?<!\d)(\d{4,8})(?!\d)/', $raw, $m)) {
            return $m[1];
        }

        // 3. Fallback to 3-8 digits
        if (preg_match('/(\d{3,8})/', $raw, $m)) {
            return $m[1];
        }

        return $raw;
    }

    public function cancelOrder(string $orderId): array
    {
        $urlsToTry = array_filter([$this->baseUrl, ...$this->backupBaseUrls]);

        foreach ($urlsToTry as $url) {
            try {
                $response = Http::timeout(15)->get($url, [
                    'api_key' => $this->apiKey,
                    'action' => 'setStatus',
                    'id' => $orderId,
                    'status' => 8,
                ]);

                $body = trim((string) $response->body());
                Log::info("HeroSMS cancelOrder response", [
                    'url_tried' => $url,
                    'order_id' => $orderId,
                    'body' => $body,
                ]);

                if ($body === 'ACCESS_CANCEL' || $body === 'STATUS_CANCEL' || str_starts_with($body, 'ACCESS_')) {
                    return ['success' => true, 'body' => $body];
                }

                if ($body !== '') {
                    break;
                }
            } catch (\Throwable $e) {
                Log::warning("HeroSMS cancelOrder exception: {$e->getMessage()}");
                continue;
            }
        }

        return ['success' => false];
    }

    public function finishOrder(string $orderId): array
    {
        $urlsToTry = array_filter([$this->baseUrl, ...$this->backupBaseUrls]);

        foreach ($urlsToTry as $url) {
            try {
                $response = Http::timeout(15)->get($url, [
                    'api_key' => $this->apiKey,
                    'action' => 'setStatus',
                    'id' => $orderId,
                    'status' => 3,
                ]);

                $body = trim((string) $response->body());
                Log::info("HeroSMS finishOrder response", [
                    'url_tried' => $url,
                    'order_id' => $orderId,
                    'body' => $body,
                ]);

                if (str_starts_with($body, 'ACCESS_')) {
                    return ['success' => true, 'body' => $body];
                }

                if ($body !== '') {
                    break;
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        return ['success' => false];
    }
}
