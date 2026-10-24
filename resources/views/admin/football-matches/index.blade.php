@extends('layouts.admin')

@section('admin_content')
@push('header_title')
    Football Matches
@endpush
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <div class="flex items-center gap-2">
            <h2 class="text-2xl font-extrabold text-gray-900">Football Matches</h2>
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black bg-teal-50 text-teal-700 border border-teal-200">
                LIVE &amp; FIXTURES
            </span>
        </div>
        <div class="text-sm font-bold text-gray-500 mt-0.5">Manage live schedules, automated football-data sync, and scores</div>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <!-- Sync Settings Button -->
        <button type="button" onclick="document.getElementById('sync-settings-dialog').showModal()" class="inline-flex items-center rounded-xl bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 text-sm font-extrabold shadow-sm transition active:scale-95">
            <svg class="w-4 h-4 mr-2 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
            </svg>
            Sync Settings
        </button>

        <!-- Sync from API Button -->
        <form method="POST" action="/admin/football-matches/sync" onsubmit="return confirm('Sync upcoming matches from football-data.org? This will refresh active schedules.');">
            @csrf
            <button type="submit" class="inline-flex items-center rounded-xl bg-[#0d9488] hover:bg-[#0f766e] text-white px-4 py-2 text-sm font-extrabold shadow-sm transition active:scale-95">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Sync Now ({{ $syncDays }}d)
            </button>
        </form>

        <a href="/football-matches" target="_blank" class="inline-flex items-center rounded-xl bg-gray-100 px-3.5 py-2 text-sm font-extrabold text-gray-700 hover:bg-gray-200 transition">
            View Live Page ↗
        </a>
    </div>
</div>

<!-- Sync Status Overview Card -->
<div class="mb-6 p-4 rounded-2xl border {{ empty($apiToken) ? 'bg-amber-50 border-amber-200' : 'bg-slate-900 border-slate-800 text-white shadow-sm' }} flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl {{ empty($apiToken) ? 'bg-amber-200 text-amber-800' : 'bg-teal-500/20 text-teal-400 border border-teal-500/30' }} flex items-center justify-center text-lg shrink-0">
            {{ empty($apiToken) ? '⚠️' : '⚽' }}
        </div>
        <div>
            @if(empty($apiToken))
                <div class="text-sm font-black text-amber-900">Football API Key Missing</div>
                <div class="text-xs font-semibold text-amber-700">Click &ldquo;Sync Settings&rdquo; to input your free Football-Data.org API key to enable match synchronization.</div>
            @else
                <div class="text-sm font-black text-white flex items-center gap-2">
                    <span>Football-Data.org API Active</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                </div>
                <div class="text-xs text-slate-300 mt-0.5">
                    Sync Range: <span class="font-bold text-teal-400">{{ $syncDays }} Days</span>
                    &bull; Auto-Sync: <span class="font-bold {{ $autoSync ? 'text-emerald-400' : 'text-slate-400' }}">{{ $autoSync ? 'Enabled (Daily 00:00)' : 'Disabled' }}</span>
                    @if($lastSyncAt)
                        &bull; Last synced: <span class="text-slate-200 font-bold">{{ \Carbon\Carbon::parse($lastSyncAt)->diffForHumans() }}</span> ({{ $lastSyncCount ?? 0 }} matches)
                    @endif
                </div>
            @endif
        </div>
    </div>
    <div>
        <button type="button" onclick="document.getElementById('sync-settings-dialog').showModal()" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 rounded-xl text-xs font-black {{ empty($apiToken) ? 'bg-amber-600 text-white hover:bg-amber-700' : 'bg-white/10 text-white hover:bg-white/20 border border-white/20' }} transition">
            ⚙️ Configure Settings
        </button>
    </div>
</div>

@if(session('success'))
    <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl shadow-sm">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl shadow-sm">
        {{ session('error') }}
    </div>
@endif

