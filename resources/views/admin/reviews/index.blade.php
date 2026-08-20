@extends('layouts.admin')

@section('admin_content')
@if(session('success'))
    <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-4 rounded-2xl shadow-sm flex items-center gap-3">
        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span class="font-bold text-sm">{{ session('success') }}</span>
    </div>
@endif

<div class="space-y-6">
    <!-- Page Header & Stat Summary -->
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900">User Reviews & Ratings</h1>
            <p class="text-xs font-semibold text-gray-500 mt-1">Customer feedback and star ratings submitted across SMS, Funding, Logs, and Boosting.</p>
        </div>
    </div>

    <!-- Stats Cards Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
            <div class="text-[10px] font-extrabold text-gray-400 uppercase tracking-wider">Total Reviews</div>
            <div class="text-2xl font-black text-gray-900 mt-1">{{ number_format($stats['total']) }}</div>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
            <div class="text-[10px] font-extrabold text-gray-400 uppercase tracking-wider">Average Rating</div>
            <div class="text-2xl font-black text-amber-500 mt-1 flex items-center gap-1">
                <span>⭐ {{ $stats['avg_rating'] }}</span>
                <span class="text-xs font-bold text-gray-400">/ 5.0</span>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
            <div class="text-[10px] font-extrabold text-gray-400 uppercase tracking-wider">5-Star Ratings</div>
            <div class="text-2xl font-black text-emerald-600 mt-1">{{ number_format($stats['five_star']) }}</div>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
            <div class="text-[10px] font-extrabold text-gray-400 uppercase tracking-wider">4-Star Ratings</div>
            <div class="text-2xl font-black text-indigo-600 mt-1">{{ number_format($stats['four_star']) }}</div>
        </div>
    </div>

    <!-- Reviews Table Container -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between flex-wrap gap-4">
            <h3 class="text-sm font-extrabold text-gray-900 uppercase tracking-wider">Customer Feedback</h3>
            
            <!-- Filters -->
            <form action="/admin/reviews" method="GET" class="flex items-center gap-3 flex-wrap">
                <select name="rating" onchange="this.form.submit()" class="text-xs font-bold rounded-xl border-gray-200 py-2 px-3 focus:ring-0 focus:border-[#0d9488]">
                    <option value="">All Ratings</option>
                    <option value="5" {{ request('rating') == '5' ? 'selected' : '' }}>⭐ 5 Stars</option>
                    <option value="4" {{ request('rating') == '4' ? 'selected' : '' }}>⭐ 4 Stars</option>
                    <option value="3" {{ request('rating') == '3' ? 'selected' : '' }}>⭐ 3 Stars</option>
                    <option value="2" {{ request('rating') == '2' ? 'selected' : '' }}>⭐ 2 Stars</option>
                    <option value="1" {{ request('rating') == '1' ? 'selected' : '' }}>⭐ 1 Star</option>
                </select>

                <select name="service_type" onchange="this.form.submit()" class="text-xs font-bold rounded-xl border-gray-200 py-2 px-3 focus:ring-0 focus:border-[#0d9488]">
                    <option value="">All Services</option>
                    <option value="sms" {{ request('service_type') == 'sms' ? 'selected' : '' }}>📱 SMS Numbers</option>
                    <option value="funding" {{ request('service_type') == 'funding' ? 'selected' : '' }}>💳 Funding</option>
                    <option value="logs" {{ request('service_type') == 'logs' ? 'selected' : '' }}>🛒 Store Logs</option>
                    <option value="boosting" {{ request('service_type') == 'boosting' ? 'selected' : '' }}>🚀 Boosting</option>
                    <option value="general" {{ request('service_type') == 'general' ? 'selected' : '' }}>💬 General</option>
                </select>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50 border-b border-gray-100 text-gray-400 uppercase font-black tracking-wider">
                    <tr>
                        <th class="px-6 py-4">User</th>
                        <th class="px-6 py-4">Rating</th>
                        <th class="px-6 py-4">Service</th>
                        <th class="px-6 py-4">Comments / Review</th>
                        <th class="px-6 py-4">Date</th>
                        <th class="px-6 py-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-medium text-gray-700">
                    @forelse($reviews as $rev)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-6 py-4 font-bold text-gray-900">
                                <a href="/admin/users/{{ $rev->user_id }}" class="hover:underline text-[#0d9488]">
                                    {{ $rev->user->name ?? 'User #' . $rev->user_id }}
                                </a>
                                <div class="text-[11px] text-gray-400 font-normal">{{ $rev->user->email ?? '' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center text-amber-400">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="w-4 h-4 {{ $i <= $rev->rating ? 'fill-current' : 'text-gray-200 fill-current' }}" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                    @endfor
                                    <span class="ml-1 text-xs font-extrabold text-gray-800">{{ $rev->rating }}.0</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-gray-100 text-gray-700">
                                    {{ $rev->service_type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 max-w-xs truncate text-gray-800">
                                {{ $rev->review_text ?: '— No written comment —' }}
                            </td>
                            <td class="px-6 py-4 text-gray-400 whitespace-nowrap">
                                {{ $rev->created_at ? $rev->created_at->format('M d, Y H:i') : '—' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <form action="/admin/reviews/{{ $rev->id }}" method="POST" onsubmit="return confirm('Delete this review?');" class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-600 hover:text-rose-800 font-bold text-xs bg-rose-50 px-3 py-1.5 rounded-lg border border-rose-100 hover:bg-rose-100 transition">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-400 font-bold">
                                No reviews found matching filter criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reviews->hasPages())
            <div class="p-6 border-t border-gray-100">
                {{ $reviews->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
