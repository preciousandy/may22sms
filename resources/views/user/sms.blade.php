@extends('layouts.dashboard')

@section('page_title', 'SMS Verifications')

@section('dashboard_content')
@php
    $walletBal = (float) (auth()->user()->walletBalance ?? 0);
    $activeOrder = $orders->first(fn($o) => in_array(strtoupper($o->status), ['PENDING', 'WAITING'], true));
@endphp

<!-- Toast Alert Container -->
<div id="toastNotification" class="fixed top-5 right-5 z-50 transform transition-all duration-300 translate-y-[-100px] opacity-0 pointer-events-none">
    <div class="flex items-center gap-3 px-5 py-3.5 rounded-2xl shadow-xl border text-sm font-bold text-white bg-gray-900" id="toastInner">
        <span id="toastIcon">✨</span>
        <span id="toastMessage">Notification</span>
    </div>
</div>

<!-- If User has an Active Pending Order, Show a Hero Live Verification Card -->
@if($activeOrder)
@php
    $activeCreatedTs = optional($activeOrder->created_at)->getTimestamp() ?? time();
    $activeProviderLabel = strtolower((string)($activeOrder->provider ?? '')) === 'daisysms' ? 'USA Server' : 'Global Server';
@endphp
<div class="mb-8 rounded-3xl p-6 sm:p-8 bg-gradient-to-br from-teal-900 via-slate-900 to-emerald-950 text-white shadow-xl border border-teal-500/30 relative overflow-hidden"
     x-data="smsChecker({{ $activeOrder->id }}, @js($activeOrder->status), {{ $activeCreatedTs }})" 
     x-init="initChecker()">
    <!-- Ambient Glow Background -->
    <div class="absolute -right-16 -top-16 w-64 h-64 bg-teal-500/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -left-16 -bottom-16 w-64 h-64 bg-emerald-500/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
        <div>
            <div class="flex items-center gap-3 mb-2 flex-wrap">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                    Live Verification in Progress
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-white/10 text-gray-300">
                    {{ $activeProviderLabel }}
                </span>
            </div>
            <h3 class="text-2xl font-black tracking-tight text-white capitalize flex items-center gap-2">
                <span>{{ $activeOrder->product }}</span>
                <span class="text-xs px-2.5 py-1 rounded-lg bg-teal-800/80 text-teal-200 uppercase font-mono font-bold">{{ $activeOrder->country }}</span>
            </h3>
            <p class="text-xs text-teal-200/80 mt-1">Copy this phone number into the app to request your verification code.</p>
        </div>

        <!-- Phone Number & Live OTP Display -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 bg-white/5 backdrop-blur-md p-4 rounded-2xl border border-white/10">
            <!-- Number Box -->
            <div class="flex items-center justify-between sm:justify-start gap-4">
                <div>
                    <span class="text-[10px] uppercase font-bold tracking-wider text-gray-400 block">Phone Number</span>
                    <span class="text-xl sm:text-2xl font-mono font-black text-white select-all">{{ $activeOrder->phone_number }}</span>
                </div>
                <button type="button" 
                        onclick="copyTextToClipboard('{{ $activeOrder->phone_number }}', 'Phone number copied!')"
                        class="p-2.5 rounded-xl bg-teal-500 hover:bg-teal-400 text-gray-950 font-black transition flex items-center gap-1 text-xs shadow-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                    <span>Copy</span>
                </button>
            </div>

            <div class="hidden sm:block w-px h-10 bg-white/10"></div>

            <!-- OTP Arrival Box -->
            <div class="flex items-center justify-between sm:justify-start gap-4 min-w-[200px]">
                <template x-if="status === 'RECEIVED' || status === 'received' || status === 'completed' || status === 'COMPLETED'">
                    <div class="flex items-center gap-3 bg-emerald-500/20 border border-emerald-500/40 p-2.5 rounded-xl w-full justify-between animate-bounce">
                        <div>
                            <span class="text-[10px] uppercase font-black tracking-wider text-emerald-300 block">OTP Code</span>
                            <span class="text-2xl font-mono font-black text-emerald-200 select-all tracking-widest" x-text="smsCode"></span>
                        </div>
                        <button type="button" @click="copyCode()" class="px-3 py-1.5 bg-emerald-400 hover:bg-emerald-300 text-emerald-950 rounded-lg text-xs font-black transition">
                            Copy
                        </button>
                    </div>
                </template>

                <template x-if="status === 'PENDING' || status === 'pending' || status === 'WAITING' || status === 'waiting'">
                    <div class="flex items-center gap-3">
                        <svg class="animate-spin h-5 w-5 text-teal-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <div>
                            <span class="text-xs font-bold text-teal-200" x-text="pollingMsg">Waiting for SMS...</span>
                            <span x-show="showCountdown" class="block text-[10px] text-gray-400 font-mono" x-text="countdownText"></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Live Card Actions (Cancel / Status) -->
    <div class="mt-4 pt-4 border-t border-white/10 flex flex-wrap items-center justify-between gap-3 text-xs">
        <div class="text-gray-400 flex items-center gap-2">
            <span>Order #{{ $activeOrder->id }}</span>
            <span>•</span>
            <span>Charge: <strong class="text-white">{{ $currencySymbol ?? '$' }}{{ number_format($activeOrder->price, 2) }}</strong></span>
            <span>•</span>
            <span class="text-teal-300">Auto-refunded if no SMS arrives within 20 mins</span>
        </div>

        <div class="flex items-center gap-2">
            <template x-if="canCancel && !canceling">
                <button type="button" @click="handleCancel()" class="px-3 py-1.5 rounded-lg bg-rose-500/20 hover:bg-rose-500/30 text-rose-300 border border-rose-500/40 font-bold transition flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span>Cancel & Refund</span>
                </button>
            </template>
            <span x-show="canceling" class="text-gray-400">Canceling order...</span>
            <span x-show="cancelError" class="text-rose-400 font-semibold" x-text="cancelError"></span>
        </div>
    </div>
</div>
@endif

