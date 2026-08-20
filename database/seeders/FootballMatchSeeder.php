<?php

namespace Database\Seeders;

use App\Models\FootballMatch;
use Illuminate\Database\Seeder;

class FootballMatchSeeder extends Seeder
{
    public function run(): void
    {
        FootballMatch::create([
            'home_team' => 'Manchester United',
            'away_team' => 'Liverpool',
            'competition' => 'Premier League',
            'match_time' => now()->addDays(1),
            'home_team_logo' => 'https://cdn-icons-png.flaticon.com/512/805/805425.png',
            'away_team_logo' => 'https://cdn-icons-png.flaticon.com/512/805/805423.png',
            'is_active' => true,
        ]);

        FootballMatch::create([
            'home_team' => 'Chelsea',
            'away_team' => 'Arsenal',
            'competition' => 'Premier League',
            'match_time' => now()->addDays(2),
            'home_team_logo' => 'https://cdn-icons-png.flaticon.com/512/805/805424.png',
            'away_team_logo' => 'https://cdn-icons-png.flaticon.com/512/805/805422.png',
            'is_active' => true,
        ]);

        FootballMatch::create([
            'home_team' => 'Barcelona',
            'away_team' => 'Real Madrid',
            'competition' => 'La Liga',
            'match_time' => now()->addDays(3),
            'home_team_logo' => 'https://cdn-icons-png.flaticon.com/512/805/805426.png',
            'away_team_logo' => 'https://cdn-icons-png.flaticon.com/512/805/805427.png',
            'is_active' => true,
        ]);
    }
}
