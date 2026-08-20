<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FiveSimService
{
    protected $apiKey;

    protected $baseUrl = 'https://5sim.net/v1';

    public function __construct()
    {
        $this->apiKey = config('services.fivesim.api_key');
    }

    /**
     * Get list of countries
     */
    public function getCountries()
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
        ])->get("{$this->baseUrl}/guest/countries");

        return $response->json();
    }

    /**
     * Get services and prices for a country
     */
    public function getPrices($country)
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
        ])->get("{$this->baseUrl}/guest/prices", ['country' => $country]);

        return $response->json();
    }

    public function getBalance()
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->apiKey,
            'Accept' => 'application/json',
        ])->get("{$this->baseUrl}/guest/profile");

        return $response->json();
    }

    /**
     * Buy a number for a specific country and service
     */
    public function buyNumber($country, $operator, $product)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->apiKey,
            'Accept' => 'application/json',
        ])->get("{$this->baseUrl}/user/buy/activation/{$country}/{$operator}/{$product}");

        $data = $response->json();

        if (isset($data['id']) && isset($data['phone'])) {
            // 5sim returns 'cost', but our code expects 'price', map it over
            $data['price'] = $data['cost'] ?? 0.0;
            return $data;
        }

        // If there's an error, return it with the error message
        return ['error' => $data['error'] ?? $response->body() ?? 'Unknown error'];
    }

    /**
     * Check SMS for an order
     */
    public function checkSms($orderId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->apiKey,
                'Accept' => 'application/json',
            ])->timeout(20)->get("{$this->baseUrl}/user/check/{$orderId}");

            $data = $response->json();
            if (is_array($data) && isset($data['status']) && ($data['status'] === 'RECEIVED' || $data['status'] === 'FINISHED')) {
                if (empty($data['sms']) && !empty($data['sms_code'])) {
                    $data['sms'] = [['code' => $data['sms_code'], 'text' => $data['text'] ?? $data['sms_code']]];
                }
            }
            return $data;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("FiveSim checkSms exception: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Cancel (refund) an order. 5sim: action=ban/cancel - cancel order
     */
    public function cancelOrder($orderId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->apiKey,
                'Accept' => 'application/json',
            ])->timeout(15)->get("{$this->baseUrl}/user/cancel/{$orderId}");

            $data = $response->json();
            return ['success' => $response->successful(), 'body' => $data];
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("FiveSim cancelOrder exception: {$e->getMessage()}");
            return ['success' => false];
        }
    }

    /**
     * Mark order as finished ("I have the code").
     */
    public function finishOrder($orderId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->apiKey,
                'Accept' => 'application/json',
            ])->timeout(15)->get("{$this->baseUrl}/user/finish/{$orderId}");

            $data = $response->json();
            return ['success' => $response->successful(), 'body' => $data];
        } catch (\Throwable $e) {
            return ['success' => false];
        }
    }
}
