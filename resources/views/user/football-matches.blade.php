@extends('layouts.dashboard')

@section('page_title', 'Football Matches')

@section('dashboard_content')
<div x-data="footballMatchesData()">
    <div class="mb-6">
        <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900">Football Matches</h2>
        <p class="text-sm font-bold text-gray-500">Browse all matches, scores, and competitions</p>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-extrabold text-gray-700 mb-2">Filter Matches</label>
                <div class="flex gap-2 bg-gray-100 rounded-xl p-1">
                    <button
                        x-on:click="filter = 'upcoming'; fetchMatches()"
                        :class="filter === 'upcoming' ? 'bg-gradient-to-r from-purple-600 to-pink-500 text-white shadow-md' : 'text-gray-700 hover:bg-gray-200'"
                        class="flex-1 px-4 py-2 rounded-lg font-extrabold text-sm transition-all">
                        Upcoming
                    </button>
                    <button
                        x-on:click="filter = 'past'; fetchMatches()"
                        :class="filter === 'past' ? 'bg-gradient-to-r from-cyan-500 to-blue-600 text-white shadow-md' : 'text-gray-700 hover:bg-gray-200'"
                        class="flex-1 px-4 py-2 rounded-lg font-extrabold text-sm transition-all">
                        Past
                    </button>
                    <button
                        x-on:click="filter = 'all'; fetchMatches()"
                        :class="filter === 'all' ? 'bg-gradient-to-r from-yellow-500 to-orange-500 text-white shadow-md' : 'text-gray-700 hover:bg-gray-200'"
                        class="flex-1 px-4 py-2 rounded-lg font-extrabold text-sm transition-all">
                        All
                    </button>
                </div>
            </div>

            <div>
                <label for="competition-filter" class="block text-sm font-extrabold text-gray-700 mb-2">Competition</label>
                <select
                    id="competition-filter"
                    x-model="selectedCompetition"
                    x-on:change="fetchMatches()"
                    class="w-full rounded-xl border-2 border-gray-200 px-4 py-3 font-extrabold text-gray-900 focus:ring-0 focus:border-purple-500 transition-all">
                    <option value="all">All Competitions</option>
                    <template x-for="competition in competitions" :key="competition">
                        <option :value="competition" x-text="competition"></option>
                    </template>
                </select>
            </div>
        </div>
    </div>

    <div x-show="loading">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <template x-for="i in 6" :key="i">
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <div class="animate-pulse">
                        <div class="h-4 bg-gray-200 rounded w-1/2 mb-4"></div>
                        <div class="flex gap-4 mb-4">
                            <div class="flex-1 flex flex-col items-center">
                                <div class="h-16 w-16 bg-gray-200 rounded-full mb-2"></div>
                                <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                            </div>
                            <div class="flex items-center">
                                <div class="h-8 w-8 bg-gray-200 rounded-full"></div>
                            </div>
                            <div class="flex-1 flex flex-col items-center">
                                <div class="h-16 w-16 bg-gray-200 rounded-full mb-2"></div>
                                <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                            </div>
                        </div>
                        <div class="h-3 bg-gray-200 rounded w-1/3"></div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <div x-show="!loading && matches.length === 0" class="text-center py-12">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8">
            <div class="text-5xl mb-4">⚽</div>
            <h3 class="text-xl font-extrabold text-gray-900 mb-2" x-text="noMatchesText"></h3>
            <p class="text-gray-500">Check back later for more matches.</p>
        </div>
    </div>

    <div x-show="!loading && matches.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <template x-for="match in matches" :key="match.id">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div
                    :class="filter === 'upcoming' ? 'bg-gradient-to-r from-purple-600 to-pink-500' : (filter === 'past' ? 'bg-gradient-to-r from-cyan-500 to-blue-600' : 'bg-gradient-to-r from-yellow-500 to-orange-500')"
                    class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="text-xs font-extrabold text-white/80 uppercase tracking-wider" x-text="match.competition"></div>
                        <div class="text-xs font-extrabold text-yellow-200" x-text="formatDate(match.match_time)"></div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-3 gap-4 items-center">
                        <div class="flex flex-col items-center text-center">
                            <div x-data="{ imageFailed: false }">
                                <template x-if="match.home_team_logo">
                                    <div>
                                        <img
                                            x-show="!imageFailed"
                                            :src="match.home_team_logo"
                                            :alt="match.home_team"
                                            class="h-16 w-16 object-contain mb-3 rounded-full bg-gray-50 p-2 border border-gray-100"
                                            x-on:error="imageFailed = true">
                                        <div
                                            x-show="imageFailed"
                                            class="h-16 w-16 rounded-full bg-gray-100 flex items-center justify-center mb-3 text-2xl font-black text-gray-700"
                                            x-text="match.home_team.substring(0, 2).toUpperCase()">
                                        </div>
                                    </div>
                                </template>
                                <template x-if="!match.home_team_logo">
                                    <div class="h-16 w-16 rounded-full bg-gray-100 flex items-center justify-center mb-3 text-2xl font-black text-gray-700" x-text="match.home_team.substring(0, 2).toUpperCase()"></div>
                                </template>
                            </div>
                            <div class="font-extrabold text-lg text-gray-900" x-text="match.home_team"></div>
                        </div>

                        <div class="flex flex-col items-center justify-center">
                            <template x-if="match.home_score !== null && match.away_score !== null">
                                <div class="text-3xl font-black text-gray-900">
                                    <span x-text="match.home_score"></span>
                                    <span class="text-gray-400 mx-2">-</span>
                                    <span x-text="match.away_score"></span>
                                </div>
                                <div class="text-xs font-bold text-green-600 uppercase tracking-wider">Final</div>
                            </template>
                            <template x-if="match.home_score === null || match.away_score === null">
                                <div class="text-2xl font-black text-gray-300">VS</div>
                            </template>
                        </div>

                        <div class="flex flex-col items-center text-center">
                            <div x-data="{ imageFailed: false }">
                                <template x-if="match.away_team_logo">
                                    <div>
                                        <img
                                            x-show="!imageFailed"
                                            :src="match.away_team_logo"
                                            :alt="match.away_team"
                                            class="h-16 w-16 object-contain mb-3 rounded-full bg-gray-50 p-2 border border-gray-100"
                                            x-on:error="imageFailed = true">
                                        <div
                                            x-show="imageFailed"
                                            class="h-16 w-16 rounded-full bg-gray-100 flex items-center justify-center mb-3 text-2xl font-black text-gray-700"
                                            x-text="match.away_team.substring(0, 2).toUpperCase()">
                                        </div>
                                    </div>
                                </template>
                                <template x-if="!match.away_team_logo">
                                    <div class="h-16 w-16 rounded-full bg-gray-100 flex items-center justify-center mb-3 text-2xl font-black text-gray-700" x-text="match.away_team.substring(0, 2).toUpperCase()"></div>
                                </template>
                            </div>
                            <div class="font-extrabold text-lg text-gray-900" x-text="match.away_team"></div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>

<script>
function footballMatchesData() {
    return {
        matches: [],
        competitions: [],
        loading: true,
        filter: 'upcoming',
        selectedCompetition: 'all',

        init() {
            this.fetchMatches();
        },

        async fetchMatches() {
            this.loading = true;
            const params = new URLSearchParams();
            params.set('filter', this.filter);
            if (this.selectedCompetition !== 'all') {
                params.set('competition', this.selectedCompetition);
            }
            const res = await fetch('/api/football-matches?' + params.toString());
            const data = await res.json();
            this.matches = data.matches || [];
            this.competitions = data.competitions || [];
            this.loading = false;
        },

        formatDate(iso) {
            return new Date(iso).toLocaleString('en-US', {
                weekday: 'short',
                month: 'short',
                day: 'numeric',
                hour: 'numeric',
                minute: '2-digit'
            });
        },

        get noMatchesText() {
            if (this.filter === 'upcoming') {
                return 'No Upcoming Matches';
            } else if (this.filter === 'past') {
                return 'No Past Matches';
            }
            return 'No Matches';
        }
    };
}
</script>
@endsection
