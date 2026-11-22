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
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        <span>Dashboard</span>
                    </a>

                    <a href="/dashboard#daily-gift" class="flex items-center justify-between py-2.5 px-3.5 rounded-xl font-bold text-sm transition-all text-amber-900 bg-gradient-to-r from-amber-50 to-orange-50 hover:from-amber-100 hover:to-orange-100 border border-amber-200/80 shadow-xs">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 shrink-0 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V4a2 2 0 112 2h-2zm0 0V4a2 2 0 10-2 2h2zm-8 4h16a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1v-2a1 1 0 011-1zm2 4v6a1 1 0 001 1h10a1 1 0 001-1v-6"/></svg>
                            <span>Daily Gift</span>
                        </div>
                        <span class="text-[10px] font-black uppercase tracking-wider bg-gradient-to-r from-amber-500 to-orange-500 text-white px-2 py-0.5 rounded-full shadow-xs animate-pulse">Claim</span>
                    </a>
                    
                    <a href="/sms-verify" class="flex items-center gap-3 py-2.5 px-3.5 rounded-xl font-bold text-sm transition-all {{ request()->is('sms-verify') ? 'bg-[#0d9488] text-white shadow-md shadow-teal-600/20' : 'text-slate-700 hover:bg-teal-50/80 hover:text-[#0d9488]' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        <span>SMS Verification</span>
                    </a>
                    
                    <a href="/logs-store" class="flex items-center gap-3 py-2.5 px-3.5 rounded-xl font-bold text-sm transition-all {{ request()->is('logs-store*') ? 'bg-[#0d9488] text-white shadow-md shadow-teal-600/20' : 'text-slate-700 hover:bg-teal-50/80 hover:text-[#0d9488]' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Digital Logs Store</span>
                    </a>
                    
                    <a href="/social-boosting" class="flex items-center gap-3 py-2.5 px-3.5 rounded-xl font-bold text-sm transition-all {{ request()->is('social-boosting') ? 'bg-[#0d9488] text-white shadow-md shadow-teal-600/20' : 'text-slate-700 hover:bg-teal-50/80 hover:text-[#0d9488]' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.63 8.41m5.96 5.96a14.926 14.926 0 01-5.841 2.58m-.119-8.54a6 6 0 00-7.381 5.84h4.8m2.58-5.84a14.927 14.927 0 00-2.58 5.84m2.699 2.7a1.5 1.5 0 11-2.121-2.121 1.5 1.5 0 012.121 2.121z"/></svg>
                        <span>Social Boosting</span>
                    </a>

                    <a href="/sporty-bet-codes" class="flex items-center gap-3 py-2.5 px-3.5 rounded-xl font-bold text-sm transition-all {{ request()->is('sporty-bet-codes') ? 'bg-[#0d9488] text-white shadow-md shadow-teal-600/20' : 'text-slate-700 hover:bg-teal-50/80 hover:text-[#0d9488]' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3a9 9 0 016.36 15.36M12 3a9 9 0 00-6.36 15.36M12 3v18M3 12h18"/></svg>
                        <span>SportyBet Codes</span>
                    </a>

                    <a href="/football-matches" class="flex items-center gap-3 py-2.5 px-3.5 rounded-xl font-bold text-sm transition-all {{ request()->is('football-matches') ? 'bg-[#0d9488] text-white shadow-md shadow-teal-600/20' : 'text-slate-700 hover:bg-teal-50/80 hover:text-[#0d9488]' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4a5 5 0 0010 0V3M5 3h10M5 3H3v3a4 4 0 004 4h1m7-7h2v3a4 4 0 01-4 4h-1m-4 5v3m-3 3h10a1 1 0 001-1v-1a1 1 0 00-1-1H7a1 1 0 00-1 1v1a1 1 0 001 1z"/></svg>
                        <span>Football Matches</span>
                    </a>

                    <!-- Upcoming Services (Coming Soon) -->
                    <div class="pt-2 mt-2 border-t border-slate-100">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-3 mb-1">Bills</p>
                        <a href="/services/crypto-to-naira" class="flex items-center justify-between py-2 px-3.5 rounded-xl font-bold text-xs transition-all {{ request()->is('services/crypto-to-naira') ? 'bg-[#0d9488] text-white shadow-xs' : 'text-slate-600 hover:bg-teal-50 hover:text-[#0d9488]' }}">
                            <div class="flex items-center gap-2.5">
                                <svg class="w-4 h-4 shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 7v10m-3-7h6a2 2 0 010 4H9m0-4h6"/></svg>
                                <span>Receive Crypto</span>
                            </div>
                            <span class="text-[9px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 px-1.5 py-0.2 rounded-md">NEW</span>
                        </a>
                        <a href="/services/airtime-data" class="flex items-center justify-between py-2 px-3.5 rounded-xl font-bold text-xs transition-all {{ request()->is('services/airtime-data') ? 'bg-[#0d9488] text-white shadow-xs' : 'text-slate-600 hover:bg-teal-50 hover:text-[#0d9488]' }}">
                            <div class="flex items-center gap-2.5">
                                <svg class="w-4 h-4 shrink-0 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>
                                <span>Airtime &amp; Data</span>
                            </div>
                            <span class="text-[9px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 px-1.5 py-0.2 rounded-md">NEW</span>
                        </a>
                        <a href="/services/electricity" class="flex items-center justify-between py-2 px-3.5 rounded-xl font-bold text-xs transition-all {{ request()->is('services/electricity') ? 'bg-[#0d9488] text-white shadow-xs' : 'text-slate-600 hover:bg-teal-50 hover:text-[#0d9488]' }}">
                            <div class="flex items-center gap-2.5">
                                <svg class="w-4 h-4 shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                <span>Electricity</span>
                            </div>
                            <span class="text-[9px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 px-1.5 py-0.2 rounded-md">NEW</span>
                        </a>
                        <a href="/services/betting" class="flex items-center justify-between py-2 px-3.5 rounded-xl font-bold text-xs transition-all {{ request()->is('services/betting') ? 'bg-[#0d9488] text-white shadow-xs' : 'text-slate-600 hover:bg-teal-50 hover:text-[#0d9488]' }}">
                            <div class="flex items-center gap-2.5">
                                <svg class="w-4 h-4 shrink-0 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"/><circle cx="12" cy="12" r="5" stroke-width="2"/><circle cx="12" cy="12" r="1.5" fill="currentColor"/></svg>
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
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        <span>Fund Wallet</span>
                    </a>
                    <a href="/wallet" class="flex items-center gap-3 py-2.5 px-3.5 rounded-xl font-bold text-sm transition-all {{ request()->is('wallet') ? 'bg-[#0d9488] text-white shadow-md' : 'text-slate-700 hover:bg-teal-50 hover:text-[#0d9488]' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <span>Wallet Ledger</span>
                    </a>
                    <a href="/savings" class="flex items-center justify-between py-2.5 px-3.5 rounded-xl font-bold text-sm transition-all {{ request()->is('savings*') ? 'bg-[#0d9488] text-white shadow-md shadow-teal-600/20' : 'text-slate-700 hover:bg-teal-50 hover:text-[#0d9488]' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Save Small Small</span>
                        </div>
                        <span class="text-[9px] font-black uppercase tracking-wider bg-amber-100 text-amber-900 px-1.5 py-0.2 rounded-md">16.9%</span>
                    </a>
                    <a href="/orders" class="flex items-center gap-3 py-2.5 px-3.5 rounded-xl font-bold text-sm transition-all {{ request()->is('orders') ? 'bg-[#0d9488] text-white shadow-md' : 'text-slate-700 hover:bg-teal-50 hover:text-[#0d9488]' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        <span>My Orders</span>
                    </a>
                    <a href="/logs-store/cart" class="flex items-center gap-3 py-2.5 px-3.5 rounded-xl font-bold text-sm transition-all {{ request()->is('logs-store/cart') ? 'bg-[#0d9488] text-white shadow-md' : 'text-slate-700 hover:bg-teal-50 hover:text-[#0d9488]' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
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
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-5.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/></svg>
                            <span>Tutorials &amp; Guides</span>
                        </div>
                        <span class="text-[9px] font-black uppercase tracking-wider bg-teal-100 text-teal-800 px-1.5 py-0.2 rounded-md">GUIDE</span>
                    </a>
                    <a href="/profile" class="flex items-center gap-3 py-2.5 px-3.5 rounded-xl font-bold text-sm transition-all {{ request()->is('profile') ? 'bg-[#0d9488] text-white shadow-md' : 'text-slate-700 hover:bg-teal-50 hover:text-[#0d9488]' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span>My Profile</span>
                    </a>
                    <a href="/security" class="flex items-center gap-3 py-2.5 px-3.5 rounded-xl font-bold text-sm transition-all {{ request()->is('security') ? 'bg-[#0d9488] text-white shadow-md' : 'text-slate-700 hover:bg-teal-50 hover:text-[#0d9488]' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <span>Security &amp; 2FA</span>
                    </a>
                    <a href="/tickets" class="flex items-center gap-3 py-2.5 px-3.5 rounded-xl font-bold text-sm transition-all {{ request()->is('tickets*') ? 'bg-[#0d9488] text-white shadow-md' : 'text-slate-700 hover:bg-teal-50 hover:text-[#0d9488]' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 00-14 0v4a3 3 0 003 3h1a1 1 0 001-1v-4a1 1 0 00-1-1H7a5 5 0 0110 0h-2a1 1 0 00-1 1v4a1 1 0 001 1h1a3 3 0 003-3v-4z"/></svg>
                        <span>Support Desk</span>
                    </a>
                    <a href="https://wa.me/16573369673?text=Hello%20May22SMS%20Support%2C%20I%20need%20assistance." target="_blank" rel="noopener noreferrer" class="flex items-center justify-between py-2.5 px-3.5 rounded-xl font-bold text-sm transition-all text-emerald-700 bg-emerald-50/80 hover:bg-emerald-100 border border-emerald-200/70">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 shrink-0 text-emerald-600" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.669-.699c.969.54 1.771.82 2.788.82 3.185 0 5.772-2.587 5.773-5.767.001-3.182-2.585-5.805-5.77-5.805zm3.376 8.21c-.14.394-.712.729-1.02.775-.297.045-.67.073-2.138-.535-1.748-.724-2.859-2.5-2.946-2.617-.087-.116-.708-.941-.708-1.794 0-.853.447-1.272.607-1.447.16-.175.349-.219.465-.219.117 0 .233.001.335.006.107.005.251-.041.393.3.146.35.496 1.21.54 1.297.044.088.073.19.015.306-.059.117-.088.19-.175.292-.087.102-.185.228-.264.306-.088.087-.179.182-.077.357.102.175.454.748.974 1.211.67.597 1.235.782 1.41.87.175.088.277.073.38-.044.102-.117.437-.51.554-.685.117-.175.234-.146.394-.088.16.059 1.02.481 1.195.568.175.088.291.131.335.204.044.073.044.423-.096.817z"/>
                            </svg>
                            <span>WhatsApp Chat</span>
                        </div>
                        <span class="text-[9px] font-black uppercase tracking-wider bg-emerald-500 text-white px-1.5 py-0.5 rounded-md">LIVE</span>
                    </a>
                    <a href="/notifications" class="flex items-center gap-3 py-2.5 px-3.5 rounded-xl font-bold text-sm transition-all {{ request()->is('notifications') ? 'bg-[#0d9488] text-white shadow-md' : 'text-slate-700 hover:bg-teal-50 hover:text-[#0d9488]' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
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
                        <div class="text-[10px] font-bold text-slate-400 truncate">{{ auth()->user()->email ?? '' }}</div>
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
                            </div>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded-md bg-emerald-50 text-[10px] font-extrabold text-emerald-700 border border-emerald-200/60">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Verified
                                </span>
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
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-5.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/></svg>
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
                            <svg class="w-5 h-5 text-amber-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                            <span class="text-xs sm:text-sm font-black">{{ $announcementText }}</span>
                        </div>
                        @if(!empty($announcementLink ?? ''))
                            <a href="{{ $announcementLink }}" class="inline-flex items-center justify-center rounded-xl bg-amber-400 hover:bg-amber-500 px-3.5 py-1.5 text-xs font-black text-amber-950 transition shrink-0">
                                View Details &rarr;
                            </a>
                        @endif
                    </div>
                @endif

                <!-- Missing Phone Number Guide Banner (For Google Sign-Up Users) -->
                @if(auth()->check() && empty(trim((string) (auth()->user()->phone_number ?? ''))) && !request()->is('profile'))
                    <div class="rounded-2xl border-2 border-teal-500/40 bg-gradient-to-r from-teal-50 via-emerald-50/70 to-amber-50/50 p-4 text-slate-900 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 shadow-sm">
                        <div class="flex items-start sm:items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-[#0d9488] text-white flex items-center justify-center shrink-0 font-black text-sm shadow-xs">
                                📱
                            </div>
                            <div>
                                <div class="text-xs sm:text-sm font-black text-slate-900 flex items-center gap-2">
                                    <span>Complete Your Google Profile Setup</span>
                                    <span class="bg-amber-400 text-amber-950 text-[10px] font-black px-2 py-0.5 rounded-full uppercase">1 Step Left</span>
                                </div>
                                <p class="text-xs text-slate-600 mt-0.5">
                                    Add your phone number on your Profile page so virtual bank accounts, wallet funding, and order alerts work smoothly.
                                </p>
                            </div>
                        </div>
                        <a href="/profile#phone_number_input" class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-[#0d9488] hover:bg-teal-700 px-4 py-2.5 text-xs font-black text-white shadow-sm transition shrink-0">
                            <span>Update Phone Number</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    </div>
                @endif

                @yield('dashboard_content')
            </div>
        </main>

        <!-- OPay Mobile App Signature Bottom Navigation Bar (Fixed on Mobile Screens) -->
        <nav class="lg:hidden fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-200/90 px-2 py-1.5 flex items-center justify-around shadow-lg">
            
            <!-- Tab 1: Home / Dashboard -->
            <a href="/dashboard" class="flex flex-col items-center justify-center py-1 px-3 rounded-xl transition {{ request()->is('dashboard') ? 'text-[#0d9488]' : 'text-slate-500 hover:text-slate-800' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span class="text-[10px] font-black mt-1 {{ request()->is('dashboard') ? 'text-[#0d9488]' : 'text-slate-600' }}">Home</span>
            </a>

            <!-- Tab 2: SMS Verification -->
            <a href="/sms-verify" class="flex flex-col items-center justify-center py-1 px-3 rounded-xl transition {{ request()->is('sms-verify') ? 'text-[#0d9488]' : 'text-slate-500 hover:text-slate-800' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span class="text-[10px] font-black mt-1 {{ request()->is('sms-verify') ? 'text-[#0d9488]' : 'text-slate-600' }}">SMS</span>
            </a>

            <!-- Tab 3: Center Floating "Fund / Wallet" Action -->
            <a href="/funding" class="flex flex-col items-center justify-center -mt-5 group">
                <div class="w-12 h-12 rounded-full bg-gradient-to-tr from-[#0d9488] via-teal-500 to-amber-400 p-0.5 shadow-lg shadow-teal-500/30 group-active:scale-95 transition-transform flex items-center justify-center">
                    <div class="w-full h-full rounded-full bg-[#0d9488] flex items-center justify-center text-white">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    </div>
                </div>
                <span class="text-[10px] font-black text-[#0d9488] mt-0.5">Add Funds</span>
            </a>

            <!-- Tab 4: Orders History -->
            <a href="/orders" class="flex flex-col items-center justify-center py-1 px-3 rounded-xl transition {{ request()->is('orders') ? 'text-[#0d9488]' : 'text-slate-500 hover:text-slate-800' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <span class="text-[10px] font-black mt-1 {{ request()->is('orders') ? 'text-[#0d9488]' : 'text-slate-600' }}">Orders</span>
            </a>

            <!-- Tab 5: Profile / Me -->
            <a href="/profile" class="flex flex-col items-center justify-center py-1 px-3 rounded-xl transition {{ request()->is('profile') ? 'text-[#0d9488]' : 'text-slate-500 hover:text-slate-800' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <span class="text-[10px] font-black mt-1 {{ request()->is('profile') ? 'text-[#0d9488]' : 'text-slate-600' }}">Me</span>
            </a>
        </nav>

        <!-- Side-Docked WhatsApp Direct Support Tab -->
        <a href="https://wa.me/16573369673?text=Hello%20May22SMS%20Support%2C%20I%20need%20assistance."
           target="_blank"
           rel="noopener noreferrer"
           aria-label="Chat on WhatsApp Support"
           title="Chat with Support on WhatsApp"
           style="position: fixed; right: 0; top: 50%; transform: translateY(-50%); z-index: 40; border-top-left-radius: 14px; border-bottom-left-radius: 14px;"
           class="inline-flex flex-col items-center gap-1 px-2.5 py-3 bg-emerald-500 hover:bg-emerald-600 text-white font-black text-[10px] shadow-lg border border-r-0 border-emerald-400/50 transition active:scale-95">
            <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.669-.699c.969.54 1.771.82 2.788.82 3.185 0 5.772-2.587 5.773-5.767.001-3.182-2.585-5.805-5.77-5.805zm3.376 8.21c-.14.394-.712.729-1.02.775-.297.045-.67.073-2.138-.535-1.748-.724-2.859-2.5-2.946-2.617-.087-.116-.708-.941-.708-1.794 0-.853.447-1.272.607-1.447.16-.175.349-.219.465-.219.117 0 .233.001.335.006.107.005.251-.041.393.3.146.35.496 1.21.54 1.297.044.088.073.19.015.306-.059.117-.088.19-.175.292-.087.102-.185.228-.264.306-.088.087-.179.182-.077.357.102.175.454.748.974 1.211.67.597 1.235.782 1.41.87.175.088.277.073.38-.044.102-.117.437-.51.554-.685.117-.175.234-.146.394-.088.16.059 1.02.481 1.195.568.175.088.291.131.335.204.044.073.044.423-.096.817z"/>
            </svg>
            <span style="writing-mode: vertical-rl; text-orientation: mixed; letter-spacing: 0.04em;">Support</span>
        </a>
        
    </div>
</div>
@endsection
