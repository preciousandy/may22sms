<?php

namespace App\Console\Commands;

use App\Models\FootballMatch;
use App\Services\FootballDataService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncFootballMatches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-football-matches {--days=7 : Number of days to sync}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync upcoming football matches from football-data.org';

    /**
     * Execute the console command.
     */
    public function handle(FootballDataService $service)
    {
        $days = (int) $this->option('days');
        $this->info("Syncing matches for the next {$days} days...");

        $data = $service->getUpcomingMatches($days);

        if (!isset($data['matches']) || !is_array($data['matches'])) {
            $this->error('No matches found or API error.');
            return 1;
        }

        DB::transaction(function () use ($data) {
            FootballMatch::query()->delete();

            foreach ($data['matches'] as $matchData) {
                $homeTeam = $matchData['homeTeam']['name'] ?? 'Home Team';
                $awayTeam = $matchData['awayTeam']['name'] ?? 'Away Team';
                $competition = $matchData['competition']['name'] ?? 'Competition';
                $matchTime = $matchData['utcDate'] ?? now();
                $homeCrest = $matchData['homeTeam']['crest'] ?? null;
                $awayCrest = $matchData['awayTeam']['crest'] ?? null;

                FootballMatch::create([
                    'home_team' => $homeTeam,
                    'away_team' => $awayTeam,
                    'competition' => $competition,
                    'match_time' => $matchTime,
                    'home_team_logo' => $homeCrest,
                    'away_team_logo' => $awayCrest,
                    'is_active' => true,
                ]);
            }
        });

        $count = count($data['matches']);
        $this->info("Successfully synced {$count} matches!");

        return 0;
    }
}
