@extends('layouts.dashboard')

@section('page_title', 'Telecom & Digital Utilities')

@section('dashboard_content')
<div class="max-w-5xl mx-auto space-y-6" x-data="utilitiesApp()">
    
    <!-- Top Greeting & Wallet Balance Header -->
    <div class="bg-gradient-to-r from-slate-900 via-teal-950 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="absolute -right-12 -top-12 w-48 h-48 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute right-12 bottom-0 w-32 h-32 bg-amber-500/10 rounded-full blur-2xl pointer-events-none"></div>

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6 relative z-10">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-500/20 text-teal-300 text-xs font-black uppercase tracking-wider mb-2 border border-teal-500/30">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                    Instant VTU &amp; Utility Bills
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight">Airtime, Data, Bills &amp; Betting</h1>
                <p class="text-slate-300 text-sm mt-1 max-w-lg">
                    Recharge instantly with 99.9% uptime. 0% convenience fees on electricity &amp; instant betting wallet top-ups.
                </p>
            </div>

            <!-- Wallet Balance Card -->
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 sm:p-5 border border-white/15 flex items-center justify-between sm:justify-start gap-5 shrink-0">
                <div>
                    <div class="text-[11px] font-extrabold uppercase tracking-wider text-slate-300">Your Wallet Balance</div>
                    <div class="text-2xl sm:text-3xl font-black text-white mt-0.5">
                        ₦{{ number_format($balanceNgn, 2) }}
                    </div>
                    <div class="text-[11px] text-teal-300 font-bold">
                        ≈ ${{ number_format((float)$user->walletBalance, 2) }} USD (Rate: ₦{{ number_format($rate, 2) }}/$)
                    </div>
                </div>
                <a href="/funding" class="px-3.5 py-2 rounded-xl bg-[#0d9488] hover:bg-[#0f766e] text-white font-black text-xs uppercase tracking-wider shadow-md transition active:scale-95 shrink-0">
                    + Top Up
                </a>
            </div>
        </div>

        <!-- 4 Service Tab Selectors -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 mt-6 pt-6 border-t border-white/10">
            <button type="button" 
                    @click="activeTab = 'airtime'"
                    :class="activeTab === 'airtime' ? 'bg-[#0d9488] text-white shadow-lg shadow-teal-900/40' : 'bg-white/10 text-slate-200 hover:bg-white/15'"
                    class="flex items-center justify-center gap-2 py-3 px-3 rounded-2xl font-black text-xs uppercase tracking-wider transition-all active:scale-95">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                <span>Airtime</span>
                @if(isset($serviceStatuses['airtime']) && !$serviceStatuses['airtime']['is_enabled'])
                    <span class="px-1.5 py-0.2 rounded-md bg-rose-500 text-white text-[9px] font-black">Paused</span>
                @else
                    <span class="px-1.5 py-0.2 rounded-md bg-amber-400 text-slate-950 text-[9px] font-black">2% Off</span>
                @endif
            </button>

            <button type="button" 
                    @click="activeTab = 'data'"
                    :class="activeTab === 'data' ? 'bg-[#0d9488] text-white shadow-lg shadow-teal-900/40' : 'bg-white/10 text-slate-200 hover:bg-white/15'"
                    class="flex items-center justify-center gap-2 py-3 px-3 rounded-2xl font-black text-xs uppercase tracking-wider transition-all active:scale-95">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>
                <span>Data Bundles</span>
                @if(isset($serviceStatuses['data']) && !$serviceStatuses['data']['is_enabled'])
                    <span class="px-1.5 py-0.2 rounded-md bg-rose-500 text-white text-[9px] font-black">Paused</span>
                @endif
            </button>

            <button type="button" 
                    @click="activeTab = 'electricity'"
                    :class="activeTab === 'electricity' ? 'bg-[#0d9488] text-white shadow-lg shadow-teal-900/40' : 'bg-white/10 text-slate-200 hover:bg-white/15'"
                    class="flex items-center justify-center gap-2 py-3 px-3 rounded-2xl font-black text-xs uppercase tracking-wider transition-all active:scale-95">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <span>Electricity</span>
                @if(isset($serviceStatuses['electricity']) && !$serviceStatuses['electricity']['is_enabled'])
                    <span class="px-1.5 py-0.2 rounded-md bg-rose-500 text-white text-[9px] font-black">Paused</span>
                @else
                    <span class="px-1.5 py-0.2 rounded-md bg-emerald-400 text-slate-950 text-[9px] font-black">₦0 Fee</span>
                @endif
            </button>

            <button type="button" 
                    @click="activeTab = 'betting'"
                    :class="activeTab === 'betting' ? 'bg-[#0d9488] text-white shadow-lg shadow-teal-900/40' : 'bg-white/10 text-slate-200 hover:bg-white/15'"
                    class="flex items-center justify-center gap-2 py-3 px-3 rounded-2xl font-black text-xs uppercase tracking-wider transition-all active:scale-95">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2.5"/><circle cx="12" cy="12" r="5" stroke-width="2.5"/><circle cx="12" cy="12" r="1.5" fill="currentColor"/></svg>
                <span>Betting</span>
                @if(isset($serviceStatuses['betting']) && !$serviceStatuses['betting']['is_enabled'])
                    <span class="px-1.5 py-0.2 rounded-md bg-rose-500 text-white text-[9px] font-black">Paused</span>
                @endif
            </button>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-sm font-bold">{{ session('success') }}</span>
            </div>
            <button @click="$el.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 text-lg">&times;</button>
        </div>
    @endif

    @if(session('warning'))
        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span class="text-sm font-bold">{{ session('warning') }}</span>
            </div>
            <button @click="$el.parentElement.remove()" class="text-amber-600 hover:text-amber-900 text-lg">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-sm font-bold">{{ session('error') }}</span>
            </div>
            <button @click="$el.parentElement.remove()" class="text-rose-600 hover:text-rose-900 text-lg">&times;</button>
        </div>
    @endif

    <!-- MAIN FORM CARD -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-100 shadow-sm">
        
        <!-- ================= TAB 1: AIRTIME ================= -->
        <div x-show="activeTab === 'airtime'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-black text-slate-900">Instant Airtime Top-Up</h2>
                        <p class="text-xs text-slate-500 font-semibold mt-0.5">Automated top-up across all Nigerian telecom networks</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-black border border-emerald-200">
                        {{ $airtimeDiscount }}% Cashback
                    </span>
                </div>
            </div>

            @if(isset($serviceStatuses['airtime']) && !$serviceStatuses['airtime']['is_enabled'])
                <div class="mb-6 p-4 rounded-2xl bg-amber-500/15 border border-amber-500/30 text-amber-900 flex items-start gap-3">
                    <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <div>
                        <div class="font-black text-xs uppercase tracking-wider text-amber-950">Airtime Service Unavailable</div>
                        <div class="text-xs text-amber-900 mt-0.5">{{ $serviceStatuses['airtime']['maintenance_message'] ?? 'This service is temporarily unavailable. Please try again later.' }}</div>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('utilities.purchase.airtime') }}" @submit.prevent="confirmAirtime()">
                @csrf
                <div class="space-y-6">
                    <!-- Network Selector -->
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">1. Select Network</label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <label class="relative flex flex-col items-center justify-center p-4 rounded-2xl border-2 cursor-pointer transition-all active:scale-95"
                                   :class="airtime.network === 'mtn' ? 'border-amber-400 bg-amber-50 shadow-sm' : 'border-slate-100 hover:border-slate-200 bg-slate-50/50'">
                                <input type="radio" name="network" value="mtn" x-model="airtime.network" class="sr-only">
                                <div class="w-12 h-12 rounded-xl bg-yellow-400 flex items-center justify-center font-black text-slate-950 text-base shadow-sm mb-2">
                                    MTN
                                </div>
                                <span class="text-xs font-black text-slate-800">MTN Nigeria</span>
                            </label>

                            <label class="relative flex flex-col items-center justify-center p-4 rounded-2xl border-2 cursor-pointer transition-all active:scale-95"
                                   :class="airtime.network === 'airtel' ? 'border-rose-400 bg-rose-50 shadow-sm' : 'border-slate-100 hover:border-slate-200 bg-slate-50/50'">
                                <input type="radio" name="network" value="airtel" x-model="airtime.network" class="sr-only">
                                <div class="w-12 h-12 rounded-xl bg-red-600 flex items-center justify-center font-black text-white text-base shadow-sm mb-2">
                                    airtel
                                </div>
                                <span class="text-xs font-black text-slate-800">Airtel</span>
                            </label>

                            <label class="relative flex flex-col items-center justify-center p-4 rounded-2xl border-2 cursor-pointer transition-all active:scale-95"
                                   :class="airtime.network === 'glo' ? 'border-emerald-400 bg-emerald-50 shadow-sm' : 'border-slate-100 hover:border-slate-200 bg-slate-50/50'">
                                <input type="radio" name="network" value="glo" x-model="airtime.network" class="sr-only">
                                <div class="w-12 h-12 rounded-xl bg-emerald-600 flex items-center justify-center font-black text-white text-base shadow-sm mb-2">
                                    glo
                                </div>
                                <span class="text-xs font-black text-slate-800">Glo</span>
                            </label>

                            <label class="relative flex flex-col items-center justify-center p-4 rounded-2xl border-2 cursor-pointer transition-all active:scale-95"
                                   :class="airtime.network === '9mobile' ? 'border-teal-500 bg-teal-50 shadow-sm' : 'border-slate-100 hover:border-slate-200 bg-slate-50/50'">
                                <input type="radio" name="network" value="9mobile" x-model="airtime.network" class="sr-only">
                                <div class="w-12 h-12 rounded-xl bg-emerald-950 flex items-center justify-center font-black text-lime-400 text-sm shadow-sm mb-2">
                                    9mobile
                                </div>
                                <span class="text-xs font-black text-slate-800">9mobile</span>
                            </label>
                        </div>
                    </div>

                    <!-- Phone Number Input -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-xs font-black uppercase tracking-wider text-slate-700">2. Recipient Phone Number</label>
                            @if(!empty($user->phone_number))
                                <button type="button" @click="airtime.phone = '{{ $user->phone_number }}'; autoDetectNetwork(airtime.phone, 'airtime')" class="text-xs font-bold text-[#0d9488] hover:underline">
                                    Use My Number ({{ $user->phone_number }})
                                </button>
                            @endif
                        </div>
                        <div class="relative">
                            <input type="tel" 
                                   name="phone_number" 
                                   x-model="airtime.phone" 
                                   @input="autoDetectNetwork(airtime.phone, 'airtime')"
                                   placeholder="e.g. 08012345678" 
                                   required 
                                   class="w-full rounded-2xl border-2 border-slate-200 px-4 py-3.5 pr-28 text-base font-bold text-slate-900 focus:ring-0 focus:border-[#0d9488] transition">
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center">
                                <template x-if="detectCarrier(airtime.phone)">
                                    <span class="px-2.5 py-1 rounded-xl text-xs font-black shadow-sm flex items-center gap-1.5 ring-1"
                                          :class="detectCarrier(airtime.phone).color">
                                        <span class="w-2 h-2 rounded-full bg-current shrink-0"></span>
                                        <span x-text="detectCarrier(airtime.phone).name"></span>
                                    </span>
                                </template>
                                <template x-if="!detectCarrier(airtime.phone)">
                                    <span class="text-slate-400 text-xs font-bold px-2.5 py-1 bg-slate-100 rounded-xl flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>
                                        <span x-text="normalizePhoneNumber(airtime.phone).length >= 4 ? 'Unknown' : 'Auto-detect'"></span>
                                    </span>
                                </template>
                            </div>
                        </div>

                        <!-- Network Match / Mismatch Banner -->
                        <div x-show="detectCarrier(airtime.phone) && detectCarrier(airtime.phone).key !== airtime.network" 
                             class="mt-2.5 p-3 rounded-2xl bg-amber-50 border border-amber-200 flex items-center justify-between gap-3 text-xs" style="display: none;">
                            <div class="flex items-center gap-2 text-amber-900 font-semibold">
                                <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <span>Detected as an <strong class="font-black" x-text="detectCarrier(airtime.phone)?.name"></strong> line, but you have <strong class="font-black uppercase" x-text="airtime.network"></strong> selected.</span>
                            </div>
                            <button type="button" 
                                    @click="switchNetwork(detectCarrier(airtime.phone).key, 'airtime')"
                                    class="shrink-0 px-3 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-black text-xs shadow-sm transition active:scale-95 flex items-center gap-1">
                                <span>Switch to</span>
                                <span x-text="detectCarrier(airtime.phone)?.name"></span>
                            </button>
                        </div>

                        <div x-show="detectCarrier(airtime.phone) && detectCarrier(airtime.phone).key === airtime.network" 
                             class="mt-1.5 flex items-center gap-1.5 text-xs font-bold text-emerald-700" style="display: none;">
                            <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            <span>Verified <span class="uppercase" x-text="airtime.network"></span> mobile number</span>
                        </div>

                        <div x-show="normalizePhoneNumber(airtime.phone).length >= 11 && !detectCarrier(airtime.phone)" 
                             class="mt-1.5 flex items-center gap-1.5 text-xs font-bold text-rose-600" style="display: none;">
                            <svg class="w-3.5 h-3.5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>Unrecognized Nigerian network prefix. Please check the number.</span>
                        </div>
                    </div>

                    <!-- Amount Selection -->
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">3. Enter Amount (₦)</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 font-black text-lg text-slate-400">₦</span>
                            <input type="number" 
                                   name="amount" 
                                   x-model="airtime.amount" 
                                   min="50" 
                                   max="50000" 
                                   placeholder="Min ₦50 - Max ₦50,000" 
                                   required 
                                   class="w-full rounded-2xl border-2 border-slate-200 pl-10 pr-4 py-3.5 text-lg font-black text-slate-900 focus:ring-0 focus:border-[#0d9488] transition">
                        </div>

                        <!-- Quick Amount Buttons -->
                        <div class="flex flex-wrap gap-2 mt-3">
                            <template x-for="amt in [100, 200, 500, 1000, 2000, 5000]" :key="amt">
                                <button type="button" 
                                        @click="airtime.amount = amt" 
                                        :class="airtime.amount == amt ? 'bg-slate-900 text-white font-black' : 'bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold'"
                                        class="px-3.5 py-1.5 rounded-xl text-xs transition active:scale-95" 
                                        x-text="'₦' + amt">
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Summary & Review Pill -->
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between" x-show="airtime.amount > 0">
                        <div>
                            <div class="text-[11px] font-bold text-slate-500 uppercase">You Pay (with {{ $airtimeDiscount }}% discount)</div>
                            <div class="text-xl font-black text-emerald-600" x-text="'₦' + Number(airtime.amount * (1 - ({{ $airtimeDiscount }}/100))).toFixed(2)"></div>
                        </div>
                        <div class="text-right">
                            <div class="text-[11px] font-bold text-slate-500 uppercase">Wallet Equivalent</div>
                            <div class="text-sm font-black text-slate-800" x-text="'$' + ((airtime.amount * (1 - ({{ $airtimeDiscount }}/100))) / {{ $rate }}).toFixed(4) + ' USD'"></div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" 
                            class="w-full py-4 rounded-2xl bg-[#0d9488] hover:bg-[#0f766e] text-white font-black text-sm uppercase tracking-wider shadow-lg shadow-teal-600/30 transition active:scale-95 flex items-center justify-center gap-2">
                        <span>Continue to Confirm Airtime</span>
                        <span>→</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- ================= TAB 2: DATA BUNDLE ================= -->
        <div x-show="activeTab === 'data'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
            <div class="mb-6">
                <h2 class="text-xl font-black text-slate-900">High-Speed Data Bundles</h2>
                <p class="text-xs text-slate-500 font-semibold mt-0.5">SME, Corporate Gifting &amp; Direct data with instant 30-day delivery</p>
            </div>

            @if(isset($serviceStatuses['data']) && !$serviceStatuses['data']['is_enabled'])
                <div class="mb-6 p-4 rounded-2xl bg-amber-500/15 border border-amber-500/30 text-amber-900 flex items-start gap-3">
                    <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <div>
                        <div class="font-black text-xs uppercase tracking-wider text-amber-950">Data Bundles Service Unavailable</div>
                        <div class="text-xs text-amber-900 mt-0.5">{{ $serviceStatuses['data']['maintenance_message'] ?? 'This service is temporarily unavailable. Please try again later.' }}</div>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('utilities.purchase.data') }}" @submit.prevent="confirmData()">
                @csrf
                <div class="space-y-6">
                    <!-- Network Selector -->
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">1. Select Network</label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <label class="relative flex flex-col items-center justify-center p-4 rounded-2xl border-2 cursor-pointer transition-all active:scale-95"
                                   :class="dataForm.network === 'mtn' ? 'border-amber-400 bg-amber-50 shadow-sm' : 'border-slate-100 hover:border-slate-200 bg-slate-50/50'">
                                <input type="radio" name="network" value="mtn" x-model="dataForm.network" @change="fetchPlans('mtn')" class="sr-only">
                                <div class="w-12 h-12 rounded-xl bg-yellow-400 flex items-center justify-center font-black text-slate-950 text-base shadow-sm mb-2">
                                    MTN
                                </div>
                                <span class="text-xs font-black text-slate-800">MTN Data</span>
                            </label>

                            <label class="relative flex flex-col items-center justify-center p-4 rounded-2xl border-2 cursor-pointer transition-all active:scale-95"
                                   :class="dataForm.network === 'airtel' ? 'border-rose-400 bg-rose-50 shadow-sm' : 'border-slate-100 hover:border-slate-200 bg-slate-50/50'">
                                <input type="radio" name="network" value="airtel" x-model="dataForm.network" @change="fetchPlans('airtel')" class="sr-only">
                                <div class="w-12 h-12 rounded-xl bg-red-600 flex items-center justify-center font-black text-white text-base shadow-sm mb-2">
                                    airtel
                                </div>
                                <span class="text-xs font-black text-slate-800">Airtel Data</span>
                            </label>

                            <label class="relative flex flex-col items-center justify-center p-4 rounded-2xl border-2 cursor-pointer transition-all active:scale-95"
                                   :class="dataForm.network === 'glo' ? 'border-emerald-400 bg-emerald-50 shadow-sm' : 'border-slate-100 hover:border-slate-200 bg-slate-50/50'">
                                <input type="radio" name="network" value="glo" x-model="dataForm.network" @change="fetchPlans('glo')" class="sr-only">
                                <div class="w-12 h-12 rounded-xl bg-emerald-600 flex items-center justify-center font-black text-white text-base shadow-sm mb-2">
                                    glo
                                </div>
                                <span class="text-xs font-black text-slate-800">Glo Data</span>
                            </label>

                            <label class="relative flex flex-col items-center justify-center p-4 rounded-2xl border-2 cursor-pointer transition-all active:scale-95"
                                   :class="dataForm.network === '9mobile' ? 'border-teal-500 bg-teal-50 shadow-sm' : 'border-slate-100 hover:border-slate-200 bg-slate-50/50'">
                                <input type="radio" name="network" value="9mobile" x-model="dataForm.network" @change="fetchPlans('9mobile')" class="sr-only">
                                <div class="w-12 h-12 rounded-xl bg-emerald-950 flex items-center justify-center font-black text-lime-400 text-sm shadow-sm mb-2">
                                    9mobile
                                </div>
                                <span class="text-xs font-black text-slate-800">9mobile Data</span>
                            </label>
                        </div>
                    </div>

                    <!-- Step 2: Recipient Phone Number -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-xs font-black uppercase tracking-wider text-slate-700">2. Recipient Phone Number</label>
                            @if(!empty($user->phone_number))
                                <button type="button" @click="dataForm.phone = '{{ $user->phone_number }}'; autoDetectNetwork(dataForm.phone, 'data')" class="text-xs font-bold text-[#0d9488] hover:underline">
                                    Use My Number ({{ $user->phone_number }})
                                </button>
                            @endif
                        </div>
                        <div class="relative">
                            <input type="tel" 
                                   name="phone_number" 
                                   x-model="dataForm.phone" 
                                   @input="autoDetectNetwork(dataForm.phone, 'data')"
                                   placeholder="e.g. 08012345678" 
                                   required 
                                   class="w-full rounded-2xl border-2 border-slate-200 px-4 py-3.5 pr-28 text-base font-bold text-slate-900 focus:ring-0 focus:border-[#0d9488] transition">
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center">
                                <template x-if="detectCarrier(dataForm.phone)">
                                    <span class="px-2.5 py-1 rounded-xl text-xs font-black shadow-sm flex items-center gap-1.5 ring-1"
                                          :class="detectCarrier(dataForm.phone).color">
                                        <span class="w-2 h-2 rounded-full bg-current shrink-0"></span>
                                        <span x-text="detectCarrier(dataForm.phone).name"></span>
                                    </span>
                                </template>
                                <template x-if="!detectCarrier(dataForm.phone)">
                                    <span class="text-slate-400 text-xs font-bold px-2.5 py-1 bg-slate-100 rounded-xl flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>
                                        <span x-text="normalizePhoneNumber(dataForm.phone).length >= 4 ? 'Unknown' : 'Auto-detect'"></span>
                                    </span>
                                </template>
                            </div>
                        </div>

                        <!-- Network Match / Mismatch Banner -->
                        <div x-show="detectCarrier(dataForm.phone) && detectCarrier(dataForm.phone).key !== dataForm.network" 
                             class="mt-2.5 p-3 rounded-2xl bg-amber-50 border border-amber-200 flex items-center justify-between gap-3 text-xs" style="display: none;">
                            <div class="flex items-center gap-2 text-amber-900 font-semibold">
                                <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <span>Detected as an <strong class="font-black" x-text="detectCarrier(dataForm.phone)?.name"></strong> line, but you have <strong class="font-black uppercase" x-text="dataForm.network"></strong> selected.</span>
                            </div>
                            <button type="button" 
                                    @click="switchNetwork(detectCarrier(dataForm.phone).key, 'data')"
                                    class="shrink-0 px-3 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-black text-xs shadow-sm transition active:scale-95 flex items-center gap-1">
                                <span>Switch to</span>
                                <span x-text="detectCarrier(dataForm.phone)?.name"></span>
                            </button>
                        </div>

                        <div x-show="detectCarrier(dataForm.phone) && detectCarrier(dataForm.phone).key === dataForm.network" 
                             class="mt-1.5 flex items-center gap-1.5 text-xs font-bold text-emerald-700" style="display: none;">
                            <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            <span>Verified <span class="uppercase" x-text="dataForm.network"></span> mobile number</span>
                        </div>

                        <div x-show="normalizePhoneNumber(dataForm.phone).length >= 11 && !detectCarrier(dataForm.phone)" 
                             class="mt-1.5 flex items-center gap-1.5 text-xs font-bold text-rose-600" style="display: none;">
                            <svg class="w-3.5 h-3.5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>Unrecognized Nigerian network prefix. Please check the number.</span>
                        </div>
                    </div>

                    <!-- Step 3: Plans Grid Selector with Duration Sub-Tabs & Search Filter -->
                    <div>
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-700">3. Select Data Plan</label>
                                <span class="text-[11px] text-slate-400 font-semibold">Select your preferred bundle duration &amp; data quota</span>
                            </div>
                            <input type="text" 
                                   x-model="dataSearch" 
                                   placeholder="Search (e.g. 1GB, 2GB, SME)..." 
                                   class="text-xs rounded-xl border border-slate-200 px-3 py-1.5 focus:ring-0 focus:border-[#0d9488]">
                        </div>

                        <!-- Duration Tabs: Daily, Weekly, Monthly, All Plans -->
                        <div class="flex items-center gap-2 mb-3 overflow-x-auto pb-1 scrollbar-none">
                            <button type="button" 
                                    @click="dataValidityTab = 'all'"
                                    :class="dataValidityTab === 'all' ? 'bg-[#0d9488] text-white shadow-sm shadow-teal-700/20' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'"
                                    class="px-4 py-2 rounded-xl text-xs font-black transition active:scale-95 flex items-center gap-1.5 whitespace-nowrap">
                                <span>All Plans</span>
                                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black"
                                      :class="dataValidityTab === 'all' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'"
                                      x-text="currentPlans.length"></span>
                            </button>

                            <button type="button" 
                                    @click="dataValidityTab = 'daily'"
                                    :class="dataValidityTab === 'daily' ? 'bg-[#0d9488] text-white shadow-sm shadow-teal-700/20' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'"
                                    class="px-4 py-2 rounded-xl text-xs font-black transition active:scale-95 flex items-center gap-1.5 whitespace-nowrap">
                                <span>Daily</span>
                                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black"
                                      :class="dataValidityTab === 'daily' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'"
                                      x-text="getTabCount('daily')"></span>
                            </button>

                            <button type="button" 
                                    @click="dataValidityTab = 'weekly'"
                                    :class="dataValidityTab === 'weekly' ? 'bg-[#0d9488] text-white shadow-sm shadow-teal-700/20' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'"
                                    class="px-4 py-2 rounded-xl text-xs font-black transition active:scale-95 flex items-center gap-1.5 whitespace-nowrap">
                                <span>Weekly</span>
                                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black"
                                      :class="dataValidityTab === 'weekly' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'"
                                      x-text="getTabCount('weekly')"></span>
                            </button>

                            <button type="button" 
                                    @click="dataValidityTab = 'monthly'"
                                    :class="dataValidityTab === 'monthly' ? 'bg-[#0d9488] text-white shadow-sm shadow-teal-700/20' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'"
                                    class="px-4 py-2 rounded-xl text-xs font-black transition active:scale-95 flex items-center gap-1.5 whitespace-nowrap">
                                <span>Monthly</span>
                                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black"
                                      :class="dataValidityTab === 'monthly' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'"
                                      x-text="getTabCount('monthly')"></span>
                            </button>
                        </div>

                        <!-- Plans Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 max-h-80 overflow-y-auto p-1 border border-slate-100 rounded-2xl bg-slate-50/50">
                            <template x-for="plan in filteredPlans" :key="plan.id">
                                <div @click="selectDataPlan(plan)"
                                     :class="dataForm.planId == plan.id ? 'border-teal-600 bg-teal-50/80 shadow-md ring-2 ring-teal-500/30' : 'border-slate-200 bg-white hover:border-teal-300'"
                                     class="p-4 rounded-2xl border-2 cursor-pointer transition-all flex flex-col justify-between">
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <span class="text-base font-black text-slate-900" x-text="plan.size || plan.name"></span>
                                            <span class="block text-[11px] font-bold text-slate-400" x-text="plan.validity || '30 Days'"></span>
                                        </div>
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase"
                                              :class="plan.type === 'SME' ? 'bg-amber-100 text-amber-800' : 'bg-teal-100 text-teal-800'"
                                              x-text="plan.type || 'Direct'">
                                        </span>
                                    </div>
                                    <div class="mt-3 flex items-center justify-between pt-2 border-t border-slate-100">
                                        <span class="text-sm font-black text-[#0d9488]" x-text="'₦' + Number(plan.price_ngn || plan.price).toLocaleString()"></span>
                                        <span class="text-[10px] text-slate-400 font-bold" x-text="'$' + ((plan.price_ngn || plan.price) / {{ $rate }}).toFixed(2)"></span>
                                    </div>
                                </div>
                            </template>

                            <div x-show="filteredPlans.length === 0" class="col-span-full py-8 text-center text-slate-400" style="display: none;">
                                <svg class="w-8 h-8 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                <div class="text-xs font-bold text-slate-600">No data bundles found in this category.</div>
                                <div class="text-[11px] text-slate-400 mt-0.5">Try selecting another duration tab or clearing your search filter.</div>
                            </div>
                        </div>
                    </div>

                    <!-- Selected Plan Preview -->
                    <div class="p-4 rounded-2xl bg-teal-50/60 border border-teal-100 flex items-center justify-between" x-show="dataForm.planId">
                        <div>
                            <span class="text-[11px] font-bold text-teal-700 uppercase">Selected Package</span>
                            <div class="text-base font-black text-slate-900" x-text="dataForm.planName"></div>
                        </div>
                        <div class="text-right">
                            <div class="text-xl font-black text-[#0d9488]" x-text="'₦' + Number(dataForm.amountNgn).toLocaleString()"></div>
                            <div class="text-[11px] text-slate-500 font-bold" x-text="'$' + (dataForm.amountNgn / {{ $rate }}).toFixed(4) + ' USD'"></div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" 
                            :disabled="!dataForm.planId"
                            class="w-full py-4 rounded-2xl bg-[#0d9488] hover:bg-[#0f766e] disabled:opacity-50 text-white font-black text-sm uppercase tracking-wider shadow-lg shadow-teal-600/30 transition active:scale-95 flex items-center justify-center gap-2">
                        <span>Continue to Confirm Data Plan</span>
                        <span>→</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- ================= TAB 3: ELECTRICITY ================= -->
        <div x-show="activeTab === 'electricity'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-black text-slate-900">Electricity Bill Payment &amp; Tokens</h2>
                    <p class="text-xs text-slate-500 font-semibold mt-0.5">Prepaid token generation and postpaid bill payment across all DisCos with ₦0 fee</p>
                </div>
                <span class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-black border border-emerald-200">
                    Instant Meter Verification
                </span>
            </div>

            @if(isset($serviceStatuses['electricity']) && !$serviceStatuses['electricity']['is_enabled'])
                <div class="mb-6 p-4 rounded-2xl bg-amber-500/15 border border-amber-500/30 text-amber-900 flex items-start gap-3">
                    <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <div>
                        <div class="font-black text-xs uppercase tracking-wider text-amber-950">Electricity Bill Payment Service Unavailable</div>
                        <div class="text-xs text-amber-900 mt-0.5">{{ $serviceStatuses['electricity']['maintenance_message'] ?? 'This service is temporarily unavailable. Please try again later.' }}</div>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('utilities.pay.electricity') }}" @submit.prevent="confirmElectricity()">
                @csrf
                <div class="space-y-6">
                    <!-- DisCo Selection -->
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">1. Electricity Distribution Company (DisCo)</label>
                        <select name="disco" 
                                x-model="electricity.disco" 
                                @change="electricity.verified = false; electricity.customerName = ''; electricity.address = ''; electricity.errorMessage = ''"
                                required 
                                class="w-full rounded-2xl border-2 border-slate-200 px-4 py-3.5 text-base font-bold text-slate-900 focus:ring-0 focus:border-[#0d9488] transition">
                            <option value="">-- Choose Power Company --</option>
                            @foreach($supportedDiscos as $code => $disco)
                                <option value="{{ $code }}">{{ $disco['name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Meter Type Toggle -->
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">2. Meter Type</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="flex items-center justify-center gap-2 p-3.5 rounded-2xl border-2 cursor-pointer font-black text-sm transition"
                                   :class="electricity.meterType === 'prepaid' ? 'border-[#0d9488] bg-teal-50 text-teal-800' : 'border-slate-200 text-slate-600'">
                                <input type="radio" name="meter_type" value="prepaid" x-model="electricity.meterType" @change="electricity.verified = false; electricity.customerName = ''; electricity.address = ''; electricity.errorMessage = ''" class="sr-only">
                                <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                <span>Prepaid (Token)</span>
                            </label>
                            <label class="flex items-center justify-center gap-2 p-3.5 rounded-2xl border-2 cursor-pointer font-black text-sm transition"
                                   :class="electricity.meterType === 'postpaid' ? 'border-[#0d9488] bg-teal-50 text-teal-800' : 'border-slate-200 text-slate-600'">
                                <input type="radio" name="meter_type" value="postpaid" x-model="electricity.meterType" @change="electricity.verified = false; electricity.customerName = ''; electricity.address = ''; electricity.errorMessage = ''" class="sr-only">
                                <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span>Postpaid (Bill)</span>
                            </label>
                        </div>
                    </div>

                    <!-- Meter Number & Verify Step -->
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">3. Meter Number</label>
                        <div class="flex gap-2">
                            <input type="text" 
                                   name="meter_number" 
                                   x-model="electricity.meterNumber" 
                                   @input="electricity.verified = false; electricity.customerName = ''; electricity.address = ''; electricity.errorMessage = ''"
                                   placeholder="Enter 11 or 13 digit meter number" 
                                   required 
                                   class="flex-1 rounded-2xl border-2 border-slate-200 px-4 py-3.5 text-base font-bold text-slate-900 focus:ring-0 focus:border-[#0d9488] transition">
                            
                            <button type="button" 
                                    @click="verifyElectricityMeter()"
                                    :disabled="verifyingMeter || !electricity.meterNumber || !electricity.disco"
                                    class="px-5 py-3.5 rounded-2xl bg-slate-900 hover:bg-slate-800 disabled:opacity-50 text-white font-black text-xs uppercase tracking-wider shadow-sm transition active:scale-95 shrink-0 flex items-center gap-2">
                                <span x-show="!verifyingMeter">Verify Meter</span>
                                <span x-show="verifyingMeter" class="flex items-center gap-2" style="display: none;">
                                    <svg class="animate-spin h-3.5 w-3.5 text-teal-400" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span>Verifying...</span>
                                </span>
                            </button>
                        </div>

                        <!-- Hidden form field for customer name -->
                        <input type="hidden" name="customer_name" :value="electricity.customerName">

                        <!-- Inline Verification Error Alert -->
                        <div x-show="electricity.errorMessage" 
                             x-transition 
                             class="mt-3 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-start gap-3"
                             style="display: none;">
                            <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <div class="flex-1 space-y-1">
                                <span class="block font-black text-rose-900 uppercase text-[10px] tracking-wider">Meter Verification Failed</span>
                                <div class="font-bold text-rose-800" x-text="electricity.errorMessage"></div>
                                <div class="text-[11px] text-rose-700/90 pt-1 leading-relaxed">
                                    <strong>Troubleshooting:</strong><br>
                                    1. Confirm that you selected the correct <strong>Power Company (DisCo)</strong> that manages this meter.<br>
                                    2. Confirm the <strong>Meter Type</strong> (Prepaid vs Postpaid).<br>
                                    3. Ensure the meter number contains digits only (no spaces or hyphens).<br>
                                    4. For demo or sandbox testing, use test meter <code class="bg-rose-100 text-rose-900 px-1.5 py-0.5 rounded font-mono font-bold">45028475923</code> or <code class="bg-rose-100 text-rose-900 px-1.5 py-0.5 rounded font-mono font-bold">0101150041444</code>.
                                </div>
                            </div>
                        </div>

                        <!-- Prominent Verified User Name Card -->
                        <div x-show="electricity.verified && electricity.customerName" 
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 translate-y-2 scale-98"
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             class="mt-3 p-4 rounded-2xl bg-gradient-to-r from-emerald-50 via-teal-50 to-emerald-50/70 border-2 border-emerald-300 shadow-sm flex items-start justify-between gap-3"
                             style="display: none;">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-[10px] font-black uppercase tracking-wider text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded-md">Verified Meter Owner</span>
                                        <span class="text-[11px] font-bold text-slate-500 uppercase" x-text="'• ' + (electricity.meterType || '')"></span>
                                    </div>
                                    <div class="text-base sm:text-lg font-black text-slate-900 mt-1 tracking-tight" x-text="electricity.customerName"></div>
                                    <div class="text-xs text-slate-600 font-medium mt-0.5 flex items-center gap-1.5" x-show="electricity.address">
                                        <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        <span x-text="electricity.address"></span>
                                    </div>
                                    <div class="text-[11px] text-emerald-700 font-semibold mt-1">
                                        Meter Number: <span class="font-mono font-bold text-slate-800" x-text="electricity.meterNumber"></span>
                                    </div>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 rounded-full bg-emerald-200/80 text-emerald-900 text-[10px] font-black uppercase tracking-wider shrink-0 border border-emerald-300">
                                Verified
                            </span>
                        </div>
                    </div>

                    <!-- Amount Selection -->
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">4. Amount to Recharge (₦)</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 font-black text-lg text-slate-400">₦</span>
                            <input type="number" 
                                   name="amount" 
                                   x-model="electricity.amount" 
                                   min="500" 
                                   max="500000" 
                                   placeholder="Min ₦500 - Max ₦500,000" 
                                   required 
                                   class="w-full rounded-2xl border-2 border-slate-200 pl-10 pr-4 py-3.5 text-lg font-black text-slate-900 focus:ring-0 focus:border-[#0d9488] transition">
                        </div>

                        <!-- Quick Chips -->
                        <div class="flex flex-wrap gap-2 mt-3">
                            <template x-for="amt in [1000, 2000, 3000, 5000, 10000, 20000]" :key="amt">
                                <button type="button" 
                                        @click="electricity.amount = amt" 
                                        :class="electricity.amount == amt ? 'bg-slate-900 text-white font-black' : 'bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold'"
                                        class="px-3.5 py-1.5 rounded-xl text-xs transition active:scale-95" 
                                        x-text="'₦' + Number(amt).toLocaleString()">
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Phone Number for Token SMS Delivery -->
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">5. Phone Number for Token SMS Notification</label>
                        <input type="tel" 
                               name="phone_number" 
                               x-model="electricity.phone" 
                               placeholder="e.g. 08012345678" 
                               class="w-full rounded-2xl border-2 border-slate-200 px-4 py-3.5 text-base font-bold text-slate-900 focus:ring-0 focus:border-[#0d9488] transition">
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" 
                            class="w-full py-4 rounded-2xl bg-[#0d9488] hover:bg-[#0f766e] text-white font-black text-sm uppercase tracking-wider shadow-lg shadow-teal-600/30 transition active:scale-95 flex items-center justify-center gap-2">
                        <span>Continue to Confirm Electricity Bill</span>
                        <span>→</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- ================= TAB 4: BETTING ================= -->
        <div x-show="activeTab === 'betting'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-black text-slate-900">Betting Wallet Funding</h2>
                    <p class="text-xs text-slate-500 font-semibold mt-0.5">Top-up SportyBet, Bet9ja, 1xBet, BetKing &amp; more directly from your wallet with ₦0 fee</p>
                </div>
                <span class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-black border border-emerald-200">
                    Instant Credit
                </span>
            </div>

            @if(isset($serviceStatuses['betting']) && !$serviceStatuses['betting']['is_enabled'])
                <div class="mb-6 p-4 rounded-2xl bg-amber-500/15 border border-amber-500/30 text-amber-900 flex items-start gap-3">
                    <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <div>
                        <div class="font-black text-xs uppercase tracking-wider text-amber-950">Betting Wallet Top-Up Service Unavailable</div>
                        <div class="text-xs text-amber-900 mt-0.5">{{ $serviceStatuses['betting']['maintenance_message'] ?? 'This service is temporarily unavailable. Please try again later.' }}</div>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('utilities.fund.betting') }}" @submit.prevent="confirmBetting()">
                @csrf
                <div class="space-y-6">
                    <!-- Platform Selector -->
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">1. Select Gaming Platform</label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            @foreach($supportedBetting as $code => $platform)
                                <label class="p-3.5 rounded-2xl border-2 cursor-pointer flex flex-col items-center justify-center text-center transition active:scale-95"
                                       :class="betting.platform === '{{ $code }}' ? 'border-[#0d9488] bg-teal-50 text-teal-800' : 'border-slate-100 hover:border-slate-200 bg-slate-50/50 text-slate-800'">
                                    <input type="radio" name="platform" value="{{ $code }}" x-model="betting.platform" @change="betting.verified = false; betting.customerName = ''" class="sr-only">
                                    <svg class="w-5 h-5 mb-1.5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"/><circle cx="12" cy="12" r="5" stroke-width="2"/><circle cx="12" cy="12" r="1.5" fill="currentColor"/></svg>
                                    <span class="text-xs font-black">{{ $platform['name'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Customer ID / User ID & Verification -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-xs font-black uppercase tracking-wider text-slate-700">2. Betting Account User ID / Customer ID</label>
                            <span class="text-[11px] font-bold text-teal-700" x-text="betting.platform === 'sportybet' ? 'Phone Number Required' : 'Numerical User ID'"></span>
                        </div>
                        <div class="flex gap-2">
                            <input type="text" 
                                   name="customer_id" 
                                   x-model="betting.customerId" 
                                   @input="betting.verified = false; betting.customerName = ''; betting.errorMessage = ''"
                                   :placeholder="getBettingPlaceholder()" 
                                   required 
                                   class="flex-1 rounded-2xl border-2 border-slate-200 px-4 py-3.5 text-base font-bold text-slate-900 focus:ring-0 focus:border-[#0d9488] transition">
                            
                            <button type="button" 
                                    @click="verifyBettingAccount()"
                                    :disabled="verifyingBetting || !betting.customerId || !betting.platform"
                                    class="px-5 py-3.5 rounded-2xl bg-slate-900 hover:bg-slate-800 disabled:opacity-50 text-white font-black text-xs uppercase tracking-wider shadow-sm transition active:scale-95 shrink-0 flex items-center gap-2">
                                <span x-show="!verifyingBetting">Verify Account</span>
                                <span x-show="verifyingBetting" class="flex items-center gap-2" style="display: none;">
                                    <svg class="animate-spin h-3.5 w-3.5 text-teal-400" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span>Checking...</span>
                                </span>
                            </button>
                        </div>
                        <div class="text-[11px] text-slate-500 font-medium mt-1.5 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-[#0d9488] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span x-text="getBettingHelperText()"></span>
                        </div>

                        <!-- Hidden form field for customer name -->
                        <input type="hidden" name="customer_name" :value="betting.customerName">

                        <!-- Inline Verification Error Alert -->
                        <div x-show="betting.errorMessage" 
                             x-transition 
                             class="mt-3 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-start gap-3"
                             style="display: none;">
                            <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <div class="flex-1 space-y-1">
                                <span class="block font-black text-rose-900 uppercase text-[10px] tracking-wider">Account Verification Failed</span>
                                <div class="font-bold text-rose-800" x-text="betting.errorMessage"></div>
                                <div class="text-[11px] text-rose-700/90 pt-1 leading-relaxed">
                                    <strong>Troubleshooting:</strong><br>
                                    • <strong>SportyBet / MSport:</strong> Enter your registered Nigerian phone number (e.g. <code class="bg-rose-100 text-rose-900 px-1 py-0.5 rounded font-mono font-bold">080...</code> or <code class="bg-rose-100 text-rose-900 px-1 py-0.5 rounded font-mono font-bold">090...</code>).<br>
                                    • <strong>Bet9ja / BetKing / 1xBet:</strong> Enter your numerical User / Account ID from your betting profile.<br>
                                    • <strong>Sandbox Testing:</strong> Use test account ID <code class="bg-rose-100 text-rose-900 px-1.5 py-0.5 rounded font-mono font-bold">10482941</code> or <code class="bg-rose-100 text-rose-900 px-1.5 py-0.5 rounded font-mono font-bold">9847291</code>.
                                </div>
                            </div>
                        </div>

                        <!-- Verified Bettor Name Card -->
                        <div x-show="betting.verified && betting.customerName" 
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 translate-y-2 scale-98"
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             class="mt-3 p-4 rounded-2xl bg-gradient-to-r from-emerald-50 via-teal-50 to-emerald-50/70 border-2 border-emerald-300 shadow-sm flex items-start justify-between gap-3"
                             style="display: none;">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-[10px] font-black uppercase tracking-wider text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded-md">Verified Punter Account</span>
                                        <span class="text-[11px] font-bold text-slate-500 uppercase" x-text="'• ' + (betting.platform || '')"></span>
                                    </div>
                                    <div class="text-base sm:text-lg font-black text-slate-900 mt-1 tracking-tight" x-text="betting.customerName"></div>
                                    <div class="text-[11px] text-emerald-700 font-semibold mt-1">
                                        Account User ID: <span class="font-mono font-bold text-slate-800" x-text="betting.customerId"></span>
                                    </div>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 rounded-full bg-emerald-200/80 text-emerald-900 text-[10px] font-black uppercase tracking-wider shrink-0 border border-emerald-300">
                                Verified
                            </span>
                        </div>
                    </div>

                    <!-- Amount Selection -->
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">3. Enter Funding Amount (₦)</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 font-black text-lg text-slate-400">₦</span>
                            <input type="number" 
                                   name="amount" 
                                   x-model="betting.amount" 
                                   min="100" 
                                   max="500000" 
                                   placeholder="Min ₦100 - Max ₦500,000" 
                                   required 
                                   class="w-full rounded-2xl border-2 border-slate-200 pl-10 pr-4 py-3.5 text-lg font-black text-slate-900 focus:ring-0 focus:border-[#0d9488] transition">
                        </div>

                        <div class="flex flex-wrap gap-2 mt-3">
                            <template x-for="amt in [200, 500, 1000, 2000, 5000, 10000]" :key="amt">
                                <button type="button" 
                                        @click="betting.amount = amt" 
                                        :class="betting.amount == amt ? 'bg-slate-900 text-white font-black' : 'bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold'"
                                        class="px-3.5 py-1.5 rounded-xl text-xs transition active:scale-95" 
                                        x-text="'₦' + Number(amt).toLocaleString()">
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" 
                            class="w-full py-4 rounded-2xl bg-[#0d9488] hover:bg-[#0f766e] text-white font-black text-sm uppercase tracking-wider shadow-lg shadow-teal-600/30 transition active:scale-95 flex items-center justify-center gap-2">
                        <span>Continue to Confirm Betting Top-Up</span>
                        <span>→</span>
                    </button>
                </div>
            </form>
        </div>

    </div>

    <!-- RECENT TRANSACTIONS LEDGER PREVIEW -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-100 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-black text-slate-900">Recent Utility Transactions</h3>
                <p class="text-xs text-slate-500 font-semibold">Track your latest airtime, bundle, electricity and betting orders</p>
            </div>
            <a href="{{ route('utilities.history') }}" class="text-xs font-black text-[#0d9488] hover:underline flex items-center gap-1">
                <span>View Full History</span>
                <span>→</span>
            </a>
        </div>

        @if($recentTransactions->isEmpty())
            <div class="text-center py-8 bg-slate-50 rounded-2xl border border-slate-100">
                <div class="w-12 h-12 mx-auto rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400 mb-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <p class="text-sm font-bold text-slate-700 mt-2">No utility transactions yet</p>
                <p class="text-xs text-slate-400">Your airtime, data, electricity, and betting purchases will appear here.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 text-[11px] font-black uppercase tracking-wider">
                            <th class="py-3 px-3">Service</th>
                            <th class="py-3 px-3">Target Details</th>
                            <th class="py-3 px-3">Amount</th>
                            <th class="py-3 px-3">Status</th>
                            <th class="py-3 px-3 text-right">Receipt</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($recentTransactions as $tx)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="py-3.5 px-3">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 
                                             @if($tx->service_type === 'airtime') bg-teal-50 text-[#0d9488] border border-teal-200
                                             @elseif($tx->service_type === 'data') bg-blue-50 text-blue-600 border border-blue-200
                                             @elseif($tx->service_type === 'electricity') bg-amber-50 text-amber-600 border border-amber-200
                                             @else bg-purple-50 text-purple-600 border border-purple-200 @endif">
                                            @if($tx->service_type === 'airtime')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                            @elseif($tx->service_type === 'data')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>
                                            @elseif($tx->service_type === 'electricity')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"/><circle cx="12" cy="12" r="5" stroke-width="2"/><circle cx="12" cy="12" r="1.5" fill="currentColor"/></svg>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="font-black text-slate-900 text-xs">{{ $tx->service_label }}</div>
                                            <div class="text-[10px] text-slate-400 font-bold uppercase">{{ $tx->network_or_disco }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-3">
                                    <div class="font-bold text-slate-800 text-xs">{{ $tx->customer_target }}</div>
                                    @if($tx->customer_name)
                                        <div class="text-[10px] text-slate-400 truncate max-w-xs">{{ $tx->customer_name }}</div>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3">
                                    <div class="font-black text-slate-900 text-xs">₦{{ number_format($tx->amount_ngn, 2) }}</div>
                                    <div class="text-[10px] text-slate-400 font-bold">${{ number_format($tx->total_charged_usd, 2) }}</div>
                                </td>
                                <td class="py-3.5 px-3">
                                    @php $badge = $tx->status_badge; @endphp
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black border {{ $badge['bg'] }}">
                                        @if($tx->status === 'completed')
                                            <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        @elseif($tx->status === 'pending')
                                            <svg class="w-3 h-3 text-amber-600 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        @elseif($tx->status === 'failed')
                                            <svg class="w-3 h-3 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                                        @else
                                            <svg class="w-3 h-3 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        @endif
                                        <span>{{ $badge['label'] }}</span>
                                    </span>
                                </td>
                                <td class="py-3.5 px-3 text-right">
                                    <a href="{{ route('utilities.receipt', $tx->reference) }}" class="inline-flex items-center gap-1 text-xs font-black text-[#0d9488] hover:text-[#0f766e]">
                                        <span>Receipt</span>
                                        <span>↗</span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- ================= CONFIRMATION MODAL ================= -->
    <div x-show="modalOpen" 
         x-transition:enter="transition ease-out duration-200" 
         x-transition:enter-start="opacity-0" 
         x-transition:enter-end="opacity-100" 
         class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4" 
         style="display: none;">
        
        <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-7 shadow-2xl border border-slate-100 space-y-5" @click.outside="modalOpen = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-2xl flex items-center justify-center shrink-0"
                         :class="{
                             'bg-teal-100 text-[#0d9488]': confirmDataModal.actionType === 'airtime',
                             'bg-blue-100 text-blue-700': confirmDataModal.actionType === 'data',
                             'bg-amber-100 text-amber-700': confirmDataModal.actionType === 'electricity',
                             'bg-purple-100 text-purple-700': confirmDataModal.actionType === 'betting'
                         }">
                        <template x-if="confirmDataModal.actionType === 'airtime'">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        </template>
                        <template x-if="confirmDataModal.actionType === 'data'">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>
                        </template>
                        <template x-if="confirmDataModal.actionType === 'electricity'">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </template>
                        <template x-if="confirmDataModal.actionType === 'betting'">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"/><circle cx="12" cy="12" r="5" stroke-width="2"/><circle cx="12" cy="12" r="1.5" fill="currentColor"/></svg>
                        </template>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-900" x-text="confirmDataModal.title"></h3>
                        <p class="text-[11px] text-slate-400 font-bold">Review transaction details before wallet debit</p>
                    </div>
                </div>
                <button type="button" @click="modalOpen = false" class="text-slate-400 hover:text-slate-700 text-xl font-bold">&times;</button>
            </div>

            <!-- Details List -->
            <div class="space-y-3 bg-slate-50 p-4 rounded-2xl border border-slate-100 text-xs">
                <div class="flex justify-between">
                    <span class="text-slate-500 font-bold">Service:</span>
                    <span class="font-black text-slate-800" x-text="confirmDataModal.service"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 font-bold">Provider / Network:</span>
                    <span class="font-black text-slate-800 uppercase" x-text="confirmDataModal.network"></span>
                </div>
                <div class="flex justify-between" x-show="confirmDataModal.product">
                    <span class="text-slate-500 font-bold">Product / Plan:</span>
                    <span class="font-black text-slate-800" x-text="confirmDataModal.product"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500 font-bold">Recipient / Target:</span>
                    <span class="font-black text-slate-800 font-mono" x-text="confirmDataModal.target"></span>
                </div>
                <div class="flex justify-between" x-show="confirmDataModal.customerName">
                    <span class="text-slate-500 font-bold">Customer Name:</span>
                    <span class="font-black text-emerald-700" x-text="confirmDataModal.customerName"></span>
                </div>

                <!-- Fee / Discount Transparency Breakdown -->
                <div class="flex justify-between text-slate-600" x-show="confirmDataModal.faceValueNgn && confirmDataModal.faceValueNgn != confirmDataModal.amountNgn">
                    <span class="font-bold">Recharge / Face Value:</span>
                    <span class="font-bold font-mono" x-text="'₦' + Number(confirmDataModal.faceValueNgn).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                </div>
                <div class="flex justify-between text-blue-600" x-show="confirmDataModal.feeNgn > 0">
                    <span class="font-bold" x-text="confirmDataModal.feeLabel || 'Service Fee:'"></span>
                    <span class="font-black font-mono" x-text="'+₦' + Number(confirmDataModal.feeNgn).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                </div>
                <div class="flex justify-between text-emerald-600" x-show="confirmDataModal.discountNgn > 0">
                    <span class="font-bold">Instant Discount:</span>
                    <span class="font-black font-mono" x-text="'-₦' + Number(confirmDataModal.discountNgn).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                </div>

                <div class="flex justify-between pt-2 border-t border-slate-200">
                    <span class="text-slate-700 font-black">Total Customer Charge:</span>
                    <span class="text-base font-black text-[#0d9488]" x-text="'₦' + Number(confirmDataModal.amountNgn).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                </div>
                <div class="flex justify-between text-[11px]">
                    <span class="text-slate-500">Wallet Debit (USD):</span>
                    <span class="font-bold text-slate-700 font-mono" x-text="'$' + (confirmDataModal.amountNgn / {{ $rate }}).toFixed(4) + ' USD'"></span>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-2.5 pt-2">
                <button type="button" 
                        @click="submitFinalPurchase()" 
                        :disabled="isSubmitting"
                        class="flex-1 py-3.5 rounded-xl bg-[#0d9488] hover:bg-[#0f766e] text-white font-black text-xs uppercase tracking-wider shadow-lg shadow-teal-600/30 transition active:scale-95 flex items-center justify-center gap-2">
                    <span x-show="!isSubmitting">Confirm &amp; Pay Now</span>
                    <span x-show="isSubmitting">Processing Order...</span>
                </button>
                <button type="button" @click="modalOpen = false" class="px-5 py-3.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-black text-xs uppercase tracking-wider transition">
                    Cancel
                </button>
            </div>
        </div>
    </div>

</div>

<!-- Alpine Logic -->
<script>
function utilitiesApp() {
    return {
        activeTab: '{{ $activeTab }}',
        modalOpen: false,
        isSubmitting: false,
        verifyingMeter: false,
        verifyingBetting: false,
        dataValidityTab: 'all',
        dataSearch: '',

        airtime: {
            network: 'mtn',
            phone: '{{ $user->phone_number ?? "" }}',
            amount: 500,
        },

        dataForm: {
            network: 'mtn',
            phone: '{{ $user->phone_number ?? "" }}',
            planId: '',
            planName: '',
            amountNgn: 0,
        },

        electricity: {
            disco: 'ikeja-electric',
            meterType: 'prepaid',
            meterNumber: '',
            customerName: '',
            address: '',
            verified: false,
            errorMessage: '',
            amount: 2000,
            phone: '{{ $user->phone_number ?? "" }}',
        },

        betting: {
            platform: 'sportybet',
            customerId: '',
            customerName: '',
            verified: false,
            errorMessage: '',
            amount: 1000,
        },

        plansCatalog: {
            mtn: @json($mtnPlans),
            airtel: @json($airtelPlans),
            glo: @json($gloPlans),
            '9mobile': @json($nineMobilePlans),
        },

        pricingSettings: @json($pricingSettings ?? []),

        confirmDataModal: {
            title: '',
            service: '',
            network: '',
            product: '',
            target: '',
            customerName: '',
            amountNgn: 0,
            actionType: '',
        },

        get currentPlans() {
            return this.plansCatalog[this.dataForm.network] || [];
        },

        getPlanDurationCategory(plan) {
            const text = (((plan.validity || '') + ' ' + (plan.name || ''))).toLowerCase();
            if (text.includes('7day') || text.includes('7 day') || text.includes('7-20') || 
                text.includes('14day') || text.includes('14 day') || text.includes('weekly')) {
                return 'weekly';
            }
            if (text.includes('30 day') || text.includes('30day') || text.includes('30d') || 
                text.includes('month') || text.includes('60 day') || text.includes('90 day') || 
                text.includes('120 day') || text.includes('year') || text.includes('sme') || 
                text.includes('corporate') || text.includes('30')) {
                return 'monthly';
            }
            if (text.includes('1 day') || text.includes('1day') || text.includes('2 day') || 
                text.includes('2day') || text.includes('3 day') || text.includes('3day') || 
                text.includes('daily') || text.includes('day') || text.includes('night') || 
                text.includes('24h')) {
                return 'daily';
            }
            return 'monthly';
        },

        getTabCount(tab) {
            if (!this.currentPlans || this.currentPlans.length === 0) return 0;
            if (tab === 'all') return this.currentPlans.length;
            return this.currentPlans.filter(p => this.getPlanDurationCategory(p) === tab).length;
        },

        get filteredPlans() {
            let plans = this.currentPlans || [];
            if (this.dataValidityTab !== 'all') {
                plans = plans.filter(p => this.getPlanDurationCategory(p) === this.dataValidityTab);
            }
            if (this.dataSearch) {
                const q = this.dataSearch.toLowerCase().trim();
                plans = plans.filter(p => 
                    (p.name && p.name.toLowerCase().includes(q)) || 
                    (p.size && p.size.toLowerCase().includes(q)) ||
                    (p.type && p.type.toLowerCase().includes(q)) ||
                    (p.validity && p.validity.toLowerCase().includes(q))
                );
            }
            return plans;
        },

        normalizePhoneNumber(phone) {
            if (!phone) return '';
            let clean = String(phone).replace(/[^0-9]/g, '');
            if (clean.startsWith('234') && clean.length >= 13) {
                clean = '0' + clean.substring(3);
            } else if ((clean.startsWith('7') || clean.startsWith('8') || clean.startsWith('9')) && clean.length === 10) {
                clean = '0' + clean;
            }
            return clean;
        },

        detectCarrier(phone) {
            const clean = this.normalizePhoneNumber(phone);
            if (!clean || clean.length < 4) return null;
            
            const p5 = clean.substring(0, 5);
            if (['07025', '07026'].includes(p5)) {
                return { key: 'mtn', name: 'MTN', color: 'bg-yellow-400 text-slate-950 ring-yellow-400/40' };
            }
            
            const p4 = clean.substring(0, 4);
            if (['0803','0806','0703','0706','0813','0816','0810','0814','0903','0906','0913','0916','0704','0702'].includes(p4)) {
                return { key: 'mtn', name: 'MTN', color: 'bg-yellow-400 text-slate-950 ring-yellow-400/40' };
            }
            if (['0802','0808','0708','0812','0701','0902','0901','0904','0907','0912','0911'].includes(p4)) {
                return { key: 'airtel', name: 'Airtel', color: 'bg-red-600 text-white ring-red-500/40' };
            }
            if (['0805','0807','0705','0815','0811','0905','0915'].includes(p4)) {
                return { key: 'glo', name: 'Glo', color: 'bg-emerald-600 text-white ring-emerald-500/40' };
            }
            if (['0809','0817','0818','0909','0908'].includes(p4)) {
                return { key: '9mobile', name: '9mobile', color: 'bg-emerald-950 text-lime-400 ring-lime-500/40' };
            }
            return null;
        },

        autoDetectNetwork(phone, formType) {
            // Handled reactively by detectCarrier
        },

        switchNetwork(newNetwork, formType) {
            if (formType === 'airtime') {
                this.airtime.network = newNetwork;
            } else if (formType === 'data') {
                this.dataForm.network = newNetwork;
                this.fetchPlans(newNetwork);
            }
        },

        fetchPlans(net) {
            this.dataForm.planId = '';
            this.dataForm.planName = '';
            this.dataForm.amountNgn = 0;
            if (!this.plansCatalog[net] || this.plansCatalog[net].length === 0) {
                fetch(`/api/utilities/plans?network=${net}`)
                    .then(r => r.json())
                    .then(data => {
                        if (data.success && data.plans) {
                            this.plansCatalog[net] = data.plans;
                        }
                    }).catch(() => {});
            }
        },

        getBettingPlaceholder() {
            if (this.betting.platform === 'sportybet' || this.betting.platform === 'msport') {
                return 'Registered Phone Number (e.g. 08012345678)';
            }
            if (this.betting.platform === 'bet9ja') {
                return 'Bet9ja User ID (e.g. 10482941)';
            }
            if (this.betting.platform === '1xbet') {
                return '1xBet Account ID (e.g. 9847291)';
            }
            return 'Enter Account / User ID (e.g. 10482941)';
        },

        getBettingHelperText() {
            if (this.betting.platform === 'sportybet' || this.betting.platform === 'msport') {
                return 'For SportyBet & MSport, enter your registered 11-digit phone number.';
            }
            if (this.betting.platform === 'bet9ja') {
                return 'Enter your numerical Bet9ja User ID (found in your Bet9ja profile).';
            }
            return 'Enter your gaming account identifier to verify the account owner.';
        },

        selectDataPlan(plan) {
            this.dataForm.planId = plan.id;
            this.dataForm.planName = plan.name || plan.size;
            this.dataForm.amountNgn = plan.price_ngn || plan.price;
        },

        async verifyElectricityMeter() {
            if (!this.electricity.disco) {
                this.electricity.errorMessage = 'Please choose an Electricity Provider (DisCo) first.';
                return false;
            }
            if (!this.electricity.meterNumber || this.electricity.meterNumber.trim().length < 6) {
                this.electricity.errorMessage = 'Please enter a valid meter number (at least 6 digits).';
                return false;
            }

            this.verifyingMeter = true;
            this.electricity.errorMessage = '';
            try {
                const res = await fetch('{{ route("utilities.api.verify-meter") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        disco: this.electricity.disco,
                        meter_number: this.electricity.meterNumber,
                        meter_type: this.electricity.meterType,
                    }),
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    this.electricity.customerName = data.customer_name || data.name || 'Verified Customer';
                    this.electricity.address = data.address || '';
                    this.electricity.verified = true;
                    this.electricity.errorMessage = '';
                    return true;
                } else {
                    this.electricity.verified = false;
                    this.electricity.customerName = '';
                    this.electricity.address = '';
                    this.electricity.errorMessage = data.message || 'Could not verify meter number. Please check the meter number and provider.';
                    return false;
                }
            } catch (e) {
                this.electricity.verified = false;
                this.electricity.errorMessage = 'Verification request failed. Please check your internet connection.';
                return false;
            } finally {
                this.verifyingMeter = false;
            }
        },

        async verifyBettingAccount() {
            if (!this.betting.platform) {
                this.betting.errorMessage = 'Please select a Gaming Platform.';
                return false;
            }
            if (!this.betting.customerId || this.betting.customerId.trim().length < 3) {
                this.betting.errorMessage = 'Please enter your Betting Account User ID.';
                return false;
            }

            this.verifyingBetting = true;
            this.betting.errorMessage = '';
            try {
                const res = await fetch('{{ route("utilities.api.verify-betting") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        platform: this.betting.platform,
                        customer_id: this.betting.customerId,
                    }),
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    this.betting.customerName = data.customer_name || data.name || 'Verified Bettor';
                    this.betting.verified = true;
                    this.betting.errorMessage = '';
                    return true;
                } else {
                    this.betting.verified = false;
                    this.betting.customerName = '';
                    this.betting.errorMessage = data.message || 'Could not verify betting account ID.';
                    return false;
                }
            } catch (e) {
                this.betting.verified = false;
                this.betting.errorMessage = 'Verification request failed. Please check your internet connection.';
                return false;
            } finally {
                this.verifyingBetting = false;
            }
        },

        confirmAirtime() {
            const cleanPhone = this.normalizePhoneNumber(this.airtime.phone);
            if (!cleanPhone || cleanPhone.length < 10 || cleanPhone.length > 11) {
                alert('Please enter a valid 11-digit Nigerian phone number (e.g. 08012345678).');
                return;
            }
            const detected = this.detectCarrier(cleanPhone);
            if (detected && detected.key !== this.airtime.network) {
                const ok = confirm(`Network Notice:\n\nThe phone number (${cleanPhone}) looks like an ${detected.name} line, but you have selected ${this.airtime.network.toUpperCase()}.\n\nClick OK to proceed anyway, or Cancel to switch to ${detected.name}.`);
                if (!ok) {
                    this.switchNetwork(detected.key, 'airtime');
                    return;
                }
            }
            if (!this.airtime.amount || this.airtime.amount < 50) {
                alert('Please enter a valid amount (Min ₦50).');
                return;
            }

            const faceValue = Number(this.airtime.amount);
            const discountPercent = Number(this.pricingSettings.utility_airtime_discount_percent ?? {{ $airtimeDiscount }});
            const feeFixed = Number(this.pricingSettings.utility_airtime_fee_fixed ?? 0);
            const feePercent = Number(this.pricingSettings.utility_airtime_fee_percent ?? 0);
            const discountNgn = Math.round(((faceValue * discountPercent) / 100) * 100) / 100;
            const feeNgn = Math.round((feeFixed + ((faceValue * feePercent) / 100)) * 100) / 100;
            const totalPayable = Math.max(0, faceValue - discountNgn + feeNgn);

            this.confirmDataModal = {
                title: 'Confirm Airtime Purchase',
                service: 'Airtime Top-Up',
                network: this.airtime.network.toUpperCase(),
                product: 'Instant VTU Airtime',
                target: cleanPhone,
                customerName: '',
                faceValueNgn: faceValue,
                feeNgn: feeNgn,
                feeLabel: 'Service Fee:',
                discountNgn: discountNgn,
                amountNgn: totalPayable,
                actionType: 'airtime',
            };
            this.modalOpen = true;
        },

        confirmData() {
            const cleanPhone = this.normalizePhoneNumber(this.dataForm.phone);
            if (!cleanPhone || cleanPhone.length < 10 || cleanPhone.length > 11) {
                alert('Please enter a valid 11-digit Nigerian phone number (e.g. 08012345678).');
                return;
            }
            const detected = this.detectCarrier(cleanPhone);
            if (detected && detected.key !== this.dataForm.network) {
                const ok = confirm(`Network Notice:\n\nThe phone number (${cleanPhone}) looks like an ${detected.name} line, but you have selected ${this.dataForm.network.toUpperCase()}.\n\nClick OK to proceed anyway, or Cancel to switch to ${detected.name}.`);
                if (!ok) {
                    this.switchNetwork(detected.key, 'data');
                    return;
                }
            }
            if (!this.dataForm.planId) {
                alert('Please select a data bundle plan.');
                return;
            }

            const sellingPrice = Number(this.dataForm.amountNgn);

            this.confirmDataModal = {
                title: 'Confirm Data Bundle Purchase',
                service: 'Data Bundle',
                network: this.dataForm.network.toUpperCase(),
                product: this.dataForm.planName,
                target: cleanPhone,
                customerName: '',
                faceValueNgn: sellingPrice,
                feeNgn: 0,
                feeLabel: '',
                discountNgn: 0,
                amountNgn: sellingPrice,
                actionType: 'data',
            };
            this.modalOpen = true;
        },

        async confirmElectricity() {
            if (!this.electricity.disco) {
                this.electricity.errorMessage = 'Please choose an Electricity Provider (DisCo).';
                return;
            }
            if (!this.electricity.meterNumber || this.electricity.meterNumber.trim().length < 6) {
                this.electricity.errorMessage = 'Please enter a valid meter number.';
                return;
            }
            if (!this.electricity.amount || this.electricity.amount < 500) {
                alert('Please enter a valid amount (Min ₦500).');
                return;
            }

            // Auto-verify if user hasn't clicked verify button
            if (!this.electricity.verified) {
                const ok = await this.verifyElectricityMeter();
                if (!ok) {
                    return;
                }
            }

            const rechargeAmount = Number(this.electricity.amount);
            const feeMethod = this.pricingSettings.utility_electricity_fee_method || 'fixed';
            let feeNgn = 0;
            if (feeMethod === 'fixed') {
                feeNgn = Number(this.pricingSettings.utility_electricity_fee_fixed ?? 100);
            } else if (feeMethod === 'percent') {
                feeNgn = Math.round(((rechargeAmount * Number(this.pricingSettings.utility_electricity_fee_percent ?? 0)) / 100) * 100) / 100;
            }
            const totalCustomerCharge = rechargeAmount + feeNgn;

            this.confirmDataModal = {
                title: 'Confirm Electricity Payment',
                service: 'Electricity Recharge',
                network: this.electricity.disco.toUpperCase(),
                product: 'Meter (' + this.electricity.meterType.toUpperCase() + ')',
                target: this.electricity.meterNumber,
                customerName: this.electricity.customerName || 'Verified Customer',
                faceValueNgn: rechargeAmount,
                feeNgn: feeNgn,
                feeLabel: 'Convenience Fee:',
                discountNgn: 0,
                amountNgn: totalCustomerCharge,
                actionType: 'electricity',
            };
            this.modalOpen = true;
        },

        async confirmBetting() {
            if (!this.betting.platform) {
                this.betting.errorMessage = 'Please select a Gaming Platform.';
                return;
            }
            if (!this.betting.customerId || this.betting.customerId.trim().length < 3) {
                this.betting.errorMessage = 'Please enter your Betting Account User ID.';
                return;
            }
            if (!this.betting.amount || this.betting.amount < 100) {
                alert('Please enter a valid funding amount (Min ₦100).');
                return;
            }

            // Auto-verify if user hasn't clicked verify button
            if (!this.betting.verified) {
                const ok = await this.verifyBettingAccount();
                if (!ok) {
                    return;
                }
            }

            const depositAmount = Number(this.betting.amount);
            const feeMethod = this.pricingSettings.utility_betting_fee_method || 'percent';
            let feeNgn = 0;
            if (feeMethod === 'percent') {
                feeNgn = Math.round(((depositAmount * Number(this.pricingSettings.utility_betting_fee_percent ?? 1.5)) / 100) * 100) / 100;
            } else if (feeMethod === 'fixed') {
                feeNgn = Number(this.pricingSettings.utility_betting_fee_fixed ?? 0);
            }
            const totalCustomerCharge = depositAmount + feeNgn;

            this.confirmDataModal = {
                title: 'Confirm Betting Wallet Top-Up',
                service: 'Gaming Wallet Funding',
                network: this.betting.platform.toUpperCase(),
                product: 'Wallet Deposit',
                target: this.betting.customerId,
                customerName: this.betting.customerName || 'Verified Bettor',
                faceValueNgn: depositAmount,
                feeNgn: feeNgn,
                feeLabel: 'Processing Fee (' + (feeMethod === 'percent' ? (this.pricingSettings.utility_betting_fee_percent ?? '1.5') + '%' : 'Fixed') + '):',
                discountNgn: 0,
                amountNgn: totalCustomerCharge,
                actionType: 'betting',
            };
            this.modalOpen = true;
        },

        submitFinalPurchase() {
            this.isSubmitting = true;
            // Create a form and submit natively with CSRF protection
            const form = document.createElement('form');
            form.method = 'POST';

            let actionUrl = '';
            let payload = {};

            if (this.confirmDataModal.actionType === 'airtime') {
                actionUrl = '{{ route("utilities.purchase.airtime") }}';
                payload = {
                    network: this.airtime.network,
                    phone_number: this.normalizePhoneNumber(this.airtime.phone),
                    amount: this.airtime.amount,
                };
            } else if (this.confirmDataModal.actionType === 'data') {
                actionUrl = '{{ route("utilities.purchase.data") }}';
                payload = {
                    network: this.dataForm.network,
                    phone_number: this.normalizePhoneNumber(this.dataForm.phone),
                    plan_id: this.dataForm.planId,
                };
            } else if (this.confirmDataModal.actionType === 'electricity') {
                actionUrl = '{{ route("utilities.pay.electricity") }}';
                payload = {
                    disco: this.electricity.disco,
                    meter_number: this.electricity.meterNumber,
                    meter_type: this.electricity.meterType,
                    amount: this.electricity.amount,
                    customer_name: this.electricity.customerName,
                    phone_number: this.electricity.phone,
                };
            } else if (this.confirmDataModal.actionType === 'betting') {
                actionUrl = '{{ route("utilities.fund.betting") }}';
                payload = {
                    platform: this.betting.platform,
                    customer_id: this.betting.customerId,
                    customer_name: this.betting.customerName,
                    amount: this.betting.amount,
                };
            }

            form.action = actionUrl;

            // CSRF
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);

            // Payload fields
            for (const [key, value] of Object.entries(payload)) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                form.appendChild(input);
            }

            document.body.appendChild(form);
            form.submit();
        }
    };
}
</script>
@endsection
