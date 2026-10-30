@extends('layouts.dashboard')

@section('page_title', 'Receive Crypto & Convert to Naira')

@section('dashboard_content')
<div class="max-w-4xl mx-auto space-y-6 pb-16" x-data="cryptoApp({
    isTestMode: @js($isTestMode),
    initialRef: @js($activeTx ? $activeTx->merchant_reference : null),
    usdtRate: @js($usdtRate),
    btcRate: @js($btcRate),
    banks: @js($banks),
    currencies: @js($supportedCurrencies)
})">

    <!-- Flash Alerts -->
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-4 rounded-2xl shadow-sm flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <span class="font-bold text-xs sm:text-sm">{{ session('success') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-rose-50 border border-rose-200 text-rose-800 px-5 py-4 rounded-2xl shadow-sm flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <span class="font-bold text-xs sm:text-sm">{{ session('error') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-rose-500 hover:text-rose-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    @endif

    @if(!$isAvailable)
        <div class="bg-amber-50 border border-amber-200 text-amber-900 p-5 rounded-2xl flex items-center gap-3">
            <svg class="w-6 h-6 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <div>
                <h4 class="font-black text-sm">Service Temporarily Paused</h4>
                <p class="text-xs mt-0.5">{{ $maintenanceMessage ?: 'Crypto deposits and conversions are currently undergoing scheduled maintenance. Please check back shortly.' }}</p>
            </div>
        </div>
    @endif

    <!-- Header Banner with Live Exchange Rates -->
    <div class="relative overflow-hidden bg-gradient-to-r from-slate-900 via-teal-950 to-slate-900 p-6 sm:p-8 rounded-3xl text-white shadow-xl">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6 relative z-10">
            <div>
                <div class="flex items-center gap-2 flex-wrap mb-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-teal-500/20 text-teal-300 text-[11px] font-black uppercase tracking-wider border border-teal-500/30">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                       May22Sms-Crypto-Convert
                    </span>
                   
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight">Receive Crypto &amp; Convert to Naira</h1>
                <p class="text-xs sm:text-sm font-medium text-slate-300 mt-1 max-w-xl">
                    Deposit cryptocurrency to your dedicated address and automatically receive Nigerian naira in your May22SMS wallet or bank account.
                </p>
            </div>

            <!-- Live Tickers -->
            <div class="flex sm:flex-col gap-2.5 shrink-0">
                <div class="bg-white/10 backdrop-blur-md px-4 py-2.5 rounded-2xl border border-white/15">
                    <div class="text-[10px] font-bold text-teal-300 uppercase tracking-wider">Live USDT / NGN Rate</div>
                    <div class="text-lg font-black text-white">₦{{ number_format($usdtRate, 2) }}</div>
                </div>
                <div class="bg-white/10 backdrop-blur-md px-4 py-2.5 rounded-2xl border border-white/15">
                    <div class="text-[10px] font-bold text-amber-300 uppercase tracking-wider">Live BTC / NGN Rate</div>
                    <div class="text-base font-black text-white">₦{{ number_format($btcRate, 0) }}</div>
                </div>
            </div>
        </div>

        <!-- Tab Navigation Buttons -->
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 mt-6 pt-6 border-t border-white/10">
            <button type="button" 
                    @click="activeTab = 'convert'"
                    :class="activeTab === 'convert' ? 'bg-[#0d9488] text-white shadow-lg shadow-teal-900/40' : 'bg-white/10 text-slate-200 hover:bg-white/15'"
                    class="flex items-center justify-center gap-2 py-3 px-3 rounded-2xl font-black text-xs uppercase tracking-wider transition-all active:scale-95">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                <span>Convert Crypto</span>
            </button>

            <button type="button" 
                    @click="activeTab = 'active'"
                    :class="activeTab === 'active' ? 'bg-[#0d9488] text-white shadow-lg shadow-teal-900/40' : 'bg-white/10 text-slate-200 hover:bg-white/15'"
                    class="flex items-center justify-center gap-2 py-3 px-3 rounded-2xl font-black text-xs uppercase tracking-wider transition-all active:scale-95 relative">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>Deposit &amp; Status</span>
                <template x-if="activeTx">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400 absolute top-2 right-2 animate-ping"></span>
                </template>
            </button>

            <button type="button" 
                    @click="activeTab = 'history'"
                    :class="activeTab === 'history' ? 'bg-[#0d9488] text-white shadow-lg shadow-teal-900/40' : 'bg-white/10 text-slate-200 hover:bg-white/15'"
                    class="col-span-2 sm:col-span-1 flex items-center justify-center gap-2 py-3 px-3 rounded-2xl font-black text-xs uppercase tracking-wider transition-all active:scale-95">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>History</span>
            </button>
        </div>
    </div>

    <!-- TAB 1: ACTIVE DEPOSIT & LIVE TRACKING (USER-FRIENDLY & RESPONSIVE) -->
    <div x-show="activeTab === 'active'" x-cloak>
        <template x-if="activeTx">
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm space-y-6">
                <!-- Order Status Header -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-slate-100">
                    <div>
                        <div class="flex items-center gap-2.5 flex-wrap">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Order Reference:</span>
                            <span class="font-mono text-xs font-black text-slate-900 bg-slate-100 px-2.5 py-1 rounded-lg" x-text="activeTx.merchant_reference"></span>
                            <span class="text-xs font-black uppercase px-3 py-1 rounded-full" :class="getStatusBadgeClass(activeTx.status)" x-text="activeTx.status"></span>
                        </div>
                        <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight mt-2">
                            Send <span class="text-teal-600" x-text="activeTx.from_amount + ' ' + (activeTx.from_currency || 'USDT').toUpperCase()"></span> 
                            → Receive <span class="text-emerald-600" x-text="'₦' + Number(activeTx.to_amount).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                        </h2>
                        <p class="text-xs font-medium text-slate-500 mt-1">
                            Destination: 
                            <strong class="text-slate-800" x-text="activeTx.payout_destination === 'wallet' ? 'May22SMS Wallet Balance' : (activeTx.bank_name + ' (' + activeTx.account_number + ')')"></strong>
                        </p>
                    </div>

                    <button type="button" @click="activeTab = 'convert'" class="self-start sm:self-center px-4 py-2.5 rounded-xl border-2 border-slate-200 text-xs font-black text-slate-700 hover:border-slate-300 hover:bg-slate-50 transition active:scale-95">
                        + New Conversion
                    </button>
                </div>

                <!-- Dedicated Deposit Address Card (Full Width & No Text-Wrap Glitches) -->
                <div class="bg-slate-50 border-2 border-teal-500/30 rounded-3xl p-6 sm:p-7 space-y-5">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-emerald-500 animate-ping"></span>
                            <span class="text-xs font-black uppercase tracking-wider text-slate-700">
                                Dedicated Deposit Address (<span class="text-teal-700 font-extrabold uppercase" x-text="activeTx.network"></span> Network Only)
                            </span>
                        </div>
                        <span class="text-xs font-bold text-slate-500">Scan or copy to send</span>
                    </div>

                    <!-- Deposit Address Display Box -->
                    <div class="bg-white border-2 border-slate-200 rounded-2xl p-4 sm:p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-xs" x-data="{ copied: false }">
                        <div class="min-w-0 flex-1">
                            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Generated Deposit Address</div>
                            <div class="font-mono text-sm sm:text-base font-black text-slate-900 select-all break-all tracking-wide" x-text="activeTx.deposit_address || 'Generating address...'"></div>
                        </div>

                        <button type="button" 
                                @click="copyText(activeTx.deposit_address); copied = true; setTimeout(() => copied = false, 2500)" 
                                class="px-6 py-3.5 rounded-xl font-black text-xs uppercase tracking-wider text-white transition flex items-center justify-center gap-2 shrink-0 shadow-md active:scale-95"
                                :class="copied ? 'bg-emerald-600' : 'bg-[#0d9488] hover:bg-[#0f766e]'">
                            <template x-if="!copied">
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                    <span>Copy Address</span>
                                </span>
                            </template>
                            <template x-if="copied">
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                    <span>Copied!</span>
                                </span>
                            </template>
                        </button>
                    </div>

                    <!-- Destination Tag / Memo if returned (e.g. XRP / TON) -->
                    <template x-if="activeTx.deposit_tag">
                        <div class="bg-amber-100/70 border border-amber-300 p-4 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs" x-data="{ copiedTag: false }">
                            <div>
                                <span class="font-black text-amber-950 uppercase tracking-wide block">Required Destination Tag / Memo:</span>
                                <span class="font-mono font-black text-amber-950 text-base" x-text="activeTx.deposit_tag"></span>
                            </div>
                            <button type="button" @click="copyText(activeTx.deposit_tag); copiedTag = true; setTimeout(() => copiedTag = false, 2500)" class="px-4 py-2 bg-amber-600 text-white rounded-xl font-bold text-xs shrink-0">
                                <span x-text="copiedTag ? 'Tag Copied!' : 'Copy Tag'"></span>
                            </button>
                        </div>
                    </template>

                    <!-- Crucial Network Warning -->
                    <div class="p-4 rounded-2xl bg-rose-50 border-2 border-rose-200 text-rose-900 text-xs font-semibold flex items-start gap-3">
                        <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <div class="leading-relaxed">
                            <strong class="font-black uppercase tracking-wide">Network Warning:</strong> Send only <span class="uppercase font-bold" x-text="activeTx.from_currency"></span> through the <span class="uppercase font-black underline" x-text="activeTx.network"></span> network. Deposits sent over unsupported networks or wrong coins cannot be recovered.
                        </div>
                    </div>
                </div>

                <!-- Live Progress Timeline & Manual Refresh -->
                <div class="bg-slate-50 border border-slate-200 p-6 rounded-3xl space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-black uppercase tracking-wider text-slate-500">Conversion Lifecycle Tracker</span>
                        <button type="button" @click="pollStatus(activeTx.merchant_reference)" class="text-xs font-bold text-teal-700 hover:text-teal-900 flex items-center gap-1.5 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            <span>Refresh Status</span>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                        <!-- Step 1 -->
                        <div class="bg-white border p-4 rounded-2xl flex items-center gap-3.5" :class="activeTx.deposit_address ? 'border-emerald-300' : 'border-slate-200'">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center font-black text-xs shrink-0" :class="activeTx.deposit_address ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-600'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <div>
                                <div class="text-xs font-black text-slate-900">1. Address Ready</div>
                                <div class="text-[11px] text-slate-500">Deposit address assigned</div>
                            </div>
                        </div>

                        <!-- Step 2 -->
                        <div class="bg-white border p-4 rounded-2xl flex items-center gap-3.5" :class="['processing', 'completed'].includes(activeTx.status) ? 'border-emerald-300' : 'border-slate-200'">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center font-black text-xs shrink-0" :class="['processing', 'completed'].includes(activeTx.status) ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-600'">
                                <template x-if="activeTx.status === 'processing'">
                                    <span class="w-2.5 h-2.5 rounded-full bg-white animate-ping"></span>
                                </template>
                                <template x-if="activeTx.status === 'completed'">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                </template>
                                <template x-if="!['processing', 'completed'].includes(activeTx.status)">
                                    <span>2</span>
                                </template>
                            </div>
                            <div>
                                <div class="text-xs font-black text-slate-900">2. Blockchain Detected</div>
                                <div class="text-[11px] text-slate-500">Confirming &amp; converting</div>
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div class="bg-white border p-4 rounded-2xl flex items-center gap-3.5" :class="activeTx.status === 'completed' ? 'border-emerald-300' : 'border-slate-200'">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center font-black text-xs shrink-0" :class="activeTx.status === 'completed' ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-600'">
                                <template x-if="activeTx.status === 'completed'">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                </template>
                                <template x-if="activeTx.status !== 'completed'">
                                    <span>3</span>
                                </template>
                            </div>
                            <div>
                                <div class="text-xs font-black text-slate-900" x-text="activeTx.payout_destination === 'wallet' ? '3. Wallet Credited' : '3. Payout Sent'"></div>
                                <div class="text-[11px] text-slate-500" x-text="activeTx.status === 'completed' ? 'Settled successfully' : 'Pending final settlement'"></div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center pt-2 text-[11px] font-bold text-slate-400">
                        🔄 Polling blockchain settlement status in real-time...
                    </div>
                </div>
            </div>
        </template>

        <!-- No Active Transaction Empty State -->
        <template x-if="!activeTx">
            <div class="bg-white rounded-3xl p-10 sm:p-12 border border-slate-200 text-center space-y-4">
                <div class="w-16 h-16 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center mx-auto text-2xl">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h3 class="text-lg font-black text-slate-900">No Active Deposit in Progress</h3>
                <p class="text-xs text-slate-500 max-w-md mx-auto">
                    You do not have any pending crypto conversion. Use the conversion calculator to generate a dedicated deposit address.
                </p>
                <div>
                    <button type="button" @click="activeTab = 'convert'" class="px-6 py-3 rounded-2xl bg-[#0d9488] hover:bg-[#0f766e] text-white font-black text-xs uppercase tracking-wider transition active:scale-95 shadow-md">
                        Start New Conversion →
                    </button>
                </div>
            </div>
        </template>
    </div>

    <!-- TAB 2: CONVERT CRYPTO FORM & INSTANT QUOTE CALCULATOR -->
    <div x-show="activeTab === 'convert'" class="space-y-6">
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm space-y-8">
            <div class="border-b border-slate-100 pb-5">
                <h2 class="text-xl font-black text-slate-900 tracking-tight">Convert Cryptocurrency to Naira</h2>
                <p class="text-xs text-slate-500 font-medium mt-1">Select your cryptocurrency, specify your payout method, and generate a verified deposit address.</p>
            </div>

            <form action="/services/crypto-to-naira/create" method="POST" class="space-y-8" @submit="isSubmitting = true">
                @csrf

                <!-- Step 1: Select Cryptocurrency -->
                <div class="space-y-3">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700">1. Select Cryptocurrency</label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                        <template x-for="(meta, key) in currencies" :key="key">
                            <button type="button" 
                                    @click="selectCurrency(key)" 
                                    class="p-4 rounded-2xl border-2 text-left transition flex items-center justify-between gap-3 active:scale-98"
                                    :class="selectedCurrency === key ? 'border-[#0d9488] bg-teal-50/40 shadow-xs' : 'border-slate-200 hover:border-slate-300 bg-white'">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center font-black text-sm text-white shrink-0" :class="key === 'usdt' ? 'bg-emerald-600' : (key === 'usdc' ? 'bg-blue-600' : 'bg-amber-600')">
                                        <span x-text="meta.symbol ? meta.symbol.slice(0, 1) : 'C'"></span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-black text-slate-900" x-text="meta.symbol"></div>
                                        <div class="text-[11px] text-slate-500 font-bold" x-text="meta.name"></div>
                                    </div>
                                </div>
                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0" :class="selectedCurrency === key ? 'border-teal-600 bg-teal-600 text-white' : 'border-slate-300'">
                                    <svg x-show="selectedCurrency === key" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                            </button>
                        </template>
                    </div>
                    <input type="hidden" name="currency" :value="selectedCurrency">
                </div>

                <!-- Step 2: Select Network -->
                <div class="space-y-3">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700">2. Select Blockchain Network</label>
                    <div class="flex flex-wrap gap-3">
                        <template x-for="net in (currencies[selectedCurrency]?.networks || ['bep20'])" :key="net">
                            <button type="button" 
                                    @click="selectedNetwork = net; updateQuote()" 
                                    class="px-5 py-3 rounded-2xl border-2 text-xs font-black uppercase transition active:scale-95 flex items-center gap-2"
                                    :class="selectedNetwork === net ? 'border-[#0d9488] bg-teal-50/50 text-teal-900 shadow-xs' : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300'">
                                <span x-text="net"></span>
                                <template x-if="net === 'bep20'">
                                    <span class="px-2 py-0.5 rounded-md text-[9px] font-black bg-emerald-100 text-emerald-800">Fast &amp; Cheap</span>
                                </template>
                                <template x-if="net === 'trc20'">
                                    <span class="px-2 py-0.5 rounded-md text-[9px] font-black bg-amber-100 text-amber-800">Popular</span>
                                </template>
                            </button>
                        </template>
                    </div>
                    <input type="hidden" name="network" :value="selectedNetwork">
                </div>

                <!-- Step 3: Enter Amount -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-black uppercase tracking-wider text-slate-700">3. Amount to Convert</label>
                        <span class="text-xs font-bold text-slate-400">
                            Minimum: <span class="text-slate-900 font-black" x-text="currencies[selectedCurrency]?.min_amount || 1"></span> <span class="uppercase" x-text="selectedCurrency"></span>
                        </span>
                    </div>

                    <div class="relative">
                        <input type="number" 
                               step="any" 
                               name="amount" 
                               x-model="amount" 
                               @input.debounce.300ms="updateQuote()" 
                               class="w-full rounded-2xl border-2 border-slate-200 pl-5 pr-24 py-4 focus:outline-none focus:border-[#0d9488] font-black text-lg text-slate-900 placeholder:text-slate-300 transition" 
                               placeholder="e.g. 50" 
                               required>
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 flex items-center gap-2">
                            <span class="font-black text-xs text-slate-700 uppercase bg-slate-100 px-2.5 py-1 rounded-lg" x-text="selectedCurrency"></span>
                        </div>
                    </div>

                    <!-- Quick Amount Selectors -->
                    <div class="flex flex-wrap gap-2 pt-1">
                        <button type="button" @click="amount = 10; updateQuote()" class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">+10</button>
                        <button type="button" @click="amount = 50; updateQuote()" class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">+50</button>
                        <button type="button" @click="amount = 100; updateQuote()" class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">+100</button>
                        <button type="button" @click="amount = 500; updateQuote()" class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">+500</button>
                    </div>
                </div>

                <!-- Step 4: Payout Destination -->
                <div class="space-y-4">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700">4. Where Should We Send Your Naira?</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Option A: May22SMS Wallet (Recommended) -->
                        <div @click="payoutDestination = 'wallet'" 
                             class="p-5 rounded-2xl border-2 cursor-pointer transition flex items-start gap-3.5 relative"
                             :class="payoutDestination === 'wallet' ? 'border-[#0d9488] bg-teal-50/40 shadow-xs' : 'border-slate-200 hover:border-slate-300 bg-white'">
                            <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 mt-0.5" :class="payoutDestination === 'wallet' ? 'border-teal-600 bg-teal-600 text-white' : 'border-slate-300'">
                                <svg x-show="payoutDestination === 'wallet'" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-black text-slate-900">May22SMS Wallet</span>
                                    <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase bg-emerald-100 text-emerald-800">Instant • 0% Fee</span>
                                </div>
                                <p class="text-xs text-slate-500 font-medium mt-1">
                                    Credited automatically to your May22SMS USD/NGN balance upon blockchain confirmation.
                                </p>
                            </div>
                        </div>

                        <!-- Option B: Direct Nigerian Bank Account -->
                        <div @click="payoutDestination = 'bank_account'" 
                             class="p-5 rounded-2xl border-2 cursor-pointer transition flex items-start gap-3.5 relative"
                             :class="payoutDestination === 'bank_account' ? 'border-[#0d9488] bg-teal-50/40 shadow-xs' : 'border-slate-200 hover:border-slate-300 bg-white'">
                            <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 mt-0.5" :class="payoutDestination === 'bank_account' ? 'border-teal-600 bg-teal-600 text-white' : 'border-slate-300'">
                                <svg x-show="payoutDestination === 'bank_account'" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-black text-slate-900">Direct Bank Account</span>
                                    <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase bg-slate-200 text-slate-800">Commercial Bank</span>
                                </div>
                                <p class="text-xs text-slate-500 font-medium mt-1">
                                    Quidax sends NGN directly to your commercial Nigerian bank account via NIP transfer.
                                </p>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="payout_destination" :value="payoutDestination">

                    <!-- Bank Account Details (Conditional) -->
                    <div x-show="payoutDestination === 'bank_account'" x-cloak class="p-5 rounded-2xl bg-slate-50 border border-slate-200 space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Select Nigerian Bank</label>
                                <select name="bank_code" x-model="selectedBankCode" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-xs font-bold text-slate-900 focus:outline-none focus:border-[#0d9488]">
                                    <option value="">-- Choose your Bank --</option>
                                    <template x-for="bank in banks" :key="bank.code">
                                        <option :value="bank.code" x-text="bank.name"></option>
                                    </template>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">10-Digit Account Number</label>
                                <input type="text" name="account_number" x-model="accountNumber" maxlength="10" placeholder="e.g. 0123456789" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-xs font-bold text-slate-900 focus:outline-none focus:border-[#0d9488]">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Account Name (Optional)</label>
                            <input type="text" name="account_name" x-model="accountName" placeholder="Account Name as registered on bank" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-xs font-bold text-slate-900 focus:outline-none focus:border-[#0d9488]">
                        </div>
                    </div>
                </div>

                <!-- Step 5: Real-time Quote Breakdown Box -->
                <div class="bg-gradient-to-br from-slate-900 to-slate-950 p-6 rounded-3xl text-white space-y-4 shadow-lg">
                    <div class="flex items-center justify-between pb-3 border-b border-white/10 text-xs">
                        <span class="font-bold text-slate-400">Guaranteed Conversion Rate</span>
                        <span class="font-black text-teal-300" x-text="'1 ' + (selectedCurrency || 'USDT').toUpperCase() + ' = ₦' + Number(quoteData.exchange_rate).toLocaleString()"></span>
                    </div>

                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-400 font-medium">Gross Conversion Amount</span>
                        <span class="font-bold text-white" x-text="'₦' + (Number(amount || 0) * Number(quoteData.exchange_rate)).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                    </div>

                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-400 font-medium">Platform Fee (0.5%)</span>
                        <span class="font-bold text-slate-300" x-text="'- ₦' + Number(quoteData.platform_fee_ngn || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                    </div>

                    <div class="pt-3 border-t border-white/10 flex items-center justify-between">
                        <div>
                            <div class="text-[11px] font-extrabold uppercase tracking-wider text-emerald-400">Net Estimated Payout</div>
                            <div class="text-xs text-slate-400">Delivered directly to chosen destination</div>
                        </div>
                        <div class="text-2xl sm:text-3xl font-black text-emerald-400" x-text="'₦' + Number(quoteData.estimated_payout_ngn || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></div>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        :disabled="isSubmitting || !amount || amount <= 0" 
                        class="w-full py-4 rounded-2xl bg-[#0d9488] hover:bg-[#0f766e] text-white font-black text-sm uppercase tracking-wider shadow-lg transition active:scale-98 flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50">
                    <span x-show="isSubmitting" class="w-4 h-4 rounded-full border-2 border-white border-t-transparent animate-spin"></span>
                    <span>Generate Deposit Address &amp; Convert →</span>
                </button>
            </form>
        </div>
    </div>

    <!-- TAB 3: TRANSACTION HISTORY & RECEIPTS -->
    <div x-show="activeTab === 'history'" class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm space-y-6" x-cloak>
        <div class="border-b border-slate-100 pb-5">
            <h2 class="text-xl font-black text-slate-900 tracking-tight">Conversion Transaction History</h2>
            <p class="text-xs text-slate-500 font-medium mt-1">Review your completed, processing, and pending off-ramp conversions.</p>
        </div>

        @if($transactions->isEmpty())
            <div class="text-center py-12 text-slate-400 text-xs">
                No crypto transactions found. Start your first conversion above!
            </div>
        @else
            <div class="divide-y divide-slate-100">
                @foreach($transactions as $tx)
                    <div class="py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-black text-sm text-slate-900">
                                    {{ number_format($tx->from_amount, 4) }} {{ strtoupper($tx->from_currency) }} 
                                    → ₦{{ number_format($tx->to_amount, 2) }}
                                </span>
                                <span class="text-[10px] font-black uppercase px-2.5 py-0.5 rounded-full {{ $tx->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : ($tx->status === 'processing' ? 'bg-sky-100 text-sky-800 animate-pulse' : 'bg-amber-100 text-amber-800') }}">
                                    {{ $tx->status }}
                                </span>
                            </div>
                            <div class="text-xs text-slate-500 font-medium mt-1">
                                Ref: <span class="font-mono text-slate-700">{{ $tx->merchant_reference }}</span> • 
                                Net: {{ strtoupper($tx->network) }} • 
                                Dest: {{ $tx->payout_destination === 'wallet' ? 'Wallet Credit' : ($tx->bank_name ?? 'Bank Account') }} • 
                                {{ $tx->created_at->format('M d, Y · H:i') }}
                            </div>
                        </div>

                        <button type="button" @click="loadActiveTx('{{ $tx->merchant_reference }}')" class="self-start sm:self-center px-4 py-2 rounded-xl border border-slate-200 hover:border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-50 transition">
                            View Details / Address
                        </button>
                    </div>
                @endforeach
            </div>

            @if($transactions->hasPages())
                <div class="pt-4 border-t border-slate-100">
                    {{ $transactions->links() }}
                </div>
            @endif
        @endif
    </div>

</div>

<script>
function cryptoApp(config) {
    return {
        isTestMode: config.isTestMode,
        usdtRate: Number(config.usdtRate) || 1380,
        btcRate: Number(config.btcRate) || 111000000,
        currencies: config.currencies || {},
        banks: config.banks || [],
        
        activeTab: config.initialRef ? 'active' : 'convert',
        selectedCurrency: 'usdt',
        selectedNetwork: 'bep20',
        amount: 10,
        payoutDestination: 'wallet',
        selectedBankCode: '',
        accountNumber: '',
        accountName: '',
        isSubmitting: false,
        activeTx: null,
        pollingTimer: null,
        quoteData: {
            exchange_rate: Number(config.usdtRate) || 1380,
            platform_fee_ngn: 69,
            estimated_payout_ngn: 13731
        },

        init() {
            this.updateQuote();

            if (config.initialRef) {
                this.loadActiveTx(config.initialRef);
            }
        },

        selectCurrency(key) {
            this.selectedCurrency = key;
            const netList = this.currencies[key]?.networks || ['bep20'];
            this.selectedNetwork = netList[0];
            this.updateQuote();
        },

        updateQuote() {
            if (!this.amount || this.amount <= 0) return;

            const rate = (this.selectedCurrency === 'btc') ? this.btcRate : this.usdtRate;
            const gross = Number(this.amount) * rate;
            const fee = gross * 0.005;
            this.quoteData = {
                exchange_rate: rate,
                platform_fee_ngn: Math.round(fee * 100) / 100,
                estimated_payout_ngn: Math.round((gross - fee) * 100) / 100
            };
        },

        loadActiveTx(ref) {
            this.activeTab = 'active';
            fetch(`/services/crypto-to-naira/status/${ref}`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                if (data && data.status === 'success') {
                    this.activeTx = data.data;
                    this.startStatusPolling(ref);
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            })
            .catch(() => {});
        },

        pollStatus(ref) {
            fetch(`/services/crypto-to-naira/status/${ref}`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                if (data && data.status === 'success') {
                    this.activeTx = data.data;
                }
            })
            .catch(() => {});
        },

        startStatusPolling(ref) {
            if (this.pollingTimer) clearInterval(this.pollingTimer);

            this.pollingTimer = setInterval(() => {
                if (!this.activeTx || ['completed', 'failed'].includes(this.activeTx.status)) {
                    clearInterval(this.pollingTimer);
                    return;
                }

                this.pollStatus(ref);
            }, 6000);
        },

        copyText(text) {
            if (!text) return;
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text);
            } else {
                const ta = document.createElement('textarea');
                ta.value = text;
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
            }
        },

        getStatusBadgeClass(status) {
            status = (status || '').toLowerCase();
            if (status === 'completed') return 'bg-emerald-100 text-emerald-800 border border-emerald-300';
            if (status === 'processing') return 'bg-sky-100 text-sky-800 border border-sky-300 animate-pulse';
            if (status === 'failed' || status === 'needs_attention') return 'bg-rose-100 text-rose-800 border border-rose-300';
            return 'bg-amber-100 text-amber-800 border border-amber-300';
        }
    };
}
</script>
@endsection
