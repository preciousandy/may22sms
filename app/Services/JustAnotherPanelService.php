<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class JustAnotherPanelService
{
    protected $apiKey;

    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = env('JAP_API_KEY');
        $this->baseUrl = env('JAP_API_URL', 'https://justanotherpanel.com/api/v2');
    }

    public function getBalance()
    {
        $response = Http::asForm()->post($this->baseUrl, [
            'key' => $this->apiKey,
            'action' => 'balance',
        ]);

        return $response->json();
    }

    public function getServices()
    {
        $response = Http::asForm()->post($this->baseUrl, [
            'key' => $this->apiKey,
            'action' => 'services',
        ]);

        return $response->json();
    }

    public function addOrder($serviceId, $link, $quantity)
    {
        try {
            $response = Http::asForm()->timeout(30)->post($this->baseUrl, [
                'key' => $this->apiKey,
                'action' => 'add',
                'service' => $serviceId,
                'link' => $link,
                'quantity' => $quantity,
            ]);

            $statusCode = $response->status();
            $result = $response->json();

            if (! $response->successful()) {
                Log::warning("JAP addOrder HTTP error: status={$statusCode}, service_id={$serviceId}", ['body' => $response->body()]);
                return [
                    'error' => 'Provider API returned HTTP '.$statusCode,
                    'error_type' => 'http',
                    'http_status' => $statusCode,
                ];
            }

            if (is_array($result) && isset($result['error'])) {
                Log::warning("JAP addOrder API error: service_id={$serviceId}, error={$result['error']}");
                $result['error_type'] = $this->classifyError($result['error']);
                return $result;
            }

            if (is_array($result) && ! isset($result['order'])) {
                Log::warning("JAP addOrder no order key: service_id={$serviceId}", ['result' => $result]);
                return [
                    'error' => 'Provider did not return an order reference',
                    'error_type' => 'unknown',
                    'raw' => $result,
                ];
            }

            return $result;
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error("JAP addOrder connection error: service_id={$serviceId}, msg={$e->getMessage()}");
            return [
                'error' => 'Provider connection failed. Please try again later.',
                'error_type' => 'connection',
            ];
        } catch (\Throwable $e) {
            Log::error("JAP addOrder exception: service_id={$serviceId}, msg={$e->getMessage()}");
            return [
                'error' => 'Unexpected error while contacting provider',
                'error_type' => 'exception',
            ];
        }
    }

    public function getOrderStatus($orderId)
    {
        try {
            $response = Http::asForm()->timeout(20)->post($this->baseUrl, [
                'key' => $this->apiKey,
                'action' => 'status',
                'order' => $orderId,
            ]);

            return $response->json();
        } catch (\Throwable $e) {
            Log::error("JAP getOrderStatus exception: order_id={$orderId}, msg={$e->getMessage()}");
            return null;
        }
    }

    public function classifyError(string $errorMessage): string
    {
        $lower = strtolower($errorMessage);

        $servicePatterns = [
            'service not found',
            'invalid service',
            'service unavailable',
            'service does not exist',
            'service is not active',
            'service disabled',
            'wrong service',
            'unknown service',
        ];

        $linkPatterns = [
            'invalid link',
            'wrong link',
            'bad link',
            'link not supported',
            'unsupported link',
            'incorrect link',
            'link format',
            'invalid url',
            'cannot parse',
            'could not find',
            'not found page',
            'profile not found',
            'post not found',
            'video not found',
            'username not found',
        ];

        $quantityPatterns = [
            'invalid quantity',
            'quantity should',
            'quantity must',
            'minimum quantity',
            'maximum quantity',
            'min quantity',
            'max quantity',
            'quantity exceeds',
            'quantity is less',
            'quantity is more',
        ];

        $balancePatterns = [
            'insufficient funds',
            'not enough funds',
            'low balance',
            'balance is low',
            'not enough balance',
            'insufficient balance',
        ];

        foreach ($servicePatterns as $p) {
            if (str_contains($lower, $p)) {
                return 'service';
            }
        }

        foreach ($linkPatterns as $p) {
            if (str_contains($lower, $p)) {
                return 'link';
            }
        }

        foreach ($quantityPatterns as $p) {
            if (str_contains($lower, $p)) {
                return 'quantity';
            }
        }

        foreach ($balancePatterns as $p) {
            if (str_contains($lower, $p)) {
                return 'balance';
            }
        }

        return 'other';
    }
}
