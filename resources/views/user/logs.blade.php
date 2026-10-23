@extends('layouts.dashboard')

@section('page_title', 'Social Media Logs Store')

@section('dashboard_content')

@php
    $popularPlatforms = [
        ['id' => 'tiktok', 'name' => 'TikTok', 'icon' => '🎵'],
        ['id' => 'instagram', 'name' => 'Instagram', 'icon' => '📸'],
        ['id' => 'twitter', 'name' => 'Twitter / X', 'icon' => '🐦'],
        ['id' => 'snapchat', 'name' => 'Snapchat', 'icon' => '👻'],
        ['id' => 'facebook', 'name' => 'Facebook', 'icon' => '📘'],
        ['id' => 'telegram', 'name' => 'Telegram', 'icon' => '✈️'],
        ['id' => 'youtube', 'name' => 'YouTube', 'icon' => '▶️'],
        ['id' => 'discord', 'name' => 'Discord', 'icon' => '💬'],
        ['id' => 'email', 'name' => 'Gmail / Email', 'icon' => '📧'],
    ];

    // Helper to count items per platform
    $platformCounts = [];
    foreach ($popularPlatforms as $p) {
        $cnt = 0;
        foreach ($logItems as $platKey => $items) {
            $pk = strtolower($platKey);
            $pid = $p['id'];
            if (str_contains($pk, $pid) || ($pid === 'twitter' && $pk === 'x') || ($pid === 'email' && (str_contains($pk, 'gmail') || str_contains($pk, 'mail')))) {
                $cnt += $items->count();
            }
        }
        $platformCounts[$p['id']] = $cnt;
    }
@endphp

<!-- Feedback Alerts -->
@if(session('success'))
    <div class="mb-5 bg-emerald-50 border border-emerald-200 text-emerald-900 px-4 py-3.5 rounded-2xl shadow-xs flex items-center justify-between gap-3 animate-fade-in">
        <div class="flex items-center gap-2.5">
            <span class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center font-black text-sm shrink-0">✓</span>
            <span class="font-extrabold text-xs sm:text-sm">{{ session('success') }}</span>
        </div>
        <button type="button" @click="$el.parentElement.remove()" class="text-emerald-700 hover:text-emerald-900 text-xs font-black p-1">✕</button>
    </div>
@endif

@if(session('error'))
    <div class="mb-5 bg-rose-50 border border-rose-200 text-rose-900 px-4 py-3.5 rounded-2xl shadow-xs flex items-center justify-between gap-3 animate-fade-in">
        <div class="flex items-center gap-2.5">
            <span class="w-8 h-8 rounded-xl bg-rose-500 text-white flex items-center justify-center font-black text-sm shrink-0">!</span>
            <span class="font-extrabold text-xs sm:text-sm">{{ session('error') }}</span>
        </div>
        <button type="button" @click="$el.parentElement.remove()" class="text-rose-700 hover:text-rose-900 text-xs font-black p-1">✕</button>
    </div>
@endif

