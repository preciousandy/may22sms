@extends('layouts.dashboard')

@section('page_title', 'Overview')

@section('dashboard_content')
<div
    x-data="{
        index: 0,
        slides: [
            {
                title: @js('Welcome back, ' . explode(' ', auth()->user()->name ?? 'User')[0] . '!'),
                body: @js('Your central hub for SMS verification, premium social media accounts, and boosting services.'),
                primaryHref: '/funding',
                primaryLabel: 'Add Funds',
                secondaryHref: '/social-boosting',
                secondaryLabel: 'Start Boosting'
            },
            {
                title: @js('Quick Tip'),
                body: @js('Switch USD/NGN from the top right to see the real cost of services in your currency.'),
                primaryHref: '/sms-verify',
                primaryLabel: 'Get a Number',
                secondaryHref: '/logs-store',
                secondaryLabel: 'Browse Logs'
            },
            {
                title: @js('Keep Your Account Updated'),
                body: @js('Update your profile details and change your password anytime from your Profile page.'),
                primaryHref: '/profile',
                primaryLabel: 'Update Profile',
                secondaryHref: '/funding',
                secondaryLabel: 'View Funding'
            }
        ],
        next() { this.index = (this.index + 1) % this.slides.length; },
        prev() { this.index = (this.index - 1 + this.slides.length) % this.slides.length; },
        go(i) { this.index = i; }
    }"
    x-init="setInterval(() => next(), 6500)"
    class="bg-gradient-to-r from-[#0d9488] to-teal-700 rounded-2xl p-6 sm:p-8 mb-8 text-white shadow-lg relative overflow-hidden"
>
    <div class="relative z-10 w-full">
        <h2 class="text-2xl sm:text-3xl font-bold mb-2" x-text="slides[index].title"></h2>
        <p class="text-teal-100 max-w-xl text-sm sm:text-base" x-text="slides[index].body"></p>
        <div class="mt-6 flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4 w-full sm:w-auto">
            <a :href="slides[index].primaryHref" class="bg-[#facc15] text-black px-6 py-2 rounded-lg font-bold shadow-md hover:bg-yellow-400 transition text-center" x-text="slides[index].primaryLabel"></a>
            <a :href="slides[index].secondaryHref" class="bg-white/20 text-white backdrop-blur-sm border border-white/30 px-6 py-2 rounded-lg font-medium hover:bg-white/30 transition text-center" x-text="slides[index].secondaryLabel"></a>
        </div>

        <div class="mt-6 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <template x-for="(s, i) in slides" :key="i">
                    <button type="button" @click="go(i)" class="h-2.5 rounded-full transition" :class="index === i ? 'w-8 bg-white' : 'w-2.5 bg-white/40 hover:bg-white/60'" aria-label="Go to slide"></button>
                </template>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" @click="prev()" class="w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 border border-white/20 backdrop-blur-sm flex items-center justify-center" aria-label="Previous slide">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </button>
                <button type="button" @click="next()" class="w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 border border-white/20 backdrop-blur-sm flex items-center justify-center" aria-label="Next slide">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </button>
            </div>
        </div>
    </div>
    <div class="absolute right-0 top-0 opacity-10 transform translate-x-1/4 -translate-y-1/4">
        <svg width="300" height="300" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L2 22h20L12 2z"/></svg>
    </div>
</div>

