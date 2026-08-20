<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\FootballMatch;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FootballMatchController extends Controller
{
    public function index()
    {
        return view('user.football-matches');
    }

    public function apiIndex(Request $request)
    {
        $filter = $request->get('filter', 'upcoming'); // upcoming, past, all
        $competition = $request->get('competition', 'all');

        $query = FootballMatch::where('is_active', true);

        if ($competition !== 'all') {
            $query->where('competition', $competition);
        }

        if ($filter === 'upcoming') {
            $query->where('match_time', '>=', now())->orderBy('match_time', 'asc');
        } elseif ($filter === 'past') {
            $query->where('match_time', '<', now())->orderBy('match_time', 'desc');
        } else {
            $query->orderBy('match_time', 'desc');
        }

        $matches = $query->get()->map(fn ($match) => [
            'id' => $match->id,
            'home_team' => $match->home_team,
            'away_team' => $match->away_team,
            'competition' => $match->competition,
            'match_time' => $match->match_time->toIso8601String(),
            'home_team_logo' => $this->resolveTeamLogo($match->home_team_logo, $match->home_team),
            'away_team_logo' => $this->resolveTeamLogo($match->away_team_logo, $match->away_team),
            'banner_image' => $match->banner_image,
            'home_score' => $match->home_score,
            'away_score' => $match->away_score,
        ]);

        // Get unique competitions for filter
        $competitions = FootballMatch::where('is_active', true)
            ->distinct()
            ->pluck('competition');

        return response()->json([
            'matches' => $matches,
            'competitions' => $competitions
        ]);
    }

    private function resolveTeamLogo(?string $logo, string $teamName): string
    {
        $logo = trim((string) $logo);

        if ($logo !== '') {
            if (Str::startsWith($logo, ['http://', 'https://', 'data:'])) {
                return $logo;
            }

            return asset(ltrim($logo, '/'));
        }

        return $this->generateFallbackBadge($teamName);
    }

    private function generateFallbackBadge(string $teamName): string
    {
        $initials = collect(preg_split('/\s+/', trim($teamName)) ?: [])
            ->filter()
            ->take(2)
            ->map(fn ($word) => Str::upper(Str::substr($word, 0, 1)))
            ->implode('');

        if ($initials === '') {
            $initials = 'FC';
        }

        $palette = [
            ['#1d4ed8', '#60a5fa'],
            ['#7c3aed', '#c084fc'],
            ['#be123c', '#fb7185'],
            ['#0f766e', '#2dd4bf'],
            ['#b45309', '#f59e0b'],
        ];

        $colors = $palette[crc32($teamName) % count($palette)];
        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 96 96">
  <defs>
    <linearGradient id="g" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="{$colors[0]}"/>
      <stop offset="100%" stop-color="{$colors[1]}"/>
    </linearGradient>
  </defs>
  <circle cx="48" cy="48" r="46" fill="url(#g)"/>
  <circle cx="48" cy="48" r="41" fill="none" stroke="rgba(255,255,255,0.35)" stroke-width="2"/>
  <text x="48" y="56" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="30" font-weight="700" fill="#ffffff">{$initials}</text>
</svg>
SVG;

        return 'data:image/svg+xml;utf8,'.rawurlencode($svg);
    }
}
