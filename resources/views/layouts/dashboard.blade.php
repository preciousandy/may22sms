@extends('layouts.app')

@section('content')
<div x-data="{ sidebarOpen: false }" class="flex h-screen bg-gray-50 font-sans">
    
    <!-- Mobile sidebar backdrop -->
    <div x-show="sidebarOpen" class="fixed inset-0 z-20 bg-black bg-opacity-50 lg:hidden" @click="sidebarOpen = false" style="display: none;"></div>

    <!-- Sidebar -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-30 w-64 bg-white shadow-lg flex min-h-0 flex-col transform transition-transform duration-300 lg:translate-x-0 lg:static lg:inset-auto">
        <div class="flex-1 overflow-y-auto">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <a href="/" class="flex items-center gap-3">
                    <div class="h-11 w-11 rounded-2xl bg-white border border-gray-100 shadow-sm overflow-hidden flex items-center justify-center">
                        <img src="{{ asset('logo/favicon-512.png') }}" alt="{{ $siteName ?? 'Logo' }}" class="h-8 w-8 object-contain">
                    </div>
                    <div class="leading-tight">
                        <div class="text-lg font-extrabold text-gray-900 tracking-tight">{{ $siteName ?? 'May22SMS' }}</div>
                        <div class="text-xs font-bold text-gray-500">User dashboard</div>
                    </div>
                </a>
                <button @click="sidebarOpen = false" class="text-gray-500 focus:outline-none lg:hidden">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <div class="p-4">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4 ml-3">Menu</p>
                <nav class="space-y-2">
                    <a href="/dashboard" class="flex items-center py-3 px-4 rounded-lg transition duration-200 font-medium {{ request()->is('dashboard') ? 'bg-[#0d9488] text-white shadow-md' : 'text-gray-600 hover:bg-teal-50 hover:text-[#0d9488]' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                        Dashboard Overview
                    </a>
                    
                    <a href="/sms-verify" class="flex items-center py-3 px-4 rounded-lg transition duration-200 font-medium {{ request()->is('sms-verify') ? 'bg-[#0d9488] text-white shadow-md' : 'text-gray-600 hover:bg-teal-50 hover:text-[#0d9488]' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                        SMS Verification
                    </a>
                    
                    <a href="/logs-store" class="flex items-center px-4 py-3 text-gray-700 hover:bg-teal-50 hover:text-[#0d9488] rounded-xl transition font-medium">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        Social Media Logs
                    </a>
                    
                    <a href="/social-boosting" class="flex items-center py-3 px-4 rounded-lg transition duration-200 font-medium {{ request()->is('social-boosting') ? 'bg-[#0d9488] text-white shadow-md' : 'text-gray-600 hover:bg-teal-50 hover:text-[#0d9488]' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                        Social Boosting
                    </a>

                    <a href="/football-matches" class="flex items-center py-3 px-4 rounded-lg transition duration-200 font-medium {{ request()->is('football-matches') ? 'bg-[#0d9488] text-white shadow-md' : 'text-gray-600 hover:bg-teal-50 hover:text-[#0d9488]' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" stroke-width="2"></circle>
                            <path d="M12 6v6l4 2" stroke-width="2" stroke-linecap="round"></path>
                        </svg>
                        Football Matches
                    </a>

                    <a href="/sporty-bet-codes" class="flex items-center py-3 px-4 rounded-lg transition duration-200 font-medium {{ request()->is('sporty-bet-codes') ? 'bg-[#0d9488] text-white shadow-md' : 'text-gray-600 hover:bg-teal-50 hover:text-[#0d9488]' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                        SportyBet Codes
                    </a>

                    <a href="/orders" class="flex items-center py-3 px-4 rounded-lg transition duration-200 font-medium {{ request()->is('orders') ? 'bg-[#0d9488] text-white shadow-md' : 'text-gray-600 hover:bg-teal-50 hover:text-[#0d9488]' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                        Orders
                    </a>

                    <a href="/profile" class="flex items-center py-3 px-4 rounded-lg transition duration-200 font-medium {{ request()->is('profile') ? 'bg-[#0d9488] text-white shadow-md' : 'text-gray-600 hover:bg-teal-50 hover:text-[#0d9488]' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" stroke-width="2" stroke-linecap="round"></path>
                            <circle cx="12" cy="7" r="4" stroke-width="2"></circle>
                        </svg>
                        Profile
                    </a>

                    <a href="/security" class="flex items-center py-3 px-4 rounded-lg transition duration-200 font-medium {{ request()->is('security') ? 'bg-[#0d9488] text-white shadow-md' : 'text-gray-600 hover:bg-teal-50 hover:text-[#0d9488]' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2" stroke-width="2"></rect>
                            <path d="M16 11V7a4 4 0 00-8 0v4" stroke-width="2" stroke-linecap="round"></path>
                        </svg>
                        Security
                    </a>

                    <a href="/tickets" class="flex items-center py-3 px-4 rounded-lg transition duration-200 font-medium {{ request()->is('tickets*') ? 'bg-[#0d9488] text-white shadow-md' : 'text-gray-600 hover:bg-teal-50 hover:text-[#0d9488]' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                        Support
                    </a>

                    <a href="/notifications" class="flex items-center py-3 px-4 rounded-lg transition duration-200 font-medium {{ request()->is('notifications') ? 'bg-[#0d9488] text-white shadow-md' : 'text-gray-600 hover:bg-teal-50 hover:text-[#0d9488]' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                            <path d="M13.73 21a2 2 0 01-3.46 0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                        Notifications
                    </a>
                </nav>
            </div>

            <div class="p-4">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4 ml-3">Finance</p>
                <nav class="space-y-2">
                    <a href="/funding" class="flex items-center py-3 px-4 rounded-lg transition duration-200 font-medium {{ request()->is('funding') ? 'bg-[#facc15] text-black shadow-md' : 'bg-gray-100 text-gray-800 hover:bg-[#facc15] hover:text-black' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Add Funds
                    </a>
                    <a href="/dashboard#exchange" class="flex items-center py-3 px-4 rounded-lg transition duration-200 font-medium bg-gray-100 text-gray-800 hover:bg-gray-900 hover:text-white">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 10h-1V7a4 4 0 00-8 0v3H7a2 2 0 00-2 2v7a2 2 0 002 2h10a2 2 0 002-2v-7a2 2 0 00-2-2zm-6 0V7a2 2 0 114 0v3h-4z"></path></svg>
                        Exchange Rate
                    </a>
                    <a href="/wallet" class="flex items-center py-3 px-4 rounded-lg transition duration-200 font-medium {{ request()->is('wallet') ? 'bg-gray-900 text-white shadow-md' : 'bg-gray-100 text-gray-800 hover:bg-gray-900 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Wallet Ledger
                    </a>
                    <a href="/logs-store/cart" class="flex items-center py-3 px-4 rounded-lg transition duration-200 font-medium {{ request()->is('logs-store/cart') ? 'bg-gray-900 text-white shadow-md' : 'bg-gray-100 text-gray-800 hover:bg-gray-900 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.5 7H19M7 13l.4-2M10 21a1 1 0 100-2 1 1 0 000 2zm8 0a1 1 0 100-2 1 1 0 000 2z"></path></svg>
                        Logs Cart
                    </a>
                </nav>
            </div>
        </div>

        <div class="flex-shrink-0 p-4 border-t border-gray-100">
            <div class="flex items-center p-3 bg-gray-50 rounded-xl">
                <div class="w-10 h-10 rounded-full bg-[#0d9488] text-white flex items-center justify-center font-bold uppercase">
                    {{ substr(auth()->user()->name ?? 'JD', 0, 2) }}
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-800">{{ auth()->user()->name ?? 'Guest' }}</p>
                    <a href="/logout" class="text-xs text-red-500 hover:underline">Log out</a>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden relative">
        @if(session('impersonator_id'))
            <div class="bg-amber-500 text-black px-4 py-2.5 text-xs font-black flex flex-wrap items-center justify-between gap-2 z-50 shadow-md">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-black shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span><strong>Admin Impersonation Mode:</strong> Logged in as <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->email }})</span>
                </div>
                <form action="/admin/users/stop-impersonating" method="POST">
                    @csrf
                    <button type="submit" class="bg-gray-900 text-white px-3.5 py-1.5 rounded-xl text-xs font-extrabold hover:bg-gray-800 transition shadow-sm">
                        Return to Admin Panel ➔
                    </button>
                </form>
            </div>
        @endif
        <!-- Header -->
        <header class="flex justify-between items-center py-4 px-4 sm:px-8 bg-white/80 backdrop-blur-md border-b border-gray-200 sticky top-0 z-10">
            <div class="flex items-center">
                <button @click="sidebarOpen = true" class="text-gray-500 focus:outline-none lg:hidden mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                <h2 class="text-xl sm:text-2xl font-bold text-gray-800 truncate">
                    @yield('page_title', 'Dashboard')
                </h2>
            </div>
            <div class="flex items-center space-x-3 sm:space-x-6">
                <div class="hidden md:flex items-center bg-gray-100 rounded-full px-4 py-2">
                    <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input type="text" placeholder="Search..." class="bg-transparent border-none focus:ring-0 text-sm w-32 md:w-48 text-gray-700 outline-none">
                </div>

                @php
                    $unreadCount = auth()->user()->unreadNotifications()->count();
                    $unreadText = $unreadCount > 99 ? '99+' : (string) $unreadCount;
                @endphp

                <a href="/notifications" class="relative inline-flex items-center justify-center h-10 w-10 rounded-full bg-gray-100 border border-gray-200 text-gray-700 hover:bg-gray-200 transition" aria-label="Notifications">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                        <path d="M13.73 21a2 2 0 01-3.46 0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                    @if($unreadCount > 0)
                        <span class="absolute -top-1 -right-1 bg-red-600 text-white text-[10px] font-extrabold px-1.5 py-0.5 rounded-full leading-none border-2 border-white">
                            {{ $unreadText }}
                        </span>
                    @endif
                </a>
                
                <form action="{{ route('currency.set') }}" method="POST">
                    @csrf
                    <select name="currency" onchange="this.form.submit()" class="bg-gray-100 border border-gray-200 text-gray-800 font-bold text-sm rounded-full px-3 py-2 focus:ring-0 focus:border-[#0d9488]">
                        <option value="USD" {{ ($currency ?? 'USD') === 'USD' ? 'selected' : '' }}>USD</option>
                        <option value="NGN" {{ ($currency ?? 'USD') === 'NGN' ? 'selected' : '' }}>NGN</option>
                    </select>
                </form>

                @if(!is_null($liveNgnPerUsd ?? null))
                    <div class="hidden md:flex items-center bg-gray-100 border border-gray-200 text-gray-800 font-bold text-sm rounded-full px-4 py-2">
                        <span class="mr-2 text-gray-500">Live</span>
                        <span>1 USD = ₦{{ number_format((float) $liveNgnPerUsd, 2) }}</span>
                    </div>
                @endif

                <div class="flex items-center bg-[#facc15] px-3 sm:px-5 py-2 rounded-full shadow-sm border border-yellow-400">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-yellow-800 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="font-bold text-sm sm:text-base text-gray-900">{{ $currencySymbol ?? '$' }}{{ number_format((auth()->user()->walletBalance ?? 0) * ($currencyFx ?? 1), 2) }}</span>
                </div>
            </div>
        </header>

        <!-- Main section -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 sm:p-8">
            <div class="max-w-7xl mx-auto">
                @if(($announcementEnabled ?? false) && !empty($announcementText ?? ''))
                    <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-amber-900 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div class="font-bold">{{ $announcementText }}</div>
                        @if(!empty($announcementLink ?? ''))
                            <a href="{{ $announcementLink }}" class="inline-flex items-center justify-center rounded-xl bg-amber-200 px-4 py-2 text-sm font-extrabold text-amber-900 hover:bg-amber-300 transition">
                                View
                            </a>
                        @endif
                    </div>
                @endif
                @if(is_array($publicPromos ?? null) && count($publicPromos ?? []) > 0)
                    @php
                        $routeContext = null;
                        if (request()->is('funding')) {
                            $routeContext = 'funding';
                        } elseif (request()->is('sms-verify')) {
                            $routeContext = 'sms';
                        } elseif (request()->is('logs-store*')) {
                            $routeContext = 'logs';
                        } elseif (request()->is('social-boosting')) {
                            $routeContext = 'boost';
                        }

                        $promo = null;
                        foreach ((array) $publicPromos as $p) {
                            $applies = (array) ($p['applies_to'] ?? []);
                            if ($routeContext === null || empty($applies) || in_array($routeContext, $applies, true)) {
                                $promo = (array) $p;
                                break;
                            }
                        }
                        if (!is_array($promo) || empty($promo)) {
                            $promo = (array) ($publicPromos[0] ?? []);
                        }

                        $applies = (array) ($promo['applies_to'] ?? []);
                        $appliesText = empty($applies) ? 'All services' : implode(', ', array_map(fn ($v) => strtoupper((string) $v), $applies));
                        $valueText = (($promo['type'] ?? '') === 'fixed')
                            ? ('$'.number_format((float) ($promo['value'] ?? 0), 2).' off')
                            : (number_format((float) ($promo['value'] ?? 0), 2).'% off');

                        $ctaUrl = '/funding';
                        $ctaLabel = 'Go to Funding';
                        if ($routeContext === 'sms') {
                            $ctaUrl = '/sms-verify';
                            $ctaLabel = 'Go to SMS';
                        } elseif ($routeContext === 'logs') {
                            $ctaUrl = '/logs-store';
                            $ctaLabel = 'Go to Logs';
                        } elseif ($routeContext === 'boost') {
                            $ctaUrl = '/social-boosting';
                            $ctaLabel = 'Go to Boosting';
                        } elseif ($routeContext === null && !empty($applies)) {
                            $first = (string) ($applies[0] ?? '');
                            if ($first === 'sms') {
                                $ctaUrl = '/sms-verify';
                                $ctaLabel = 'Go to SMS';
                            } elseif ($first === 'logs') {
                                $ctaUrl = '/logs-store';
                                $ctaLabel = 'Go to Logs';
                            } elseif ($first === 'boost') {
                                $ctaUrl = '/social-boosting';
                                $ctaLabel = 'Go to Boosting';
                            }
                        }
                    @endphp
                    <div class="mb-6 bg-gradient-to-r from-[#0d9488] to-gray-900 rounded-2xl p-5 sm:p-6 text-white shadow-sm" x-data="{ copied: false, copy(code) { if (navigator.clipboard) { navigator.clipboard.writeText(code); } this.copied = true; setTimeout(() => this.copied = false, 1200); } }">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <div class="text-sm font-extrabold text-white/90">{{ (string) ($promo['title'] ?? 'Promo') }}</div>
                                <div class="mt-1 text-xl sm:text-2xl font-extrabold">Use <span class="bg-white/10 px-2 py-1 rounded-lg font-mono">{{ (string) ($promo['code'] ?? '') }}</span> · {{ $valueText }}</div>
                                <div class="mt-1 text-sm text-white/80">{{ $appliesText }}</div>
                                @if(!empty($promo['message']))
                                    <div class="mt-2 text-sm text-white/80">{{ (string) $promo['message'] }}</div>
                                @endif
                            </div>
                            <div class="flex flex-col sm:items-end gap-2">
                                <button type="button" @click="copy('{{ (string) ($promo['code'] ?? '') }}')" class="bg-[#facc15] text-black px-4 py-3 rounded-xl font-extrabold hover:bg-yellow-400 transition">
                                    <span x-show="!copied">Copy Code</span>
                                    <span x-show="copied" style="display:none;">Copied</span>
                                </button>
                                <a href="{{ $ctaUrl }}" class="text-sm font-bold text-white/90 hover:text-white underline">{{ $ctaLabel }}</a>
                            </div>
                        </div>
                    </div>
                @endif
                @yield('dashboard_content')
            </div>
        </main>
    </div>
</div>
@endsection