<!-- Quick Stats -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
    <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 flex items-center">
        <div class="w-14 h-14 rounded-full bg-teal-50 flex items-center justify-center text-[#0d9488]">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div class="ml-4">
            <h3 class="text-gray-500 text-sm font-medium">Available Balance</h3>
            <p class="text-2xl font-bold text-gray-900">{{ $currencySymbol ?? '$' }}{{ ($formatAmount ?? fn($x) => number_format($x, 2))(auth()->user()->walletBalance ?? 0) }}</p>
        </div>
    </div>
    
    <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 flex items-center">
        <div class="w-14 h-14 rounded-full bg-blue-50 flex items-center justify-center text-blue-500">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
        </div>
        <div class="ml-4">
            <h3 class="text-gray-500 text-sm font-medium">SMS Numbers</h3>
            <p class="text-2xl font-bold text-gray-900">{{ $smsNumbersCount ?? 0 }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 flex items-center">
        <div class="w-14 h-14 rounded-full bg-yellow-50 flex items-center justify-center text-yellow-600">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
        </div>
        <div class="ml-4">
            <h3 class="text-gray-500 text-sm font-medium">Logs Purchased</h3>
            <p class="text-2xl font-bold text-gray-900">{{ $logsPurchasedCount ?? 0 }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 flex items-center">
        <div class="w-14 h-14 rounded-full bg-purple-50 flex items-center justify-center text-purple-500">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
        </div>
        <div class="ml-4">
            <h3 class="text-gray-500 text-sm font-medium">Active Boosts</h3>
            <p class="text-2xl font-bold text-gray-900">{{ $boostsCount ?? 0 }}</p>
        </div>
    </div>
</div>

<div id="exchange" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
    <div class="px-6 py-5 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
        <div>
            <h3 class="text-lg font-bold text-gray-800">Currency Exchange</h3>
            <p class="text-sm text-gray-500">Live rate for reference (display only).</p>
        </div>
        <div class="text-sm font-bold text-gray-800">
            1 USD = ₦{{ number_format((float) ($liveNgnPerUsd ?? $ngnPerUsd ?? 1500), 2) }}
        </div>
    </div>
    <div class="p-6" x-data="{
        rate: {{ (float) ($liveNgnPerUsd ?? $ngnPerUsd ?? 1500) }},
        usd: '',
        ngn: '',
        format2(v) { return Number(v || 0).toFixed(2); },
        fromUsd() {
            const u = Number(this.usd || 0);
            if (!isFinite(u) || this.rate <= 0) { this.ngn = ''; return; }
            this.ngn = this.format2(u * this.rate);
        },
        fromNgn() {
            const n = Number(this.ngn || 0);
            if (!isFinite(n) || this.rate <= 0) { this.usd = ''; return; }
            this.usd = this.format2(n / this.rate);
        }
    }">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">USD</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <span class="text-gray-500 font-bold">$</span>
                            </div>
                            <input type="number" inputmode="decimal" step="0.01" min="0" x-model="usd" @input="fromUsd()" class="block w-full pl-10 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-0 focus:border-[#0d9488] text-lg font-bold text-gray-900 transition" placeholder="0.00">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">NGN</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <span class="text-gray-500 font-bold">₦</span>
                            </div>
                            <input type="number" inputmode="decimal" step="1" min="0" x-model="ngn" @input="fromNgn()" class="block w-full pl-10 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-0 focus:border-[#0d9488] text-lg font-bold text-gray-900 transition" placeholder="0.00">
                        </div>
                    </div>
                </div>

                <div class="mt-5 flex flex-wrap gap-2">
                    <button type="button" @click="usd='5'; fromUsd()" class="px-3 py-2 rounded-lg bg-gray-100 text-gray-800 font-bold text-sm hover:bg-gray-200 transition">$5</button>
                    <button type="button" @click="usd='10'; fromUsd()" class="px-3 py-2 rounded-lg bg-gray-100 text-gray-800 font-bold text-sm hover:bg-gray-200 transition">$10</button>
                    <button type="button" @click="usd='20'; fromUsd()" class="px-3 py-2 rounded-lg bg-gray-100 text-gray-800 font-bold text-sm hover:bg-gray-200 transition">$20</button>
                    <button type="button" @click="ngn='1000'; fromNgn()" class="px-3 py-2 rounded-lg bg-gray-100 text-gray-800 font-bold text-sm hover:bg-gray-200 transition">₦1,000</button>
                    <button type="button" @click="ngn='5000'; fromNgn()" class="px-3 py-2 rounded-lg bg-gray-100 text-gray-800 font-bold text-sm hover:bg-gray-200 transition">₦5,000</button>
                    <button type="button" @click="usd=''; ngn='';" class="px-3 py-2 rounded-lg bg-white border border-gray-200 text-gray-700 font-bold text-sm hover:bg-gray-50 transition">Clear</button>
                </div>
            </div>

            <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100">
                <div class="text-sm font-bold text-gray-800 mb-2">Rate Details</div>
                <div class="text-sm text-gray-600">1 USD = ₦{{ number_format((float) ($liveNgnPerUsd ?? $ngnPerUsd ?? 1500), 2) }}</div>
                <div class="text-sm text-gray-600">₦{{ number_format((float) ($liveNgnPerUsd ?? $ngnPerUsd ?? 1500), 2) }} = 1 USD</div>
                <div class="mt-4 flex flex-col gap-2">
                    <a href="/funding" class="bg-[#facc15] text-black px-4 py-2 rounded-lg font-bold text-center hover:bg-yellow-400 transition">Add Funds</a>
                    <a href="/profile" class="bg-gray-900 text-white px-4 py-2 rounded-lg font-bold text-center hover:bg-gray-800 transition">Update Profile</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Recent Orders Table -->
    <div x-data="{ filter: 'all' }" class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center">
            <div>
                <h3 class="text-lg font-bold text-gray-800">Recent Activity</h3>
                <div class="mt-2 flex flex-wrap gap-2">
                    <button type="button" @click="filter='all'" class="text-xs font-bold px-3 py-1 rounded-full" :class="filter==='all' ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'">All</button>
                    <button type="button" @click="filter='sms'" class="text-xs font-bold px-3 py-1 rounded-full" :class="filter==='sms' ? 'bg-blue-600 text-white' : 'bg-blue-50 text-blue-700 hover:bg-blue-100'">SMS</button>
                    <button type="button" @click="filter='logs'" class="text-xs font-bold px-3 py-1 rounded-full" :class="filter==='logs' ? 'bg-yellow-600 text-white' : 'bg-yellow-50 text-yellow-700 hover:bg-yellow-100'">LOGS</button>
                    <button type="button" @click="filter='boost'" class="text-xs font-bold px-3 py-1 rounded-full" :class="filter==='boost' ? 'bg-purple-600 text-white' : 'bg-purple-50 text-purple-700 hover:bg-purple-100'">BOOST</button>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="/profile" class="text-sm font-medium text-white bg-gray-900 px-4 py-2 rounded-lg hover:bg-gray-800 transition">Profile</a>
                <a href="/sms-verify" class="text-sm font-medium text-[#0d9488] hover:underline">View SMS</a>
            </div>
        </div>
        @if(isset($recentActivities) && $recentActivities->count() > 0)
            <div class="divide-y divide-gray-100">
                @foreach($recentActivities as $activity)
                    <div x-show="filter === 'all' || filter === @js($activity['type'])" class="p-5 sm:p-6 flex items-start justify-between gap-4" style="display:none;">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center {{ $activity['type'] === 'sms' ? 'bg-blue-50 text-blue-600' : ($activity['type'] === 'boost' ? 'bg-purple-50 text-purple-600' : 'bg-yellow-50 text-yellow-700') }}">
                                @if($activity['type'] === 'sms')
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                                @elseif($activity['type'] === 'boost')
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                @else
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18"></path></svg>
                                @endif
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <div class="font-bold text-gray-900">{{ $activity['title'] }}</div>
                                    <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $activity['type'] === 'sms' ? 'bg-blue-50 text-blue-700' : ($activity['type'] === 'boost' ? 'bg-purple-50 text-purple-700' : 'bg-yellow-50 text-yellow-700') }}">
                                        {{ strtoupper($activity['type']) }}
                                    </span>
                                </div>
                                <div class="text-sm text-gray-500 mt-1">
                                    @if(($activity['type'] ?? '') === 'sms')
                                        {{ $activity['meta'] ?? '' }}
                                    @elseif(isset($activity['meta_items'], $activity['meta_amount_usd']))
                                        {{ $activity['meta_items'] }} • {{ $currencySymbol ?? '$' }}{{ ($formatAmount ?? fn($x) => number_format($x, 2))($activity['meta_amount_usd']) }}
                                    @else
                                        —
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs font-bold text-gray-600">{{ \Illuminate\Support\Carbon::parse($activity['created_at'])->format('M d, Y H:i') }}</div>
                            <div class="mt-1 text-xs font-bold px-2 py-1 rounded-lg inline-block bg-gray-50 text-gray-700">
                                {{ $activity['status'] }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-6 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 mb-4 text-gray-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                </div>
                <h4 class="text-gray-900 font-medium">No recent orders found</h4>
                <p class="text-gray-500 text-sm mt-1 mb-4">You haven't made any purchases yet.</p>
                <a href="/sms-verify" class="inline-block bg-gray-900 text-white px-5 py-2 rounded-lg font-medium text-sm hover:bg-gray-800 transition">Get a Number</a>
            </div>
        @endif
    </div>

    <!-- Quick Shortcuts -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100">
            <h3 class="text-lg font-bold text-gray-800">Top Services</h3>
        </div>
        <div class="p-4">
            <a href="/social-boosting" class="flex items-center p-3 hover:bg-gray-50 rounded-xl transition group">
                <div class="w-10 h-10 rounded-lg bg-pink-50 flex items-center justify-center text-pink-500 mr-4 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                </div>
                <div>
                    <h4 class="text-gray-900 font-medium">Instagram Followers</h4>
                    <p class="text-xs text-gray-500">From $1.20/1k</p>
                </div>
            </a>
            
            <a href="/logs-store" class="flex items-center p-3 hover:bg-gray-50 rounded-xl transition group mt-2">
                <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center text-blue-500 mr-4 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                </div>
                <div>
                    <h4 class="text-gray-900 font-medium">Facebook Aged Accounts</h4>
                    <p class="text-xs text-gray-500">Marketplace active logs</p>
                </div>
            </a>

            <a href="/sms-verify" class="flex items-center p-3 hover:bg-gray-50 rounded-xl transition group mt-2">
                <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center text-green-500 mr-4 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path></svg>
                </div>
                <div>
                    <h4 class="text-gray-900 font-medium">WhatsApp Verification</h4>
                    <p class="text-xs text-gray-500">Global numbers available</p>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection
