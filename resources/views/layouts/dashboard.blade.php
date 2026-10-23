@extends('layouts.app')

@section('content')
<div x-data="{ sidebarOpen: false }" class="flex h-screen bg-[#f8fafc] font-sans antialiased overflow-hidden">
    
    <!-- Mobile sidebar backdrop -->
    <div x-show="sidebarOpen" 
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-40 bg-slate-950/60 backdrop-blur-xs lg:hidden" 
         @click="sidebarOpen = false" 
         style="display: none;"></div>

    <!-- OPay Style Left Sidebar (Desktop Navigation & Mobile Slide-Out Drawer) -->
    <aside :class="sidebarOpen ? 'translate-x-0 shadow-2xl' : '-translate-x-full lg:translate-x-0'" 
           class="fixed inset-y-0 left-0 z-50 w-72 bg-white border-r border-slate-200/80 flex flex-col transition-transform duration-300 ease-in-out lg:static lg:inset-auto shrink-0">
        
        <!-- Brand Header Section -->
        <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-white">
            <a href="/" class="flex items-center gap-3 group">
                <div class="h-11 w-11 rounded-2xl bg-gradient-to-tr from-[#0d9488] to-teal-500 p-0.5 shadow-md shadow-teal-500/10 group-hover:scale-105 transition-transform shrink-0">
                    <div class="w-full h-full bg-white rounded-[14px] flex items-center justify-center p-1.5">
                        <img src="{{ asset('logo/favicon-192.png') }}" alt="{{ $siteName ?? 'Logo' }}" class="w-full h-full object-contain">
                    </div>
                </div>
                <div class="leading-tight">
                    <div class="text-lg font-black text-slate-900 tracking-tight flex items-center gap-1">
                        {{ $siteName ?? 'May22SMS' }}<span class="text-[#0d9488] font-black">.</span>
                    </div>
                    <div class="text-[11px] font-extrabold text-[#0d9488] uppercase tracking-wider flex items-center gap-1 mt-0.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Automated Hub
                    </div>
                </div>
            </a>
            <button @click="sidebarOpen = false" class="text-slate-400 hover:text-slate-700 p-2 rounded-xl hover:bg-slate-100 focus:outline-none lg:hidden cursor-pointer" aria-label="Close sidebar">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Quick User Wallet Pill in Sidebar -->
        <div class="p-4 pb-2">
            <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-teal-950 rounded-2xl p-4 text-white shadow-sm border border-slate-700/40 relative overflow-hidden">
                <div class="relative z-10">
                    <div class="flex items-center justify-between text-xs text-slate-300 mb-1">
                        <span class="font-bold">Wallet Balance</span>
                        <span class="px-2 py-0.5 rounded-full bg-white/10 text-[10px] font-black uppercase text-teal-300">Active</span>
                    </div>
                    <div class="text-2xl font-black text-white tracking-tight flex items-baseline gap-1">
                        <span>{{ $currencySymbol ?? '$' }}{{ number_format((auth()->user()->walletBalance ?? 0) * ($currencyFx ?? 1), 2) }}</span>
                    </div>
                    <div class="mt-3 flex items-center gap-2">
                        <a href="/funding" class="flex-1 py-1.5 px-3 rounded-xl bg-gradient-to-r from-[#0d9488] to-teal-500 text-white text-xs font-black text-center shadow-xs hover:brightness-110 transition">
                            + Add Money
                        </a>
                        <a href="/wallet" class="py-1.5 px-3 rounded-xl bg-white/10 text-slate-200 text-xs font-bold text-center hover:bg-white/20 transition">
                            Ledger
                        </a>
                    </div>
                </div>
                <!-- Ambient watermarked logo -->
                <div class="absolute right-0 bottom-0 translate-x-3 translate-y-3 opacity-10 pointer-events-none">
                    <svg class="w-24 h-24 text-white" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L2 22h20L12 2z"/></svg>
                </div>
            </div>
        </div>
        
        <!-- Navigation Scroll Area -->
        <div class="flex-1 overflow-y-auto px-4 py-2 space-y-6 scrollbar-thin">
            <!-- Main Services Section -->
            <div>
                <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest px-3 mb-2">Core Services</p>
                <nav class="space-y-1">
                    <a href="/dashboard" class="flex items-center gap-3 py-2.5 px-3.5 rounded-xl font-bold text-sm transition-all {{ request()->is('dashboard') ? 'bg-[#0d9488] text-white shadow-md shadow-teal-600/20' : 'text-slate-700 hover:bg-teal-50/80 hover:text-[#0d9488]' }}">
                        <span class="text-lg">🏠</span>
                        <span>Dashboard</span>
                    </a>

                    <a href="/dashboard#daily-gift" class="flex items-center justify-between py-2.5 px-3.5 rounded-xl font-bold text-sm transition-all text-amber-900 bg-gradient-to-r from-amber-50 to-orange-50 hover:from-amber-100 hover:to-orange-100 border border-amber-200/80 shadow-xs">
                        <div class="flex items-center gap-3">
                            <span class="text-lg">🎁</span>
                            <span>Daily Gift</span>
                        </div>
                        <span class="text-[10px] font-black uppercase tracking-wider bg-gradient-to-r from-amber-500 to-orange-500 text-white px-2 py-0.5 rounded-full shadow-xs animate-pulse">Claim</span>
                    </a>
                    
                    <a href="/sms-verify" class="flex items-center gap-3 py-2.5 px-3.5 rounded-xl font-bold text-sm transition-all {{ request()->is('sms-verify') ? 'bg-[#0d9488] text-white shadow-md shadow-teal-600/20' : 'text-slate-700 hover:bg-teal-50/80 hover:text-[#0d9488]' }}">
                        <span class="text-lg">📱</span>
                        <span>SMS Verification</span>
                    </a>
                    
                    <a href="/logs-store" class="flex items-center gap-3 py-2.5 px-3.5 rounded-xl font-bold text-sm transition-all {{ request()->is('logs-store*') ? 'bg-[#0d9488] text-white shadow-md shadow-teal-600/20' : 'text-slate-700 hover:bg-teal-50/80 hover:text-[#0d9488]' }}">
                        <span class="text-lg">📄</span>
                        <span>Digital Logs Store</span>
                    </a>
                    
                    <a href="/social-boosting" class="flex items-center gap-3 py-2.5 px-3.5 rounded-xl font-bold text-sm transition-all {{ request()->is('social-boosting') ? 'bg-[#0d9488] text-white shadow-md shadow-teal-600/20' : 'text-slate-700 hover:bg-teal-50/80 hover:text-[#0d9488]' }}">
                        <span class="text-lg">🚀</span>
                        <span>Social Boosting</span>
                    </a>

                    <a href="/sporty-bet-codes" class="flex items-center gap-3 py-2.5 px-3.5 rounded-xl font-bold text-sm transition-all {{ request()->is('sporty-bet-codes') ? 'bg-[#0d9488] text-white shadow-md shadow-teal-600/20' : 'text-slate-700 hover:bg-teal-50/80 hover:text-[#0d9488]' }}">
                        <span class="text-lg">⚽</span>
                        <span>SportyBet Codes</span>
                    </a>

                    <a href="/football-matches" class="flex items-center gap-3 py-2.5 px-3.5 rounded-xl font-bold text-sm transition-all {{ request()->is('football-matches') ? 'bg-[#0d9488] text-white shadow-md shadow-teal-600/20' : 'text-slate-700 hover:bg-teal-50/80 hover:text-[#0d9488]' }}">
                        <span class="text-lg">🏆</span>
                        <span>Football Matches</span>
                    </a>

                    <!-- Upcoming Services (Coming Soon) -->
                    <div class="pt-2 mt-2 border-t border-slate-100">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-3 mb-1">Coming Soon</p>
                        <a href="/services/crypto-to-naira" class="flex items-center justify-between py-2 px-3.5 rounded-xl font-bold text-xs transition-all {{ request()->is('services/crypto-to-naira') ? 'bg-[#0d9488] text-white shadow-xs' : 'text-slate-600 hover:bg-teal-50 hover:text-[#0d9488]' }}">
                            <div class="flex items-center gap-2.5">
                                <span class="text-base">🪙</span>
                                <span>Receive Crypto</span>
                            </div>
                            <span class="text-[9px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 px-1.5 py-0.2 rounded-md">NEW</span>
                        </a>
                        <a href="/services/airtime-data" class="flex items-center justify-between py-2 px-3.5 rounded-xl font-bold text-xs transition-all {{ request()->is('services/airtime-data') ? 'bg-[#0d9488] text-white shadow-xs' : 'text-slate-600 hover:bg-teal-50 hover:text-[#0d9488]' }}">
                            <div class="flex items-center gap-2.5">
                                <span class="text-base">📶</span>
                                <span>Airtime &amp; Data</span>
                            </div>
                            <span class="text-[9px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 px-1.5 py-0.2 rounded-md">NEW</span>
                        </a>
                        <a href="/services/electricity" class="flex items-center justify-between py-2 px-3.5 rounded-xl font-bold text-xs transition-all {{ request()->is('services/electricity') ? 'bg-[#0d9488] text-white shadow-xs' : 'text-slate-600 hover:bg-teal-50 hover:text-[#0d9488]' }}">
                            <div class="flex items-center gap-2.5">
                                <span class="text-base">⚡</span>
                                <span>Electricity</span>
                            </div>
                            <span class="text-[9px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 px-1.5 py-0.2 rounded-md">NEW</span>
                        </a>
                        <a href="/services/betting" class="flex items-center justify-between py-2 px-3.5 rounded-xl font-bold text-xs transition-all {{ request()->is('services/betting') ? 'bg-[#0d9488] text-white shadow-xs' : 'text-slate-600 hover:bg-teal-50 hover:text-[#0d9488]' }}">
                            <div class="flex items-center gap-2.5">
                                <span class="text-base">🎯</span>
                                <span>Betting</span>
                            </div>
                            <span class="text-[9px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 px-1.5 py-0.2 rounded-md">NEW</span>
                        </a>
                    </div>
                </nav>
            </div>

            <!-- Finance & Orders Section -->
            <div>
                <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest px-3 mb-2">Finance &amp; Activity</p>
                <nav class="space-y-1">
                    <a href="/funding" class="flex items-center gap-3 py-2.5 px-3.5 rounded-xl font-bold text-sm transition-all {{ request()->is('funding') ? 'bg-[#0d9488] text-white shadow-md' : 'text-slate-700 hover:bg-teal-50 hover:text-[#0d9488]' }}">
                        <span class="text-lg">💳</span>
                        <span>Fund Wallet</span>
                    </a>
                    <a href="/wallet" class="flex items-center gap-3 py-2.5 px-3.5 rounded-xl font-bold text-sm transition-all {{ request()->is('wallet') ? 'bg-[#0d9488] text-white shadow-md' : 'text-slate-700 hover:bg-teal-50 hover:text-[#0d9488]' }}">
                        <span class="text-lg">📋</span>
                        <span>Wallet Ledger</span>
                    </a>
                    <a href="/savings" class="flex items-center justify-between py-2.5 px-3.5 rounded-xl font-bold text-sm transition-all {{ request()->is('savings*') ? 'bg-[#0d9488] text-white shadow-md shadow-teal-600/20' : 'text-slate-700 hover:bg-teal-50 hover:text-[#0d9488]' }}">
                        <div class="flex items-center gap-3">
                            <span class="text-lg">💰</span>
                            <span>Save Small Small</span>
                        </div>
                        <span class="text-[9px] font-black uppercase tracking-wider bg-amber-100 text-amber-900 px-1.5 py-0.2 rounded-md">16.9%</span>
                    </a>
                    <a href="/orders" class="flex items-center gap-3 py-2.5 px-3.5 rounded-xl font-bold text-sm transition-all {{ request()->is('orders') ? 'bg-[#0d9488] text-white shadow-md' : 'text-slate-700 hover:bg-teal-50 hover:text-[#0d9488]' }}">
                        <span class="text-lg">📦</span>
                        <span>My Orders</span>
                    </a>
                    <a href="/logs-store/cart" class="flex items-center gap-3 py-2.5 px-3.5 rounded-xl font-bold text-sm transition-all {{ request()->is('logs-store/cart') ? 'bg-[#0d9488] text-white shadow-md' : 'text-slate-700 hover:bg-teal-50 hover:text-[#0d9488]' }}">
                        <span class="text-lg">🛒</span>
                        <span>Logs Cart</span>
                    </a>
                </nav>
            </div>

            <!-- Profile & Help Section -->
            <div>
                <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest px-3 mb-2">Account &amp; Help</p>
                <nav class="space-y-1">
                    <a href="/tutorials" class="flex items-center justify-between py-2.5 px-3.5 rounded-xl font-bold text-sm transition-all {{ request()->is('tutorials*') || request()->is('how-it-works*') ? 'bg-[#0d9488] text-white shadow-md shadow-teal-600/20' : 'text-slate-700 hover:bg-teal-50 hover:text-[#0d9488]' }}">
                        <div class="flex items-center gap-3">
                            <span class="text-lg">🎓</span>
                            <span>Tutorials &amp; Guides</span>
                        </div>
                        <span class="text-[9px] font-black uppercase tracking-wider bg-teal-100 text-teal-800 px-1.5 py-0.2 rounded-md">GUIDE</span>
                    </a>
                    <a href="/profile" class="flex items-center gap-3 py-2.5 px-3.5 rounded-xl font-bold text-sm transition-all {{ request()->is('profile') ? 'bg-[#0d9488] text-white shadow-md' : 'text-slate-700 hover:bg-teal-50 hover:text-[#0d9488]' }}">
                        <span class="text-lg">👤</span>
                        <span>My Profile</span>
                    </a>
                    <a href="/security" class="flex items-center gap-3 py-2.5 px-3.5 rounded-xl font-bold text-sm transition-all {{ request()->is('security') ? 'bg-[#0d9488] text-white shadow-md' : 'text-slate-700 hover:bg-teal-50 hover:text-[#0d9488]' }}">
                        <span class="text-lg">🔒</span>
                        <span>Security &amp; 2FA</span>
                    </a>
                    <a href="/tickets" class="flex items-center gap-3 py-2.5 px-3.5 rounded-xl font-bold text-sm transition-all {{ request()->is('tickets*') ? 'bg-[#0d9488] text-white shadow-md' : 'text-slate-700 hover:bg-teal-50 hover:text-[#0d9488]' }}">
                        <span class="text-lg">🎧</span>
                        <span>Support Desk</span>
                    </a>
                    <a href="/notifications" class="flex items-center gap-3 py-2.5 px-3.5 rounded-xl font-bold text-sm transition-all {{ request()->is('notifications') ? 'bg-[#0d9488] text-white shadow-md' : 'text-slate-700 hover:bg-teal-50 hover:text-[#0d9488]' }}">
                        <span class="text-lg">🔔</span>
                        <span>Notifications</span>
                    </a>
                </nav>
            </div>
        </div>

        <!-- Sidebar User Footer -->
        <div class="p-4 border-t border-slate-100 bg-slate-50/70">
            <div class="flex items-center justify-between p-2 rounded-xl bg-white border border-slate-200/70 shadow-xs">
                <div class="flex items-center gap-2.5 overflow-hidden">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-[#0d9488] to-teal-500 text-white font-black text-xs flex items-center justify-center shrink-0 uppercase shadow-xs">
                        {{ substr(auth()->user()->name ?? 'U', 0, 2) }}
                    </div>
                    <div class="overflow-hidden">
                        <div class="text-xs font-black text-slate-800 truncate">{{ auth()->user()->name ?? 'User' }}</div>
                        <div class="text-[10px] font-bold text-slate-400 truncate">ID: #{{ auth()->user()->id ?? 0 }}</div>
                    </div>
                </div>
                <a href="/logout" class="p-2 text-slate-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition" title="Log out">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                </a>
            </div>
        </div>
    </aside>

    <!-- Main Content Canvas -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden relative">
        
        <!-- Impersonation Alert Banner -->
        @if(session('impersonator_id'))
            <div class="bg-amber-500 text-black px-4 py-2 text-xs font-black flex items-center justify-between z-50 shadow-md shrink-0">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-black shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>Impersonating <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->email }})</span>
                </div>
                <form action="/admin/users/stop-impersonating" method="POST">
                    @csrf
                    <button type="submit" class="bg-slate-900 text-white px-3 py-1 rounded-lg text-[11px] font-bold hover:bg-slate-800">
                        Exit Impersonation
                    </button>
                </form>
            </div>
        @endif

        <!-- OPay Mobile-App Styled Top Navigation Bar -->
        <header class="bg-white/95 backdrop-blur-md border-b border-slate-200/80 px-4 sm:px-8 py-3 sticky top-0 z-30 shrink-0">
            <div class="flex items-center justify-between gap-3">
                
                <!-- Left: Mobile Menu Toggle & User Greeting -->
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = true" class="p-2 -ml-1 text-slate-600 hover:text-slate-900 rounded-xl hover:bg-slate-100 lg:hidden cursor-pointer" aria-label="Open sidebar menu">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>

                    <!-- Mobile App Profile Pill -->
                    <div class="flex items-center gap-2.5">
                        <a href="/profile" class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-gradient-to-tr from-[#0d9488] to-teal-500 text-white font-black text-xs sm:text-sm flex items-center justify-center shrink-0 uppercase shadow-xs border-2 border-white">
                            {{ substr(auth()->user()->name ?? 'U', 0, 2) }}
                        </a>
                        <div>
                            <div class="text-xs sm:text-sm font-black text-slate-900 flex items-center gap-1.5 leading-tight">
                                <span>Hi, {{ explode(' ', auth()->user()->name ?? 'User')[0] }}</span>
                                <span class="hidden sm:inline-block">👋</span>
                            </div>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded-md bg-emerald-50 text-[10px] font-extrabold text-emerald-700 border border-emerald-200/60">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Verified
                                </span>
                                <span class="text-[10px] font-bold text-slate-400 hidden sm:inline">#{{ auth()->user()->id ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Quick Currency Switch, Support, Notifications, Balance Badge -->
                <div class="flex items-center gap-2 sm:gap-3">
                    
                    <!-- Live Exchange Rate Indicator -->
                    @if(!is_null($liveNgnPerUsd ?? null))
                        <div class="hidden md:flex items-center bg-slate-100/80 border border-slate-200/80 text-slate-700 font-bold text-xs rounded-full px-3 py-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                            <span>1 USD = ₦{{ number_format((float) $liveNgnPerUsd, 2) }}</span>
                        </div>
                    @endif

                    <!-- Currency Switcher Pill (USD ⇄ NGN) -->
                    <form action="{{ route('currency.set') }}" method="POST" class="m-0">
                        @csrf
                        <div class="relative inline-flex items-center bg-slate-100 hover:bg-slate-200/80 rounded-full p-0.5 border border-slate-200 transition">
                            <button type="submit" name="currency" value="USD" class="px-2.5 py-1 rounded-full text-xs font-black transition {{ ($currency ?? 'USD') === 'USD' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-800' }}">
                                USD
                            </button>
                            <button type="submit" name="currency" value="NGN" class="px-2.5 py-1 rounded-full text-xs font-black transition {{ ($currency ?? 'USD') === 'NGN' ? 'bg-[#0d9488] text-white shadow-xs' : 'text-slate-500 hover:text-slate-800' }}">
                                NGN
                            </button>
                        </div>
                    </form>

                    <!-- Tutorials & Guides Quick Link -->
                    <a href="/tutorials" class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-slate-100 hover:bg-teal-50 hover:text-[#0d9488] border border-slate-200/80 text-slate-700 flex items-center justify-center transition" title="Site Tutorials &amp; How-To Guides" aria-label="Tutorials">
                        <span class="text-base sm:text-lg">🎓</span>
                    </a>

                    <!-- Support Quick Headset Link -->
                    <a href="/tickets" class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-slate-100 hover:bg-teal-50 hover:text-[#0d9488] border border-slate-200/80 text-slate-700 flex items-center justify-center transition" title="24/7 Support Desk" aria-label="Support">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </a>

                    <!-- Notifications Bell with unread counter badge -->
                    @php
                        $unreadCount = auth()->user()->unreadNotifications()->count();
                        $unreadText = $unreadCount > 99 ? '99+' : (string) $unreadCount;
                    @endphp
                    <a href="/notifications" class="relative w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-slate-100 hover:bg-slate-200 border border-slate-200/80 text-slate-700 flex items-center justify-center transition" aria-label="Notifications">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                            <path d="M13.73 21a2 2 0 01-3.46 0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                        @if($unreadCount > 0)
                            <span class="absolute -top-1 -right-1 bg-rose-500 text-white text-[10px] font-black px-1.5 py-0.2 rounded-full leading-tight border-2 border-white shadow-xs">
                                {{ $unreadText }}
                            </span>
                        @endif
                    </a>

                    <!-- Top Header Quick Add Money (Desktop only) -->
                    <a href="/funding" class="hidden sm:inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-[#0d9488] hover:bg-teal-700 text-white font-black text-xs shadow-xs hover:shadow-md transition">
                        <span>+ Fund</span>
                    </a>
                </div>
            </div>
        </header>

        <!-- Main Scrollable Application Content -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto px-4 py-5 sm:px-8 sm:py-7 pb-24 lg:pb-8">
            <div class="max-w-7xl mx-auto space-y-6">
                
                <!-- System Announcement Banner (If active) -->
                @if(($announcementEnabled ?? false) && !empty($announcementText ?? ''))
                    <div class="rounded-2xl border border-amber-200 bg-gradient-to-r from-amber-50 to-orange-50 px-4 py-3 text-amber-900 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 shadow-xs">
                        <div class="flex items-center gap-2.5">
                            <span class="text-xl">📢</span>
                            <span class="text-xs sm:text-sm font-black">{{ $announcementText }}</span>
                        </div>
                        @if(!empty($announcementLink ?? ''))
                            <a href="{{ $announcementLink }}" class="inline-flex items-center justify-center rounded-xl bg-amber-400 hover:bg-amber-500 px-3.5 py-1.5 text-xs font-black text-amber-950 transition shrink-0">
                                View Details &rarr;
                            </a>
                        @endif
                    </div>
                @endif

                @yield('dashboard_content')
            </div>
        </main>

        <!-- OPay Mobile App Signature Bottom Navigation Bar (Fixed on Mobile Screens) -->
        <nav class="lg:hidden fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-200/90 px-2 py-1.5 flex items-center justify-around shadow-lg">
            
            <!-- Tab 1: Home / Dashboard -->
            <a href="/dashboard" class="flex flex-col items-center justify-center py-1 px-3 rounded-xl transition {{ request()->is('dashboard') ? 'text-[#0d9488]' : 'text-slate-500 hover:text-slate-800' }}">
                <span class="text-xl leading-none">🏠</span>
                <span class="text-[10px] font-black mt-1 {{ request()->is('dashboard') ? 'text-[#0d9488]' : 'text-slate-600' }}">Home</span>
            </a>

            <!-- Tab 2: SMS Verification -->
            <a href="/sms-verify" class="flex flex-col items-center justify-center py-1 px-3 rounded-xl transition {{ request()->is('sms-verify') ? 'text-[#0d9488]' : 'text-slate-500 hover:text-slate-800' }}">
                <span class="text-xl leading-none">📱</span>
                <span class="text-[10px] font-black mt-1 {{ request()->is('sms-verify') ? 'text-[#0d9488]' : 'text-slate-600' }}">SMS</span>
            </a>

            <!-- Tab 3: Center Floating "Fund / Wallet" Action -->
            <a href="/funding" class="flex flex-col items-center justify-center -mt-5 group">
                <div class="w-12 h-12 rounded-full bg-gradient-to-tr from-[#0d9488] via-teal-500 to-amber-400 p-0.5 shadow-lg shadow-teal-500/30 group-active:scale-95 transition-transform flex items-center justify-center">
                    <div class="w-full h-full rounded-full bg-[#0d9488] flex items-center justify-center text-white text-xl">
                        💳
                    </div>
                </div>
                <span class="text-[10px] font-black text-[#0d9488] mt-0.5">Add Funds</span>
            </a>

            <!-- Tab 4: Orders History -->
            <a href="/orders" class="flex flex-col items-center justify-center py-1 px-3 rounded-xl transition {{ request()->is('orders') ? 'text-[#0d9488]' : 'text-slate-500 hover:text-slate-800' }}">
                <span class="text-xl leading-none">📦</span>
                <span class="text-[10px] font-black mt-1 {{ request()->is('orders') ? 'text-[#0d9488]' : 'text-slate-600' }}">Orders</span>
            </a>

            <!-- Tab 5: Profile / Me -->
            <a href="/profile" class="flex flex-col items-center justify-center py-1 px-3 rounded-xl transition {{ request()->is('profile') ? 'text-[#0d9488]' : 'text-slate-500 hover:text-slate-800' }}">
                <span class="text-xl leading-none">👤</span>
                <span class="text-[10px] font-black mt-1 {{ request()->is('profile') ? 'text-[#0d9488]' : 'text-slate-600' }}">Me</span>
            </a>
        </nav>
        
    </div>
</div>
@endsection
