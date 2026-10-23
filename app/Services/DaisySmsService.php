<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class DaisySmsService
{
    protected $apiKey;

    protected $baseUrl;
    protected $backupBaseUrls = [];
    protected $proxy;
    protected $flareSolverrUrl;

    // DaisySMS only provides USA numbers
    protected $countries = [
        'usa' => ['text_en' => 'United States', 'iso' => 'US', 'id' => 187],
    ];

    public function __construct()
    {
        $dbKey = '';
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                $dbKey = (string) (\App\Models\Setting::where('key', 'daisysms_api_key')->value('value') ?? '');
            }
        } catch (\Throwable $e) {}

        $this->apiKey = $dbKey !== '' ? $dbKey : (string) (config('services.daisysms.api_key') ?: '');
        $this->baseUrl = (string) (config('services.daisysms.base_url') ?: 'https://daisysms.io/stubs/handler_api.php');
        $this->proxy = (string) (config('services.daisysms.proxy') ?: '');
        $this->flareSolverrUrl = (string) (config('services.daisysms.flaresolverr_url') ?: '');
    }

    /**
     * Build HTTP client with browser-like headers and proxy configuration to avoid bot protection blocks.
     */
    protected function request(int $timeout = 15)
    {
        $client = Http::timeout($timeout)->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            'Accept' => 'application/json, text/plain, */*',
            'Accept-Language' => 'en-US,en;q=0.9',
            'Cache-Control' => 'no-cache',
        ]);

        if (!empty($this->proxy)) {
            $client = $client->withOptions(['proxy' => $this->proxy]);
        }

        return $client;
    }

    /**
     * Send GET request directly or via FlareSolverr if configured.
     * Returns array with 'body', 'headers' (lowercase keys), and 'status'.
     */
    protected function sendGet(string $url, array $params = [], int $timeout = 15): array
    {
        $queryString = http_build_query($params);
        $fullUrl = $queryString !== '' ? ($url . (str_contains($url, '?') ? '&' : '?') . $queryString) : $url;

        if (!empty($this->flareSolverrUrl)) {
            try {
                $fsResponse = Http::timeout(max($timeout, 45))->post($this->flareSolverrUrl, [
                    'cmd' => 'request.get',
                    'url' => $fullUrl,
                    'maxTimeout' => 45000,
                ]);

                if ($fsResponse->successful()) {
                    $fsData = $fsResponse->json();
                    if (($fsData['status'] ?? '') === 'ok' && isset($fsData['solution']['response'])) {
                        $rawBody = (string) $fsData['solution']['response'];
                        if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $rawBody, $m)) {
                            $extracted = trim(strip_tags($m[1]));
                            if ($extracted !== '') {
                                $rawBody = $extracted;
                            }
                        }

                        $headers = [];
                        if (isset($fsData['solution']['headers']) && is_array($fsData['solution']['headers'])) {
                            foreach ($fsData['solution']['headers'] as $k => $v) {
                                $headers[strtolower($k)] = $v;
                            }
                        }

                        return [
                            'body' => trim($rawBody),
                            'headers' => $headers,
                            'status' => (int) ($fsData['solution']['status'] ?? 200),
                        ];
                    }
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("FlareSolverr request failed for {$fullUrl}: " . $e->getMessage());
            }
        }

        $response = $this->request($timeout)->get($url, $params);

        return [
            'body' => trim((string) $response->body()),
            'headers' => array_change_key_case($response->headers(), CASE_LOWER),
            'status' => $response->status(),
        ];
    }

    /**
     * Detect if a response from DaisySMS is a Cloudflare anti-bot challenge page.
     */
    protected function isCloudflareChallenge(string $body): bool
    {
        return str_contains($body, '<title>Just a moment...</title>')
            || str_contains($body, 'challenges.cloudflare.com')
            || str_contains($body, '__cf_chl_')
            || str_contains($body, 'Enable JavaScript and cookies to continue');
    }

    /**
     * Get list of countries
     */
    public function getCountries()
    {
        return $this->countries;
    }


    public function getPrices($country)
    {
        $cacheKey = 'daisysms:prices:' . $country;

        $cached = \Illuminate\Support\Facades\Cache::get($cacheKey);
        if (!empty($cached) && is_array($cached)) {
            return $cached;
        }

        $urlsToTry = array_filter([$this->baseUrl, ...$this->backupBaseUrls]);

        foreach ($urlsToTry as $url) {
            try {
                $res = $this->sendGet($url, [
                    'api_key' => $this->apiKey,
                    'action' => 'getPrices',
                    'country' => $country
                ], 15);

                $data = $res['body'];

                if ($this->isCloudflareChallenge($data)) {
                    \Illuminate\Support\Facades\Log::warning("DaisySMS Cloudflare challenge detected for {$url}. The server IP may be blocked by Cloudflare bot protection.", [
                        'url_tried' => $url,
                        'country' => $country,
                    ]);
                    break;
                }

                \Illuminate\Support\Facades\Log::info("DaisySMS getPrices response", [
                    'url_tried' => $url,
                    'country' => $country,
                    'body' => substr($data, 0, 500)
                ]);

                if (!str_starts_with($data, 'ERROR') && !str_starts_with($data, 'WRONG_') && !str_starts_with($data, 'BAD_')) {
                    $prices = json_decode($data, true);

                    if (is_array($prices) && !empty($prices)) {
                        $result = $this->formatPrices($country, $prices);
                        \Illuminate\Support\Facades\Cache::put($cacheKey, $result, 600);

                        try {
                            file_put_contents(storage_path("app/daisysms_prices_{$country}.json"), $data);
                        } catch (\Throwable $e) {}

                        return $result;
                    }
                }

                if ($data !== '') {
                    break;
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("DaisySMS getPrices exception for {$url}: " . $e->getMessage());
            }
        }

        // Fallback: load saved or bundled prices so services are always visible to customers
        return $this->getFallbackPrices($country);
    }

    /**
     * Format raw DaisySMS prices into 5sim-compatible structure.
     */
    protected function formatPrices(string $country, array $prices): array
    {
        $result = [];
        $result[$country] = [];
        foreach ($prices as $service => $details) {
            $result[$country][$service] = [
                'any' => [
                    'cost' => (float)($details['cost'] ?? 0.0),
                    'count' => (int)($details['count'] ?? 999)
                ]
            ];
        }
        return $result;
    }

    /**
     * Retrieve fallback prices from storage or bundled resource files.
     */
    protected function getFallbackPrices(string $country): array
    {
        $paths = [
            storage_path("app/daisysms_prices_{$country}.json"),
            resource_path("data/daisysms_prices_{$country}.json"),
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                try {
                    $raw = file_get_contents($path);
                    $prices = json_decode($raw, true);
                    if (is_array($prices) && !empty($prices)) {
                        $result = $this->formatPrices($country, $prices);
                        // Cache fallback for 120 seconds so it retries live API periodically
                        \Illuminate\Support\Facades\Cache::put('daisysms:prices:' . $country, $result, 120);
                        return $result;
                    }
                } catch (\Throwable $e) {}
            }
        }

        return [];
    }

    public function getBalance()
    {
        $urlsToTry = array_filter([$this->baseUrl, ...$this->backupBaseUrls]);

        foreach ($urlsToTry as $url) {
            try {
                $res = $this->sendGet($url, [
                    'api_key' => $this->apiKey,
                    'action' => 'getBalance'
                ], 15);

                $body = $res['body'];

                if ($this->isCloudflareChallenge($body)) {
                    \Illuminate\Support\Facades\Log::warning("DaisySMS getBalance encountered Cloudflare challenge for {$url}");
                    return ['error' => 'DaisySMS blocked request with a Cloudflare bot challenge. Server IP might require whitelisting or proxy.'];
                }

                \Illuminate\Support\Facades\Log::info("DaisySMS getBalance response", [
                    'url_tried' => $url,
                    'body' => $body
                ]);

                if (str_starts_with($body, 'ACCESS_BALANCE:')) {
                    $balance = substr($body, 15);
                    return ['balance' => (float) $balance];
                }

                if ($body !== '') {
                    return ['error' => $body];
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("DaisySMS getBalance exception for {$url}: " . $e->getMessage());
            }
        }

        return ['error' => 'Failed to get balance from provider.'];
    }

    /**
     * Buy a number for a specific country and service
     */
    public function buyNumber($country, $operator, $product)
    {
        // First get prices to find the cost if we can't get it from headers
        $prices = $this->getPrices($country);
        $fallbackPrice = 0.0;
        if (isset($prices[$country][$product]['any']['cost'])) {
            $fallbackPrice = (float) $prices[$country][$product]['any']['cost'];
        }

        $urlsToTry = array_filter([$this->baseUrl, ...$this->backupBaseUrls]);
        $lastError = null;

        foreach ($urlsToTry as $url) {
            try {
                $res = $this->sendGet($url, [
                    'api_key' => $this->apiKey,
                    'action' => 'getNumber',
                    'service' => $product,
                    'country' => $country
                ], 45);

                $body = $res['body'];

                if ($this->isCloudflareChallenge($body)) {
                    \Illuminate\Support\Facades\Log::warning("DaisySMS buyNumber encountered Cloudflare challenge for {$url}");
                    $lastError = 'DaisySMS blocked request with Cloudflare verification challenge. Server IP may be restricted.';
                    break;
                }

                \Illuminate\Support\Facades\Log::info("DaisySMS buyNumber response", [
                    'url_tried' => $url,
                    'country' => $country,
                    'product' => $product,
                    'body' => $body
                ]);

                if (str_starts_with($body, 'ACCESS_NUMBER:')) {
                    $parts = explode(':', $body);
                    $id = $parts[1] ?? '';
                    $phone = $parts[2] ?? '';

                    // Try to get price from headers first, else use fallback
                    $priceHeader = $res['headers']['x-price'][0] ?? ($res['headers']['x-price'] ?? 0);
                    $price = !empty($priceHeader) && is_numeric($priceHeader) ? (float) $priceHeader : $fallbackPrice;

                    return [
                        'id' => $id,
                        'phone' => $phone,
                        'price' => (float)$price
                    ];
                }

                // Parse error body returned by DaisySMS
                if ($body === 'NO_NUMBERS' || str_contains(strtolower($body), 'try again')) {
                    $lastError = 'No numbers available right now for this service. Please try again later.';
                } elseif ($body === 'NO_BALANCE') {
                    $lastError = 'Provider balance is low. Please contact support.';
                } elseif ($body === 'WRONG_KEY' || $body === 'BAD_KEY' || $body === 'WRONG_API_KEY') {
                    $lastError = 'Invalid DaisySMS API key.';
                } elseif ($body === 'TOO_MANY_ACTIVATIONS' || $body === 'MAX_REQS') {
                    $lastError = 'Too many requests. Please wait a few seconds and try again.';
                } elseif ($body !== '') {
                    $lastError = $body;
                }

                if ($body !== '') {
                    break;
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("DaisySMS buyNumber exception for {$url}: " . $e->getMessage());
                $lastError = 'Connection to DaisySMS failed.';
            }
        }

        return ['error' => $lastError ?? 'Failed to buy number from provider.'];
    }

    /**
     * Check SMS for an order
     */
    public function checkSms($orderId)
    {
        $urlsToTry = array_filter([$this->baseUrl, ...$this->backupBaseUrls]);
        $result = null;

        foreach ($urlsToTry as $url) {
            try {
                $res = $this->sendGet($url, [
                    'api_key' => $this->apiKey,
                    'action' => 'getStatus',
                    'id' => $orderId,
                    'text' => 1
                ], 20);

                $body = $res['body'];
                $textHeader = $res['headers']['x-text'][0] ?? ($res['headers']['x-text'] ?? '');
                $text = (string) $textHeader;

                if ($this->isCloudflareChallenge($body)) {
                    \Illuminate\Support\Facades\Log::warning("DaisySMS checkSms encountered Cloudflare challenge for order {$orderId}");
                    return ['status' => 'PENDING'];
                }

                \Illuminate\Support\Facades\Log::info("DaisySMS checkSms response", [
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
                \Illuminate\Support\Facades\Log::warning("DaisySMS checkSms exception: {$e->getMessage()}");
                continue;
            }
        }

        if (!$result) {
            return ['status' => 'PENDING'];
        }

        return $result;
    }

    /**
     * Cancel (refund) an order. DaisySMS uses setStatus action with status=8 (default cancel).
     * Statuses: 8 — cancel (refund), 3 — "I have the code" (finish), 6 — spam.
     */
    public function cancelOrder($orderId)
    {
        $urlsToTry = array_filter([$this->baseUrl, ...$this->backupBaseUrls]);

        foreach ($urlsToTry as $url) {
            try {
                $res = $this->sendGet($url, [
                    'api_key' => $this->apiKey,
                    'action' => 'setStatus',
                    'id' => $orderId,
                    'status' => 8,
                ], 15);

                $body = $res['body'];
                \Illuminate\Support\Facades\Log::info("DaisySMS cancelOrder response", [
                    'url_tried' => $url,
                    'order_id' => $orderId,
                    'body' => $body,
                ]);

                if (str_starts_with($body, 'ACCESS_CANCEL') || str_starts_with($body, 'STATUS_CANCEL') || str_starts_with($body, 'ACCESS_')) {
                    return ['success' => true, 'body' => $body];
                }

                if ($body !== '') {
                    break;
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("DaisySMS cancelOrder exception: {$e->getMessage()}");
                continue;
            }
        }

        return ['success' => false];
    }

    /**
     * Mark order as "I have the code" (finish). Used after successful reception.
     */
    public function finishOrder($orderId)
    {
        $urlsToTry = array_filter([$this->baseUrl, ...$this->backupBaseUrls]);

        foreach ($urlsToTry as $url) {
            try {
                $res = $this->sendGet($url, [
                    'api_key' => $this->apiKey,
                    'action' => 'setStatus',
                    'id' => $orderId,
                    'status' => 3,
                ], 15);

                $body = $res['body'];
                \Illuminate\Support\Facades\Log::info("DaisySMS finishOrder response", [
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
