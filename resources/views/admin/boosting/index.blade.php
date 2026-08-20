@extends('layouts.admin')

@section('admin_content')
@push('header_title')
    Manage Social Boosting
@endpush

@if(session('success'))
    <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-4 rounded-2xl shadow-sm flex items-center gap-3">
        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span class="font-bold text-sm">{{ session('success') }}</span>
    </div>
@endif

@if($errors->any())
    <div class="mb-6 bg-rose-50 border border-rose-200 text-rose-800 px-5 py-4 rounded-2xl shadow-sm">
        <ul class="list-disc pl-5 font-bold text-sm space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- Top Overview Metrics -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <div class="text-xs font-extrabold text-gray-500 uppercase tracking-wider">Total Services</div>
            <div class="text-2xl font-black text-gray-900 mt-1">{{ method_exists($services, 'total') ? number_format($services->total()) : number_format($services->count()) }}</div>
            <div class="text-xs font-bold text-teal-600 mt-0.5">{{ count($categories ?? []) }} Categories</div>
        </div>
        <div class="p-3 bg-teal-50 text-[#0d9488] rounded-xl">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <div class="text-xs font-extrabold text-gray-500 uppercase tracking-wider">Global Markup</div>
            <div class="text-2xl font-black text-gray-900 mt-1">{{ number_format($markup, 1) }}%</div>
            <div class="text-xs font-bold text-gray-500 mt-0.5">Applied on base rate</div>
        </div>
        <div class="p-3 bg-amber-50 text-amber-600 rounded-xl">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <div class="text-xs font-extrabold text-gray-500 uppercase tracking-wider">Failing Services</div>
            <div class="text-2xl font-black text-[#0d9488] mt-1">{{ number_format((int) ($failingCount ?? 0)) }}</div>
            <div class="text-xs font-bold text-rose-600 mt-0.5">{{ (int) ($failingCount ?? 0) > 0 ? 'Requires attention' : 'All healthy' }}</div>
        </div>
        <div class="p-3 bg-rose-50 text-rose-600 rounded-xl">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <div class="text-xs font-extrabold text-gray-500 uppercase tracking-wider">Auto Sync</div>
            <div class="text-2xl font-black text-gray-900 mt-1">{{ ($autoSyncEnabled ?? '0') === '1' ? 'Enabled' : 'Disabled' }}</div>
            <div class="text-xs font-bold text-gray-500 mt-0.5">{{ !empty($autoSyncLastRun) ? 'Last: '.$autoSyncLastRun : 'Hourly schedule' }}</div>
        </div>
        <div class="p-3 bg-indigo-50 text-indigo-600 rounded-xl">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Pricing Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden lg:col-span-1 flex flex-col justify-between">
        <div>
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/80">
                <h3 class="text-base font-black text-gray-900">Pricing Markups</h3>
            </div>
            <div class="p-6">
                <form action="/admin/boosting/settings" method="POST">
                    @csrf
                    <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-2">Global Markup (%)</label>
                    <div class="flex gap-2">
                        <input type="number" step="0.1" min="0" name="boost_markup_percent" value="{{ old('boost_markup_percent', $markup) }}" class="w-full text-sm rounded-xl border border-gray-200 px-4 py-2.5 focus:border-[#0d9488] focus:ring-0 font-bold" required>
                        <button type="submit" class="bg-gray-900 text-white px-5 py-2.5 rounded-xl font-extrabold text-xs hover:bg-gray-800 transition shrink-0">
                            Save
                        </button>
                    </div>
                </form>

                <div class="mt-6 pt-6 border-t border-gray-100">
                    <div class="text-xs font-extrabold text-gray-900 uppercase tracking-wider mb-1">Category Markups</div>
                    <div class="text-xs font-semibold text-gray-500 mb-3">Format: Category=Percent (e.g. Instagram Followers=25)</div>
                    @php
                        $catLines = [];
                        $cats = is_array($categories ?? null) ? $categories : [];
                        $over = is_array($boostMarkupByCategory ?? null) ? $boostMarkupByCategory : [];
                        foreach ($cats as $c) {
                            $v = array_key_exists($c, $over) ? $over[$c] : null;
                            $catLines[] = $c . '=' . ($v !== null ? (string) $v : '');
                        }
                    @endphp
                    <form action="/admin/boosting/category-markups" method="POST">
                        @csrf
                        <textarea name="category_markups" rows="7" class="w-full rounded-xl border border-gray-200 p-3 text-xs font-mono focus:border-[#0d9488] focus:ring-0" placeholder="Instagram Followers=25&#10;TikTok Likes=20">{{ old('category_markups', implode("\n", $catLines)) }}</textarea>
                        <button type="submit" class="mt-3 w-full bg-[#0d9488] hover:bg-[#0f766e] text-white px-4 py-2.5 rounded-xl text-xs font-extrabold transition shadow-md">
                            Save Category Markups
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Provider Sync Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden lg:col-span-2 flex flex-col justify-between">
        <div>
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/80 flex items-center justify-between">
                <h3 class="text-base font-black text-gray-900">JustAnotherPanel Provider Sync</h3>
                <span class="bg-[#0d9488] text-white text-xs font-black px-3 py-1 rounded-full shadow-sm">{{ method_exists($services, 'total') ? number_format($services->total()) : number_format($services->count()) }} Services Active</span>
            </div>
            <div class="p-6">
                <p class="text-sm font-semibold text-gray-600 mb-6">Synchronize boost services from JustAnotherPanel API to update available categories, base rates, and order limits.</p>
                
                <form action="/admin/boosting/sync" method="POST">
                    @csrf
                    <button type="submit" class="w-full bg-[#0d9488] hover:bg-[#0f766e] text-white px-6 py-3.5 rounded-2xl font-black text-sm shadow-md transition flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        Sync Services from Provider Now
                    </button>
                </form>

                <div class="mt-8 pt-6 border-t border-gray-100">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <div class="font-extrabold text-sm text-gray-900">Hourly Auto-Sync</div>
                            <div class="text-xs font-semibold text-gray-500">Automatically sync service catalog every hour via cron scheduler.</div>
                            @if(!empty($autoSyncLastRun))
                                <div class="text-xs font-bold text-teal-600 mt-1">Last Sync: {{ $autoSyncLastRun }}</div>
                            @endif
                        </div>
                        <form action="/admin/boosting/auto-sync" method="POST">
                            @csrf
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="boost_auto_sync_enabled" value="1" onchange="this.form.submit()" class="sr-only peer" {{ ($autoSyncEnabled ?? '0') === '1' ? 'checked' : '' }}>
                                <div class="relative w-12 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#0d9488]"></div>
                            </label>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Services Table -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
    <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/80 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h3 class="text-lg font-black text-gray-900">Boosting Services Catalog</h3>
            @if(method_exists($services, 'firstItem') && $services->total() > 0)
                <div class="text-xs font-bold text-gray-500 mt-0.5">
                    Showing {{ number_format($services->firstItem()) }}–{{ number_format($services->lastItem()) }} of {{ number_format($services->total()) }} services
                </div>
            @endif
        </div>
        
        <div class="flex flex-wrap items-center gap-2">
            <form method="GET" action="/admin/boosting" class="flex items-center gap-2">
                <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Search service/category/error..." class="w-56 px-3 py-2 text-xs font-medium rounded-xl border border-gray-200 focus:border-[#0d9488] focus:ring-0">
                <select name="status" class="px-3 py-2 text-xs font-bold rounded-xl border border-gray-200 focus:border-[#0d9488] focus:ring-0">
                    <option value="all" {{ ($status ?? 'all') === 'all' ? 'selected' : '' }}>All Services</option>
                    <option value="active" {{ ($status ?? 'all') === 'active' ? 'selected' : '' }}>Active Only</option>
                    <option value="inactive" {{ ($status ?? 'all') === 'inactive' ? 'selected' : '' }}>Inactive Only</option>
                    <option value="failing" {{ ($status ?? 'all') === 'failing' ? 'selected' : '' }}>Failing ({{ (int) ($failingCount ?? 0) }})</option>
                </select>
                <button type="submit" class="bg-gray-900 text-white px-4 py-2 rounded-xl text-xs font-extrabold hover:bg-gray-800 transition">Filter</button>
            </form>
            @if(($failingCount ?? 0) > 0)
                <form action="/admin/boosting/reset-all-failures" method="POST" onsubmit="return confirm('Reset failure statistics across all services?');">
                    @csrf
                    <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white px-3.5 py-2 rounded-xl text-xs font-extrabold transition">
                        Reset Fail Stats
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Service Name & ID</th>
                    <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Rate / 1k</th>
                    <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Min / Max</th>
                    <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Override Rate</th>
                    <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Failures</th>
                    <th class="px-6 py-3.5 text-right text-xs font-extrabold text-gray-500 uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @foreach($services as $service)
                    @php
                        $cat = (string) ($service->category ?? 'Other');
                        $markupForService = (float) ((is_array($boostMarkupByCategory ?? null) && array_key_exists($cat, $boostMarkupByCategory)) ? $boostMarkupByCategory[$cat] : $markup);
                        $finalRate = $service->finalRate($markupForService);
                        $hasFailure = ((int) ($service->fail_count ?? 0)) > 0;
                    @endphp
                    <tr class="{{ $hasFailure ? 'bg-rose-50/40' : 'hover:bg-gray-50/50' }} transition">
                        <td class="px-6 py-4 text-xs font-bold text-gray-600 whitespace-nowrap">
                            <span class="px-2.5 py-1 rounded-lg bg-gray-100 text-gray-700 font-extrabold">{{ $service->category ?? 'Other' }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <div class="font-extrabold text-gray-900">{{ $service->name }}</div>
                            <div class="text-xs text-gray-500 font-semibold">JAP ID #{{ $service->jap_service_id }} · {{ $service->type }}</div>
                            @if($hasFailure && ! empty($service->last_error))
                                <div class="mt-1 text-xs font-bold text-rose-600 max-w-xs truncate" title="{{ e($service->last_error) }}">
                                    Error: {{ e($service->last_error) }}
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700 whitespace-nowrap">
                            <div class="font-black text-[#0d9488]">${{ number_format($finalRate, 4) }}</div>
                            <div class="text-xs text-gray-400 font-semibold">Base: ${{ number_format($service->rate, 4) }}</div>
                        </td>
                        <td class="px-6 py-4 text-xs font-bold text-gray-600 whitespace-nowrap">
                            {{ number_format((int) $service->min) }} / {{ number_format((int) $service->max) }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700 whitespace-nowrap">
                            <form action="/admin/boosting/services/{{ $service->id }}" method="POST" class="flex items-center gap-2">
                                @csrf
                                <input type="number" step="0.0001" min="0" name="price_override" value="{{ $service->price_override !== null ? (float) $service->price_override : '' }}" placeholder="Auto" class="w-28 text-xs font-bold rounded-xl border border-gray-200 px-2.5 py-1.5 focus:border-[#0d9488] focus:ring-0">
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700 whitespace-nowrap">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_active" class="sr-only peer" {{ $service->is_active ? 'checked' : '' }}>
                                    <div class="relative w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#0d9488]"></div>
                                </label>
                        </td>
                        <td class="px-6 py-4 text-sm whitespace-nowrap">
                            @if($hasFailure)
                                <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-extrabold {{ (int)$service->fail_count >= 3 ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ (int) $service->fail_count }} fail(s)
                                </span>
                            @else
                                <span class="text-xs text-gray-400 font-bold">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-2">
                                <button type="submit" class="bg-gray-900 text-white px-3 py-1.5 rounded-xl text-xs font-extrabold hover:bg-gray-800 transition">
                                    Save
                                </button>
                            </form>
                            @if($hasFailure)
                                <form action="/admin/boosting/services/{{ $service->id }}/reset-failures" method="POST" onsubmit="return confirm('Reset failure stats for this service?');">
                                    @csrf
                                    <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white px-3 py-1.5 rounded-xl text-xs font-extrabold transition">
                                        Reset
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach

                @if($services->isEmpty())
                    <tr>
                        <td colspan="8" class="px-6 py-10 text-center text-gray-500 font-bold">No services match your filter.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
    @if(method_exists($services, 'links'))
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
            {{ $services->onEachSide(1)->links() }}
        </div>
    @endif
</div>
@endsection