<!-- Main Purchase Widget Card -->
<div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mb-8">
    <!-- Header with Balance & Live Status -->
    <div class="px-6 py-5 border-b border-gray-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-gradient-to-r from-gray-50/80 via-white to-teal-50/30">
        <div>
            <h2 class="text-xl font-black text-gray-900 flex items-center gap-2.5">
                <span>Buy Virtual Number</span>
                <span class="bg-emerald-100 text-emerald-800 text-[10px] font-black px-2.5 py-0.5 rounded-full uppercase tracking-wider">Instant OTP</span>
            </h2>
            <p class="text-xs text-gray-500 mt-1">Select an app or search below to get a dedicated verification phone number instantly.</p>
        </div>

        <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
            <!-- Quick Tutorial / Guide Link -->
            <a href="/tutorials?tab=sms" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-2xl bg-teal-50 hover:bg-teal-100 text-[#0d9488] border border-teal-200/80 text-xs font-black transition">
                <span>🎓</span>
                <span>How to Use</span>
            </a>

            <!-- WhatsApp Direct Support -->
            <a href="https://wa.me/16573369673?text=Hello%20May22SMS%20Support%2C%20I%20need%20help%20with%20SMS%20verification." target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-2xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200/80 text-xs font-black transition">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.669-.699c.969.54 1.771.82 2.788.82 3.185 0 5.772-2.587 5.773-5.767.001-3.182-2.585-5.805-5.77-5.805zm3.376 8.21c-.14.394-.712.729-1.02.775-.297.045-.67.073-2.138-.535-1.748-.724-2.859-2.5-2.946-2.617-.087-.116-.708-.941-.708-1.794 0-.853.447-1.272.607-1.447.16-.175.349-.219.465-.219.117 0 .233.001.335.006.107.005.251-.041.393.3.146.35.496 1.21.54 1.297.044.088.073.19.015.306-.059.117-.088.19-.175.292-.087.102-.185.228-.264.306-.088.087-.179.182-.077.357.102.175.454.748.974 1.211.67.597 1.235.782 1.41.87.175.088.277.073.38-.044.102-.117.437-.51.554-.685.117-.175.234-.146.394-.088.16.059 1.02.481 1.195.568.175.088.291.131.335.204.044.073.044.423-.096.817z"/>
                </svg>
                <span>WhatsApp Support</span>
            </a>

            <!-- Wallet Balance Badge with Quick Top-Up -->
            <div class="flex items-center gap-2 bg-gray-100/80 hover:bg-gray-100 border border-gray-200/60 px-3.5 py-1.5 rounded-2xl transition">
                <span class="text-xs text-gray-500 font-medium">Wallet:</span>
                <span class="text-sm font-black text-gray-900">{{ $currencySymbol ?? '$' }}{{ number_format($walletBal, 2) }}</span>
                <a href="{{ url('/funding') }}" class="text-xs font-extrabold text-[#0d9488] hover:text-teal-700 ml-1 flex items-center gap-0.5" title="Add funds to wallet">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                    Top up
                </a>
            </div>
        </div>
    </div>

    <div class="p-6 md:p-8 space-y-8">
        <!-- 1. Destination / Server Pill Selector -->
        <div>
            <div class="flex items-center justify-between mb-3">
                <label class="text-xs font-black uppercase tracking-wider text-gray-500">1. Destination / Server</label>
                <span class="text-[11px] text-gray-400">Choose server location</span>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-w-2xl">
                <!-- Daisy SMS Option (USA) -->
                @if(($daisysmsEnabled ?? true))
                <label class="relative cursor-pointer group">
                    <input type="radio" name="sms_provider_choice" value="daisysms" class="peer sr-only" id="providerDaisy" checked>
                    <div class="rounded-2xl border-2 border-gray-200 p-4 peer-checked:border-[#0d9488] peer-checked:bg-teal-50/40 hover:border-teal-300 transition flex items-center justify-between shadow-sm">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">🇺🇸</span>
                            <div>
                                <h4 class="font-extrabold text-gray-900 text-sm flex items-center gap-1.5">
                                    <span>United States (USA)</span>
                                    <span class="bg-amber-100 text-amber-800 text-[9px] font-black px-1.5 py-0.5 rounded">High Success</span>
                                </h4>
                                <p class="text-[11px] text-gray-500">Dedicated physical SIM lines for US verifications.</p>
                            </div>
                        </div>
                        <div class="w-5 h-5 rounded-full border-2 border-gray-300 peer-checked:border-[#0d9488] peer-checked:bg-[#0d9488] flex items-center justify-center">
                            <div class="w-2 h-2 rounded-full bg-white opacity-0 peer-checked:opacity-100"></div>
                        </div>
                    </div>
                </label>
                @endif

                <!-- Hero SMS Option (Global) -->
                @if(($herosmsEnabled ?? true))
                <label class="relative cursor-pointer group">
                    <input type="radio" name="sms_provider_choice" value="herosms" class="peer sr-only" id="providerHero" {{ !($daisysmsEnabled ?? true) ? 'checked' : '' }}>
                    <div class="rounded-2xl border-2 border-gray-200 p-4 peer-checked:border-[#0d9488] peer-checked:bg-teal-50/40 hover:border-teal-300 transition flex items-center justify-between shadow-sm">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">🌍</span>
                            <div>
                                <h4 class="font-extrabold text-gray-900 text-sm flex items-center gap-1.5">
                                    <span>Global Numbers (50+ Countries)</span>
                                </h4>
                                <p class="text-[11px] text-gray-500">UK, Canada, Europe, Asia, Africa & more.</p>
                            </div>
                        </div>
                        <div class="w-5 h-5 rounded-full border-2 border-gray-300 peer-checked:border-[#0d9488] peer-checked:bg-[#0d9488] flex items-center justify-center">
                            <div class="w-2 h-2 rounded-full bg-white opacity-0 peer-checked:opacity-100"></div>
                        </div>
                    </div>
                </label>
                @endif
            </div>
        </div>

        <!-- 2. Country Selector (Visible when Global is chosen) -->
        <div id="countrySection" class="hidden transition-all duration-300">
            <div class="flex items-center justify-between mb-3">
                <label class="text-xs font-black uppercase tracking-wider text-gray-500">2. Select Country</label>
                <span id="countryNotice" class="text-xs font-semibold text-[#0d9488]"></span>
            </div>

            <!-- Quick Country Chips for Top Regions -->
            <div class="flex items-center gap-2 overflow-x-auto pb-2 mb-3 scrollbar-none" id="popularCountriesPills">
                <button type="button" onclick="selectQuickCountry('united kingdom')" class="quick-country-btn px-3 py-1.5 rounded-xl border border-gray-200 hover:border-teal-400 bg-gray-50 text-xs font-bold text-gray-700 whitespace-nowrap transition flex items-center gap-1.5">
                    <span>🇬🇧</span> UK
                </button>
                <button type="button" onclick="selectQuickCountry('canada')" class="quick-country-btn px-3 py-1.5 rounded-xl border border-gray-200 hover:border-teal-400 bg-gray-50 text-xs font-bold text-gray-700 whitespace-nowrap transition flex items-center gap-1.5">
                    <span>🇨🇦</span> Canada
                </button>
                <button type="button" onclick="selectQuickCountry('nigeria')" class="quick-country-btn px-3 py-1.5 rounded-xl border border-gray-200 hover:border-teal-400 bg-gray-50 text-xs font-bold text-gray-700 whitespace-nowrap transition flex items-center gap-1.5">
                    <span>🇳🇬</span> Nigeria
                </button>
                <button type="button" onclick="selectQuickCountry('kenya')" class="quick-country-btn px-3 py-1.5 rounded-xl border border-gray-200 hover:border-teal-400 bg-gray-50 text-xs font-bold text-gray-700 whitespace-nowrap transition flex items-center gap-1.5">
                    <span>🇰🇪</span> Kenya
                </button>
                <button type="button" onclick="selectQuickCountry('south africa')" class="quick-country-btn px-3 py-1.5 rounded-xl border border-gray-200 hover:border-teal-400 bg-gray-50 text-xs font-bold text-gray-700 whitespace-nowrap transition flex items-center gap-1.5">
                    <span>🇿🇦</span> South Africa
                </button>
                <button type="button" onclick="selectQuickCountry('germany')" class="quick-country-btn px-3 py-1.5 rounded-xl border border-gray-200 hover:border-teal-400 bg-gray-50 text-xs font-bold text-gray-700 whitespace-nowrap transition flex items-center gap-1.5">
                    <span>🇩🇪</span> Germany
                </button>
                <button type="button" onclick="selectQuickCountry('france')" class="quick-country-btn px-3 py-1.5 rounded-xl border border-gray-200 hover:border-teal-400 bg-gray-50 text-xs font-bold text-gray-700 whitespace-nowrap transition flex items-center gap-1.5">
                    <span>🇫🇷</span> France
                </button>
                <button type="button" onclick="selectQuickCountry('india')" class="quick-country-btn px-3 py-1.5 rounded-xl border border-gray-200 hover:border-teal-400 bg-gray-50 text-xs font-bold text-gray-700 whitespace-nowrap transition flex items-center gap-1.5">
                    <span>🇮🇳</span> India
                </button>
            </div>

            <div class="max-w-md">
                <select id="countrySelect" class="block w-full px-4 py-3 border-2 border-gray-200 rounded-2xl focus:ring-0 focus:border-[#0d9488] transition text-sm font-semibold bg-white">
                    @if(isset($countries) && $countries->count() > 0)
                        @foreach($countries as $country)
                            <option value="{{ strtolower($country->slug) }}">{{ $country->name }}</option>
                        @endforeach
                    @else
                        <option value="usa">United States</option>
                    @endif
                </select>
            </div>
        </div>

        <!-- 3. Popular Apps 1-Click Grid -->
        <div>
            <div class="flex items-center justify-between mb-3">
                <label class="text-xs font-black uppercase tracking-wider text-gray-500">3. Popular Services (1-Click Select)</label>
                <span class="text-[11px] text-teal-700 font-bold" id="serviceLoadingIndicator">Loading pricing...</span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3" id="popularAppsGrid">
                <!-- App Card: WhatsApp -->
                <button type="button" onclick="selectAppCard('whatsapp')" id="appCard-whatsapp" class="app-card group relative p-3.5 rounded-2xl border-2 border-gray-200 hover:border-[#0d9488] hover:bg-teal-50/30 transition flex flex-col items-center text-center bg-white shadow-sm">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl mb-2 group-hover:scale-110 transition">
                        💬
                    </div>
                    <span class="text-xs font-extrabold text-gray-900 block truncate w-full">WhatsApp</span>
                    <span class="text-[11px] font-black text-[#0d9488] mt-1 app-price-badge" data-service="whatsapp">--</span>
                    <span class="text-[9px] text-gray-400 font-medium app-count-badge" data-service="whatsapp"></span>
                </button>

                <!-- App Card: Telegram -->
                <button type="button" onclick="selectAppCard('telegram')" id="appCard-telegram" class="app-card group relative p-3.5 rounded-2xl border-2 border-gray-200 hover:border-[#0d9488] hover:bg-teal-50/30 transition flex flex-col items-center text-center bg-white shadow-sm">
                    <div class="w-10 h-10 rounded-xl bg-sky-100 text-sky-600 flex items-center justify-center text-xl mb-2 group-hover:scale-110 transition">
                        ✈️
                    </div>
                    <span class="text-xs font-extrabold text-gray-900 block truncate w-full">Telegram</span>
                    <span class="text-[11px] font-black text-[#0d9488] mt-1 app-price-badge" data-service="telegram">--</span>
                    <span class="text-[9px] text-gray-400 font-medium app-count-badge" data-service="telegram"></span>
                </button>

                <!-- App Card: Google -->
                <button type="button" onclick="selectAppCard('google')" id="appCard-google" class="app-card group relative p-3.5 rounded-2xl border-2 border-gray-200 hover:border-[#0d9488] hover:bg-teal-50/30 transition flex flex-col items-center text-center bg-white shadow-sm">
                    <div class="w-10 h-10 rounded-xl bg-red-100 text-red-600 flex items-center justify-center text-xl mb-2 group-hover:scale-110 transition">
                        🔍
                    </div>
                    <span class="text-xs font-extrabold text-gray-900 block truncate w-full">Google</span>
                    <span class="text-[11px] font-black text-[#0d9488] mt-1 app-price-badge" data-service="google">--</span>
                    <span class="text-[9px] text-gray-400 font-medium app-count-badge" data-service="google"></span>
                </button>

                <!-- App Card: Instagram -->
                <button type="button" onclick="selectAppCard('instagram')" id="appCard-instagram" class="app-card group relative p-3.5 rounded-2xl border-2 border-gray-200 hover:border-[#0d9488] hover:bg-teal-50/30 transition flex flex-col items-center text-center bg-white shadow-sm">
                    <div class="w-10 h-10 rounded-xl bg-pink-100 text-pink-600 flex items-center justify-center text-xl mb-2 group-hover:scale-110 transition">
                        📸
                    </div>
                    <span class="text-xs font-extrabold text-gray-900 block truncate w-full">Instagram</span>
                    <span class="text-[11px] font-black text-[#0d9488] mt-1 app-price-badge" data-service="instagram">--</span>
                    <span class="text-[9px] text-gray-400 font-medium app-count-badge" data-service="instagram"></span>
                </button>

                <!-- App Card: TikTok -->
                <button type="button" onclick="selectAppCard('tiktok')" id="appCard-tiktok" class="app-card group relative p-3.5 rounded-2xl border-2 border-gray-200 hover:border-[#0d9488] hover:bg-teal-50/30 transition flex flex-col items-center text-center bg-white shadow-sm">
                    <div class="w-10 h-10 rounded-xl bg-gray-900 text-white flex items-center justify-center text-xl mb-2 group-hover:scale-110 transition">
                        🎵
                    </div>
                    <span class="text-xs font-extrabold text-gray-900 block truncate w-full">TikTok</span>
                    <span class="text-[11px] font-black text-[#0d9488] mt-1 app-price-badge" data-service="tiktok">--</span>
                    <span class="text-[9px] text-gray-400 font-medium app-count-badge" data-service="tiktok"></span>
                </button>

                <!-- App Card: OpenAI -->
                <button type="button" onclick="selectAppCard('openai')" id="appCard-openai" class="app-card group relative p-3.5 rounded-2xl border-2 border-gray-200 hover:border-[#0d9488] hover:bg-teal-50/30 transition flex flex-col items-center text-center bg-white shadow-sm">
                    <div class="w-10 h-10 rounded-xl bg-emerald-950 text-emerald-400 flex items-center justify-center text-xl mb-2 group-hover:scale-110 transition">
                        🤖
                    </div>
                    <span class="text-xs font-extrabold text-gray-900 block truncate w-full">OpenAI / GPT</span>
                    <span class="text-[11px] font-black text-[#0d9488] mt-1 app-price-badge" data-service="openai">--</span>
                    <span class="text-[9px] text-gray-400 font-medium app-count-badge" data-service="openai"></span>
                </button>

                <!-- App Card: Tinder -->
                <button type="button" onclick="selectAppCard('tinder')" id="appCard-tinder" class="app-card group relative p-3.5 rounded-2xl border-2 border-gray-200 hover:border-[#0d9488] hover:bg-teal-50/30 transition flex flex-col items-center text-center bg-white shadow-sm">
                    <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center text-xl mb-2 group-hover:scale-110 transition">
                        🔥
                    </div>
                    <span class="text-xs font-extrabold text-gray-900 block truncate w-full">Tinder</span>
                    <span class="text-[11px] font-black text-[#0d9488] mt-1 app-price-badge" data-service="tinder">--</span>
                    <span class="text-[9px] text-gray-400 font-medium app-count-badge" data-service="tinder"></span>
                </button>

                <!-- App Card: Facebook -->
                <button type="button" onclick="selectAppCard('facebook')" id="appCard-facebook" class="app-card group relative p-3.5 rounded-2xl border-2 border-gray-200 hover:border-[#0d9488] hover:bg-teal-50/30 transition flex flex-col items-center text-center bg-white shadow-sm">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-xl mb-2 group-hover:scale-110 transition">
                        👥
                    </div>
                    <span class="text-xs font-extrabold text-gray-900 block truncate w-full">Facebook</span>
                    <span class="text-[11px] font-black text-[#0d9488] mt-1 app-price-badge" data-service="facebook">--</span>
                    <span class="text-[9px] text-gray-400 font-medium app-count-badge" data-service="facebook"></span>
                </button>
            </div>
        </div>

        <!-- 4. Search All 150+ Services -->
        <div class="relative">
            <label class="text-xs font-black uppercase tracking-wider text-gray-500 mb-2 block">Or Search Any of 150+ Services</label>
            <div class="relative">
                <input type="text" id="serviceSearch" 
                       class="block w-full px-4 py-3 pl-11 border-2 border-gray-200 rounded-2xl focus:ring-0 focus:border-[#0d9488] transition text-sm font-semibold bg-white" 
                       placeholder="🔍 Search service (e.g. PayPal, Uber, Twitter/X, Discord, Netflix, DoorDash)..." 
                       autocomplete="off">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>

            <!-- Custom Live Dropdown Results -->
            <div id="serviceSearchResults" class="hidden absolute left-0 right-0 top-full mt-2 bg-white rounded-2xl shadow-2xl border border-gray-200 z-30 max-h-60 overflow-y-auto divide-y divide-gray-100">
                <!-- Dynamically populated -->
            </div>

            <!-- Hidden native select for form state/compatibility -->
            <select id="serviceSelect" class="sr-only">
                <option value="">Select a service</option>
            </select>
        </div>

        <!-- 5. Speed / Price Tier Selection (Visible for HeroSMS) -->
        <div id="priceTierSection" class="rounded-2xl border-2 border-gray-200/80 bg-gray-50/60 p-4 sm:p-5">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">
                <div>
                    <label for="priceTierSelect" class="text-xs font-black uppercase tracking-wider text-gray-600 block">5. Speed / Price Tier <span class="text-[#0d9488]">(Higher = Faster Code)</span></label>
                    <p class="text-[11px] text-gray-500 mt-0.5">Pick a higher price tier for faster and more reliable SMS code delivery.</p>
                </div>
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white border border-teal-200 shadow-sm self-start sm:self-auto">
                    <span class="text-[11px] font-bold text-gray-500">Selected Amount:</span>
                    <span class="text-sm font-black text-[#0d9488]" id="operatorAmountDisplay">{{ $currencySymbol ?? '$' }}0.00</span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 items-start">
                <div id="priceTierContainer">
                    <select id="priceTierSelect" class="block w-full px-4 py-3 border-2 border-gray-200 rounded-2xl focus:ring-0 focus:border-[#0d9488] text-sm font-semibold bg-white transition">
                        <option value="">Standard (Default)</option>
                    </select>
                </div>

                <div class="rounded-xl bg-amber-50/90 border border-amber-200/80 px-3.5 py-2.5 text-[11px] text-amber-900 flex items-start gap-2">
                    <span class="text-sm leading-none mt-0.5">⚡</span>
                    <span>
                        <strong>Faster Code Tip:</strong> Not receiving codes on Standard? Choose a higher <strong>Speed / Price Tier</strong> — <strong>the higher the price, the faster and more reliably you will receive your code.</strong>
                    </span>
                </div>
            </div>
        </div>

        <!-- Advanced Accordion: Promo & Premium -->
        <div class="pt-1">
            <details class="group rounded-2xl border border-gray-200/80 bg-gray-50/50 p-4 transition [&_summary::-webkit-details-marker]:hidden">
                <summary class="flex items-center justify-between cursor-pointer font-bold text-xs text-gray-700 hover:text-gray-900">
                    <span class="flex items-center gap-2">
                        <span>⚙️</span>
                        <span>Advanced Options: Promo Code & Priority Routing</span>
                    </span>
                    <span class="text-gray-400 transition group-open:rotate-180">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </span>
                </summary>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 pt-4 border-t border-gray-200/60">
                    <!-- Promo Code -->
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 mb-1">Promo Code</label>
                        <input type="text" id="promoCodeInput" class="block w-full px-3.5 py-2.5 border border-gray-300 rounded-xl focus:ring-0 focus:border-[#0d9488] text-xs font-mono bg-white uppercase" placeholder="e.g. DISCOUNT10">
                        <span class="text-[10px] text-gray-400 mt-1 block">Optional discount code</span>
                    </div>

                    <!-- Premium High-Speed Routing -->
                    @if(($premiumEnabled ?? false))
                    <div class="md:col-span-2">
                        <label class="flex items-center gap-3 p-3 rounded-xl border border-teal-200 bg-teal-50/40 cursor-pointer hover:bg-teal-50 transition">
                            <input type="checkbox" id="premiumToggle" class="w-4 h-4 rounded border-gray-300 text-[#0d9488] focus:ring-[#0d9488]">
                            <div>
                                <span class="block text-xs font-bold text-gray-800">Premium Priority Routing (+{{ (float)($premiumMarkupPercent ?? 40) }}%)</span>
                                <span class="block text-[10px] text-gray-500">Optimized line priority for time-sensitive OTP verification</span>
                            </div>
                        </label>
                    </div>
                    @endif
                </div>
            </details>
        </div>

        <!-- Sticky / Prominent Bottom Action Bar -->
        <div class="rounded-3xl p-5 sm:p-6 bg-gradient-to-r from-gray-900 to-slate-900 text-white flex flex-col md:flex-row items-center justify-between gap-5 shadow-lg">
            <!-- Selected Summary -->
            <div class="flex items-center gap-4 w-full md:w-auto">
                <div class="w-12 h-12 rounded-2xl bg-white/10 flex items-center justify-center text-2xl" id="summaryAppIcon">
                    📱
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-black text-white" id="summaryAppName">Select a service</span>
                        <span class="text-xs px-2 py-0.5 rounded bg-white/15 text-gray-300 uppercase font-mono font-bold" id="summaryCountry">USA</span>
                    </div>
                    <div class="text-xs text-gray-400 mt-0.5" id="summaryStock">Choose an app above to view stock & price</div>
                </div>
            </div>

            <!-- Price & Buy Action -->
            <div class="flex items-center justify-between md:justify-end gap-5 w-full md:w-auto">
                <div class="text-right">
                    <span class="text-[10px] uppercase font-bold tracking-wider text-gray-400 block">Total Price</span>
                    <div class="text-2xl sm:text-3xl font-black text-emerald-400" id="priceDisplay">{{ $currencySymbol ?? '$' }}0.00</div>
                </div>

                <button id="buyBtn" disabled 
                        class="px-8 py-3.5 rounded-2xl font-black shadow-lg transition-all duration-200 transform hover:-translate-y-0.5 disabled:opacity-40 disabled:cursor-not-allowed text-sm sm:text-base flex items-center justify-center gap-2 bg-[#facc15] hover:bg-yellow-400 text-gray-950">
                    <span>⚡ Get Phone Number</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </button>
            </div>
        </div>

        <div id="alertMessage" class="hidden p-4 rounded-2xl text-sm font-bold"></div>
    </div>
</div>

<!-- Active & Recent Orders Section -->
<div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/60">
        <div>
            <h3 class="text-lg font-black text-gray-900">Your Virtual Numbers History</h3>
            <p class="text-xs text-gray-500">Real-time status updates, active codes, and previous orders</p>
        </div>
        <button onclick="location.reload()" class="text-xs text-[#0d9488] font-bold bg-teal-50 hover:bg-teal-100 px-3 py-2 rounded-xl transition flex items-center gap-1.5 shadow-sm">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
            Refresh List
        </button>
    </div>
    
    <div class="p-0">
        @if($orders->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50/70">
                        <tr>
                            <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Service / Country</th>
                            <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Phone Number</th>
                            <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Price</th>
                            <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">SMS OTP</th>
                            <th class="px-6 py-3.5 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100 text-sm">
                        @foreach($orders as $order)
                        @php
                            $createdTs = optional($order->created_at)->getTimestamp() ?? time();
                            $isPending = in_array(strtoupper($order->status), ['PENDING', 'WAITING'], true);
                            $isReceived = in_array(strtoupper($order->status), ['RECEIVED', 'COMPLETED', 'FINISHED'], true);
                            $isCanceled = in_array(strtoupper($order->status), ['CANCELED', 'CANCELLED', 'TIMEOUT', 'REFUNDED'], true);
                            $providerLabel = strtolower((string)($order->provider ?? '')) === 'daisysms' ? 'USA' : 'Global';
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-extrabold text-gray-900 capitalize">{{ $order->product }}</div>
                                <div class="text-xs text-gray-500 capitalize flex items-center gap-1.5 mt-0.5">
                                    <span>{{ $order->country }}</span>
                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-gray-100 text-gray-600 border border-gray-200">{{ $providerLabel }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <span class="text-base font-mono font-black text-gray-900 select-all">{{ $order->phone_number }}</span>
                                    <button type="button" onclick="copyTextToClipboard('{{ $order->phone_number }}', 'Phone number copied!')" class="p-1.5 rounded-lg bg-gray-100 hover:bg-teal-50 text-gray-600 hover:text-[#0d9488] transition" title="Copy Number">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                    </button>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-900">
                                {{ $currencySymbol ?? '$' }}{{ number_format($order->price, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($isPending)
                                    <span class="px-2.5 py-1 inline-flex text-xs font-black rounded-full bg-amber-100 text-amber-800 animate-pulse">Waiting for SMS</span>
                                @elseif($isReceived)
                                    <span class="px-2.5 py-1 inline-flex text-xs font-black rounded-full bg-emerald-100 text-emerald-800">Received</span>
                                @elseif($isCanceled)
                                    <span class="px-2.5 py-1 inline-flex text-xs font-bold rounded-full bg-red-100 text-red-700">Refunded</span>
                                @else
                                    <span class="px-2.5 py-1 inline-flex text-xs font-bold rounded-full bg-gray-100 text-gray-800">{{ $order->status }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($order->sms_code)
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono font-black text-lg text-emerald-700 tracking-wider select-all">{{ $order->sms_code }}</span>
                                        <button type="button" onclick="copyTextToClipboard('{{ $order->sms_code }}', 'SMS code copied!')" class="p-1 rounded bg-emerald-100 hover:bg-emerald-200 text-emerald-800 text-xs font-bold">
                                            Copy
                                        </button>
                                    </div>
                                @else
                                    <span class="text-gray-400 italic">--</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div x-data="smsChecker({{ $order->id }}, @js($order->status), {{ $createdTs }})" x-init="initChecker()">
                                    <template x-if="status === 'pending' || status === 'PENDING'">
                                        <div class="flex items-center justify-end gap-2">
                                            <button
                                                type="button"
                                                x-show="canCancel && !canceling"
                                                @click="handleCancel()"
                                                class="px-2.5 py-1 text-xs font-bold rounded-lg bg-rose-600 hover:bg-rose-700 text-white transition">
                                                Cancel & Refund
                                            </button>
                                            <span x-show="!canCancel" class="text-[11px] text-gray-400 font-mono" x-text="countdownText"></span>
                                        </div>
                                    </template>

                                    <div class="mt-1 flex items-center justify-end" x-data="{ reported: @js((bool)($order->is_reported ?? false)), submitting: false }">
                                        <template x-if="reported">
                                            <span class="text-[10px] font-black text-amber-700 bg-amber-50 px-2 py-0.5 rounded">🚩 Reported</span>
                                        </template>
                                        <template x-if="!reported && !['received', 'completed', 'finished'].includes((status || '').toLowerCase())">
                                            <button 
                                                type="button" 
                                                @click="
                                                    if (confirm('Report issue with this SMS order to admin?')) {
                                                        submitting = true;
                                                        fetch('/orders/report', {
                                                            method: 'POST',
                                                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                                            body: JSON.stringify({ order_type: 'sms', order_id: {{ $order->id }}, reason: 'SMS Verification issue reported from SMS page.' })
                                                        })
                                                        .then(r => r.json())
                                                        .then(data => {
                                                            submitting = false;
                                                            if (data.success) {
                                                                reported = true;
                                                                showNotification('Report submitted successfully.');
                                                            } else {
                                                                alert(data.message || 'Could not report order.');
                                                            }
                                                        })
                                                        .catch(() => { submitting = false; alert('Error submitting report.'); });
                                                    }
                                                "
                                                class="text-[11px] text-gray-400 hover:text-rose-600 transition"
                                                :disabled="submitting">
                                                <span x-show="!submitting">Report</span>
                                                <span x-show="submitting">...</span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-6 border-t border-gray-100">
                {{ $orders->links() }}
            </div>
        @else
            <div class="p-12 text-center text-gray-500">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 mb-3 text-gray-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                </div>
                <p class="font-bold text-gray-700">No active virtual numbers.</p>
                <p class="text-xs text-gray-400 mt-1">Select an app above to purchase your first virtual number.</p>
            </div>
        @endif
    </div>
</div>

<script>
    let currentServicesData = null;
    let selectedServiceKey = '';
    let selectedBasePrice = 0;
    let selectedPrice = 0;
    let selectedMaxPrice = null;
    let priceTierRequestToken = 0;
    const currencySymbol = @js($currencySymbol ?? '$');
    const currencyFx = {{ (float) ($currencyFx ?? 1) }};
    const userWalletBalance = {{ (float) ($walletBal ?? 0) }};
    const premiumEnabled = @js((bool) ($premiumEnabled ?? false));
    const premiumMarkupPercent = {{ (float) ($premiumMarkupPercent ?? 40) }};

    const countrySelect = document.getElementById('countrySelect');
    const countrySection = document.getElementById('countrySection');
    const countryNotice = document.getElementById('countryNotice');
    const serviceSearch = document.getElementById('serviceSearch');
    const serviceSearchResults = document.getElementById('serviceSearchResults');
    const serviceSelect = document.getElementById('serviceSelect');
    const operatorAmountDisplay = document.getElementById('operatorAmountDisplay');
    const priceTierSection = document.getElementById('priceTierSection');
    const priceTierContainer = document.getElementById('priceTierContainer');
    const priceTierSelect = document.getElementById('priceTierSelect');
    const priceDisplay = document.getElementById('priceDisplay');
    const buyBtn = document.getElementById('buyBtn');
    const promoCodeInput = document.getElementById('promoCodeInput');
    const premiumToggle = document.getElementById('premiumToggle');
    const summaryAppIcon = document.getElementById('summaryAppIcon');
    const summaryAppName = document.getElementById('summaryAppName');
    const summaryCountry = document.getElementById('summaryCountry');
    const summaryStock = document.getElementById('summaryStock');
    const serviceLoadingIndicator = document.getElementById('serviceLoadingIndicator');

    const appIcons = {
        'whatsapp': '💬',
        'telegram': '✈️',
        'google': '🔍',
        'instagram': '📸',
        'tiktok': '🎵',
        'openai': '🤖',
        'tinder': '🔥',
        'facebook': '👥',
        'twitter': '🐦',
        'discord': '🎮',
        'netflix': '🍿',
        'uber': '🚗',
        'paypal': '💳',
        'amazon': '📦',
        'snapchat': '👻',
        'microsoft': '🪟',
        'apple': '🍏',
        'doordash': '🍔',
        'steam': '🕹️',
    };

    function getAppIcon(key) {
        return appIcons[(key || '').toLowerCase()] || '📱';
    }

    function formatServiceName(service) {
        const names = {
            'whatsapp': 'WhatsApp',
            'telegram': 'Telegram',
            'tiktok': 'TikTok',
            'facebook': 'Facebook',
            'instagram': 'Instagram',
            'google': 'Google / Gmail',
            'twitter': 'Twitter / X',
            'discord': 'Discord',
            'openai': 'OpenAI / ChatGPT',
            'paypal': 'PayPal',
            'bigolive': 'Bigo Live',
            'doordash': 'DoorDash',
            'protonmail': 'ProtonMail',
            'kakaotalk': 'KakaoTalk',
            'other': 'Any Other',
        };
        const key = (service || '').toLowerCase();
        return names[key] || (service.charAt(0).toUpperCase() + service.slice(1));
    }

    function getSelectedProvider() {
        const selectedRadio = document.querySelector('input[name="sms_provider_choice"]:checked');
        return selectedRadio ? selectedRadio.value : 'daisysms';
    }

    function round2(x) {
        return Math.round((x + Number.EPSILON) * 100) / 100;
    }

    function isPremiumSelected() {
        return premiumEnabled && premiumToggle && premiumToggle.checked;
    }

    function premiumMultiplier() {
        return isPremiumSelected() ? (1 + (premiumMarkupPercent / 100)) : 1;
    }

    // Play subtle audio chime on OTP arrival
    function playOtpArrivalChime() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(587.33, ctx.currentTime); // D5
            osc.frequency.setValueAtTime(880.00, ctx.currentTime + 0.1); // A5
            gain.gain.setValueAtTime(0.1, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.4);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.4);
        } catch (e) {}
    }

    function showNotification(msg, icon = '✨') {
        const toast = document.getElementById('toastNotification');
        const toastMsg = document.getElementById('toastMessage');
        const toastIcon = document.getElementById('toastIcon');
        toastMsg.textContent = msg;
        toastIcon.textContent = icon;
        toast.classList.remove('translate-y-[-100px]', 'opacity-0', 'pointer-events-none');
        setTimeout(() => {
            toast.classList.add('translate-y-[-100px]', 'opacity-0', 'pointer-events-none');
        }, 3500);
    }

    function copyTextToClipboard(text, msg = 'Copied to clipboard!') {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(() => showNotification(msg, '📋')).catch(() => {});
        } else {
            showNotification(msg, '📋');
        }
    }

    function updateDestinationMode() {
        const provider = getSelectedProvider();
        if (provider === 'daisysms') {
            countrySection.classList.add('hidden');
            countrySelect.value = 'usa';
            summaryCountry.textContent = 'USA 🇺🇸';
        } else {
            countrySection.classList.remove('hidden');
            summaryCountry.textContent = (countrySelect.options[countrySelect.selectedIndex]?.text || 'Global').toUpperCase();
        }
    }

    function selectQuickCountry(slug) {
        document.querySelectorAll('.quick-country-btn').forEach(btn => {
            btn.classList.remove('bg-teal-50', 'border-[#0d9488]', 'text-[#0d9488]');
        });
        const matched = Array.from(countrySelect.options).find(opt => opt.value.toLowerCase() === slug.toLowerCase() || opt.text.toLowerCase().includes(slug.toLowerCase()));
        if (matched) {
            countrySelect.value = matched.value;
            countrySelect.dispatchEvent(new Event('change'));
        }
    }

    async function loadServices() {
        updateDestinationMode();
        serviceLoadingIndicator.textContent = 'Loading pricing...';
        serviceSearch.disabled = true;
        buyBtn.disabled = true;

        const provider = getSelectedProvider();
        const country = (provider === 'daisysms') ? 'usa' : (countrySelect.value || 'usa');

        try {
            const response = await fetch(`/api/sms/services?provider=${provider}&country=${country}`);
            const data = await response.json();
            currentServicesData = data;
            serviceLoadingIndicator.textContent = 'Live pricing loaded';

            // Populate Popular App Grid prices
            updatePopularCards();

            // Enable search
            serviceSearch.disabled = false;

            // If an app is already selected, re-calculate its price
            if (selectedServiceKey && currentServicesData[selectedServiceKey]) {
                selectAppCard(selectedServiceKey, false);
            } else {
                // Auto-select WhatsApp by default if available
                if (currentServicesData['whatsapp']) {
                    selectAppCard('whatsapp', false);
                }
            }
        } catch (error) {
            console.error('Error loading services:', error);
            serviceLoadingIndicator.textContent = 'Error loading services';
            showNotification('Could not load services. Please retry.', '⚠️');
        }
    }

    function updatePopularCards() {
        if (!currentServicesData) return;

        document.querySelectorAll('.app-card').forEach(card => {
            const serviceKey = card.id.replace('appCard-', '');
            const priceBadge = card.querySelector('.app-price-badge');
            const countBadge = card.querySelector('.app-count-badge');

            if (currentServicesData[serviceKey]) {
                const opKeys = Object.keys(currentServicesData[serviceKey]);
                const firstOp = opKeys[0] ? currentServicesData[serviceKey][opKeys[0]] : null;
                if (firstOp) {
                    const cost = calculateEffectivePrice(firstOp.cost, serviceKey);
                    priceBadge.textContent = `${currencySymbol}${(cost * currencyFx).toFixed(2)}`;
                    countBadge.textContent = `${firstOp.count || '99+'} avail`;
                    card.classList.remove('opacity-40');
                }
            } else {
                priceBadge.textContent = 'Unavailable';
                countBadge.textContent = '';
                card.classList.add('opacity-40');
            }
        });
    }

    function calculateEffectivePrice(rawBase, serviceKey) {
        const mult = premiumMultiplier();
        const currentCountry = (getSelectedProvider() === 'daisysms') ? 'usa' : (countrySelect.value || '').toLowerCase();
        let effective = round2(parseFloat(rawBase) * mult);

        const ngnRate = currencyFx > 1 ? currencyFx : 1500;
        if ((currentCountry === 'singapore' || currentCountry === 'sg') && (serviceKey === 'whatsapp' || serviceKey === 'wa')) {
            effective = round2(20000 / ngnRate);
        } else if ((effective * ngnRate) < 1000) {
            effective = round2(1500 / ngnRate);
        }
        return effective;
    }

    function selectAppCard(serviceKey, announce = true) {
        if (!currentServicesData || !currentServicesData[serviceKey]) {
            if (announce) showNotification('Service currently unavailable for this destination.', '⚠️');
            return;
        }

        selectedServiceKey = serviceKey;
        serviceSelect.value = serviceKey;

        // Highlight selected app card & remove previous highlights
        document.querySelectorAll('.app-card').forEach(c => {
            c.classList.remove('border-[#0d9488]', 'bg-teal-50/60', 'ring-2', 'ring-[#0d9488]/40');
        });
        const activeCard = document.getElementById(`appCard-${serviceKey}`);
        if (activeCard) {
            activeCard.classList.add('border-[#0d9488]', 'bg-teal-50/60', 'ring-2', 'ring-[#0d9488]/40');
        }

        // Close search results dropdown
        serviceSearchResults.classList.add('hidden');
        serviceSearch.value = formatServiceName(serviceKey);

        // Update Summary Bar
        summaryAppIcon.textContent = getAppIcon(serviceKey);
        summaryAppName.textContent = formatServiceName(serviceKey);

        // Populate Operators and Auto-Select First/Cheapest
        renderOperators(serviceKey);
    }

    function formatOperatorName(op) {
        const key = (op || '').toLowerCase();
        const map = {
            'any': 'Any / Best Available (Recommended)',
            'at_t': 'AT&T',
            'tmobile': 'T-Mobile',
            'physic': 'Physical SIM',
            'o2': 'O2',
            'ee': 'EE',
            'us_mobile': 'US Mobile',
            'h2o_wireless': 'H2O Wireless',
        };
        if (map[key]) return map[key];
        return key
            .split('_')
            .filter(Boolean)
            .map(w => w.charAt(0).toUpperCase() + w.slice(1))
            .join(' ');
    }

    function renderOperators(serviceKey) {
        const serviceData = currentServicesData[serviceKey];
        if (!serviceData) return;

        const firstKey = Object.keys(serviceData)[0];
        const firstDetails = serviceData['any'] || (firstKey ? serviceData[firstKey] : null);
        const totalCount = firstDetails ? (firstDetails.count || 0) : 0;

        if (firstDetails) {
            selectedBasePrice = parseFloat(firstDetails.cost || 0);
            selectedPrice = calculateEffectivePrice(firstDetails.cost, serviceKey);
        }

        summaryStock.textContent = `${totalCount || 'Multiple'} numbers available in pool`;
        selectedMaxPrice = null;
        updatePriceAndButton();
        loadPriceTiers(serviceKey);
    }

    async function loadPriceTiers(serviceKey) {
        const provider = getSelectedProvider();
        if (!priceTierSelect || !priceTierContainer) return;

        if (provider !== 'herosms') {
            if (priceTierSection) priceTierSection.classList.add('hidden');
            priceTierContainer.classList.add('hidden');
            priceTierSelect.innerHTML = '<option value="">Standard (Default)</option>';
            selectedMaxPrice = null;
            return;
        }

        if (priceTierSection) priceTierSection.classList.remove('hidden');
        priceTierContainer.classList.remove('hidden');
        const token = ++priceTierRequestToken;
        const country = countrySelect.value || 'usa';

        // Default option while loading
        const baseEffective = calculateEffectivePrice(selectedBasePrice, serviceKey);
        priceTierSelect.innerHTML = `<option value="" data-base-price="${selectedBasePrice}" data-price="${baseEffective}">Standard (Default) — ${currencySymbol}${(baseEffective * currencyFx).toFixed(2)}</option>`;
        selectedMaxPrice = null;

        try {
            const res = await fetch(`/api/sms/price-tiers?provider=${encodeURIComponent(provider)}&country=${encodeURIComponent(country)}&service=${encodeURIComponent(serviceKey)}`);
            const tiers = await res.json();
            if (token !== priceTierRequestToken) return;

            if (!Array.isArray(tiers) || tiers.length === 0) {
                return;
            }

            priceTierSelect.innerHTML = '';
            tiers.forEach((tier, idx) => {
                const effective = calculateEffectivePrice(tier.cost, serviceKey);
                const opt = document.createElement('option');
                opt.value = tier.max_price !== null && tier.max_price !== undefined ? String(tier.max_price) : '';
                opt.dataset.basePrice = String(tier.cost);
                opt.dataset.price = String(effective);
                const availTxt = tier.count ? ` (${Number(tier.count).toLocaleString()} avail)` : '';
                opt.textContent = `${tier.label} — ${currencySymbol}${(effective * currencyFx).toFixed(2)}${idx > 0 ? availTxt : ''}`;
                priceTierSelect.appendChild(opt);
            });

            priceTierSelect.selectedIndex = tiers.length > 1 ? 1 : 0;
            applySelectedPricing();
        } catch (e) {
            // Keep default tier on network error
        }
    }

    function applySelectedPricing() {
        const tierOpt = priceTierSelect ? priceTierSelect.options[priceTierSelect.selectedIndex] : null;
        if (tierOpt && tierOpt.dataset.price) {
            selectedMaxPrice = tierOpt.value !== '' ? parseFloat(tierOpt.value) : null;
            selectedBasePrice = parseFloat(tierOpt.dataset.basePrice || '0');
            selectedPrice = parseFloat(tierOpt.dataset.price || '0');
        } else if (currentServicesData && selectedServiceKey && currentServicesData[selectedServiceKey]) {
            const serviceData = currentServicesData[selectedServiceKey];
            const firstKey = Object.keys(serviceData)[0];
            const firstDetails = serviceData['any'] || (firstKey ? serviceData[firstKey] : null);
            if (firstDetails) {
                selectedMaxPrice = null;
                selectedBasePrice = parseFloat(firstDetails.cost || 0);
                selectedPrice = calculateEffectivePrice(firstDetails.cost, selectedServiceKey);
            }
        }

        updatePriceAndButton();
    }

    function updatePriceAndButton() {
        const finalPrice = selectedPrice;
        const finalConverted = finalPrice * currencyFx;
        const formattedAmount = `${currencySymbol}${finalConverted.toFixed(2)}`;
        priceDisplay.textContent = formattedAmount;
        if (operatorAmountDisplay) {
            operatorAmountDisplay.textContent = formattedAmount;
        }

        // Check if user has sufficient balance
        if (userWalletBalance < finalPrice) {
            buyBtn.disabled = true;
            buyBtn.innerHTML = `<span>⚠️ Low Balance (${currencySymbol}${(userWalletBalance * currencyFx).toFixed(2)})</span>`;
            buyBtn.title = "Please top up your wallet to continue.";
        } else {
            buyBtn.disabled = false;
            buyBtn.innerHTML = `<span>⚡ Get Phone Number (${formattedAmount})</span>`;
            buyBtn.title = "";
        }
    }

    // Search Box Autocomplete for 150+ apps
    serviceSearch.addEventListener('input', (e) => {
        const query = e.target.value.trim().toLowerCase();
        if (!currentServicesData || query.length === 0) {
            serviceSearchResults.classList.add('hidden');
            return;
        }

        const matches = Object.keys(currentServicesData).filter(s => {
            return s.toLowerCase().includes(query) || formatServiceName(s).toLowerCase().includes(query);
        });

        if (matches.length === 0) {
            serviceSearchResults.innerHTML = '<div class="p-4 text-center text-xs text-gray-500">No services match your search.</div>';
            serviceSearchResults.classList.remove('hidden');
            return;
        }

        serviceSearchResults.innerHTML = '';
        matches.slice(0, 15).forEach(s => {
            const firstOp = Object.values(currentServicesData[s])[0];
            const cost = firstOp ? calculateEffectivePrice(firstOp.cost, s) : 0;
            const row = document.createElement('div');
            row.className = 'p-3 hover:bg-teal-50 cursor-pointer flex items-center justify-between text-xs transition';
            row.innerHTML = `
                <div class="flex items-center gap-2.5">
                    <span class="text-base">${getAppIcon(s)}</span>
                    <span class="font-bold text-gray-900">${formatServiceName(s)}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-gray-400 font-medium">${firstOp?.count || '99+'} avail</span>
                    <span class="font-black text-[#0d9488]">${currencySymbol}${(cost * currencyFx).toFixed(2)}</span>
                </div>
            `;
            row.addEventListener('click', () => {
                selectAppCard(s);
            });
            serviceSearchResults.appendChild(row);
        });

        serviceSearchResults.classList.remove('hidden');
    });

    // Close search dropdown on click outside
    document.addEventListener('click', (e) => {
        if (!serviceSearch.contains(e.target) && !serviceSearchResults.contains(e.target)) {
            serviceSearchResults.classList.add('hidden');
        }
    });

    // Listeners for destination radio
    document.querySelectorAll('input[name="sms_provider_choice"]').forEach(r => {
        r.addEventListener('change', () => {
            loadServices();
        });
    });

    countrySelect.addEventListener('change', () => {
        loadServices();
    });

    if (priceTierSelect) {
        priceTierSelect.addEventListener('change', () => {
            applySelectedPricing();
        });
    }

    if (premiumToggle) {
        premiumToggle.addEventListener('change', () => {
            if (selectedServiceKey) {
                renderOperators(selectedServiceKey);
            }
            updatePopularCards();
        });
    }

    // Purchase Button Handler
    buyBtn.addEventListener('click', async () => {
        if (!selectedServiceKey) {
            showNotification('Please select a service first.', '⚠️');
            return;
        }

        buyBtn.disabled = true;
        buyBtn.innerHTML = `
            <svg class="animate-spin h-4 w-4 text-gray-900" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Allocating Number...</span>
        `;

        const provider = getSelectedProvider();
        const country = (provider === 'daisysms') ? 'usa' : (countrySelect.value || 'usa');
        const formattedService = formatServiceName(selectedServiceKey);

        if (window.showAppLoader) {
            window.showAppLoader(
                'Securing Private Line...', 
                `Allocating verified ${formattedService} line (${provider === 'daisysms' ? 'USA 🇺🇸' : country.toUpperCase()})`
            );
        }

        try {
            const response = await fetch('/api/sms/buy', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    provider: provider,
                    country: country,
                    product: selectedServiceKey,
                    operator: 'any',
                    price: selectedPrice,
                    max_price: selectedMaxPrice,
                    promo_code: promoCodeInput ? promoCodeInput.value.trim() : '',
                    is_premium: isPremiumSelected()
                })
            });

            const result = await response.json();

            if (result.success) {
                if (window.showAppLoader) {
                    window.showAppLoader('Line Secured! 🎉', 'Setting up your live verification tracker...');
                }
                showNotification('Number purchased successfully! Redirecting...', '🎉');
                setTimeout(() => location.reload(), 1200);
            } else {
                if (window.hideAppLoader) window.hideAppLoader();
                showNotification(result.message || 'Purchase failed.', '❌');
                buyBtn.disabled = false;
                updatePriceAndButton();
            }
        } catch (error) {
            if (window.hideAppLoader) window.hideAppLoader();
            showNotification('Network error during purchase.', '❌');
            buyBtn.disabled = false;
            updatePriceAndButton();
        }
    });

    // Initialize Alpine & Polling Checker
    document.addEventListener('alpine:init', () => {
        Alpine.data('smsChecker', (orderId, initialStatus, createdTs) => ({
            orderId: orderId,
            status: (initialStatus || 'PENDING').toString().toUpperCase(),
            smsCode: null,
            pollingMsg: 'Waiting for SMS...',
            minWaitSeconds: 120,
            cancelWindowSeconds: 1200,
            createdTs: createdTs || Math.floor(Date.now() / 1000),
            canCancel: false,
            showCountdown: false,
            countdownText: '',
            canceling: false,
            cancelError: null,
            
            initChecker() {
                this.refreshCancelState();

                if (this.status === 'PENDING' || this.status === 'WAITING') {
                    this.pollingMsg = 'Waiting for SMS...';
                    this.intervalId = setInterval(() => {
                        this.pollStatus();
                    }, 5000);
                    this.pollStatus();

                    this.countdownId = setInterval(() => {
                        this.refreshCancelState();
                    }, 1000);
                }
            },

            refreshCancelState() {
                const nowSec = Math.floor(Date.now() / 1000);
                const elapsed = Math.max(0, nowSec - (this.createdTs | 0));
                const maxRemaining = this.cancelWindowSeconds - elapsed;

                if (maxRemaining <= 0 || (this.status !== 'PENDING' && this.status !== 'WAITING')) {
                    this.canCancel = false;
                    this.showCountdown = false;
                    this.countdownText = '';
                    if (this.countdownId && maxRemaining <= 0) {
                        clearInterval(this.countdownId);
                        this.countdownId = null;
                    }
                    return;
                }

                if (elapsed < this.minWaitSeconds) {
                    this.canCancel = false;
                    this.showCountdown = true;
                    const waitLeft = this.minWaitSeconds - elapsed;
                    const mm = Math.floor(waitLeft / 60).toString().padStart(2, '0');
                    const ss = (waitLeft % 60).toString().padStart(2, '0');
                    this.countdownText = `Cancel in ${mm}:${ss}`;
                } else {
                    this.canCancel = true;
                    this.showCountdown = true;
                    const mm = Math.floor(maxRemaining / 60).toString().padStart(2, '0');
                    const ss = (maxRemaining % 60).toString().padStart(2, '0');
                    this.countdownText = `Expires in ${mm}:${ss}`;
                }
            },

            async handleCancel() {
                if (!this.canCancel || this.canceling) return;
                this.canceling = true;
                this.cancelError = null;

                try {
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
                    const response = await fetch(`/api/sms/cancel/${this.orderId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                        },
                    });
                    const data = await response.json();
                    if (data && data.success) {
                        this.status = 'CANCELED';
                        this.canCancel = false;
                        this.showCountdown = false;
                        clearInterval(this.intervalId);
                        if (this.countdownId) clearInterval(this.countdownId);
                        showNotification('Order canceled & refunded.', '🔄');
                        setTimeout(() => location.reload(), 1200);
                    } else {
                        this.cancelError = (data && data.message) ? data.message : 'Could not cancel this order.';
                    }
                } catch (error) {
                    this.cancelError = 'Network error while canceling.';
                } finally {
                    this.canceling = false;
                }
            },
            
            async pollStatus() {
                if (this.status !== 'PENDING' && this.status !== 'WAITING') {
                    clearInterval(this.intervalId);
                    return;
                }

                try {
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
                    const response = await fetch(`/api/sms/check/${this.orderId}`, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                        },
                    });
                    
                    const data = await response.json();
                    const s = (data?.status || '').toString().toUpperCase();
                    
                    if (s === 'RECEIVED' || s === 'COMPLETED' || s === 'FINISHED') {
                        this.status = 'RECEIVED';
                        this.smsCode = data?.sms_code || this.smsCode;
                        this.canCancel = false;
                        this.showCountdown = false;
                        clearInterval(this.intervalId);
                        if (this.countdownId) clearInterval(this.countdownId);
                        playOtpArrivalChime();
                        showNotification(`SMS Received! Code: ${this.smsCode}`, '🎉');
                    } else if (s === 'CANCELED' || s === 'CANCELLED' || s === 'TIMEOUT') {
                        this.status = 'CANCELED';
                        this.canCancel = false;
                        this.showCountdown = false;
                        clearInterval(this.intervalId);
                        if (this.countdownId) clearInterval(this.countdownId);
                        showNotification('Order was canceled and refunded.', 'ℹ️');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        this.pollingMsg = 'Waiting for SMS...';
                    }
                } catch (error) {
                    this.pollingMsg = 'Reconnecting...';
                }
            },

            copyCode() {
                if (!this.smsCode) return;
                copyTextToClipboard(String(this.smsCode), `Copied OTP Code: ${this.smsCode}`);
            }
        }));
    });

    // Initial Load on page start
    loadServices();
</script>
@endsection