@if($errors->any())
    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl shadow-sm">
        <ul class="list-disc pl-5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-extrabold text-gray-900">Matches List</h3>
                <div class="flex gap-2">
                    <form method="GET" action="{{ request()->url() }}" class="flex gap-2">
                        <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Search matches..." class="rounded-xl border border-gray-200 px-4 py-2 text-sm focus:ring-0 focus:border-[#0d9488]">
                        <button type="submit" class="bg-gray-900 text-white px-4 py-2 rounded-xl text-sm font-extrabold hover:bg-gray-800">Search</button>
                    </form>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left px-6 py-3 font-extrabold text-gray-600">Home</th>
                            <th class="text-center px-6 py-3 font-extrabold text-gray-600">Score</th>
                            <th class="text-left px-6 py-3 font-extrabold text-gray-600">Away</th>
                            <th class="text-left px-6 py-3 font-extrabold text-gray-600">Competition</th>
                            <th class="text-left px-6 py-3 font-extrabold text-gray-600">Match Time</th>
                            <th class="text-left px-6 py-3 font-extrabold text-gray-600">Status</th>
                            <th class="text-right px-6 py-3 font-extrabold text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($matches as $match)
                        <tr>
                            <td class="px-6 py-4 font-bold text-gray-900">
                                <div class="flex items-center gap-2">
                                    @if($match->home_team_logo)
                                        <img src="{{ $match->home_team_logo }}" class="h-8 w-8 object-cover rounded-full border border-gray-200">
                                    @endif
                                    {{ $match->home_team }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center font-extrabold text-gray-900">
                                @if($match->home_score !== null && $match->away_score !== null)
                                    {{ $match->home_score }} - {{ $match->away_score }}
                                @else
                                    <span class="text-gray-400">vs</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-bold text-gray-900">
                                <div class="flex items-center gap-2">
                                    @if($match->away_team_logo)
                                        <img src="{{ $match->away_team_logo }}" class="h-8 w-8 object-cover rounded-full border border-gray-200">
                                    @endif
                                    {{ $match->away_team }}
                                </div>
                            </td>
                            <td class="px-6 py-4 font-bold text-gray-700">{{ $match->competition }}</td>
                            <td class="px-6 py-4 font-bold text-gray-700">{{ $match->match_time->format('M d, Y h:i A') }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full border text-xs font-extrabold {{ $match->is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-yellow-50 text-yellow-700 border-yellow-100' }}">
                                    {{ $match->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button" x-data="{}" @click="document.getElementById('edit-{{ $match->id }}').showModal()" class="bg-gray-100 hover:bg-gray-200 border border-gray-200 px-3 py-1.5 rounded-lg text-sm font-extrabold">Edit</button>
                                    <form method="POST" action="/admin/football-matches/{{ $match->id }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Are you sure you want to delete this match?')" class="bg-red-100 hover:bg-red-200 border border-red-200 text-red-700 px-3 py-1.5 rounded-lg text-sm font-extrabold">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if(method_exists($matches, 'links'))
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                    {{ $matches->onEachSide(1)->links() }}
                </div>
            @endif
        </div>
    </div>
    <div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-extrabold text-gray-900">Add New Match</h3>
            </div>
            <div class="p-6">
                <form method="POST" action="/admin/football-matches">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-extrabold text-gray-700 mb-1">Home Team</label>
                            <input type="text" name="home_team" required class="w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:ring-0 focus:border-[#0d9488]" placeholder="e.g. Manchester United">
                        </div>
                        <div>
                            <label class="block text-sm font-extrabold text-gray-700 mb-1">Away Team</label>
                            <input type="text" name="away_team" required class="w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:ring-0 focus:border-[#0d9488]" placeholder="e.g. Liverpool">
                        </div>
                        <div>
                            <label class="block text-sm font-extrabold text-gray-700 mb-1">Competition</label>
                            <input type="text" name="competition" required class="w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:ring-0 focus:border-[#0d9488]" placeholder="e.g. Premier League">
                        </div>
                        <div>
                            <label class="block text-sm font-extrabold text-gray-700 mb-1">Match Time</label>
                            <input type="datetime-local" name="match_time" required class="w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:ring-0 focus:border-[#0d9488]">
                        </div>
                        <div>
                            <label class="block text-sm font-extrabold text-gray-700 mb-1">Home Team Logo (URL)</label>
                            <input type="text" name="home_team_logo" class="w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:ring-0 focus:border-[#0d9488]" placeholder="https://...">
                        </div>
                        <div>
                            <label class="block text-sm font-extrabold text-gray-700 mb-1">Away Team Logo (URL)</label>
                            <input type="text" name="away_team_logo" class="w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:ring-0 focus:border-[#0d9488]" placeholder="https://...">
                        </div>
                        <div>
                            <label class="block text-sm font-extrabold text-gray-700 mb-1">Banner Image (URL)</label>
                            <input type="text" name="banner_image" class="w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:ring-0 focus:border-[#0d9488]" placeholder="https://...">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-extrabold text-gray-700 mb-1">Home Score</label>
                                <input type="number" name="home_score" class="w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:ring-0 focus:border-[#0d9488]" placeholder="0" min="0">
                            </div>
                            <div>
                                <label class="block text-sm font-extrabold text-gray-700 mb-1">Away Score</label>
                                <input type="number" name="away_score" class="w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:ring-0 focus:border-[#0d9488]" placeholder="0" min="0">
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="is_active" id="is_active_new" value="1" checked class="h-4 w-4 rounded border-gray-300 text-[#0d9488] focus:ring-[#0d9488]">
                            <label for="is_active_new" class="text-sm font-extrabold text-gray-700">Active</label>
                        </div>
                        <button type="submit" class="w-full bg-[#0d9488] hover:bg-[#0f766e] text-white px-4 py-3 rounded-xl font-extrabold shadow-sm">Add Match</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@foreach($matches as $match)
<dialog id="edit-{{ $match->id }}" class="rounded-2xl border border-gray-100 shadow-xl p-0 w-full max-w-md">
    <div class="p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-extrabold text-gray-900">Edit Match</h3>
            <form method="dialog">
                <button type="submit" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </form>
        </div>
        <form method="POST" action="/admin/football-matches/{{ $match->id }}">
            @csrf
            @method('POST')
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-extrabold text-gray-700 mb-1">Home Team</label>
                    <input type="text" name="home_team" value="{{ $match->home_team }}" required class="w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:ring-0 focus:border-[#0d9488]">
                </div>
                <div>
                    <label class="block text-sm font-extrabold text-gray-700 mb-1">Away Team</label>
                    <input type="text" name="away_team" value="{{ $match->away_team }}" required class="w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:ring-0 focus:border-[#0d9488]">
                </div>
                <div>
                    <label class="block text-sm font-extrabold text-gray-700 mb-1">Competition</label>
                    <input type="text" name="competition" value="{{ $match->competition }}" required class="w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:ring-0 focus:border-[#0d9488]">
                </div>
                <div>
                    <label class="block text-sm font-extrabold text-gray-700 mb-1">Match Time</label>
                    <input type="datetime-local" name="match_time" value="{{ $match->match_time->format('Y-m-d\TH:i') }}" required class="w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:ring-0 focus:border-[#0d9488]">
                </div>
                <div>
                    <label class="block text-sm font-extrabold text-gray-700 mb-1">Home Team Logo (URL)</label>
                    <input type="text" name="home_team_logo" value="{{ $match->home_team_logo }}" class="w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:ring-0 focus:border-[#0d9488]">
                </div>
                <div>
                    <label class="block text-sm font-extrabold text-gray-700 mb-1">Away Team Logo (URL)</label>
                    <input type="text" name="away_team_logo" value="{{ $match->away_team_logo }}" class="w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:ring-0 focus:border-[#0d9488]">
                </div>
                <div>
                    <label class="block text-sm font-extrabold text-gray-700 mb-1">Banner Image (URL)</label>
                    <input type="text" name="banner_image" value="{{ $match->banner_image }}" class="w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:ring-0 focus:border-[#0d9488]">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-extrabold text-gray-700 mb-1">Home Score</label>
                        <input type="number" name="home_score" value="{{ $match->home_score }}" class="w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:ring-0 focus:border-[#0d9488]" placeholder="0" min="0">
                    </div>
                    <div>
                        <label class="block text-sm font-extrabold text-gray-700 mb-1">Away Score</label>
                        <input type="number" name="away_score" value="{{ $match->away_score }}" class="w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:ring-0 focus:border-[#0d9488]" placeholder="0" min="0">
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="is_active_{{ $match->id }}" value="1" {{ $match->is_active ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-[#0d9488] focus:ring-[#0d9488]">
                    <label for="is_active_{{ $match->id }}" class="text-sm font-extrabold text-gray-700">Active</label>
                </div>
                <div class="flex gap-2 pt-2">
                    <button type="submit" class="flex-1 bg-[#0d9488] hover:bg-[#0f766e] text-white px-4 py-2.5 rounded-xl font-extrabold shadow-sm">Save Changes</button>
                    <form method="dialog" class="flex-1">
                        <button type="submit" class="w-full bg-gray-100 hover:bg-gray-200 border border-gray-200 text-gray-900 px-4 py-2.5 rounded-xl font-extrabold">Cancel</button>
                    </form>
                </div>
            </div>
        </form>
    </div>
</dialog>
@endforeach

<!-- SYNC SETTINGS DIALOG -->
<dialog id="sync-settings-dialog" class="rounded-2xl p-0 w-full max-w-lg shadow-2xl backdrop:bg-black/60 border-0 overflow-hidden">
    <div class="bg-white" x-data="{ showToken: false }">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-slate-900 text-white">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-teal-500/20 text-teal-400 border border-teal-500/30 flex items-center justify-center font-black text-base">
                    ⚙️
                </div>
                <div>
                    <h3 class="text-base font-black text-white">Football Sync Settings</h3>
                    <p class="text-[11px] text-slate-400">Configure Football-Data.org API and synchronization</p>
                </div>
            </div>
            <form method="dialog">
                <button type="submit" class="text-slate-400 hover:text-white transition p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </form>
        </div>

        <form method="POST" action="/admin/football-matches/settings" class="p-6 space-y-4">
            @csrf

            <!-- API Token -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-black uppercase tracking-wider text-gray-700">Football-Data.org API Token</label>
                    <a href="https://www.football-data.org/client/register" target="_blank" class="text-xs font-bold text-[#0d9488] hover:underline flex items-center gap-1">
                        Get free token ↗
                    </a>
                </div>
                <div class="relative">
                    <input :type="showToken ? 'text' : 'password'" 
                           name="football_data_api_token" 
                           value="{{ old('football_data_api_token', $apiToken) }}" 
                           placeholder="e.g. 9f8a7b6c5d4e3f2a1b0c..."
                           class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 pr-20 text-sm font-mono focus:ring-0 focus:border-[#0d9488]">
                    <button type="button" 
                            @click="showToken = !showToken" 
                            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-xs font-black px-2 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-600">
                        <span x-text="showToken ? 'Hide' : 'Show'"></span>
                    </button>
                </div>
                <p class="text-[11px] text-gray-500 mt-1">
                    Sign up on <a href="https://www.football-data.org" target="_blank" class="underline font-bold text-gray-700">football-data.org</a> for free to get an API key.
                </p>
            </div>

            <!-- Sync Days Range -->
            <div>
                <label class="block text-xs font-black uppercase tracking-wider text-gray-700 mb-1.5">Sync Range (Days Ahead)</label>
                <select name="football_sync_days" class="w-full rounded-xl border border-gray-200 px-3.5 py-2.5 text-sm font-bold focus:ring-0 focus:border-[#0d9488]">
                    <option value="1" {{ (int) old('football_sync_days', $syncDays) === 1 ? 'selected' : '' }}>1 Day (Today &amp; Tomorrow)</option>
                    <option value="3" {{ (int) old('football_sync_days', $syncDays) === 3 ? 'selected' : '' }}>3 Days (Next 72 Hours)</option>
                    <option value="7" {{ (int) old('football_sync_days', $syncDays) === 7 ? 'selected' : '' }}>7 Days (Upcoming Week - Recommended)</option>
                    <option value="14" {{ (int) old('football_sync_days', $syncDays) === 14 ? 'selected' : '' }}>14 Days (Next 2 Weeks)</option>
                    <option value="30" {{ (int) old('football_sync_days', $syncDays) === 30 ? 'selected' : '' }}>30 Days (Full Month)</option>
                </select>
                <p class="text-[11px] text-gray-500 mt-1">Determines how many days of upcoming matches are fetched into the database.</p>
            </div>

            <!-- Auto-Sync Toggle -->
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 flex items-start gap-3">
                <input type="checkbox" 
                       name="football_auto_sync_enabled" 
                       id="football_auto_sync_enabled" 
                       value="1" 
                       {{ old('football_auto_sync_enabled', $autoSync) ? 'checked' : '' }} 
                       class="h-4 w-4 rounded border-gray-300 text-[#0d9488] focus:ring-[#0d9488] mt-0.5">
                <label for="football_auto_sync_enabled" class="cursor-pointer">
                    <span class="block text-xs font-black text-gray-800">Enable Automated Daily Sync</span>
                    <span class="block text-[11px] text-gray-500">When enabled, the server cron job automatically synchronizes upcoming fixtures every night at 00:00.</span>
                </label>
            </div>

            <!-- Action buttons -->
            <div class="flex flex-col sm:flex-row gap-2 pt-2 border-t border-gray-100">
                <button type="submit" name="action" value="save" class="flex-1 bg-slate-900 hover:bg-slate-800 text-white py-2.5 px-4 rounded-xl font-black text-xs uppercase tracking-wider transition">
                    Save Settings
                </button>
                <button type="submit" name="action" value="save_and_sync" class="flex-1 bg-[#0d9488] hover:bg-[#0f766e] text-white py-2.5 px-4 rounded-xl font-black text-xs uppercase tracking-wider shadow-md shadow-teal-600/20 transition">
                    Save &amp; Sync Now ⚡
                </button>
                <form method="dialog" class="sm:w-auto">
                    <button type="submit" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 py-2.5 px-4 rounded-xl font-bold text-xs transition">
                        Cancel
                    </button>
                </form>
            </div>
        </form>
    </div>
</dialog>
@endsection
