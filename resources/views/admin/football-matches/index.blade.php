@extends('layouts.admin')

@section('admin_content')
@push('header_title')
    Football Matches
@endpush
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-2xl font-extrabold text-gray-900">Football Matches</h2>
            <div class="text-sm font-bold text-gray-500">Manage and display this week's matches</div>
        </div>
        <div class="flex flex-wrap gap-2">
            <form method="POST" action="/admin/football-matches/sync" onsubmit="return confirm('Are you sure you want to sync matches from football-data.org? This will replace all existing matches.');">
                @csrf
                <button type="submit" class="inline-flex items-center rounded-xl bg-[#0d9488] hover:bg-[#0f766e] text-white px-4 py-2 text-sm font-extrabold transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Sync from API
                </button>
            </form>
            <a href="/admin/dashboard" class="inline-flex items-center rounded-xl bg-gray-100 px-4 py-2 text-sm font-extrabold text-gray-900 hover:bg-gray-200 transition">Dashboard</a>
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
@endsection
