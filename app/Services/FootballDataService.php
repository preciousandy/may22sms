<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FootballDataService
{
    protected ?string $apiToken;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiToken = Setting::where('key', 'football_data_api_token')->value('value')
            ?: config('football-data.api_token');
        $this->baseUrl = (string) config('football-data.base_url', 'https://api.football-data.org/v4');
    }

    public function getApiToken(): ?string
    {
        return $this->apiToken;
    }

    protected function request(string $endpoint, array $params = [])
    {
        if (empty($this->apiToken)) {
            return [
                'error' => true,
                'message' => 'Football-Data API token is not configured. Please enter your API token in Sync Settings.',
            ];
        }

        try {
            $response = Http::timeout(20)->withHeaders([
                'X-Auth-Token' => $this->apiToken,
            ])->get($this->baseUrl . $endpoint, $params);

            if ($response->failed()) {
                $err = $response->json();
                $message = $err['message'] ?? ('HTTP Error ' . $response->status() . ' (' . $response->reason() . ')');
                Log::warning('FootballDataService API call failed', [
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                    'message' => $message,
                ]);

                return [
                    'error' => true,
                    'message' => $message,
                    'status' => $response->status(),
                ];
            }

            return $response->json();
        } catch (\Throwable $e) {
            Log::error('FootballDataService exception: ' . $e->getMessage());

            return [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function getUpcomingMatches(int $days = 7)
    {
        $dateFrom = now()->format('Y-m-d');
        $dateTo = now()->addDays($days)->format('Y-m-d');

        return $this->request('/matches', [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'status' => 'SCHEDULED',
        ]);
    }

    public function getCompetitions()
    {
        return $this->request('/competitions');
    }
}

