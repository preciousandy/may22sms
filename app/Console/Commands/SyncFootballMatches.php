<?php

namespace App\Console\Commands;

use App\Models\FootballMatch;
use App\Models\Setting;
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
    protected $signature = 'app:sync-football-matches {--days= : Number of days to sync} {--force : Force sync even if auto-sync is disabled}';

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
        $autoSyncEnabled = (int) (Setting::where('key', 'football_auto_sync_enabled')->value('value') ?? 1) === 1;
        if (! $autoSyncEnabled && ! $this->option('force')) {
            $this->info('Football auto-sync is disabled in settings. Skipping.');
            return 0;
        }

        $configuredDays = (int) (Setting::where('key', 'football_sync_days')->value('value') ?? 7);
        $days = (int) ($this->option('days') ?: $configuredDays);
        if ($days < 1 || $days > 30) {
            $days = 7;
        }

        $this->info("Syncing matches for the next {$days} days...");

        $data = $service->getUpcomingMatches($days);

        if (isset($data['error']) && $data['error']) {
            $this->error('Sync Error: ' . ($data['message'] ?? 'Could not communicate with football-data API.'));
            return 1;
        }

        if (!isset($data['matches']) || !is_array($data['matches'])) {
            $this->error('No matches found or API error: ' . ($data['message'] ?? 'Unknown response.'));
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

        Setting::updateOrCreate(['key' => 'football_last_sync_at'], ['value' => now()->toDateTimeString()]);
        Setting::updateOrCreate(['key' => 'football_last_sync_count'], ['value' => (string) $count]);

        $this->info("Successfully synced {$count} matches!");

        return 0;
    }
}
