<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FootballMatch;
use App\Services\FootballDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        return view('admin.football-matches.index', compact('matches', 'q'));
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

    public function sync(FootballDataService $service)
    {
        $data = $service->getUpcomingMatches(7);

        if (!isset($data['matches']) || !is_array($data['matches'])) {
            return back()->with('error', 'No matches found or API error.');
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

        return back()->with('success', "Successfully synced {$count} matches!");
    }
}
