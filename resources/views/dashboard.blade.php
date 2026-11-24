@extends('layouts.dashboard')

@section('page_title', 'Dashboard')

@section('dashboard_content')
<style>
@keyframes opay-gift-bounce {
    0%, 100% { transform: translateY(0) scale(1); }
    50% { transform: translateY(-4px) scale(1.08); }
}
@keyframes opay-glow-pulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.6); }
    50% { box-shadow: 0 0 18px 4px rgba(245, 158, 11, 0.35); }
}
@keyframes opay-shimmer {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}
.opay-gift-bounce {
    animation: opay-gift-bounce 2s ease-in-out infinite;
}
.opay-glow-pulse {
    animation: opay-glow-pulse 2s ease-in-out infinite;
}
.opay-progress-shimmer {
    background: linear-gradient(90deg, #f59e0b 0%, #ea580c 50%, #facc15 100%);
    background-size: 200% 100%;
    animation: opay-shimmer 2.4s ease-in-out infinite;
}
</style>

<div class="space-y-6">

    <!-- 0. Seasonal Celebration Greeting Banner, Countdown & Claimable User Gifts -->
    <x-seasonal-dashboard-hub />

    <!-- 1. OPay Signature Emerald / Teal Wallet Balance Hero Card -->
    <div x-data="{ 
            showBalance: localStorage.getItem('opay_show_balance') !== 'false',
            toggleBalance() {
                this.showBalance = !this.showBalance;
                localStorage.setItem('opay_show_balance', this.showBalance);
            }
         }"
         class="relative bg-gradient-to-br from-[#0d9488] via-[#0f766e] to-[#042f2e] rounded-3xl p-5 sm:p-7 text-white shadow-xl shadow-teal-900/15 overflow-hidden border border-teal-600/30">
        
        <!-- Subtle Top Gold Accent Line -->
        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-teal-300 via-amber-300 to-emerald-400"></div>

        <!-- Ambient Watermark Curves in Background -->
        <div class="absolute -right-12 -top-12 w-64 h-64 rounded-full bg-white/5 blur-2xl pointer-events-none"></div>
        <div class="absolute right-6 bottom-4 opacity-10 pointer-events-none">
            <svg class="w-36 h-36 text-white" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L2 22h20L12 2z"/></svg>
        </div>

        <div class="relative z-10 flex flex-col justify-between min-h-[170px] sm:min-h-[190px]">
            
            <!-- Card Header: Balance Label & Actions -->
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="text-xs sm:text-sm font-bold text-teal-100/90 tracking-wide">Available Balance</span>
                    <!-- Hide / Show Eye Toggle -->
                    <button type="button" @click="toggleBalance()" class="text-teal-200 hover:text-white p-1 rounded-lg transition hover:bg-white/10 cursor-pointer" :title="showBalance ? 'Hide balance' : 'Show balance'" aria-label="Toggle balance visibility">
                        <template x-if="showBalance">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </template>
                        <template x-if="!showBalance">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                        </template>
                    </button>
                </div>
            </div>

            <!-- Big High-Contrast Currency Balance Display -->
            <div class="my-2">
                <div class="flex items-baseline gap-2">
                    <div class="text-3xl sm:text-4xl md:text-5xl font-black tracking-tight text-white font-mono flex items-center">
                        <span x-show="showBalance">
                            {{ $currencySymbol ?? '$' }}{{ number_format((auth()->user()->walletBalance ?? 0) * ($currencyFx ?? 1), 2) }}
                        </span>
                        <span x-show="!showBalance" style="display: none;" class="tracking-widest text-teal-200">
                            ••••••••
                        </span>
                    </div>
                    <span class="text-xs font-black uppercase tracking-wider text-teal-200 bg-teal-800/60 px-2 py-0.5 rounded-md border border-teal-400/20">
                        {{ $currency ?? 'USD' }}
                    </span>
                </div>

                <!-- Secondary Currency Hint & Live Rate -->
                <div class="text-xs text-teal-200/90 font-medium mt-1 flex items-center gap-2">
                    @if(($currency ?? 'USD') === 'NGN')
                        <span>≈ ${{ number_format((float) (auth()->user()->walletBalance ?? 0), 2) }} USD</span>
                    @else
                        <span>≈ ₦{{ number_format((float) ((auth()->user()->walletBalance ?? 0) * ($liveNgnPerUsd ?? $ngnPerUsd ?? 1500)), 2) }} NGN</span>
                    @endif
                    <span>&bull;</span>
                    <span class="text-emerald-300 font-bold flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                        Instant Automated
                    </span>
                </div>
            </div>

            <!-- OPay Quick Action Buttons Row -->
            <div class="pt-3 border-t border-white/10 flex flex-wrap items-center gap-2.5 sm:gap-3">
                <!-- + Add Money (Primary High-Contrast Button) -->
                <a href="/funding" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-2xl bg-gradient-to-r from-amber-400 to-yellow-400 hover:from-amber-300 hover:to-yellow-300 text-slate-950 font-black text-xs sm:text-sm shadow-md hover:shadow-lg transition-transform active:scale-95 shrink-0">
                    <span class="text-base font-black">+</span>
                    <span>Add Money</span>
                </a>

                <!-- Wallet Ledger -->
                <a href="/wallet" class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-2xl bg-white/15 hover:bg-white/25 backdrop-blur-xs text-white font-bold text-xs sm:text-sm transition-transform active:scale-95 shrink-0 border border-white/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span>Ledger</span>
                </a>

                <!-- Save Small Small Shortcut -->
                <a href="/savings" class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-2xl bg-gradient-to-r from-emerald-500/80 to-teal-600/80 hover:from-emerald-500 hover:to-teal-600 backdrop-blur-xs text-white font-bold text-xs sm:text-sm transition-transform active:scale-95 shrink-0 border border-emerald-400/30 shadow-sm">
                    <svg class="w-4 h-4 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Save Small Small</span>
                    <span class="text-[9px] font-black uppercase bg-[#facc15] text-slate-950 px-1.5 py-0.2 rounded-md">16.9%</span>
                </a>

                <!-- Calculator shortcut -->
                <a href="#exchange-calculator" class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-2xl bg-white/10 hover:bg-white/20 text-teal-100 font-bold text-xs sm:text-sm transition shrink-0 ml-auto">
                    <svg class="w-4 h-4 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    <span class="hidden sm:inline">Rate Calc</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Tutorial & How-To-Use Guide Banner -->
    <div class="relative overflow-hidden rounded-3xl p-4 sm:p-5 text-white shadow-md border border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4"
         style="background: linear-gradient(135deg, #090d16 0%, #0f172a 60%, #0d9488 100%) !important;">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-amber-400 to-yellow-500 text-slate-950 flex items-center justify-center shadow-md shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-5.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/></svg>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-black text-amber-300 uppercase tracking-wider">Site Tutorials</span>
                    <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase bg-white/20 text-white">User Guide</span>
                </div>
                <h4 class="text-sm sm:text-base font-black text-white">How to use every service on May22SMS</h4>
                <p class="text-xs text-slate-300">Illustrated step-by-step guides for SMS verify, logs, boosting, &amp; wallet funding.</p>
            </div>
        </div>
        <a href="/tutorials" class="px-4 py-2.5 rounded-xl bg-white hover:bg-slate-100 text-slate-900 font-black text-xs uppercase tracking-wider shrink-0 transition active:scale-95 shadow-sm flex items-center gap-1.5">
            <span>Read Tutorials</span>
            <span>&rarr;</span>
        </a>
    </div>

    <!-- 2. OPay Signature Quick Services Hub (4 Columns Mobile / 12 Columns Desktop) -->
    <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-xs border border-slate-200/80">
        <div class="flex items-center justify-between mb-4 px-1">
            <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-[#0d9488]"></span>
                Quick Services
            </h3>
            <a href="/tutorials" class="text-xs font-bold text-[#0d9488] hover:underline flex items-center gap-1">
                <span>How to use</span>
                <span>&rarr;</span>
            </a>
        </div>

        @php
            $svcMgr = app(\App\Services\ServiceManager::class);
        @endphp

        <div class="grid grid-cols-4 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-6 xl:grid-cols-12 gap-3 sm:gap-4">
            
            <!-- Tile 1: SMS Verification -->
            <a href="/sms-verify" class="flex flex-col items-center text-center p-2 rounded-2xl hover:bg-slate-50 transition-all active:scale-95 group relative {{ !$svcMgr->isAvailable('sms_verify') ? 'opacity-75' : '' }}">
                @if(!$svcMgr->isAvailable('sms_verify'))
                    <span class="absolute top-1 right-2 bg-rose-600 text-white text-[9px] font-black uppercase px-1.5 py-0.2 rounded-full shadow-xs z-10">
                        PAUSED
                    </span>
                @else
                    <span class="absolute top-1 right-2 bg-gradient-to-r from-teal-500 to-emerald-600 text-white text-[9px] font-black uppercase px-1.5 py-0.2 rounded-full shadow-xs z-10">
                        HOT
                    </span>
                @endif
                <div class="w-13 h-13 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-br from-teal-500 to-[#0d9488] text-white flex items-center justify-center shadow-md shadow-teal-500/20 group-hover:scale-110 transition-transform mb-2">
                    <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </div>
                <span class="text-xs font-black text-slate-800 leading-tight">SMS Verify</span>
                <span class="text-[10px] font-bold text-slate-400 mt-0.5">Virtual No.</span>
            </a>

            <!-- Tile 2: Digital Logs -->
            <a href="/logs-store" class="flex flex-col items-center text-center p-2 rounded-2xl hover:bg-slate-50 transition-all active:scale-95 group relative {{ !$svcMgr->isAvailable('digital_logs') ? 'opacity-75' : '' }}">
                @if(!$svcMgr->isAvailable('digital_logs'))
                    <span class="absolute top-1 right-2 bg-rose-600 text-white text-[9px] font-black uppercase px-1.5 py-0.2 rounded-full shadow-xs z-10">
                        PAUSED
                    </span>
                @endif
                <div class="w-13 h-13 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 text-white flex items-center justify-center shadow-md shadow-orange-500/20 group-hover:scale-110 transition-transform mb-2">
                    <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <span class="text-xs font-black text-slate-800 leading-tight">Logs Store</span>
                <span class="text-[10px] font-bold text-slate-400 mt-0.5">Accounts</span>
            </a>

            <!-- Tile 3: Social Boosting -->
            <a href="/social-boosting" class="flex flex-col items-center text-center p-2 rounded-2xl hover:bg-slate-50 transition-all active:scale-95 group relative {{ !$svcMgr->isAvailable('boosting') ? 'opacity-75' : '' }}">
                @if(!$svcMgr->isAvailable('boosting'))
                    <span class="absolute top-1 right-2 bg-rose-600 text-white text-[9px] font-black uppercase px-1.5 py-0.2 rounded-full shadow-xs z-10">
                        PAUSED
                    </span>
                @endif
                <div class="w-13 h-13 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-br from-purple-500 to-indigo-600 text-white flex items-center justify-center shadow-md shadow-purple-500/20 group-hover:scale-110 transition-transform mb-2">
                    <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.63 8.41m5.96 5.96a14.926 14.926 0 01-5.841 2.58m-.119-8.54a6 6 0 00-7.381 5.84h4.8m2.58-5.84a14.927 14.927 0 00-2.58 5.84m2.699 2.7a1.5 1.5 0 11-2.121-2.121 1.5 1.5 0 012.121 2.121z"/></svg>
                </div>
                <span class="text-xs font-black text-slate-800 leading-tight">Social Boost</span>
                <span class="text-[10px] font-bold text-slate-400 mt-0.5">Followers</span>
            </a>

            <!-- Tile 4: Add Money / Fund Wallet -->
            <a href="/funding" class="flex flex-col items-center text-center p-2 rounded-2xl hover:bg-slate-50 transition-all active:scale-95 group">
                <div class="w-13 h-13 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-br from-emerald-500 to-green-600 text-white flex items-center justify-center shadow-md shadow-emerald-500/20 group-hover:scale-110 transition-transform mb-2">
                    <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </div>
                <span class="text-xs font-black text-slate-800 leading-tight">Add Money</span>
                <span class="text-[10px] font-bold text-slate-400 mt-0.5">Instant Top-up</span>
            </a>

            <!-- Tile 5: Receive Crypto & Convert to Naira (NEW) -->
            <a href="/services/crypto-to-naira" class="flex flex-col items-center text-center p-2 rounded-2xl hover:bg-slate-50 transition-all active:scale-95 group relative {{ !$svcMgr->isAvailable('crypto_to_naira') ? 'opacity-75' : '' }}">
                @if(!$svcMgr->isAvailable('crypto_to_naira'))
                    <span class="absolute top-1 right-2 bg-rose-600 text-white text-[9px] font-black uppercase px-1.5 py-0.2 rounded-full shadow-xs z-10">
                        PAUSED
                    </span>
                @else
                    <span class="absolute top-1 right-2 bg-gradient-to-r from-emerald-500 to-teal-500 text-white text-[9px] font-black uppercase px-1.5 py-0.2 rounded-full shadow-xs animate-pulse z-10">
                        NEW
                    </span>
                @endif
                <div class="w-13 h-13 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-br from-amber-500 to-yellow-600 text-white flex items-center justify-center shadow-md shadow-amber-500/20 group-hover:scale-110 transition-transform mb-2">
                    <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 7v10m-3-7h6a2 2 0 010 4H9m0-4h6"/></svg>
                </div>
                <span class="text-xs font-black text-slate-800 leading-tight">Receive Crypto</span>
                <span class="text-[10px] font-bold text-slate-400 mt-0.5">Convert To Naira</span>
            </a>

            <!-- Tile 6: Airtime & Data (NEW) -->
            @php
                $vtuDisabled = !$svcMgr->isAvailable('airtime') && !$svcMgr->isAvailable('data');
            @endphp
            <a href="/services/airtime-data" class="flex flex-col items-center text-center p-2 rounded-2xl hover:bg-slate-50 transition-all active:scale-95 group relative {{ $vtuDisabled ? 'opacity-75' : '' }}">
                @if($vtuDisabled)
                    <span class="absolute top-1 right-2 bg-rose-600 text-white text-[9px] font-black uppercase px-1.5 py-0.2 rounded-full shadow-xs z-10">
                        PAUSED
                    </span>
                @else
                    <span class="absolute top-1 right-2 bg-gradient-to-r from-emerald-500 to-teal-500 text-white text-[9px] font-black uppercase px-1.5 py-0.2 rounded-full shadow-xs animate-pulse z-10">
                        NEW
                    </span>
                @endif
                <div class="w-13 h-13 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-br from-sky-500 to-blue-600 text-white flex items-center justify-center shadow-md shadow-blue-500/20 group-hover:scale-110 transition-transform mb-2">
                    <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>
                </div>
                <span class="text-xs font-black text-slate-800 leading-tight">Airtime &amp; Data</span>
                <span class="text-[10px] font-bold text-slate-400 mt-0.5">VTU &amp; Bundles</span>
            </a>

            <!-- Tile 7: Electricity (NEW) -->
            <a href="/services/electricity" class="flex flex-col items-center text-center p-2 rounded-2xl hover:bg-slate-50 transition-all active:scale-95 group relative {{ !$svcMgr->isAvailable('electricity') ? 'opacity-75' : '' }}">
                @if(!$svcMgr->isAvailable('electricity'))
                    <span class="absolute top-1 right-2 bg-rose-600 text-white text-[9px] font-black uppercase px-1.5 py-0.2 rounded-full shadow-xs z-10">
                        PAUSED
                    </span>
                @else
                    <span class="absolute top-1 right-2 bg-gradient-to-r from-emerald-500 to-teal-500 text-white text-[9px] font-black uppercase px-1.5 py-0.2 rounded-full shadow-xs animate-pulse z-10">
                        NEW
                    </span>
                @endif
                <div class="w-13 h-13 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-br from-yellow-400 to-amber-600 text-white flex items-center justify-center shadow-md shadow-yellow-500/20 group-hover:scale-110 transition-transform mb-2">
                    <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <span class="text-xs font-black text-slate-800 leading-tight">Electricity</span>
                <span class="text-[10px] font-bold text-slate-400 mt-0.5">Bills &amp; Tokens</span>
            </a>

            <!-- Tile 8: Betting (NEW) -->
            <a href="/services/betting" class="flex flex-col items-center text-center p-2 rounded-2xl hover:bg-slate-50 transition-all active:scale-95 group relative {{ !$svcMgr->isAvailable('betting') ? 'opacity-75' : '' }}">
                @if(!$svcMgr->isAvailable('betting'))
                    <span class="absolute top-1 right-2 bg-rose-600 text-white text-[9px] font-black uppercase px-1.5 py-0.2 rounded-full shadow-xs z-10">
                        PAUSED
                    </span>
                @else
                    <span class="absolute top-1 right-2 bg-gradient-to-r from-emerald-500 to-teal-500 text-white text-[9px] font-black uppercase px-1.5 py-0.2 rounded-full shadow-xs animate-pulse z-10">
                        NEW
                    </span>
                @endif
                <div class="w-13 h-13 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-br from-emerald-600 to-teal-700 text-white flex items-center justify-center shadow-md shadow-emerald-500/20 group-hover:scale-110 transition-transform mb-2">
                    <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"/><circle cx="12" cy="12" r="5" stroke-width="2"/><circle cx="12" cy="12" r="1.5" fill="currentColor"/></svg>
                </div>
                <span class="text-xs font-black text-slate-800 leading-tight">Betting</span>
                <span class="text-[10px] font-bold text-slate-400 mt-0.5">Fund Wallets</span>
            </a>

            <!-- Tile 9: Save Small Small (HOT / 16.9%) -->
            <a href="/savings" class="flex flex-col items-center text-center p-2 rounded-2xl hover:bg-slate-50 transition-all active:scale-95 group relative">
                <span class="absolute top-1 right-2 bg-gradient-to-r from-amber-500 to-emerald-500 text-white text-[9px] font-black uppercase px-1.5 py-0.2 rounded-full shadow-xs animate-pulse z-10">
                    16.9%
                </span>
                <div class="w-13 h-13 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-700 text-white flex items-center justify-center shadow-md shadow-emerald-500/20 group-hover:scale-110 transition-transform mb-2">
                    <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="text-xs font-black text-slate-800 leading-tight">Save Small</span>
                <span class="text-[10px] font-bold text-emerald-600 mt-0.5">16.9% P.A.</span>
            </a>

            <!-- Tile 10: Daily Gift (With Pulsing Free Badge) -->
            <a href="#daily-gift" class="flex flex-col items-center text-center p-2 rounded-2xl hover:bg-slate-50 transition-all active:scale-95 group relative">
                <span class="absolute top-1 right-2 bg-gradient-to-r from-red-500 to-pink-500 text-white text-[9px] font-black uppercase px-1.5 py-0.2 rounded-full shadow-xs animate-bounce z-10">
                    FREE
                </span>
                <div class="w-13 h-13 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-br from-amber-400 to-yellow-500 text-white flex items-center justify-center shadow-md shadow-amber-500/20 group-hover:scale-110 transition-transform mb-2 opay-gift-bounce">
                    <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V4a2 2 0 112 2h-2zm0 0V4a2 2 0 10-2 2h2zm-8 4h16a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1v-2a1 1 0 011-1zm2 4v6a1 1 0 001 1h10a1 1 0 001-1v-6"/></svg>
                </div>
                <span class="text-xs font-black text-slate-800 leading-tight">Daily Gift</span>
                <span class="text-[10px] font-bold text-amber-600 mt-0.5">Streak Bonus</span>
            </a>

            <!-- Tile 11: SportyBet Codes -->
            <a href="/sporty-bet-codes" class="flex flex-col items-center text-center p-2 rounded-2xl hover:bg-slate-50 transition-all active:scale-95 group">
                <div class="w-13 h-13 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-br from-rose-500 to-red-600 text-white flex items-center justify-center shadow-md shadow-red-500/20 group-hover:scale-110 transition-transform mb-2">
                    <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3a9 9 0 016.36 15.36M12 3a9 9 0 00-6.36 15.36M12 3v18M3 12h18"/></svg>
                </div>
                <span class="text-xs font-black text-slate-800 leading-tight">SportyBet</span>
                <span class="text-[10px] font-bold text-slate-400 mt-0.5">Codes &amp; Bets</span>
            </a>

            <!-- Tile 12: Orders History Tracker -->
            <a href="/orders" class="flex flex-col items-center text-center p-2 rounded-2xl hover:bg-slate-50 transition-all active:scale-95 group">
                <div class="w-13 h-13 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-br from-blue-500 to-cyan-600 text-white flex items-center justify-center shadow-md shadow-blue-500/20 group-hover:scale-110 transition-transform mb-2">
                    <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <span class="text-xs font-black text-slate-800 leading-tight">My Orders</span>
                <span class="text-[10px] font-bold text-slate-400 mt-0.5">Live Tracker</span>
            </a>

            <!-- Tile 13: 24/7 Support Desk -->
            <a href="/tickets" class="flex flex-col items-center text-center p-2 rounded-2xl hover:bg-slate-50 transition-all active:scale-95 group">
                <div class="w-13 h-13 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-br from-pink-500 to-rose-500 text-white flex items-center justify-center shadow-md shadow-pink-500/20 group-hover:scale-110 transition-transform mb-2">
                    <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 00-14 0v4a3 3 0 003 3h1a1 1 0 001-1v-4a1 1 0 00-1-1H7a5 5 0 0110 0h-2a1 1 0 00-1 1v4a1 1 0 001 1h1a3 3 0 003-3v-4z"/></svg>
                </div>
                <span class="text-xs font-black text-slate-800 leading-tight">Support</span>
                <span class="text-[10px] font-bold text-slate-400 mt-0.5">24/7 Desk</span>
            </a>

            <!-- Tile 14: Tutorials & How-To Guides (NEW) -->
            <a href="/tutorials" class="flex flex-col items-center text-center p-2 rounded-2xl hover:bg-slate-50 transition-all active:scale-95 group relative">
                <span class="absolute top-1 right-2 bg-gradient-to-r from-teal-500 to-indigo-500 text-white text-[9px] font-black uppercase px-1.5 py-0.2 rounded-full shadow-xs animate-pulse z-10">
                    GUIDE
                </span>
                <div class="w-13 h-13 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 text-white flex items-center justify-center shadow-md shadow-indigo-500/20 group-hover:scale-110 transition-transform mb-2">
                    <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-5.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/></svg>
                </div>
                <span class="text-xs font-black text-slate-800 leading-tight">Tutorials</span>
                <span class="text-[10px] font-bold text-slate-400 mt-0.5">How To Use</span>
            </a>

        </div>
    </div>

    <!-- 3. OPay Gamified Daily Check-In Rewards Card -->
    <div id="daily-gift" x-data="dailyGiftComponent()" x-init="init()" x-show="enabled" x-cloak class="bg-white rounded-3xl shadow-xs border border-amber-200 overflow-hidden relative transition-all duration-300">
        <!-- Top glowing amber stripe -->
        <div class="h-2 bg-gradient-to-r from-amber-400 via-orange-500 to-yellow-400"></div>

        <div class="p-5 sm:p-7">
            <!-- Header row -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-br from-amber-400 via-orange-500 to-amber-600 flex items-center justify-center text-white shadow-lg shadow-orange-200 shrink-0"
                         :class="!hasClaimed && !poolExhausted ? 'opay-gift-bounce opay-glow-pulse' : ''">
                        <svg class="w-7 h-7 sm:w-8 sm:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V4a2 2 0 112 2h-2zm0 0V4a2 2 0 10-2 2h2zm-8 4h16a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1v-2a1 1 0 011-1zm2 4v6a1 1 0 001 1h10a1 1 0 001-1v-6"/></svg>
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight">Daily Check-In Reward</h3>
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-black bg-gradient-to-r from-orange-100 to-amber-100 text-orange-900 border border-orange-200/80 shadow-xs">
                                <svg class="w-3.5 h-3.5 text-orange-600 inline" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.316.492-.533 1.035-.688 1.587-.197.697-.27 1.408-.258 2.096.02.99.28 1.93.75 2.766-.27-.08-.54-.18-.8-.3a6.002 6.002 0 01-3.13-3.693 1 1 0 00-1.84.52c.115 1.765.815 3.42 1.986 4.673A8.002 8.002 0 0010 18a8.003 8.003 0 007.82-6.273c.27-.894.38-1.826.32-2.753a8.04 8.04 0 00-1.57-4.22 1 1 0 00-1.42-.23c-.32.24-.59.54-.8.88-.42.68-.69 1.45-.8 2.24a4.04 4.04 0 01-.155-2.09 7.02 7.02 0 00-.999-2.999z" clip-rule="evenodd"/></svg>
                                <span x-text="'Day ' + streak + ' Streak'"></span>
                            </span>
                            <template x-if="!hasClaimed && !poolExhausted">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-black uppercase bg-emerald-500 text-white tracking-wider animate-pulse">
                                    Claim Now
                                </span>
                            </template>
                        </div>
                        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Check in consecutively every 24 hours to earn free wallet balance rewards!</p>
                    </div>
                </div>

                <!-- Reset Timer & Quota Status -->
                <div class="flex flex-wrap items-center gap-2">
                    <template x-if="maxClaims > 0">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold border"
                              :class="poolExhausted ? 'bg-red-50 text-red-700 border-red-200' : 'bg-amber-50 text-amber-800 border-amber-200'">
                            <span class="w-2 h-2 rounded-full" :class="poolExhausted ? 'bg-red-500' : 'bg-amber-500'"></span>
                            <span x-text="poolExhausted ? 'Daily pool filled' : 'Pool: ' + todayClaims + '/' + maxClaims"></span>
                        </span>
                    </template>
                    <div class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-100 text-slate-700">
                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>Resets: <strong class="font-mono text-slate-900" x-text="countdownDisplay"></strong></span>
                    </div>
                </div>
            </div>

            <!-- Success notification -->
            <div x-show="successMessage" x-transition class="mt-4 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-bold flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span x-text="successMessage"></span>
                </div>
                <button type="button" @click="successMessage = ''" class="text-emerald-700 hover:text-emerald-900 text-xs font-bold underline cursor-pointer">Dismiss</button>
            </div>

            <!-- Error notification -->
            <div x-show="errorMessage" x-transition class="mt-4 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-sm font-semibold flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span x-text="errorMessage"></span>
                </div>
                <button type="button" @click="errorMessage = ''" class="text-red-700 hover:text-red-900 text-xs font-bold underline cursor-pointer">Dismiss</button>
            </div>

            <!-- Streak Progress Bar -->
            <div class="mt-4 p-3.5 rounded-2xl bg-gradient-to-r from-amber-50/70 to-orange-50/70 border border-amber-100">
                <div class="flex items-center justify-between text-xs font-bold mb-1.5">
                    <span class="text-amber-900 font-black flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-orange-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.316.492-.533 1.035-.688 1.587-.197.697-.27 1.408-.258 2.096.02.99.28 1.93.75 2.766-.27-.08-.54-.18-.8-.3a6.002 6.002 0 01-3.13-3.693 1 1 0 00-1.84.52c.115 1.765.815 3.42 1.986 4.673A8.002 8.002 0 0010 18a8.003 8.003 0 007.82-6.273c.27-.894.38-1.826.32-2.753a8.04 8.04 0 00-1.57-4.22 1 1 0 00-1.42-.23c-.32.24-.59.54-.8.88-.42.68-.69 1.45-.8 2.24a4.04 4.04 0 01-.155-2.09 7.02 7.02 0 00-.999-2.999z" clip-rule="evenodd"/></svg>
                        7-Day Rewards Ladder
                    </span>
                    <span class="text-orange-800" x-text="'Progress: Level ' + streak + ' of 7 (' + Math.round((streak / 7) * 100) + '%)'"></span>
                </div>
                <div class="w-full bg-slate-200/80 h-2.5 rounded-full overflow-hidden">
                    <div class="h-full rounded-full opay-progress-shimmer transition-all duration-700 ease-out"
                         :style="'width: ' + Math.min(100, Math.max(14, Math.round((streak / 7) * 100))) + '%'"></div>
                </div>
            </div>

            <!-- 7-Day Interactive Ladder (Scrollable on small phones, 7-col grid on tablets/desktop) -->
            <div class="mt-4">
                <div class="flex sm:grid sm:grid-cols-4 lg:grid-cols-7 gap-2.5 overflow-x-auto pb-2 sm:pb-0 scrollbar-none">
                    <template x-for="day in [1, 2, 3, 4, 5, 6, 7]" :key="day">
                        <div class="min-w-[100px] sm:min-w-0 p-3 rounded-2xl border text-center transition-all duration-300 relative flex flex-col justify-between shrink-0"
                             :class="{
                                 'bg-emerald-50/80 border-emerald-300 ring-1 ring-emerald-200': (hasClaimed && day <= streak) || (!hasClaimed && day < streak),
                                 'bg-gradient-to-b from-amber-50 to-orange-100 border-orange-400 ring-2 ring-orange-400 shadow-md transform sm:scale-105 opay-glow-pulse': (!hasClaimed && day === streak && !poolExhausted),
                                 'bg-slate-50 border-slate-200 opacity-60': (day > streak) || (!hasClaimed && day === streak && poolExhausted)
                             }">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[11px] font-black"
                                      :class="(hasClaimed && day <= streak) || (!hasClaimed && day < streak) ? 'text-emerald-800' : (day === streak ? 'text-orange-900 font-black' : 'text-slate-500')"
                                      x-text="'Day ' + day">
                                </span>
                                <span class="text-[9px] font-black uppercase tracking-wider text-slate-400" x-text="'L' + day"></span>
                            </div>

                            <div class="py-1">
                                <div class="h-7 flex items-center justify-center mb-0.5">
                                    <span x-show="(hasClaimed && day <= streak) || (!hasClaimed && day < streak)" class="text-emerald-600">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    </span>
                                    <span x-show="!hasClaimed && day === streak" class="opay-gift-bounce inline-block text-amber-600">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V4a2 2 0 112 2h-2zm0 0V4a2 2 0 10-2 2h2zm-8 4h16a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1v-2a1 1 0 011-1zm2 4v6a1 1 0 001 1h10a1 1 0 001-1v-6"/></svg>
                                    </span>
                                    <span x-show="day > streak" class="text-slate-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    </span>
                                </div>
                                <div class="text-xs font-black text-slate-900 font-mono tracking-tight" x-text="getAmountForDay(day)"></div>
                            </div>

                            <div class="mt-1">
                                <span x-show="(hasClaimed && day <= streak) || (!hasClaimed && day < streak)" class="text-[10px] font-bold text-emerald-700 bg-emerald-100 px-1.5 py-0.5 rounded-md">Claimed</span>
                                <span x-show="!hasClaimed && day === streak" class="text-[10px] font-black text-white bg-gradient-to-r from-orange-500 to-amber-500 px-2 py-0.5 rounded-full shadow-xs">CLAIM</span>
                                <span x-show="day > streak" class="text-[10px] font-semibold text-slate-400">Locked</span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Action Button -->
            <div class="mt-5 pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="text-xs text-slate-500 text-center sm:text-left">
                    <span class="font-bold text-slate-700">Streak Rule:</span> Check in every 24 hours to maximize your streak bonus.
                </div>

                <div class="w-full sm:w-auto">
                    <template x-if="hasClaimed">
                        <button type="button" disabled class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-emerald-100 text-emerald-800 font-bold text-xs sm:text-sm cursor-not-allowed flex items-center justify-center gap-2 border border-emerald-200">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span>Claimed for Today (Next in <span x-text="countdownDisplay"></span>)</span>
                        </button>
                    </template>

                    <template x-if="!hasClaimed && poolExhausted">
                        <button type="button" disabled class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-slate-100 text-slate-500 font-bold text-xs sm:text-sm cursor-not-allowed flex items-center justify-center gap-2 border border-slate-200">
                            <span>Today's Quota Reached</span>
                        </button>
                    </template>

                    <template x-if="!hasClaimed && !poolExhausted">
                        <button type="button" @click="claimGift()" :disabled="claiming"
                                class="w-full sm:w-auto px-7 py-3 rounded-2xl bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600 text-white font-black text-xs sm:text-sm shadow-md hover:shadow-lg transition flex items-center justify-center gap-2 transform active:scale-95 disabled:opacity-50 opay-glow-pulse cursor-pointer">
                            <span x-show="claiming" class="inline-block animate-spin">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            </span>
                            <span x-show="!claiming" class="inline-block">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V4a2 2 0 112 2h-2zm0 0V4a2 2 0 10-2 2h2zm-8 4h16a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1v-2a1 1 0 011-1zm2 4v6a1 1 0 001 1h10a1 1 0 001-1v-6"/></svg>
                            </span>
                            <span x-text="claiming ? 'Claiming Gift...' : 'Claim Day ' + streak + ' Reward (' + getAmountForDay(streak) + ')'"></span>
                        </button>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Quick Activity Stats (3 Modern Cards) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        
        <!-- Stat 1: SMS Numbers -->
        <a href="/sms-verify" class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs flex items-center gap-4 hover:border-teal-300 transition group">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            </div>
            <div>
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">SMS Numbers</div>
                <div class="text-2xl font-black text-slate-900 mt-0.5">{{ $smsNumbersCount ?? 0 }}</div>
            </div>
            <div class="ml-auto text-slate-300 group-hover:text-blue-500 transition">&rarr;</div>
        </a>

        <!-- Stat 2: Logs Purchased -->
        <a href="/logs-store" class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs flex items-center gap-4 hover:border-amber-300 transition group">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Logs Purchased</div>
                <div class="text-2xl font-black text-slate-900 mt-0.5">{{ $logsPurchasedCount ?? 0 }}</div>
            </div>
            <div class="ml-auto text-slate-300 group-hover:text-amber-500 transition">&rarr;</div>
        </a>

        <!-- Stat 3: Active Boosts -->
        <a href="/social-boosting" class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs flex items-center gap-4 hover:border-purple-300 transition group">
            <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.63 8.41m5.96 5.96a14.926 14.926 0 01-5.841 2.58m-.119-8.54a6 6 0 00-7.381 5.84h4.8m2.58-5.84a14.927 14.927 0 00-2.58 5.84m2.699 2.7a1.5 1.5 0 11-2.121-2.121 1.5 1.5 0 012.121 2.121z"/></svg>
            </div>
            <div>
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Active Boosts</div>
                <div class="text-2xl font-black text-slate-900 mt-0.5">{{ $boostsCount ?? 0 }}</div>
            </div>
            <div class="ml-auto text-slate-300 group-hover:text-purple-500 transition">&rarr;</div>
        </a>
    </div>

    <!-- 5. OPay Carousel Promotional Banners -->
    <div x-data="{
            index: 0,
            slides: [
                {
                    badge: 'Instant Delivery',
                    title: 'Global SMS Numbers from $0.20',
                    desc: 'Instant verification codes for WhatsApp, Telegram, TikTok, OpenAI, PayPal, and 200+ apps.',
                    btnText: 'Get Number',
                    btnHref: '/sms-verify',
                    bg: 'from-[#0d9488] to-teal-800'
                },
                {
                    badge: 'Restocked Daily',
                    title: 'Verified Aged Digital Accounts',
                    desc: 'Facebook, Twitter, Instagram, TikTok and Google accounts with full cookies and credentials.',
                    btnText: 'Browse Logs',
                    btnHref: '/logs-store',
                    bg: 'from-amber-600 to-orange-700'
                },
                {
                    badge: 'High Speed',
                    title: 'Supercharge Your Social Reach',
                    desc: 'Real followers, likes, views, and comments delivered instantly to scale your online brand.',
                    btnText: 'Launch Boost',
                    btnHref: '/social-boosting',
                    bg: 'from-indigo-600 to-purple-800'
                }
            ],
            next() { this.index = (this.index + 1) % this.slides.length; },
            prev() { this.index = (this.index - 1 + this.slides.length) % this.slides.length; }
         }"
         x-init="setInterval(() => next(), 6000)"
         class="relative rounded-3xl overflow-hidden shadow-md text-white">
        
        <template x-for="(s, i) in slides" :key="i">
            <div x-show="index === i"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-x-4"
                 x-transition:enter-end="opacity-100 translate-x-0"
                 class="p-6 sm:p-8 bg-gradient-to-r flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6"
                 :class="s.bg">
                
                <div class="max-w-xl">
                    <span class="inline-block px-2.5 py-0.5 rounded-full bg-white/20 text-white text-[10px] font-black uppercase tracking-wider mb-2" x-text="s.badge"></span>
                    <h3 class="text-xl sm:text-2xl font-black text-white tracking-tight" x-text="s.title"></h3>
                    <p class="text-white/85 text-xs sm:text-sm mt-1 leading-relaxed" x-text="s.desc"></p>
                </div>

                <div class="flex items-center gap-3 shrink-0">
                    <a :href="s.btnHref" class="px-5 py-2.5 rounded-2xl bg-white text-slate-900 font-black text-xs sm:text-sm shadow-md hover:bg-slate-100 transition active:scale-95" x-text="s.btnText"></a>
                    <button type="button" @click="next()" class="w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 border border-white/20 flex items-center justify-center text-white" aria-label="Next promo">
                        &rarr;
                    </button>
                </div>
            </div>
        </template>
    </div>

    <!-- 6. Two-Column Row: OPay Currency Converter & Top Services -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6" id="exchange-calculator">
        
        <!-- Currency Converter (OPay Clean Exchange Tool) -->
        <div class="lg:col-span-2 bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-xs"
             x-data="{
                rate: {{ (float) ($liveNgnPerUsd ?? $ngnPerUsd ?? 1500) }},
                usd: '',
                ngn: '',
                fromUsd() {
                    const u = parseFloat(this.usd);
                    this.ngn = isNaN(u) ? '' : (u * this.rate).toFixed(2);
                },
                fromNgn() {
                    const n = parseFloat(this.ngn);
                    this.usd = isNaN(n) ? '' : (n / this.rate).toFixed(2);
                },
                setUsd(val) {
                    this.usd = val;
                    this.fromUsd();
                },
                setNgn(val) {
                    this.ngn = val;
                    this.fromNgn();
                }
             }">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div>
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        Exchange Calculator
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Live conversion rate for wallet top-up and orders</p>
                </div>
                <div class="px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200/60 text-emerald-800 text-xs font-black">
                    1 USD = ₦{{ number_format((float) ($liveNgnPerUsd ?? $ngnPerUsd ?? 1500), 2) }}
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                <!-- USD Input -->
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 focus-within:border-[#0d9488] focus-within:bg-white transition">
                    <label class="block text-[10px] font-black uppercase text-slate-400 tracking-wider">You Pay (USD)</label>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-lg font-black text-slate-400">$</span>
                        <input type="number" step="0.01" min="0" x-model="usd" @input="fromUsd()" placeholder="0.00" class="w-full bg-transparent border-none p-0 text-xl font-mono font-black text-slate-900 focus:outline-none">
                    </div>
                </div>

                <!-- NGN Input -->
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 focus-within:border-[#0d9488] focus-within:bg-white transition">
                    <label class="block text-[10px] font-black uppercase text-slate-400 tracking-wider">Equivalent (NGN)</label>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-lg font-black text-slate-400">₦</span>
                        <input type="number" step="1" min="0" x-model="ngn" @input="fromNgn()" placeholder="0.00" class="w-full bg-transparent border-none p-0 text-xl font-mono font-black text-slate-900 focus:outline-none">
                    </div>
                </div>
            </div>

            <!-- Quick Preset Chips -->
            <div class="flex flex-wrap items-center gap-2 mt-4">
                <span class="text-xs font-bold text-slate-400">Presets:</span>
                <button type="button" @click="setUsd(5)" class="px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition cursor-pointer">$5</button>
                <button type="button" @click="setUsd(10)" class="px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition cursor-pointer">$10</button>
                <button type="button" @click="setUsd(25)" class="px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition cursor-pointer">$25</button>
                <button type="button" @click="setNgn(5000)" class="px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition cursor-pointer">₦5,000</button>
                <button type="button" @click="setNgn(10000)" class="px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition cursor-pointer">₦10,000</button>
                <button type="button" @click="setNgn(25000)" class="px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-700 transition cursor-pointer">₦25,000</button>
                <button type="button" @click="usd=''; ngn='';" class="px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-rose-50 hover:text-rose-600 text-xs font-bold text-slate-500 transition cursor-pointer ml-auto">Clear</button>
            </div>
        </div>

        <!-- Featured Fast Channels -->
        <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-xs flex flex-col justify-between">
            <div>
                <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider mb-1">Payment Channels</h3>
                <p class="text-xs text-slate-400">Multiple instant automated options</p>

                <div class="mt-4 space-y-2.5">
                    <div class="flex items-center justify-between p-2.5 rounded-2xl bg-slate-50 border border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </span>
                            <div>
                                <div class="text-xs font-black text-slate-800">Bank Transfer / PalmPay</div>
                                <div class="text-[10px] text-slate-400">Monietude Automated Virtual Account</div>
                            </div>
                        </div>
                        <span class="text-[10px] font-black text-emerald-600 bg-emerald-100 px-2 py-0.5 rounded-full">Instant</span>
                    </div>

                    <div class="flex items-center justify-between p-2.5 rounded-2xl bg-slate-50 border border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-xl bg-teal-100 text-teal-700 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 7v10m-3-7h6a2 2 0 010 4H9m0-4h6"/></svg>
                            </span>
                            <div>
                                <div class="text-xs font-black text-slate-800">Cryptocurrency</div>
                                <div class="text-[10px] text-slate-400">USDT, BTC, ETH, LTC via Cryptomus</div>
                            </div>
                        </div>
                        <span class="text-[10px] font-black text-emerald-600 bg-emerald-100 px-2 py-0.5 rounded-full">Automated</span>
                    </div>
                </div>
            </div>

            <a href="/funding" class="w-full mt-4 py-2.5 px-4 rounded-2xl bg-[#0d9488] hover:bg-teal-700 text-white font-black text-xs text-center shadow-xs transition active:scale-95">
                Fund Wallet Balance &rarr;
            </a>
        </div>
    </div>

    <!-- 7. OPay Recent Transactions & Order Ledger -->
    <div x-data="{ filter: 'all' }" class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        
        <!-- Header & Filter Pills -->
        <div class="p-5 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="text-base font-black text-slate-900 tracking-tight">Recent Transactions</h3>
                    <span class="text-xs text-slate-400 font-bold">Activity</span>
                </div>
                <!-- Filter Pills -->
                <div class="flex flex-wrap gap-1.5 mt-2.5">
                    <button type="button" @click="filter = 'all'" :class="filter === 'all' ? 'bg-[#0d9488] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="px-3 py-1 rounded-full text-xs font-bold transition cursor-pointer">
                        All
                    </button>
                    <button type="button" @click="filter = 'sms'" :class="filter === 'sms' ? 'bg-[#0d9488] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="px-3 py-1 rounded-full text-xs font-bold transition cursor-pointer">
                        SMS
                    </button>
                    <button type="button" @click="filter = 'logs'" :class="filter === 'logs' ? 'bg-[#0d9488] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="px-3 py-1 rounded-full text-xs font-bold transition cursor-pointer">
                        Logs
                    </button>
                    <button type="button" @click="filter = 'boost'" :class="filter === 'boost' ? 'bg-[#0d9488] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="px-3 py-1 rounded-full text-xs font-bold transition cursor-pointer">
                        Boost
                    </button>
                </div>
            </div>

            <a href="/orders" class="inline-flex items-center gap-1 text-xs font-black text-[#0d9488] hover:underline shrink-0">
                <span>View Full Order History</span>
                <span>&rarr;</span>
            </a>
        </div>

        <!-- Transaction List -->
        @if(isset($recentActivities) && $recentActivities->count() > 0)
            <div class="divide-y divide-slate-100">
                @foreach($recentActivities as $activity)
                    <div x-show="filter === 'all' || filter === @js($activity['type'])" 
                         class="p-4 sm:p-5 flex items-center justify-between gap-3 hover:bg-slate-50/70 transition">
                        
                        <div class="flex items-center gap-3.5 min-w-0">
                            <!-- Category Icon Squircle -->
                            <div class="w-11 h-11 rounded-2xl flex items-center justify-center shrink-0 shadow-xs {{ $activity['type'] === 'sms' ? 'bg-blue-50 text-blue-600' : ($activity['type'] === 'boost' ? 'bg-purple-50 text-purple-600' : 'bg-amber-50 text-amber-700') }}">
                                @if($activity['type'] === 'sms')
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                @elseif($activity['type'] === 'boost')
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.63 8.41m5.96 5.96a14.926 14.926 0 01-5.841 2.58m-.119-8.54a6 6 0 00-7.381 5.84h4.8m2.58-5.84a14.927 14.927 0 00-2.58 5.84m2.699 2.7a1.5 1.5 0 11-2.121-2.121 1.5 1.5 0 012.121 2.121z"/></svg>
                                @else
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                @endif
                            </div>

                            <div class="min-w-0">
                                <div class="text-sm font-black text-slate-900 truncate">
                                    {{ $activity['title'] }}
                                </div>
                                <div class="text-xs text-slate-500 font-medium truncate flex items-center gap-1.5 mt-0.5">
                                    <span>{{ \Illuminate\Support\Carbon::parse($activity['created_at'])->format('M d, h:i A') }}</span>
                                    <span>&bull;</span>
                                    @if(($activity['type'] ?? '') === 'sms')
                                        <span>{{ $activity['meta'] ?? 'Order' }}</span>
                                    @elseif(isset($activity['meta_items']))
                                        <span>{{ $activity['meta_items'] }} item(s)</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Right: Amount & Status Badge -->
                        <div class="text-right shrink-0">
                            @if(isset($activity['meta_amount_usd']))
                                <div class="text-sm font-black text-slate-900 font-mono">
                                    -{{ $currencySymbol ?? '$' }}{{ number_format(((float) $activity['meta_amount_usd']) * ($currencyFx ?? 1), 2) }}
                                </div>
                            @endif

                            <div class="mt-1">
                                @php
                                    $st = strtolower((string) ($activity['status'] ?? ''));
                                    $isSuccess = in_array($st, ['completed', 'finished', 'active', 'ok', 'success'], true);
                                    $isPending = in_array($st, ['pending', 'processing', 'in_progress', 'waiting'], true);
                                @endphp
                                <span class="inline-block text-[10px] font-black uppercase px-2 py-0.5 rounded-full {{ $isSuccess ? 'bg-emerald-100 text-emerald-800' : ($isPending ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700') }}">
                                    {{ $activity['status'] }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- OPay Clean Empty State -->
            <div class="p-8 text-center">
                <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <h4 class="text-sm font-black text-slate-800">No Transactions Yet</h4>
                <p class="text-xs text-slate-500 mt-1 max-w-xs mx-auto">Get started by topping up your balance or grabbing an instant SMS number.</p>
                <div class="mt-4 flex items-center justify-center gap-2">
                    <a href="/funding" class="px-4 py-2 rounded-xl bg-[#0d9488] text-white text-xs font-black shadow-xs">Add Funds</a>
                    <a href="/sms-verify" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold hover:bg-slate-200">Get a Number</a>
                </div>
            </div>
        @endif
    </div>

</div>

<!-- Daily Login Gift Javascript Component -->
<script>
function dailyGiftComponent() {
    return {
        loading: true,
        claiming: false,
        enabled: false,
        hasClaimed: false,
        streak: 1,
        todayAmount: null,
        maxClaims: 0,
        todayClaims: 0,
        poolExhausted: false,
        remainingPool: null,
        type: 'streak',
        fixedAmount: 0.05,
        minAmount: 0.02,
        maxAmount: 0.10,
        streakAmounts: {},
        secondsLeft: 0,
        timerInterval: null,
        successMessage: '',
        errorMessage: '',

        get countdownDisplay() {
            if (this.secondsLeft <= 0) return '00:00:00';
            const h = Math.floor(this.secondsLeft / 3600).toString().padStart(2, '0');
            const m = Math.floor((this.secondsLeft % 3600) / 60).toString().padStart(2, '0');
            const s = (this.secondsLeft % 60).toString().padStart(2, '0');
            return `${h}h ${m}m ${s}s`;
        },

        init() {
            this.fetchStatus();
        },

        async fetchStatus() {
            this.loading = true;
            try {
                const res = await fetch('/api/daily-gift/status', {
                    headers: { 'Accept': 'application/json' }
                });
                if (res.ok) {
                    const data = await res.json();
                    this.enabled = !!data.enabled;
                    this.hasClaimed = !!data.has_claimed_today;
                    this.streak = data.streak || 1;
                    this.todayAmount = data.today_claimed_amount;
                    this.maxClaims = data.max_claims_per_day || 0;
                    this.todayClaims = data.today_claims_count || 0;
                    this.poolExhausted = !!data.pool_exhausted;
                    this.remainingPool = data.remaining_pool;
                    this.type = data.type || 'streak';
                    this.fixedAmount = data.fixed_amount || 0.05;
                    this.minAmount = data.min_amount || 0.02;
                    this.maxAmount = data.max_amount || 0.10;
                    this.streakAmounts = data.streak_amounts || {};
                    this.secondsLeft = data.seconds_until_reset || 0;

                    this.startCountdown();
                }
            } catch (err) {
                console.error('Failed to load daily gift status:', err);
            } finally {
                this.loading = false;
            }
        },

        startCountdown() {
            if (this.timerInterval) clearInterval(this.timerInterval);
            if (this.secondsLeft > 0) {
                this.timerInterval = setInterval(() => {
                    if (this.secondsLeft > 0) {
                        this.secondsLeft--;
                    } else {
                        clearInterval(this.timerInterval);
                        this.fetchStatus();
                    }
                }, 1000);
            }
        },

        getAmountForDay(day) {
            if (this.type === 'fixed') {
                return '$' + Number(this.fixedAmount).toFixed(2);
            }
            if (this.type === 'random') {
                return '$' + Number(this.minAmount).toFixed(2) + ' - $' + Number(this.maxAmount).toFixed(2);
            }
            return '$' + Number(this.streakAmounts[day] || 0.05).toFixed(2);
        },

        async claimGift() {
            if (this.claiming || this.hasClaimed || this.poolExhausted) return;
            this.claiming = true;
            this.errorMessage = '';
            this.successMessage = '';

            try {
                const tokenMeta = document.querySelector('meta[name="csrf-token"]');
                const token = tokenMeta ? tokenMeta.getAttribute('content') : '';
                const res = await fetch('/api/daily-gift/claim', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token
                    }
                });

                const data = await res.json();
                if (res.ok && data.success) {
                    this.hasClaimed = true;
                    this.streak = data.streak;
                    this.todayAmount = data.amount;
                    this.successMessage = data.message;
                    
                    this.fetchStatus();
                } else {
                    this.errorMessage = data.message || 'Failed to claim daily gift. Please try again.';
                    if (res.status === 400 && data.message && data.message.toLowerCase().includes('limit')) {
                        this.poolExhausted = true;
                    }
                }
            } catch (err) {
                this.errorMessage = 'Network error while claiming your gift. Please try again.';
            } finally {
                this.claiming = false;
            }
        }
    };
}
</script>
@endsection
