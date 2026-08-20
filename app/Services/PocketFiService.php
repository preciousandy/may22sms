<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PocketFiService
{
    private string $apiKey;
    private string $publicKey;
    private string $webhookSecret;
    private string $businessId;
    private string $baseUrl;
    private string $authHeaderType;

    public function __construct()
    {
        $this->apiKey = (string) config('services.pocketfi.api_key');
        $this->publicKey = (string) config('services.pocketfi.public_key');
        $this->webhookSecret = (string) config('services.pocketfi.webhook_secret');
        $this->businessId = (string) config('services.pocketfi.business_id');
        $this->baseUrl = rtrim((string) config('services.pocketfi.api_url', 'https://api.pocketfi.ng/api/v1'), '/');
        $this->authHeaderType = (string) config('services.pocketfi.auth_header_type', 'bearer');
    }

    public function isConfigured(): bool
    {
        return ! empty($this->apiKey) && ! empty($this->businessId);
    }

    private function getAuthHeaders(): array
    {
        if ($this->authHeaderType === 'x-api-key') {
            return [
                'x-api-key' => $this->apiKey,
            ];
        }
        
        return [
            'Authorization' => 'Bearer ' . $this->apiKey,
        ];
    }

    public function createCheckout(array $data): array
    {
        $maxAttempts = 3;
        $attempt = 0;
        $lastResult = [
            'ok' => false,
            'status' => 0,
            'data' => ['error' => 'No response from PocketFi'],
        ];

        while ($attempt < $maxAttempts) {
            $attempt++;

            try {
                $headers = array_merge($this->getAuthHeaders(), [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ]);
                
                \Log::info('PocketFi API request', [
                    'attempt' => $attempt,
                    'url' => $this->baseUrl . '/checkout/request',
                    'headers' => array_keys($headers),
                    'payload' => $data,
                    'business_id' => $this->businessId,
                    'auth_header_type' => $this->authHeaderType,
                ]);

                $response = Http::withHeaders($headers)
                    ->post($this->baseUrl . '/checkout/request', $data);

                $status = $response->status();
                $bodyString = $response->body();
                $body = $response->json();

                \Log::info('PocketFi API response', [
                    'attempt' => $attempt,
                    'status' => $status,
                    'body_string' => $bodyString,
                    'body_json' => $body,
                ]);

                $lastResult = [
                    'ok' => $status >= 200 && $status < 300,
                    'status' => $status,
                    'data' => $body,
                ];

                if ($lastResult['ok']) {
                    return $lastResult;
                }

                // If transient 404 or bank account creation delay, wait 500ms and retry automatically
                if ($attempt < $maxAttempts) {
                    usleep(500000);
                }
            } catch (\Throwable $e) {
                \Log::error('PocketFi API error (attempt '.$attempt.')', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                $lastResult = [
                    'ok' => false,
                    'status' => 0,
                    'data' => ['error' => $e->getMessage()],
                ];

                if ($attempt < $maxAttempts) {
                    usleep(500000);
                }
            }
        }

        return $lastResult;
    }

    public function confirmPayment(string $paymentId): array
    {
        try {
            $headers = array_merge($this->getAuthHeaders(), [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]);
            
            $response = Http::withHeaders($headers)
                ->post($this->baseUrl . '/checkout/confirm', [
                    'payment_id' => $paymentId,
                ]);

            $status = $response->status();
            $body = $response->json();

            return [
                'ok' => $status >= 200 && $status < 300,
                'status' => $status,
                'data' => $body,
            ];
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'status' => 0,
                'data' => ['error' => $e->getMessage()],
            ];
        }
    }

    public function verifyWebhookSignature(string $rawBody, ?string $signature, ?array $parsed = null): bool
    {
        if (! $signature) {
            return false;
        }

        $sig = trim((string) $signature);
        if (str_contains($sig, '=')) {
            $sig = trim((string) substr($sig, strrpos($sig, '=') + 1));
        }
        if ($sig === '') {
            return false;
        }

        $algorithms = ['sha512', 'sha256'];
        $possibleSecrets = array_filter([
            $this->webhookSecret,
            $this->apiKey,
            $this->publicKey,
        ]);
        if ($possibleSecrets === []) {
            return false;
        }

        $payloadVariants = [$rawBody];

        if (is_array($parsed) && $parsed !== []) {
            $order = is_array($parsed['order'] ?? null) ? $parsed['order'] : [];
            $transaction = is_array($parsed['transaction'] ?? null) ? $parsed['transaction'] : [];
            $accountNumber = (string) ($parsed['account_number'] ?? $parsed['accountNumber'] ?? '');
            $txRef = (string) ($transaction['reference'] ?? $parsed['transaction_reference'] ?? $parsed['reference'] ?? '');
            $amount = (string) ($order['amount'] ?? $parsed['amount'] ?? $order['totalPaymentAmount'] ?? '');
            $settlement = (string) ($order['settlement_amount'] ?? $order['settlementAmount'] ?? '');
            $fee = (string) ($order['fee'] ?? '');
            $desc = (string) ($order['description'] ?? '');

            if ($txRef !== '') {
                $payloadVariants[] = $txRef;
            }
            if ($accountNumber !== '') {
                $payloadVariants[] = $accountNumber;
            }
            if ($txRef !== '' && $amount !== '') {
                $payloadVariants[] = $txRef . '|' . $amount;
                $payloadVariants[] = $txRef . $amount;
                $payloadVariants[] = $amount . '|' . $txRef;
                $payloadVariants[] = 'reference=' . $txRef . '&amount=' . $amount;
            }
            if ($txRef !== '' && $accountNumber !== '') {
                $payloadVariants[] = $txRef . '|' . $accountNumber;
                $payloadVariants[] = $accountNumber . '|' . $txRef;
            }
            if ($txRef !== '' && $amount !== '' && $accountNumber !== '') {
                $payloadVariants[] = $txRef . '|' . $amount . '|' . $accountNumber;
                $payloadVariants[] = $accountNumber . '|' . $txRef . '|' . $amount;
            }
            if ($txRef !== '' && $settlement !== '') {
                $payloadVariants[] = $txRef . '|' . $settlement;
                $payloadVariants[] = $settlement . '|' . $txRef;
            }
            if ($txRef !== '' && $amount !== '' && $fee !== '') {
                $payloadVariants[] = $txRef . '|' . $amount . '|' . $fee;
            }
            if ($desc !== '') {
                $payloadVariants[] = $desc;
            }

            $canonicalOrder = [
                'amount' => $order['amount'] ?? null,
                'description' => $order['description'] ?? null,
                'fee' => $order['fee'] ?? null,
                'settlement_amount' => $order['settlement_amount'] ?? null,
            ];
            $canonicalTx = [
                'reference' => $transaction['reference'] ?? null,
            ];
            $canonicalFull = [
                'order' => array_filter($canonicalOrder, static fn ($v) => $v !== null),
                'transaction' => array_filter($canonicalTx, static fn ($v) => $v !== null),
            ];
            if ($accountNumber !== '') {
                $canonicalFull['account_number'] = $accountNumber;
            }
            $encoded = json_encode($canonicalFull, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            if (is_string($encoded) && $encoded !== '' && $encoded !== $rawBody) {
                $payloadVariants[] = $encoded;
            }
        }

        $payloadVariants = array_values(array_unique(array_filter($payloadVariants, static fn ($v) => is_string($v) && $v !== '')));

        foreach ($possibleSecrets as $secret) {
            foreach ($algorithms as $algo) {
                foreach ($payloadVariants as $payload) {
                    $expected = hash_hmac($algo, $payload, $secret);
                    if (hash_equals($expected, $sig)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
