<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class NowPaymentsService
{
    protected string $apiKey;

    protected string $ipnSecret;

    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = (string) config('services.nowpayments.api_key', '');
        $this->ipnSecret = (string) config('services.nowpayments.ipn_secret', '');
        $this->baseUrl = (string) config('services.nowpayments.api_url', 'https://api.nowpayments.io/v1');
    }

    public function createInvoice(array $payload): array
    {
        $response = Http::asJson()
            ->acceptJson()
            ->withHeaders([
                'x-api-key' => $this->apiKey,
            ])
            ->post(rtrim($this->baseUrl, '/').'/invoice', $payload);

        return [
            'ok' => $response->successful(),
            'status' => $response->status(),
            'data' => $response->json(),
        ];
    }

    public function verifyIpnSignature(string $rawBody, ?string $signatureHeader): bool
    {
        if ($this->ipnSecret === '' || ! $signatureHeader) {
            return false;
        }

        $sig = trim((string) $signatureHeader);
        if (str_contains($sig, '=')) {
            $sig = trim((string) substr($sig, strrpos($sig, '=') + 1));
        }

        $expected = hash_hmac('sha512', $rawBody, $this->ipnSecret);

        return hash_equals($expected, $sig);
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '' && $this->ipnSecret !== '';
    }
}
