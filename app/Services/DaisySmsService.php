<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class DaisySmsService
{
    protected $apiKey;

    protected $baseUrl;
    protected $backupBaseUrls = [];

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
        return \Illuminate\Support\Facades\Cache::remember('daisysms:prices:' . $country, 60, function () use ($country) {
            $urlsToTry = array_filter([$this->baseUrl, ...$this->backupBaseUrls]);

            foreach ($urlsToTry as $url) {
                try {
                    $response = Http::timeout(15)->get($url, [
                        'api_key' => $this->apiKey,
                        'action' => 'getPrices',
                        'country' => $country
                    ]);

                    $data = trim((string) $response->body());
                    \Illuminate\Support\Facades\Log::info("DaisySMS getPrices response", [
                        'url_tried' => $url,
                        'country' => $country,
                        'body' => $data
                    ]);

                    if (!str_starts_with($data, 'ERROR') && !str_starts_with($data, 'WRONG_') && !str_starts_with($data, 'BAD_')) {
                        $prices = json_decode($data, true);

                        // Convert DaisySMS format to 5sim-like format for compatibility
                        $result = [];
                        if (is_array($prices)) {
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
                    }

                    if ($data !== '') {
                        break;
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning("DaisySMS getPrices exception for {$url}: " . $e->getMessage());
                }
            }

            return [];
        });
    }

    public function getBalance()
    {
        $urlsToTry = array_filter([$this->baseUrl, ...$this->backupBaseUrls]);

        foreach ($urlsToTry as $url) {
            try {
                $response = Http::timeout(15)->get($url, [
                    'api_key' => $this->apiKey,
                    'action' => 'getBalance'
                ]);

                $body = trim((string) $response->body());
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
                $response = Http::timeout(45)->get($url, [
                    'api_key' => $this->apiKey,
                    'action' => 'getNumber',
                    'service' => $product,
                    'country' => $country
                ]);

                $body = trim((string) $response->body());
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
                    $price = $response->header('X-Price', 0);
                    if (empty($price) || !is_numeric($price)) {
                        $price = $fallbackPrice;
                    }

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
                $response = Http::timeout(20)->get($url, [
                    'api_key' => $this->apiKey,
                    'action' => 'getStatus',
                    'id' => $orderId,
                    'text' => 1
                ]);

                $body = trim((string) $response->body());
                $text = (string) $response->header('X-Text', '');

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
                $response = Http::timeout(15)->get($url, [
                    'api_key' => $this->apiKey,
                    'action' => 'setStatus',
                    'id' => $orderId,
                    'status' => 8,
                ]);

                $body = trim((string) $response->body());
                \Illuminate\Support\Facades\Log::info("DaisySMS cancelOrder response", [
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
                $response = Http::timeout(15)->get($url, [
                    'api_key' => $this->apiKey,
                    'action' => 'setStatus',
                    'id' => $orderId,
                    'status' => 3,
                ]);

                $body = trim((string) $response->body());
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
