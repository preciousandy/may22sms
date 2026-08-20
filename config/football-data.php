<?php

return [
    'api_token' => env('FOOTBALL_DATA_API_TOKEN'),
    'base_url' => 'https://api.football-data.org/v4',
    'competitions' => [
        // Premier League
        'PL' => 'Premier League',
        // La Liga
        'PD' => 'La Liga',
        // Bundesliga
        'BL1' => 'Bundesliga',
        // Serie A
        'SA' => 'Serie A',
        // Ligue 1
        'FL1' => 'Ligue 1',
        // Champions League
        'CL' => 'UEFA Champions League',
    ],
];
