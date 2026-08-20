<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FootballDataService
{
    protected string $apiToken;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiToken = config('football-data.api_token');
        $this->baseUrl = config('football-data.base_url');
    }

    protected function request(string $endpoint, array $params = [])
    {
        $response = Http::withHeaders([
            'X-Auth-Token' => $this->apiToken,
        ])->get($this->baseUrl . $endpoint, $params);

        return $response->json();
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