@if($errors->any())
    <div class="mb-5 bg-rose-50 border border-rose-200 text-rose-900 p-4 rounded-2xl shadow-xs">
        <div class="text-xs font-black uppercase tracking-wider text-rose-700 mb-2">Please check the following errors:</div>
        <ul class="list-disc pl-5 font-bold text-xs space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div x-data="{ 
    activeCategory: 'all', 
    searchQuery: '', 
    activeVideo: null, 
    showVideoModal: false,
    viewTab: 'store',
    platformMatches(itemPlat) {
        if (this.activeCategory === 'all') return true;
        const p = (itemPlat || '').toLowerCase();
        const cur = this.activeCategory.toLowerCase();
        if (cur === 'twitter') return p.includes('twitter') || p === 'x';
        if (cur === 'email') return p.includes('gmail') || p.includes('email') || p.includes('mail');
        return p.includes(cur);
    }
}" class="space-y-6">

    <!-- High-Contrast OPay Mobile App Styled Header Hero Banner -->
    <div class="relative overflow-hidden rounded-3xl p-5 sm:p-8 text-white shadow-xl border border-slate-800"
         style="background: linear-gradient(135deg, #090d16 0%, #0f172a 60%, #0d9488 100%) !important; background-color: #0f172a !important; color: #ffffff !important;">
        <!-- Ambient background glows -->
        <div class="absolute -right-16 -top-16 w-64 h-64 rounded-full blur-3xl pointer-events-none" style="background-color: rgba(13, 148, 136, 0.25);"></div>
        <div class="absolute -left-16 -bottom-16 w-64 h-64 rounded-full blur-3xl pointer-events-none" style="background-color: rgba(250, 204, 21, 0.15);"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="space-y-3 max-w-2xl">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[#facc15] text-[11px] font-black uppercase tracking-wider shadow-xs"
                          style="background-color: rgba(255, 255, 255, 0.12); border: 1px solid rgba(255, 255, 255, 0.25);">
                        <span class="w-2 h-2 rounded-full bg-[#facc15] animate-ping"></span>
                        ⚡ Instant Delivery
                    </span>
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-emerald-300 text-[11px] font-black uppercase tracking-wider"
                          style="background-color: rgba(255, 255, 255, 0.12); border: 1px solid rgba(255, 255, 255, 0.25);">
                        ✓ 100% Tested Accounts
                    </span>
                </div>

                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black leading-tight tracking-tight" style="color: #ffffff !important;">
                    Social Media Logs Store
                </h1>
                <p class="text-xs sm:text-sm font-medium leading-relaxed" style="color: #cbd5e1 !important;">
                    Digital Logs &amp; Social Accounts Vault. Instantly buy aged accounts, cookies, and verified tokens for TikTok, Instagram, Twitter/X, Snapchat, and more. Delivered straight to your account vault.
                </p>

                <!-- Action Shortcut Buttons -->
                <div class="pt-2 flex flex-wrap items-center gap-2.5 sm:gap-3">
                    <a href="/logs-store/cart" class="inline-flex items-center gap-2 bg-[#facc15] hover:bg-yellow-400 active:scale-95 text-slate-950 font-black text-xs uppercase tracking-wider px-5 py-3 rounded-2xl shadow-lg transition-all cursor-pointer" style="color: #020617 !important;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"></path></svg>
                        <span>View Cart</span>
                        @if(isset($cartCount) && $cartCount > 0)
                            <span class="bg-slate-950 text-[#facc15] text-[10px] font-black px-2 py-0.5 rounded-full border border-[#facc15]/30">{{ $cartCount }}</span>
                        @endif
                    </a>

                    <a href="#order-history" @click="viewTab = 'history'" class="inline-flex items-center gap-2 active:scale-95 text-white font-black text-xs uppercase tracking-wider px-4 py-3 rounded-2xl transition-all cursor-pointer"
                       style="background-color: rgba(255, 255, 255, 0.15) !important; color: #ffffff !important; border: 1px solid rgba(255, 255, 255, 0.3) !important;">
                        <span>📦 Vault</span>
                        @if(isset($orders) && $orders->total() > 0)
                            <span class="bg-teal-500 text-white text-[10px] font-black px-2 py-0.5 rounded-full">{{ $orders->total() }}</span>
                        @endif
                    </a>

                    <a href="/tutorials?tab=logs" class="inline-flex items-center gap-1.5 active:scale-95 text-white font-black text-xs uppercase tracking-wider px-3.5 py-3 rounded-2xl transition-all cursor-pointer"
                       style="background-color: rgba(255, 255, 255, 0.15) !important; color: #ffffff !important; border: 1px solid rgba(255, 255, 255, 0.3) !important;">
                        <span>🎓 Tutorial</span>
                    </a>

                    <a href="/tickets?sell_logs=1" class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-2 transition" style="color: #e2e8f0 !important;">
                        <span>💼 Sell Your Logs &rarr;</span>
                    </a>
                </div>
            </div>

            <!-- Stats KPI Cards -->
            <div class="grid grid-cols-2 gap-3 lg:w-72 p-3.5 rounded-2xl shrink-0 shadow-inner"
                 style="background-color: rgba(15, 23, 42, 0.85); border: 1px solid rgba(51, 65, 85, 0.8);">
                <div class="p-3 rounded-xl" style="background-color: rgba(2, 6, 23, 0.7); border: 1px solid rgba(51, 65, 85, 0.6);">
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Platforms</div>
                    <div class="text-xl font-black text-[#facc15] mt-1 flex items-center gap-1.5">
                        <span>{{ count($logItems) }}</span>
                        <span class="text-xs font-bold text-slate-400">Active</span>
                    </div>
                </div>
                <div class="p-3 rounded-xl" style="background-color: rgba(2, 6, 23, 0.7); border: 1px solid rgba(51, 65, 85, 0.6);">
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">In Stock</div>
                    <div class="text-xl font-black text-emerald-400 mt-1 flex items-center gap-1.5">
                        @php
                            $totalStock = 0;
                            foreach($logItems as $items) {
                                $totalStock += $items->sum('available_stock');
                            }
                        @endphp
                        <span>{{ number_format($totalStock) }}</span>
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Mode Switcher Pills (Store Catalog vs Purchased History) -->
    <div class="flex items-center justify-between gap-3 bg-white p-2 rounded-2xl border border-slate-200/80 shadow-xs">
        <div class="flex items-center gap-1.5">
            <button 
                type="button"
                @click="viewTab = 'store'"
                :class="viewTab === 'store' ? 'bg-[#0d9488] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'"
                class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all flex items-center gap-2 cursor-pointer">
                <span>🛒</span>
                <span>Logs Catalog</span>
            </button>
            <button 
                type="button"
                @click="viewTab = 'history'"
                :class="viewTab === 'history' ? 'bg-[#0d9488] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'"
                class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all flex items-center gap-2 cursor-pointer">
                <span>📦</span>
                <span>My Purchased Logs</span>
                @if(isset($orders) && $orders->total() > 0)
                    <span :class="viewTab === 'history' ? 'bg-white text-[#0d9488]' : 'bg-slate-200 text-slate-700'" class="text-[10px] font-black px-1.5 py-0.2 rounded-full">{{ $orders->total() }}</span>
                @endif
            </button>
        </div>

        <!-- Quick Cart shortcut indicator on desktop -->
        <a href="/logs-store/cart" class="hidden sm:inline-flex items-center gap-2 text-xs font-extrabold text-[#0d9488] hover:text-teal-800 px-3 py-1.5 rounded-xl hover:bg-teal-50 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"></path></svg>
            <span>Open Cart @if(isset($cartCount) && $cartCount > 0)({{ $cartCount }})@endif</span>
        </a>
    </div>

    <!-- TAB 1: STORE CATALOG -->
    <div x-show="viewTab === 'store'" class="space-y-6">

        <!-- Prominent Platform Filter Bar (TikTok, Instagram, Twitter/X, Snapchat, Facebook...) -->
        <div class="bg-white p-3.5 sm:p-4 rounded-2xl border border-slate-200/80 shadow-xs space-y-3">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                
                <!-- Popular Platform Tabs (Horizontal Swipe on Mobile) -->
                <div class="flex items-center gap-2 overflow-x-auto pb-1.5 scrollbar-none">
                    <!-- All Platforms -->
                    <button 
                        type="button"
                        @click="activeCategory = 'all'" 
                        :class="activeCategory === 'all' ? 'bg-slate-900 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                        class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition whitespace-nowrap cursor-pointer flex items-center gap-1.5">
                        <span>🌟</span>
                        <span>All Logs</span>
                    </button>

                    <!-- Popular Social Platforms: TikTok, Instagram, Twitter/X, Snapchat, Facebook, etc. -->
                    @foreach($popularPlatforms as $plat)
                        @php
                            $cnt = $platformCounts[$plat['id']] ?? 0;
                        @endphp
                        <button 
                            type="button"
                            @click="activeCategory = '{{ $plat['id'] }}'" 
                            :class="activeCategory === '{{ $plat['id'] }}' ? 'bg-[#0d9488] text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                            class="px-3.5 py-2 rounded-xl text-xs font-black capitalize transition whitespace-nowrap flex items-center gap-1.5 cursor-pointer">
                            <span>{{ $plat['icon'] }}</span>
                            <span>{{ $plat['name'] }}</span>
                            <span :class="activeCategory === '{{ $plat['id'] }}' ? 'bg-white/20 text-white' : 'bg-slate-300 text-slate-700'" class="ml-1 px-1.5 py-0.2 rounded-full text-[10px] font-black">
                                {{ $cnt }}
                            </span>
                        </button>
                    @endforeach
                </div>

                <!-- Instant Search Input -->
                <div class="relative min-w-[240px] sm:w-72">
                    <input 
                        type="text" 
                        x-model="searchQuery" 
                        placeholder="Search logs or keywords..." 
                        class="w-full pl-9 pr-8 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-[#0d9488] focus:border-transparent transition">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <button 
                        type="button" 
                        x-show="searchQuery" 
                        @click="searchQuery = ''" 
                        class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600 text-xs font-black p-0.5">✕</button>
                </div>
            </div>
        </div>

        <!-- Video Guides & Tutorials Banner (If available) -->
        @if(isset($categoryVideos) && $categoryVideos->count() > 0)
            <div class="space-y-4">
                @foreach($categoryVideos as $plat => $vids)
                    <div x-show="(activeCategory === 'all' || platformMatches('{{ $plat }}')) && {{ $vids->count() }} > 0" class="bg-gradient-to-r from-amber-500/10 via-yellow-500/5 to-teal-500/10 rounded-3xl p-5 sm:p-6 border border-amber-200/80 shadow-xs">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <span class="w-10 h-10 rounded-2xl bg-amber-400 text-slate-950 font-black flex items-center justify-center text-lg shadow-xs">
                                    🎬
                                </span>
                                <div>
                                    <h4 class="font-black text-slate-900 text-sm sm:text-base capitalize flex items-center gap-1.5">
                                        {{ $plat }} Setup Video Guides
                                    </h4>
                                    <p class="text-xs text-slate-600 font-medium">Watch step-by-step video instructions to log in and use your {{ $plat }} accounts safely.</p>
                                </div>
                            </div>
                            <span class="text-[10px] font-black uppercase tracking-wider text-amber-800 bg-amber-100 border border-amber-200 px-3 py-1 rounded-full">
                                {{ $vids->count() }} {{ Str::plural('Guide', $vids->count()) }}
                            </span>
                        </div>

                        <!-- Video Cards Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
                            @foreach($vids as $vid)
                                <div class="bg-white rounded-2xl border border-amber-200/90 p-4 shadow-xs hover:shadow-md transition-all flex flex-col justify-between space-y-3">
                                    <div>
                                        <div class="flex items-center justify-between gap-2 mb-2">
                                            <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md bg-slate-900 text-amber-300">
                                                {{ ucfirst($plat) }} Guide
                                            </span>
                                            <span class="text-[10px] font-bold text-slate-400">Tutorial</span>
                                        </div>
                                        <h5 class="font-black text-slate-900 text-xs sm:text-sm leading-snug line-clamp-2">{{ $vid->title }}</h5>
                                    </div>

                                    <button 
                                        type="button"
                                        @click="
                                            activeVideo = {
                                                title: @js($vid->title),
                                                url: @js($vid->playable_url),
                                                is_file: @js((bool)$vid->is_uploaded_file),
                                                is_embed: @js((bool)$vid->is_embeddable),
                                                raw_url: @js($vid->video_url ?? '')
                                            };
                                            showVideoModal = true;
                                        " 
                                        class="w-full inline-flex items-center justify-center gap-2 bg-gradient-to-r from-amber-400 to-yellow-500 hover:from-amber-500 hover:to-yellow-600 text-slate-950 font-black text-xs px-4 py-2.5 rounded-xl shadow-xs transition cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M6.3 2.841A1.5 1.5 0 004 4.11v11.78a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/></svg>
                                        <span>Watch Guide</span>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Special Restock Card if selected platform currently has 0 items (e.g. Snapchat) -->
        <div x-show="activeCategory !== 'all' && !@js(array_keys($logItems->toArray())).some(p => platformMatches(p))" 
             class="bg-white p-10 sm:p-14 rounded-3xl border border-slate-200 text-center space-y-4 shadow-xs" style="display: none;">
            <div class="w-16 h-16 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center text-3xl mx-auto shadow-inner">
                <span x-text="activeCategory === 'snapchat' ? '👻' : (activeCategory === 'telegram' ? '✈️' : '📦')"></span>
            </div>
            <div>
                <h3 class="text-lg font-black text-slate-900 capitalize" x-text="`${activeCategory} Accounts Restocking Soon`"></h3>
                <p class="text-xs text-slate-500 font-medium max-w-md mx-auto mt-1">
                    Fresh batches of verified aged accounts are being prepared by verified sellers. In the meantime, browse our available TikTok, Instagram, and Twitter accounts.
                </p>
            </div>
            <div class="flex items-center justify-center gap-2 pt-2">
                <button type="button" @click="activeCategory = 'all'" class="px-5 py-2.5 bg-slate-900 text-white font-black text-xs rounded-xl hover:bg-slate-800 transition cursor-pointer">
                    View All Available Accounts
                </button>
                <a href="/tickets" class="px-5 py-2.5 bg-teal-50 text-[#0d9488] border border-teal-200 font-black text-xs rounded-xl hover:bg-teal-100 transition">
                    Request Stock
                </a>
            </div>
        </div>

        <!-- Log Items Catalog (Grouped by Platform) -->
        @forelse($logItems as $platform => $items)
            <div 
                x-show="platformMatches('{{ $platform }}')"
                class="space-y-4">
                
                <!-- Section Header -->
                <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                    <h2 class="text-lg sm:text-xl font-black text-slate-900 capitalize flex items-center gap-2">
                        <span class="text-2xl">
                            @if(strtolower($platform) == 'facebook') 📘
                            @elseif(strtolower($platform) == 'instagram') 📸
                            @elseif(strtolower($platform) == 'twitter' || strtolower($platform) == 'x') 🐦
                            @elseif(strtolower($platform) == 'youtube') ▶️
                            @elseif(strtolower($platform) == 'tiktok') 🎵
                            @elseif(strtolower($platform) == 'discord') 💬
                            @elseif(strtolower($platform) == 'telegram') ✈️
                            @elseif(strtolower($platform) == 'gmail' || strtolower($platform) == 'email') 📧
                            @else 📦 @endif
                        </span>
                        <span>{{ $platform }} Accounts &amp; Logs</span>
                    </h2>
                    <span class="text-xs font-bold text-slate-500 bg-slate-100 px-3 py-1 rounded-full border border-slate-200">
                        {{ $items->count() }} {{ Str::plural('type', $items->count()) }}
                    </span>
                </div>

                <!-- Product Cards Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-5">
                    @foreach($items as $item)
                        <div 
                            x-show="searchQuery === '' || @js(strtolower($item->title)).includes(searchQuery.toLowerCase()) || @js(strtolower($item->description ?? '')).includes(searchQuery.toLowerCase())"
                            class="bg-white rounded-3xl border border-slate-200/80 shadow-xs hover:shadow-lg hover:-translate-y-1 transition-all duration-200 flex flex-col justify-between overflow-hidden group">
                            
                            <!-- Card Body -->
                            <div class="p-5 flex-1 flex flex-col justify-between space-y-3">
                                <div>
                                    <!-- Platform Pill + Price Tag -->
                                    <div class="flex items-start justify-between gap-2 mb-2">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-slate-100 text-slate-700 border border-slate-200">
                                            <span>
                                                @if(strtolower($platform) == 'facebook') 📘
                                                @elseif(strtolower($platform) == 'instagram') 📸
                                                @elseif(strtolower($platform) == 'twitter' || strtolower($platform) == 'x') 🐦
                                                @elseif(strtolower($platform) == 'youtube') ▶️
                                                @elseif(strtolower($platform) == 'tiktok') 🎵
                                                @elseif(strtolower($platform) == 'discord') 💬
                                                @elseif(strtolower($platform) == 'telegram') ✈️
                                                @elseif(strtolower($platform) == 'gmail' || strtolower($platform) == 'email') 📧
                                                @else 📦 @endif
                                            </span>
                                            <span>{{ $platform }}</span>
                                        </span>

                                        <div class="text-right">
                                            <div class="text-base sm:text-lg font-black text-[#0d9488]">
                                                {{ $currencySymbol ?? '$' }}{{ ($formatAmount ?? fn($x) => number_format($x, 2))($item->price) }}
                                            </div>
                                            <div class="text-[9px] font-bold text-slate-400 uppercase">per log</div>
                                        </div>
                                    </div>

                                    <!-- Title -->
                                    <h3 class="font-black text-slate-900 text-sm leading-snug group-hover:text-[#0d9488] transition-colors">
                                        {{ $item->title }}
                                    </h3>

                                    <!-- Description / Notes with high contrast text -->
                                    <p class="text-xs text-slate-600 font-medium leading-relaxed mt-2 line-clamp-3">
                                        {{ $item->description ?: 'Verified high quality social media account with login credentials delivered instantly upon order.' }}
                                    </p>
                                </div>

                                <!-- Inventory Stock Status Indicator -->
                                <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                                    <div class="text-xs font-black px-2.5 py-1 rounded-xl inline-flex items-center gap-1.5 {{ $item->available_stock > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/70' : 'bg-rose-50 text-rose-700 border border-rose-200/70' }}">
                                        <span class="w-2 h-2 rounded-full {{ $item->available_stock > 0 ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500' }}"></span>
                                        <span>{{ $item->available_stock > 0 ? $item->available_stock . ' in stock' : 'Out of Stock' }}</span>
                                    </div>
                                    <span class="text-[10px] font-bold text-slate-400">⚡ Instant</span>
                                </div>
                            </div>

                            <!-- Card Action Footer (OPay Style Quick Cart Stepper) -->
                            <div class="p-4 bg-slate-50/90 border-t border-slate-100" x-data="{ open: false, qty: 1, fx: {{ (float) ($currencyFx ?? 1) }}, symbol: @js($currencySymbol ?? '$') }">
                                @if($item->available_stock > 0)
                                    <!-- Closed State: Prominent Add To Cart Button -->
                                    <button 
                                        type="button"
                                        @click="open = true" 
                                        x-show="!open" 
                                        class="w-full bg-[#facc15] hover:bg-yellow-400 active:scale-95 text-slate-950 font-black py-2.5 px-4 rounded-xl shadow-xs transition uppercase tracking-wider text-xs flex items-center justify-center gap-2 cursor-pointer"
                                        style="color: #020617 !important;">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                        <span>Add to Cart</span>
                                    </button>

                                    <!-- Open State: Quick Stepper Form -->
                                    <form action="/logs-store/cart/{{ $item->id }}" method="POST" x-show="open" style="display: none;" class="space-y-2">
                                        @csrf
                                        <div class="flex items-center gap-1.5">
                                            <button 
                                                type="button" 
                                                @click="if (qty > 1) qty--" 
                                                class="w-8 h-8 rounded-lg bg-white border border-slate-300 text-slate-700 font-black text-sm flex items-center justify-center hover:bg-slate-100 active:scale-95 transition cursor-pointer">−</button>
                                            
                                            <input 
                                                type="number" 
                                                name="quantity" 
                                                x-model="qty" 
                                                min="1" 
                                                max="{{ $item->available_stock }}" 
                                                class="flex-1 text-center rounded-lg border border-slate-300 py-1.5 px-2 text-xs font-black text-slate-900 focus:ring-[#0d9488] focus:border-[#0d9488]" 
                                                required>
                                            
                                            <button 
                                                type="button" 
                                                @click="if (qty < {{ $item->available_stock }}) qty++" 
                                                class="w-8 h-8 rounded-lg bg-white border border-slate-300 text-slate-700 font-black text-sm flex items-center justify-center hover:bg-slate-100 active:scale-95 transition cursor-pointer">+</button>
                                        </div>

                                        <div class="flex items-center gap-1.5">
                                            <button 
                                                type="submit" 
                                                class="flex-1 bg-[#0d9488] hover:bg-teal-700 text-white font-black py-2 px-3 rounded-lg text-xs shadow-xs transition flex items-center justify-center gap-1 cursor-pointer">
                                                <span>Confirm</span>
                                                <span>(<span x-text="symbol"></span><span x-text="(qty * {{ $item->price }} * fx).toFixed(2)"></span>)</span>
                                            </button>
                                            <button 
                                                type="button" 
                                                @click="open = false" 
                                                class="p-2 rounded-lg bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-black cursor-pointer" title="Cancel">
                                                ✕
                                            </button>
                                        </div>
                                    </form>
                                @else
                                    <button disabled class="w-full bg-slate-200 text-slate-400 font-black py-2.5 px-4 rounded-xl text-xs cursor-not-allowed uppercase tracking-wider">
                                        Sold Out
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="bg-white p-12 sm:p-16 rounded-3xl shadow-xs text-center border border-slate-200/80 space-y-3">
                <div class="w-16 h-16 rounded-2xl bg-teal-50 text-[#0d9488] flex items-center justify-center text-3xl mx-auto shadow-inner">
                    🛒
                </div>
                <h3 class="text-xl font-black text-slate-900">Logs Store is Currently Empty</h3>
                <p class="text-slate-500 font-medium text-xs sm:text-sm max-w-md mx-auto">
                    New batches of verified social media accounts are being stocked. Check back shortly or contact our 24/7 help desk.
                </p>
                <div class="pt-2">
                    <a href="/tickets" class="inline-flex items-center gap-2 bg-[#0d9488] text-white px-5 py-2.5 rounded-xl font-black text-xs hover:bg-teal-700 transition">
                        Contact Support
                    </a>
                </div>
            </div>
        @endforelse

    </div>

    <!-- TAB 2: MY PURCHASED LOGS VAULT (Fintech Receipt & Credential Cards) -->
    <div x-show="viewTab === 'history'" id="order-history" class="space-y-4" style="display: none;">
        
        <div class="bg-white rounded-3xl shadow-xs border border-slate-200/80 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <span class="w-9 h-9 rounded-xl bg-teal-50 text-[#0d9488] flex items-center justify-center font-black text-base shadow-inner">📦</span>
                    <div>
                        <h2 class="text-base sm:text-lg font-black text-slate-900">My Purchased Logs &amp; Credentials Vault</h2>
                        <p class="text-xs text-slate-500 font-medium">All purchased account logins, cookies, and tokens available for one-click copy.</p>
                    </div>
                </div>
                <span class="text-xs font-black text-slate-700 bg-slate-100 px-3 py-1.5 rounded-full border border-slate-200 self-start sm:self-auto">
                    {{ isset($orders) ? $orders->total() : 0 }} Total Orders
                </span>
            </div>

            @if(isset($orders) && $orders->count() > 0)
                
                <!-- Desktop Table View -->
                <div class="hidden lg:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left">
                        <thead class="bg-slate-50/80">
                            <tr>
                                <th class="px-5 py-3.5 text-xs font-black text-slate-500 uppercase tracking-wider">Date &amp; ID</th>
                                <th class="px-5 py-3.5 text-xs font-black text-slate-500 uppercase tracking-wider">Item Name</th>
                                <th class="px-5 py-3.5 text-xs font-black text-slate-500 uppercase tracking-wider">Qty</th>
                                <th class="px-5 py-3.5 text-xs font-black text-slate-500 uppercase tracking-wider">Amount</th>
                                <th class="px-5 py-3.5 text-xs font-black text-slate-500 uppercase tracking-wider">Credentials</th>
                                <th class="px-5 py-3.5 text-right text-xs font-black text-slate-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach($orders as $order)
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="px-5 py-4 whitespace-nowrap text-xs">
                                        <div class="font-black text-slate-900">#{{ $order->id }}</div>
                                        <div class="text-[10px] font-bold text-slate-400 mt-0.5">{{ $order->created_at->format('M d, Y · H:i') }}</div>
                                    </td>
                                    <td class="px-5 py-4 text-xs font-black text-slate-800 max-w-xs">
                                        {{ $order->credentials->first()->item->title ?? 'Log Order #' . $order->id }}
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-xs font-black text-slate-700">
                                        {{ $order->total_items }}x
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-xs text-[#0d9488] font-black">
                                        {{ $currencySymbol ?? '$' }}{{ ($formatAmount ?? fn($x) => number_format($x, 2))($order->total_amount) }}
                                    </td>
                                    <td class="px-5 py-4 text-xs text-slate-700 min-w-[340px]">
                                        @php
                                            $allCredsText = $order->credentials->pluck('data')->implode("\n");
                                        @endphp
                                        <div x-data="{ copied: false, text: @js($allCredsText) }" class="space-y-2">
                                            <div class="bg-slate-900 text-teal-300 border border-slate-800 rounded-2xl p-3 font-mono text-[11px] font-bold max-h-32 overflow-y-auto whitespace-pre-wrap select-all shadow-inner border-l-4 border-l-[#0d9488]">
                                                @foreach($order->credentials as $cred)
                                                    <div class="mb-1.5 pb-1.5 border-b border-slate-800/80 last:border-0 last:mb-0 last:pb-0 font-mono tracking-tight text-slate-200">
                                                        {{ $cred->data }}
                                                    </div>
                                                @endforeach
                                            </div>
                                            <button 
                                                type="button" 
                                                @click="
                                                    navigator.clipboard.writeText(text);
                                                    copied = true;
                                                    setTimeout(() => copied = false, 2500);
                                                "
                                                class="inline-flex items-center gap-1.5 text-[11px] font-black px-3 py-1.5 rounded-xl transition shadow-xs bg-teal-50 hover:bg-teal-100 text-[#0d9488] border border-teal-200 cursor-pointer">
                                                <svg x-show="!copied" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                                <svg x-show="copied" style="display:none;" class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                <span x-text="copied ? '✓ Credentials Copied!' : '📋 Copy All Credentials'"></span>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-right text-xs" x-data="{ reported: @js((bool)($order->is_reported ?? false)), submitting: false }">
                                        <template x-if="reported">
                                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[10px] font-black bg-amber-100 text-amber-800 border border-amber-300">
                                                🚩 Reported
                                            </span>
                                        </template>
                                        <template x-if="!reported">
                                            <button 
                                                type="button" 
                                                @click="
                                                    if (confirm('Report issue with this log order to admin support?')) {
                                                        submitting = true;
                                                        fetch('/orders/report', {
                                                            method: 'POST',
                                                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                                            body: JSON.stringify({ order_type: 'logs', order_id: {{ $order->id }}, reason: 'Log credentials issue reported from Logs Store.' })
                                                        })
                                                        .then(r => r.json())
                                                        .then(data => {
                                                            submitting = false;
                                                            if (data.success) {
                                                                reported = true;
                                                                alert(data.message);
                                                            } else {
                                                                alert(data.message || 'Could not report order.');
                                                            }
                                                        })
                                                        .catch(e => { submitting = false; alert('Error submitting report.'); });
                                                    }
                                                "
                                                class="inline-flex items-center gap-1 text-[11px] font-black text-rose-700 hover:text-rose-900 bg-rose-50 hover:bg-rose-100 border border-rose-200 px-3 py-1.5 rounded-xl transition cursor-pointer"
                                                :disabled="submitting">
                                                <span x-show="!submitting">🚩 Report Issue</span>
                                                <span x-show="submitting">Submitting...</span>
                                            </button>
                                        </template>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Mobile App Cards View (Optimized for One-Hand Phone Browsing) -->
                <div class="lg:hidden divide-y divide-slate-100">
                    @foreach($orders as $order)
                        <div class="p-4 space-y-3" x-data="{ reported: @js((bool)($order->is_reported ?? false)), submitting: false }">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-black text-slate-900">Order #{{ $order->id }}</span>
                                        <span class="px-2 py-0.5 rounded-md bg-teal-50 text-[#0d9488] text-[10px] font-black">
                                            {{ $order->total_items }}x {{ Str::plural('account', $order->total_items) }}
                                        </span>
                                    </div>
                                    <div class="text-xs font-black text-slate-800 mt-1">
                                        {{ $order->credentials->first()->item->title ?? 'Log Order #' . $order->id }}
                                    </div>
                                    <div class="text-[10px] font-bold text-slate-400 mt-0.5">
                                        {{ $order->created_at->format('M d, Y · H:i') }}
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-black text-[#0d9488]">
                                        {{ $currencySymbol ?? '$' }}{{ ($formatAmount ?? fn($x) => number_format($x, 2))($order->total_amount) }}
                                    </div>
                                    <template x-if="reported">
                                        <span class="inline-block mt-1 text-[9px] font-black text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full">
                                            Reported
                                        </span>
                                    </template>
                                </div>
                            </div>

                            <!-- Credentials Card -->
                            @php
                                $mobileCreds = $order->credentials->pluck('data')->implode("\n");
                            @endphp
                            <div x-data="{ copied: false, text: @js($mobileCreds) }" class="space-y-2">
                                <div class="bg-slate-900 text-slate-200 border border-slate-800 rounded-xl p-3 font-mono text-[11px] font-bold max-h-32 overflow-y-auto whitespace-pre-wrap select-all shadow-inner border-l-4 border-l-[#0d9488]">
                                    @foreach($order->credentials as $cred)
                                        <div class="mb-1.5 pb-1.5 border-b border-slate-800/80 last:border-0 last:mb-0 last:pb-0">
                                            {{ $cred->data }}
                                        </div>
                                    @endforeach
                                </div>

                                <div class="flex items-center justify-between gap-2 pt-1">
                                    <button 
                                        type="button" 
                                        @click="
                                            navigator.clipboard.writeText(text);
                                            copied = true;
                                            setTimeout(() => copied = false, 2500);
                                        "
                                        class="flex-1 inline-flex items-center justify-center gap-1.5 text-xs font-black py-2 rounded-xl transition bg-teal-50 hover:bg-teal-100 text-[#0d9488] border border-teal-200 cursor-pointer">
                                        <span x-text="copied ? '✓ Copied to Clipboard!' : '📋 Copy Credentials'"></span>
                                    </button>

                                    <template x-if="!reported">
                                        <button 
                                            type="button" 
                                            @click="
                                                if (confirm('Report issue with this log order?')) {
                                                    submitting = true;
                                                    fetch('/orders/report', {
                                                        method: 'POST',
                                                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                                        body: JSON.stringify({ order_type: 'logs', order_id: {{ $order->id }}, reason: 'Log issue reported on mobile.' })
                                                    })
                                                    .then(r => r.json())
                                                    .then(data => {
                                                        submitting = false;
                                                        if (data.success) { reported = true; alert(data.message); }
                                                        else { alert(data.message || 'Error reporting order.'); }
                                                    })
                                                    .catch(e => { submitting = false; alert('Network error reporting order.'); });
                                                }
                                            "
                                            class="p-2 text-rose-700 bg-rose-50 border border-rose-200 rounded-xl text-xs font-black shrink-0 cursor-pointer"
                                            title="Report issue">
                                            🚩
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/70">
                    {{ $orders->links() }}
                </div>
            @else
                <div class="p-12 text-center text-slate-500 font-medium text-xs sm:text-sm space-y-2">
                    <div class="text-3xl">📭</div>
                    <div class="font-bold text-slate-700">No account orders yet</div>
                    <p class="text-slate-400">Items you purchase from the Logs Catalog will appear here with instant login credentials.</p>
                </div>
            @endif
        </div>

    </div>

    <!-- Video Modal Player -->
    <div 
        x-show="showVideoModal" 
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/85 backdrop-blur-md flex items-center justify-center p-4" 
        style="display: none;" 
        @keydown.escape.window="showVideoModal = false">
        
        <div class="bg-slate-900 rounded-3xl max-w-3xl w-full p-5 sm:p-6 text-white shadow-2xl relative border border-white/10" @click.away="showVideoModal = false">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-white/10">
                <div class="flex items-center gap-2.5">
                    <span class="p-2 rounded-xl bg-amber-400 text-slate-950 font-black text-sm">🎬</span>
                    <h3 class="text-base sm:text-lg font-black text-white" x-text="activeVideo ? activeVideo.title : 'Watch Video Guide'"></h3>
                </div>
                <button @click="showVideoModal = false" class="text-slate-400 hover:text-white transition p-1 cursor-pointer">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Video Player Output -->
            <template x-if="activeVideo">
                <div class="rounded-2xl overflow-hidden bg-black aspect-video relative flex items-center justify-center border border-white/10">
                    <template x-if="activeVideo.is_file">
                        <video :src="activeVideo.url" controls autoplay class="w-full h-full object-contain"></video>
                    </template>
                    <template x-if="!activeVideo.is_file && activeVideo.is_embed">
                        <iframe :src="activeVideo.url" class="w-full h-full border-0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    </template>
                    <template x-if="!activeVideo.is_file && !activeVideo.is_embed">
                        <div class="p-6 text-center space-y-3">
                            <p class="text-xs sm:text-sm font-semibold text-slate-300">This guide points to an external link:</p>
                            <p class="text-xs font-mono text-amber-400 bg-white/5 p-3 rounded-xl break-all" x-text="activeVideo.raw_url"></p>
                            <a :href="activeVideo.raw_url" target="_blank" class="inline-flex items-center gap-2 bg-[#facc15] hover:bg-yellow-400 text-slate-950 font-black text-xs px-5 py-2.5 rounded-xl shadow-lg transition">
                                Open Tutorial Link ↗
                            </a>
                        </div>
                    </template>
                </div>
            </template>

            <div class="mt-4 pt-3 border-t border-white/10 flex justify-end">
                <button @click="showVideoModal = false" class="px-5 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white font-black text-xs transition cursor-pointer">
                    Close Player
                </button>
            </div>
        </div>
    </div>

</div>

@endsection
