<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class StrollwalletService
{
    protected string $apiKey;

    protected string $webhookSecret;

    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = (string) config('services.strollwallet.api_key', '');
        $this->webhookSecret = (string) config('services.strollwallet.webhook_secret', '');
        $this->baseUrl = (string) config('services.strollwallet.api_url', '');
    }

    public function isConfigured(): bool
    {
        if ($this->apiKey === '' || $this->baseUrl === '') {
            return false;
        }

        if (str_contains($this->baseUrl, '.example')) {
            return false;
        }

        return true;
    }

    protected function endpoint(string $path): string
    {
        return rtrim($this->baseUrl, '/').'/'.ltrim($path, '/');
    }

    protected function authHeaders(): array
    {
        return [
            'Authorization' => 'Bearer '.$this->apiKey,
        ];
    }

    public function initializePayment(array $payload): array
    {
        $path = (string) config('services.strollwallet.initialize_path', '/transactions/initialize');
        $response = Http::asJson()
            ->acceptJson()
            ->withHeaders($this->authHeaders())
            ->post($this->endpoint($path), $payload);

        return [
            'ok' => $response->successful(),
            'status' => $response->status(),
            'data' => $response->json(),
        ];
    }

    public function verifyTransaction(string $reference): array
    {
        $path = (string) config('services.strollwallet.verify_path', '');
        if ($path === '') {
            return [
                'ok' => false,
                'status' => 0,
                'data' => ['message' => 'verify_path_not_configured'],
            ];
        }

        $method = strtoupper((string) config('services.strollwallet.verify_method', 'GET'));
        $resolvedPath = str_contains($path, '{reference}')
            ? str_replace('{reference}', rawurlencode($reference), $path)
            : (rtrim($path, '/').'/'.rawurlencode($reference));

        $request = Http::asJson()
            ->acceptJson()
            ->withHeaders($this->authHeaders());

        $response = $method === 'POST'
            ? $request->post($this->endpoint($resolvedPath), ['reference' => $reference])
            : $request->get($this->endpoint($resolvedPath));

        return [
            'ok' => $response->successful(),
            'status' => $response->status(),
            'data' => $response->json(),
        ];
    }

    public function verifyWebhookSignature(string $rawBody, ?string $signature): bool
    {
        if (! $signature) {
            return false;
        }

        $sig = trim((string) $signature);
        if (str_contains($sig, '=')) {
            $sig = trim((string) substr($sig, strrpos($sig, '=') + 1));
        }

        $algorithms = ['sha512', 'sha256'];
        $possibleSecrets = array_filter([
            $this->webhookSecret,
            $this->apiKey,
        ]);

        foreach ($possibleSecrets as $secret) {
            foreach ($algorithms as $algo) {
                $expected = hash_hmac($algo, $rawBody, $secret);
                if (hash_equals($expected, $sig)) {
                    return true;
                }
            }
        }

        return false;
    }
}
