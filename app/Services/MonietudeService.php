<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MonietudeService
{
    protected string $apiKey;

    protected string $baseUrl;

    protected string $defaultProvider;

    public function __construct()
    {
        $dbKey = Setting::where('key', 'monietude_api_key')->value('value');
        $this->apiKey = ! empty($dbKey) ? (string) $dbKey : (string) config('services.monietude.api_key', '');
        
        $dbUrl = Setting::where('key', 'monietude_api_url')->value('value');
        $this->baseUrl = ! empty($dbUrl) ? (string) $dbUrl : (string) config('services.monietude.api_url', 'https://monietude.com/api/gateway');

        $dbProvider = Setting::where('key', 'monietude_provider')->value('value');
        $this->defaultProvider = ! empty($dbProvider) ? (string) $dbProvider : (string) config('services.monietude.provider', 'palmpay');
    }

    public function isConfigured(): bool
    {
        return ! empty($this->apiKey) && ! empty($this->baseUrl);
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function getDefaultProvider(): string
    {
        return $this->defaultProvider;
    }

    protected function endpoint(string $path): string
    {
        return rtrim($this->baseUrl, '/') . '/' . ltrim($path, '/');
    }

    protected function headers(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Create a virtual account (PalmPay or Flutterwave).
     *
     * @param array $payload ['name' => ..., 'phone' => ..., 'email' => ..., 'provider' => ...]
     * @return array ['ok' => bool, 'status' => int, 'data' => array]
     */
    public function createVirtualAccount(array $payload): array
    {
        if (! isset($payload['provider']) || empty($payload['provider'])) {
            $payload['provider'] = $this->defaultProvider;
        }

        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(15)
                ->post($this->endpoint('/virtual-accounts'), $payload);

            return [
                'ok' => $response->successful(),
                'status' => $response->status(),
                'data' => $response->json() ?? [],
            ];
        } catch (\Throwable $e) {
            Log::error('Monietude createVirtualAccount error: ' . $e->getMessage(), ['payload' => $payload]);

            return [
                'ok' => false,
                'status' => 0,
                'data' => ['message' => $e->getMessage()],
            ];
        }
    }

    /**
     * Retrieve virtual account details by customer phone number.
     *
     * @param string $phone
     * @param string|null $provider
     * @return array ['ok' => bool, 'status' => int, 'data' => array]
     */
    public function getVirtualAccount(string $phone, ?string $provider = null): array
    {
        $provider = $provider ?: $this->defaultProvider;

        try {
            $url = $this->endpoint('/virtual-accounts/show') . '?' . http_build_query([
                'phone' => $phone,
                'provider' => $provider,
            ]);

            $response = Http::withHeaders($this->headers())
                ->timeout(15)
                ->get($url);

            return [
                'ok' => $response->successful(),
                'status' => $response->status(),
                'data' => $response->json() ?? [],
            ];
        } catch (\Throwable $e) {
            Log::error('Monietude getVirtualAccount error: ' . $e->getMessage(), ['phone' => $phone]);

            return [
                'ok' => false,
                'status' => 0,
                'data' => ['message' => $e->getMessage()],
            ];
        }
    }

    /**
     * Verify a transaction by reference.
     *
     * @param string $reference
     * @return array ['ok' => bool, 'status' => int, 'data' => array]
     */
    public function verifyPayment(string $reference): array
    {
        try {
            $url = $this->endpoint('/payments/verify/' . rawurlencode($reference));
            $response = Http::withHeaders($this->headers())
                ->timeout(15)
                ->get($url);

            return [
                'ok' => $response->successful(),
                'status' => $response->status(),
                'data' => $response->json() ?? [],
            ];
        } catch (\Throwable $e) {
            Log::error('Monietude verifyPayment error: ' . $e->getMessage(), ['reference' => $reference]);

            return [
                'ok' => false,
                'status' => 0,
                'data' => ['message' => $e->getMessage()],
            ];
        }
    }

    /**
     * Format a Nigerian phone number to valid 11 digits format (e.g. 08012345678).
     *
     * @param string|null $rawPhone
     * @param int|string $userId Fallback seed
     * @return string
     */
    public function formatPhone(?string $rawPhone, $userId = 1): string
    {
        if (empty($rawPhone)) {
            return '080' . str_pad((string) $userId, 8, '0', STR_PAD_LEFT);
        }

        $digits = preg_replace('/[^\d]/', '', $rawPhone);

        if (str_starts_with($digits, '234') && strlen($digits) === 13) {
            $digits = '0' . substr($digits, 3);
        } elseif (strlen($digits) === 10) {
            $digits = '0' . $digits;
        }

        if (strlen($digits) !== 11) {
            return '080' . str_pad((string) $userId, 8, '0', STR_PAD_LEFT);
        }

        return $digits;
    }
}
