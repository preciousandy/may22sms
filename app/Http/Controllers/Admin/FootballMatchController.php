<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FootballMatch;
use App\Services\FootballDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Setting;

class FootballMatchController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $query = FootballMatch::query()
            ->orderBy('match_time', 'asc');

        if ($q !== '') {
            $query->where(function ($qry) use ($q) {
                $qry->where('home_team', 'like', "%{$q}%")
                    ->orWhere('away_team', 'like', "%{$q}%")
                    ->orWhere('competition', 'like', "%{$q}%");
            });
        }

        $matches = $query->paginate(20)->withQueryString();

        $apiToken = Setting::where('key', 'football_data_api_token')->value('value') ?: (string) config('football-data.api_token', '');
        $syncDays = (int) (Setting::where('key', 'football_sync_days')->value('value') ?? 7);
        $autoSync = (int) (Setting::where('key', 'football_auto_sync_enabled')->value('value') ?? 1) === 1;
        $lastSyncAt = Setting::where('key', 'football_last_sync_at')->value('value');
        $lastSyncCount = Setting::where('key', 'football_last_sync_count')->value('value');

        return view('admin.football-matches.index', compact('matches', 'q', 'apiToken', 'syncDays', 'autoSync', 'lastSyncAt', 'lastSyncCount'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'home_team' => 'required|string|max:255',
            'away_team' => 'required|string|max:255',
            'competition' => 'required|string|max:255',
            'match_time' => 'required|date',
            'home_team_logo' => 'nullable|string|max:255',
            'away_team_logo' => 'nullable|string|max:255',
            'banner_image' => 'nullable|string|max:255',
            'home_score' => 'nullable|integer|min:0',
            'away_score' => 'nullable|integer|min:0',
            'is_active' => 'nullable|in:1',
        ]);

        FootballMatch::create([
            'home_team' => $request->home_team,
            'away_team' => $request->away_team,
            'competition' => $request->competition,
            'match_time' => $request->match_time,
            'home_team_logo' => $request->home_team_logo,
            'away_team_logo' => $request->away_team_logo,
            'banner_image' => $request->banner_image,
            'home_score' => $request->home_score,
            'away_score' => $request->away_score,
            'is_active' => $request->has('is_active'),
        ]);

        return back()->with('success', 'Match created successfully.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'home_team' => 'required|string|max:255',
            'away_team' => 'required|string|max:255',
            'competition' => 'required|string|max:255',
            'match_time' => 'required|date',
            'home_team_logo' => 'nullable|string|max:255',
            'away_team_logo' => 'nullable|string|max:255',
            'banner_image' => 'nullable|string|max:255',
            'home_score' => 'nullable|integer|min:0',
            'away_score' => 'nullable|integer|min:0',
            'is_active' => 'nullable|in:1',
        ]);

        $match = FootballMatch::findOrFail($id);
        $match->update([
            'home_team' => $request->home_team,
            'away_team' => $request->away_team,
            'competition' => $request->competition,
            'match_time' => $request->match_time,
            'home_team_logo' => $request->home_team_logo,
            'away_team_logo' => $request->away_team_logo,
            'banner_image' => $request->banner_image,
            'home_score' => $request->home_score,
            'away_score' => $request->away_score,
            'is_active' => $request->has('is_active'),
        ]);

        return back()->with('success', 'Match updated successfully.');
    }

    public function destroy($id)
    {
        $match = FootballMatch::findOrFail($id);
        $match->delete();

        return back()->with('success', 'Match deleted successfully.');
    }

    public function updateSettings(Request $request, FootballDataService $service)
    {
        $request->validate([
            'football_data_api_token' => 'nullable|string|max:255',
            'football_sync_days' => 'required|integer|min:1|max:30',
            'football_auto_sync_enabled' => 'nullable|in:0,1',
        ]);

        Setting::updateOrCreate(
            ['key' => 'football_data_api_token'],
            ['value' => trim((string) $request->input('football_data_api_token', ''))]
        );

        Setting::updateOrCreate(
            ['key' => 'football_sync_days'],
            ['value' => (string) $request->input('football_sync_days', '7')]
        );

        Setting::updateOrCreate(
            ['key' => 'football_auto_sync_enabled'],
            ['value' => $request->has('football_auto_sync_enabled') ? '1' : '0']
        );

        if ($request->input('action') === 'save_and_sync') {
            return $this->sync($request, new FootballDataService());
        }

        return back()->with('success', 'Football sync settings updated successfully.');
    }

    public function sync(Request $request, FootballDataService $service)
    {
        $configuredDays = (int) (Setting::where('key', 'football_sync_days')->value('value') ?? 7);
        $days = (int) ($request->input('days') ?: $configuredDays);
        if ($days < 1 || $days > 30) {
            $days = 7;
        }

        $data = $service->getUpcomingMatches($days);

        if (isset($data['error']) && $data['error']) {
            return back()->with('error', 'Sync Failed: ' . ($data['message'] ?? 'Could not communicate with football-data.org API. Check your API token.'));
        }

        if (!isset($data['matches']) || !is_array($data['matches'])) {
            $msg = $data['message'] ?? 'No match data returned from football-data.org.';
            return back()->with('error', 'Sync Warning: ' . $msg);
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

        return back()->with('success', "Successfully synced {$count} matches for the next {$days} days!");
    }
}
