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
        'austria' => ['text_en' => 'Austria', 'iso' => 'AT', 'id' => 175],
        'portugal' => ['text_en' => 'Portugal', 'iso' => 'PT', 'id' => 117],
        'greece' => ['text_en' => 'Greece', 'iso' => 'GR', 'id' => 129],
        'czech' => ['text_en' => 'Czech Republic', 'iso' => 'CZ', 'id' => 63],
        'romania' => ['text_en' => 'Romania', 'iso' => 'RO', 'id' => 32],
        'bulgaria' => ['text_en' => 'Bulgaria', 'iso' => 'BG', 'id' => 83],
        'hungary' => ['text_en' => 'Hungary', 'iso' => 'HU', 'id' => 84],
        'ukraine' => ['text_en' => 'Ukraine', 'iso' => 'UA', 'id' => 1],
        'kazakhstan' => ['text_en' => 'Kazakhstan', 'iso' => 'KZ', 'id' => 2],
        'uzbekistan' => ['text_en' => 'Uzbekistan', 'iso' => 'UZ', 'id' => 40],
        'singapore' => ['text_en' => 'Singapore', 'iso' => 'SG', 'id' => 180],
        'hongkong' => ['text_en' => 'Hong Kong', 'iso' => 'HK', 'id' => 14],
        'taiwan' => ['text_en' => 'Taiwan', 'iso' => 'TW', 'id' => 55],
        'southkorea' => ['text_en' => 'South Korea', 'iso' => 'KR', 'id' => 190],
        'saudiarabia' => ['text_en' => 'Saudi Arabia', 'iso' => 'SA', 'id' => 155],
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
        $key = strtolower(trim($countryKey));

        if (isset($this->countries[$key]['id'])) {
            return (int) $this->countries[$key]['id'];
        }

        $aliases = [
            'usa' => 187, 'us' => 187, 'united-states' => 187, 'unitedstates' => 187,
            'russia' => 0, 'ru' => 0,
            'uk' => 16, 'united-kingdom' => 16, 'unitedkingdom' => 16, 'england' => 16, 'gb' => 16,
            'nigeria' => 19, 'ng' => 19,
            'germany' => 43, 'de' => 43,
            'france' => 78, 'fr' => 78,
            'brazil' => 73, 'br' => 73,
            'india' => 22, 'in' => 22,
            'canada' => 36, 'ca' => 36,
            'vietnam' => 10, 'vn' => 10,
            'indonesia' => 6, 'id' => 6,
            'philippines' => 4, 'ph' => 4,
            'thailand' => 52, 'th' => 52,
            'malaysia' => 7, 'my' => 7,
            'mexico' => 54, 'mx' => 54,
            'argentina' => 39, 'ar' => 39,
            'colombia' => 33, 'co' => 33,
            'chile' => 151, 'cl' => 151,
            'peru' => 65, 'pe' => 65,
            'spain' => 56, 'es' => 56,
            'italy' => 86, 'it' => 86,
            'poland' => 15, 'pl' => 15,
            'netherlands' => 48, 'nl' => 48,
            'belgium' => 82, 'be' => 82,
            'sweden' => 46, 'se' => 46,
            'norway' => 174, 'no' => 174,
            'denmark' => 172, 'dk' => 172,
            'finland' => 163, 'fi' => 163,
            'switzerland' => 173, 'ch' => 173,
            'austria' => 175, 'at' => 175,
            'portugal' => 117, 'pt' => 117,
            'greece' => 129, 'gr' => 129,
            'czech' => 63, 'cz' => 63,
            'romania' => 32, 'ro' => 32,
            'bulgaria' => 83, 'bg' => 83,
            'hungary' => 84, 'hu' => 84,
            'ukraine' => 1, 'ua' => 1,
            'kazakhstan' => 2, 'kz' => 2,
            'uzbekistan' => 40, 'uz' => 40,
            'singapore' => 180, 'sg' => 180,
            'hongkong' => 14, 'hk' => 14,
            'taiwan' => 55, 'tw' => 55,
            'southkorea' => 190, 'kr' => 190,
            'saudiarabia' => 155, 'sa' => 155,
            'uae' => 95, 'ae' => 95,
            'israel' => 13, 'il' => 13,
            'turkey' => 62, 'tr' => 62,
            'egypt' => 21, 'eg' => 21,
            'southafrica' => 31, 'za' => 31,
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

        $countriesData = Cache::remember('herosms:free_price:' . $serviceCode, 120, function () use ($serviceCode) {
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

                    $extractedCode = null;
                    if (preg_match('/(\d{3,8})/', $rest, $m)) {
                        $extractedCode = $m[1];
                    } elseif ($rest !== '') {
                        $extractedCode = $rest;
                    }

                    if ($extractedCode === null && $text !== '') {
                        if (preg_match('/(\d{3,8})/', $text, $m2)) {
                            $extractedCode = $m2[1];
                        } else {
                            $extractedCode = $text;
                        }
                    }

                    $result = [
                        'status' => 'RECEIVED',
                        'sms' => [
                            ['code' => $extractedCode ?? '', 'text' => $text !== '' ? $text : $rest]
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
