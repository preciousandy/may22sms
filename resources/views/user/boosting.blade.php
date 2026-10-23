@extends('layouts.dashboard')

@section('page_title', 'Social Boosting')

@section('dashboard_content')

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
        <div class="text-xs font-black uppercase tracking-wider text-rose-700 mb-2">Please resolve the following:</div>
        <ul class="list-disc pl-5 font-bold text-xs space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div x-data="boostingPageData()" x-init="init()" class="space-y-6">

    <!-- High-Contrast OPay Style Hero Header Banner -->
    <div class="relative overflow-hidden rounded-3xl p-5 sm:p-8 text-white shadow-xl border border-slate-800"
         style="background: linear-gradient(135deg, #090d16 0%, #0f172a 60%, #1e1b4b 100%) !important; background-color: #0f172a !important; color: #ffffff !important;">
        <!-- Ambient background glows -->
        <div class="absolute -right-14 -top-14 w-60 h-60 rounded-full blur-3xl pointer-events-none" style="background-color: rgba(168, 85, 247, 0.25);"></div>
        <div class="absolute -left-14 -bottom-14 w-60 h-60 rounded-full blur-3xl pointer-events-none" style="background-color: rgba(13, 148, 136, 0.2);"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="space-y-3 max-w-2xl">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[#facc15] text-[11px] font-black uppercase tracking-wider shadow-xs"
                          style="background-color: rgba(255, 255, 255, 0.12); border: 1px solid rgba(255, 255, 255, 0.25);">
                        <span class="w-2 h-2 rounded-full bg-[#facc15] animate-ping"></span>
                        🚀 Instant API Dispatch
                    </span>
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-emerald-300 text-[11px] font-black uppercase tracking-wider"
                          style="background-color: rgba(255, 255, 255, 0.12); border: 1px solid rgba(255, 255, 255, 0.25);">
                        ✓ High Quality Retention
                    </span>
                </div>

                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black leading-tight tracking-tight" style="color: #ffffff !important;">
                    Social Media Engagement &amp; Boosting
                </h1>
                <p class="text-xs sm:text-sm font-medium leading-relaxed" style="color: #cbd5e1 !important;">
                    Boost your followers, likes, views, comments, and subscribers across TikTok, Instagram, Twitter/X, Snapchat, and YouTube. Fast delivery and live real-time progress tracking.
                </p>

                <!-- Quick Action Shortcuts -->
                <div class="pt-2 flex flex-wrap items-center gap-2.5 sm:gap-3">
                    <a href="#place-order" class="inline-flex items-center gap-2 bg-[#facc15] hover:bg-yellow-400 active:scale-95 text-slate-950 font-black text-xs uppercase tracking-wider px-5 py-3 rounded-2xl shadow-lg transition-all cursor-pointer" style="color: #020617 !important;">
                        <span>⚡ Place New Boost</span>
                    </a>

                    <a href="#boost-orders" class="inline-flex items-center gap-2 active:scale-95 text-white font-black text-xs uppercase tracking-wider px-4 py-3 rounded-2xl transition-all cursor-pointer"
                       style="background-color: rgba(255, 255, 255, 0.15) !important; color: #ffffff !important; border: 1px solid rgba(255, 255, 255, 0.3) !important;">
                        <span>📦 My Boost Orders</span>
                        @if(isset($orders) && $orders->total() > 0)
                            <span class="bg-indigo-500 text-white text-[10px] font-black px-2 py-0.5 rounded-full">{{ $orders->total() }}</span>
                        @endif
                    </a>

                    <a href="/tutorials?tab=boosting" class="inline-flex items-center gap-1.5 active:scale-95 text-white font-black text-xs uppercase tracking-wider px-3.5 py-3 rounded-2xl transition-all cursor-pointer"
                       style="background-color: rgba(255, 255, 255, 0.15) !important; color: #ffffff !important; border: 1px solid rgba(255, 255, 255, 0.3) !important;">
                        <span>🎓 Tutorial</span>
                    </a>

                    <div class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-2" style="color: #e2e8f0 !important;">
                        <span>💳 Wallet:</span>
                        <span class="text-emerald-400 font-black">{{ $currencySymbol ?? '$' }}{{ ($formatAmount ?? fn($x) => number_format($x, 2))(auth()->user()->walletBalance ?? 0) }}</span>
                    </div>
                </div>
            </div>

            <!-- Stats KPI Cards -->
            <div class="grid grid-cols-2 gap-3 lg:w-72 p-3.5 rounded-2xl shrink-0 shadow-inner"
                 style="background-color: rgba(15, 23, 42, 0.85); border: 1px solid rgba(51, 65, 85, 0.8);">
                <div class="p-3 rounded-xl" style="background-color: rgba(2, 6, 23, 0.7); border: 1px solid rgba(51, 65, 85, 0.6);">
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Services</div>
                    <div class="text-xl font-black text-[#facc15] mt-1 flex items-center gap-1.5">
                        <span x-text="services.length"></span>
                        <span class="text-[10px] font-bold text-slate-400">Live</span>
                    </div>
                </div>
                <div class="p-3 rounded-xl" style="background-color: rgba(2, 6, 23, 0.7); border: 1px solid rgba(51, 65, 85, 0.6);">
                    <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest">My Orders</div>
                    <div class="text-xl font-black text-teal-400 mt-1 flex items-center gap-1.5">
                        <span>{{ isset($orders) ? $orders->total() : 0 }}</span>
                        <span class="text-[10px] font-bold text-slate-400">Total</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($services->isEmpty())
        <div class="bg-white rounded-3xl shadow-xs border border-slate-200/80 p-12 text-center space-y-3">
            <div class="w-16 h-16 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-3xl mx-auto shadow-inner">
                🚀
            </div>
            <h3 class="text-xl font-black text-slate-900">No Boosting Services Active</h3>
            <p class="text-slate-500 font-medium text-xs sm:text-sm max-w-md mx-auto">
                Services are currently undergoing scheduled maintenance. Please check back shortly or contact our 24/7 help desk.
            </p>
        </div>
    @else
        <!-- Two Column Main App Layout (Service Selector & Order Placement Form) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6" id="place-order">
            
            <!-- LEFT COLUMN: Pick & Filter Service (5 Cols) -->
            <div class="lg:col-span-5 space-y-5">
                <div class="bg-white rounded-3xl shadow-xs border border-slate-200/80 overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-black text-xs shadow-inner">1</span>
                            <h2 class="text-sm sm:text-base font-black text-slate-900">Select Service</h2>
                        </div>
                        <button 
                            type="button" 
                            @click="toggleFavoritesOnly()" 
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl font-black text-xs transition cursor-pointer" 
                            :class="favoritesOnly ? 'bg-amber-400 text-slate-950 shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'">
                            <span x-text="favoritesOnly ? '★ Favorites Only' : '☆ All Services'"></span>
                        </button>
                    </div>

                    <div class="p-5 space-y-4">
                        
                        <!-- Popular Platform Filter Bar (TikTok, Instagram, Twitter/X, Snapchat, YouTube...) -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-[11px] font-black text-slate-500 uppercase tracking-wider">Popular Platforms</label>
                                <span class="text-[10px] font-bold text-slate-400">Swipe &rarr;</span>
                            </div>
                            <div class="flex items-center gap-1.5 overflow-x-auto pb-1.5 scrollbar-none">
                                <template x-for="p in popularPlatforms" :key="p.id">
                                    <button 
                                        type="button"
                                        @click="setPlatform(p.id)" 
                                        :class="activePlatform === p.id ? 'bg-[#0d9488] text-white shadow-md' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                        class="px-3.5 py-2 rounded-xl text-xs font-black whitespace-nowrap transition flex items-center gap-1.5 cursor-pointer">
                                        <span x-text="p.icon"></span>
                                        <span x-text="p.name"></span>
                                        <span x-show="p.count > 0" :class="activePlatform === p.id ? 'bg-white/25 text-white' : 'bg-slate-200 text-slate-700'" class="ml-0.5 px-1.5 py-0.2 rounded-full text-[10px] font-black" x-text="p.count"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- Sub-Category Filter Dropdown (Dynamically narrowed by selected platform) -->
                        <div x-show="availableCategories.length > 0">
                            <label class="block text-[11px] font-black text-slate-500 uppercase tracking-wider mb-1.5">
                                <span x-text="activePlatform === 'all' ? 'All Sub-Categories' : `${popularPlatforms.find(x => x.id === activePlatform)?.name || ''} Categories`"></span>
                            </label>
                            <select 
                                x-model="category" 
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 text-xs font-bold text-slate-900 p-2.5 focus:bg-white focus:ring-2 focus:ring-[#0d9488] transition">
                                <option value="">All Categories under this platform</option>
                                <template x-for="cat in availableCategories" :key="cat">
                                    <option :value="cat" x-text="cat"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Search Field -->
                        <div>
                            <label class="block text-[11px] font-black text-slate-500 uppercase tracking-wider mb-1.5">Search Services</label>
                            <div class="relative">
                                <input 
                                    type="text" 
                                    x-model="search" 
                                    placeholder="Search e.g. Followers, Views, Likes, Retweets..." 
                                    class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-[#0d9488] focus:border-transparent transition">
                                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                        </div>

                        <!-- Service Select Dropdown -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-[11px] font-black text-slate-500 uppercase tracking-wider">Choose Service</label>
                                <span class="text-[10px] font-black text-[#0d9488]" x-text="`${filteredServices.length} services found`"></span>
                            </div>
                            <select 
                                x-model="serviceId" 
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 text-xs font-bold text-slate-900 p-3 focus:bg-white focus:ring-2 focus:ring-[#0d9488] focus:border-transparent transition leading-tight">
                                <option value="">-- Choose a boosting service --</option>
                                <template x-for="srv in filteredServices" :key="srv.id">
                                    <option :value="srv.id" x-text="`${srv.name} — ${symbol}${format(Number(srv.rate) * fx)}/1k`"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Active Service Preview Card (When Selected) -->
                        <div x-show="selectedService" class="bg-gradient-to-br from-slate-50 to-teal-50/50 rounded-2xl border border-teal-200/90 p-4 space-y-3" style="display: none;">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-black uppercase tracking-wider bg-teal-100 text-teal-900 px-2.5 py-0.5 rounded-md" x-text="selectedService ? selectedService.category : ''"></span>
                                    <h4 class="font-black text-slate-900 text-xs sm:text-sm mt-1.5 leading-snug" x-text="selectedService ? selectedService.name : ''"></h4>
                                </div>
                                <button 
                                    type="button" 
                                    @click="toggleFav()" 
                                    class="p-2 rounded-xl text-xs font-black transition cursor-pointer" 
                                    :class="isFav(serviceId) ? 'bg-amber-400 text-slate-950 shadow-xs' : 'bg-white border border-slate-200 text-slate-500 hover:bg-slate-100'"
                                    :title="isFav(serviceId) ? 'Remove Favorite' : 'Save to Favorites'">
                                    <span x-text="isFav(serviceId) ? '★' : '☆'"></span>
                                </button>
                            </div>

                            <div class="grid grid-cols-3 gap-2 pt-2 border-t border-teal-200/60 text-center">
                                <div class="bg-white p-2 rounded-xl border border-slate-200/70 shadow-2xs">
                                    <div class="text-[9px] font-black text-slate-400 uppercase">Rate/1K</div>
                                    <div class="text-xs font-black text-[#0d9488] mt-0.5">
                                        <span x-text="symbol"></span><span x-text="selectedService ? format(Number(selectedService.rate) * fx) : ''"></span>
                                    </div>
                                </div>
                                <div class="bg-white p-2 rounded-xl border border-slate-200/70 shadow-2xs">
                                    <div class="text-[9px] font-black text-slate-400 uppercase">Min Order</div>
                                    <div class="text-xs font-black text-slate-800 mt-0.5" x-text="selectedService ? Number(selectedService.min).toLocaleString() : ''"></div>
                                </div>
                                <div class="bg-white p-2 rounded-xl border border-slate-200/70 shadow-2xs">
                                    <div class="text-[9px] font-black text-slate-400 uppercase">Max Order</div>
                                    <div class="text-xs font-black text-slate-800 mt-0.5" x-text="selectedService ? Number(selectedService.max).toLocaleString() : ''"></div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: Place Order Form (7 Cols) -->
            <div class="lg:col-span-7 space-y-5">
                <div class="bg-white rounded-3xl shadow-xs border border-slate-200/80 overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-xl bg-teal-50 text-[#0d9488] flex items-center justify-center font-black text-xs shadow-inner">2</span>
                            <h2 class="text-sm sm:text-base font-black text-slate-900">Configure &amp; Place Order</h2>
                        </div>
                        <span class="text-[11px] font-black bg-teal-50 text-[#0d9488] border border-teal-200/60 px-3 py-1 rounded-full">
                            Instant Fulfillment
                        </span>
                    </div>

                    <div class="p-5 sm:p-6">
                        <form 
                            action="/social-boosting/order" 
                            method="POST" 
                            class="space-y-4" 
                            data-loader="true" 
                            data-loader-title="Placing Boosting Order..." 
                            data-loader-subtitle="Dispatching engagement request to server" 
                            @submit="if (submitting) { $event.preventDefault(); return; } submitting = true">
                            @csrf
                            <input type="hidden" name="boost_service_id" :value="serviceId">

                            <!-- Link Input -->
                            <div>
                                <label class="block text-xs font-black text-slate-800 uppercase tracking-wider mb-1.5">
                                    <span>Target Link or Username</span>
                                    <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <input 
                                        type="url" 
                                        name="link" 
                                        x-model="link" 
                                        class="w-full rounded-xl border border-slate-200 bg-slate-50 text-xs font-bold text-slate-900 pl-9 pr-4 py-3 focus:bg-white focus:ring-2 focus:ring-[#0d9488] focus:border-transparent transition" 
                                        placeholder="https://tiktok.com/@username or post URL" 
                                        required>
                                    <span class="absolute left-3 top-3 text-slate-400">🔗</span>
                                </div>
                                <p class="text-[10px] font-bold text-slate-400 mt-1">Make sure the profile or post is set to public, not private.</p>
                            </div>

                            <!-- Quantity Input + Quick Preset Stepper Pills -->
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="block text-xs font-black text-slate-800 uppercase tracking-wider">
                                        <span>Order Quantity</span>
                                        <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="text-[11px] font-bold text-slate-400" x-show="selectedService">
                                        Limit: <span x-text="selectedService ? Number(selectedService.min).toLocaleString() : 1"></span> &ndash; <span x-text="selectedService ? Number(selectedService.max).toLocaleString() : '∞'"></span>
                                    </div>
                                </div>
                                <div class="relative">
                                    <input 
                                        type="number" 
                                        name="quantity" 
                                        x-model="qty" 
                                        min="1" 
                                        :min="selectedService ? selectedService.min : 1" 
                                        :max="selectedService && selectedService.max ? selectedService.max : null" 
                                        class="w-full rounded-xl border border-slate-200 bg-slate-50 text-xs font-black text-slate-900 pl-9 pr-4 py-3 focus:bg-white focus:ring-2 focus:ring-[#0d9488] focus:border-transparent transition" 
                                        placeholder="Enter quantity (e.g. 1000)" 
                                        required>
                                    <span class="absolute left-3 top-3 text-slate-400">🔢</span>
                                </div>

                                <!-- Quick Presets -->
                                <div class="flex items-center gap-1.5 mt-2 overflow-x-auto pb-1 scrollbar-none">
                                    <span class="text-[10px] font-black text-slate-400 uppercase mr-1">Presets:</span>
                                    <template x-for="preset in [500, 1000, 2500, 5000, 10000]" :key="preset">
                                        <button 
                                            type="button" 
                                            @click="qty = preset" 
                                            class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-teal-50 hover:text-[#0d9488] border border-slate-200 text-slate-700 text-[10px] font-black transition cursor-pointer"
                                            x-text="`+${preset.toLocaleString()}`">
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <!-- Promo Code Input (Optional) -->
                            <div>
                                <label class="block text-xs font-black text-slate-800 uppercase tracking-wider mb-1.5">Promo Coupon (Optional)</label>
                                <div class="relative">
                                    <input 
                                        type="text" 
                                        name="promo_code" 
                                        value="{{ old('promo_code') }}" 
                                        class="w-full rounded-xl border border-slate-200 bg-slate-50 text-xs font-bold text-slate-900 pl-9 pr-4 py-2.5 uppercase focus:bg-white focus:ring-2 focus:ring-[#0d9488] focus:border-transparent transition" 
                                        placeholder="e.g. BOOST10">
                                    <span class="absolute left-3 top-2.5 text-slate-400">🎟️</span>
                                </div>
                            </div>

                            <!-- OPay Dynamic Payment Summary Box -->
                            <div class="rounded-2xl p-4 sm:p-5 text-white shadow-inner space-y-3"
                                 style="background-color: #0f172a !important; border: 1px solid #1e293b !important; color: #ffffff !important;">
                                <div class="flex items-center justify-between text-xs text-slate-300">
                                    <span>Selected Rate per 1,000</span>
                                    <span class="font-bold text-slate-100" x-text="selectedService ? `${symbol}${format(Number(selectedService.rate) * fx)}` : '—'"></span>
                                </div>
                                <div class="flex items-center justify-between text-xs text-slate-300">
                                    <span>Quantity Ordered</span>
                                    <span class="font-bold text-slate-100" x-text="qty ? Number(qty).toLocaleString() : '0'"></span>
                                </div>
                                <div class="pt-3 border-t border-slate-700 flex items-center justify-between">
                                    <div>
                                        <span class="text-xs font-bold text-slate-300">Total Charge</span>
                                        <div class="text-[10px] text-slate-400">Deducted from wallet balance</div>
                                    </div>
                                    <div class="text-2xl font-black text-[#facc15]">
                                        <span x-text="symbol"></span><span x-text="total.toFixed(2)"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <button 
                                type="submit" 
                                class="w-full bg-[#facc15] hover:bg-yellow-400 active:scale-98 text-slate-950 font-black px-5 py-3.5 rounded-2xl shadow-lg shadow-yellow-500/20 transition-all uppercase tracking-wider text-xs flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed" 
                                style="color: #020617 !important;"
                                :disabled="submitting || !serviceId || !link || !qty || qty < 1">
                                <span x-show="!submitting">Confirm &amp; Place Boost Order</span>
                                <span x-show="submitting" style="display: none;" class="flex items-center gap-2">
                                    <svg class="animate-spin h-4 w-4 text-slate-950" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                    <span>Dispatching Order...</span>
                                </span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    @endif

    <!-- BOTTOM SECTION: My Boost Orders (Live Real-Time Tracker) -->
    <div class="bg-white rounded-3xl shadow-xs border border-slate-200/80 overflow-hidden" id="boost-orders">
        <div class="px-5 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <span class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-black text-base shadow-inner">📦</span>
                <div>
                    <h2 class="text-base sm:text-lg font-black text-slate-900">Live Boost Orders Feed</h2>
                    <p class="text-xs text-slate-500 font-medium">Automatic real-time status updates and delivery completion tracking.</p>
                </div>
            </div>
            <span class="text-xs font-black text-slate-700 bg-slate-100 px-3 py-1.5 rounded-full border border-slate-200 self-start sm:self-auto">
                {{ $orders->total() }} Recorded Orders
            </span>
        </div>

        @if($orders->count() === 0)
            <div class="p-12 text-center text-slate-500 font-medium text-xs sm:text-sm space-y-2">
                <div class="text-3xl">🚀</div>
                <div class="font-bold text-slate-700">No boosting orders placed yet</div>
                <p class="text-slate-400">Choose a service from the form above to launch your first social boost campaign.</p>
            </div>
        @else
            <!-- Desktop Table View -->
            <div class="hidden lg:block overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-left">
                    <thead class="bg-slate-50/80">
                        <tr>
                            <th class="px-5 py-3.5 text-xs font-black text-slate-500 uppercase tracking-wider">Date &amp; ID</th>
                            <th class="px-5 py-3.5 text-xs font-black text-slate-500 uppercase tracking-wider">Service</th>
                            <th class="px-5 py-3.5 text-xs font-black text-slate-500 uppercase tracking-wider">Qty</th>
                            <th class="px-5 py-3.5 text-xs font-black text-slate-500 uppercase tracking-wider">Charge</th>
                            <th class="px-5 py-3.5 text-xs font-black text-slate-500 uppercase tracking-wider">Live Progress</th>
                            <th class="px-5 py-3.5 text-xs font-black text-slate-500 uppercase tracking-wider">Status</th>
                            <th class="px-5 py-3.5 text-right text-xs font-black text-slate-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach($orders as $order)
                            <tr x-data="{
                                status: @js($order->status),
                                startCount: {{ $order->start_count ?? 'null' }},
                                remains: {{ $order->remains ?? 'null' }},
                                progress: null,
                                reported: @js((bool)($order->is_reported ?? false)),
                                submittingReport: false,
                                init() {
                                    const isFinal = () => {
                                        const s = (this.status || '').toLowerCase();
                                        return s === 'completed' || s === 'canceled' || s === 'cancelled' || s === 'partial';
                                    };
                                    const calcProgress = () => {
                                        if (this.remains === null) return null;
                                        const qty = {{ (int) $order->quantity }};
                                        if (!qty || qty <= 0) return null;
                                        const done = qty - Math.max(0, Number(this.remains));
                                        const pct = Math.round((done / qty) * 100);
                                        return Math.min(100, Math.max(0, pct));
                                    };
                                    this.progress = calcProgress();
                                    const poll = async () => {
                                        try {
                                            const res = await fetch(`/api/boost/check/{{ $order->id }}`);
                                            if (!res.ok) return;
                                            const data = await res.json();
                                            if (data && data.status) {
                                                this.status = data.status;
                                                if (data.start_count !== undefined) this.startCount = data.start_count;
                                                if (data.remains !== undefined) this.remains = data.remains;
                                                this.progress = calcProgress();
                                            }
                                        } catch (e) {}
                                    };

                                    const interval = setInterval(async () => {
                                        if (isFinal()) {
                                            clearInterval(interval);
                                            return;
                                        }
                                        await poll();
                                    }, 10000);
                                }
                            }" class="hover:bg-slate-50/70 transition">
                                <td class="px-5 py-4 whitespace-nowrap text-xs">
                                    <div class="font-black text-slate-900">#{{ $order->id }}</div>
                                    <div class="text-[10px] font-bold text-slate-400 mt-0.5">{{ $order->created_at->format('M d, Y · H:i') }}</div>
                                </td>
                                <td class="px-5 py-4 text-xs font-black text-slate-800 max-w-xs">
                                    <div>{{ $order->service->name ?? 'Boost Service #' . $order->boost_service_id }}</div>
                                    <div class="text-[10px] font-mono text-slate-400 truncate mt-0.5">{{ $order->link }}</div>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-xs font-black text-slate-700">
                                    {{ number_format($order->quantity) }}
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-xs font-black text-[#0d9488]">
                                    {{ $currencySymbol ?? '$' }}{{ ($formatAmount ?? fn($x) => number_format($x, 2))($order->charge) }}
                                </td>
                                <td class="px-5 py-4 text-xs">
                                    <div class="w-44 space-y-1">
                                        <div class="flex items-center justify-between text-[11px] font-bold">
                                            <span class="text-slate-700" x-text="progress === null ? 'Starting...' : (progress + '%')"></span>
                                            <span x-show="remains !== null" class="text-slate-400 text-[10px]">Left: <span x-text="remains"></span></span>
                                        </div>
                                        <div class="h-2 bg-slate-100 rounded-full overflow-hidden border border-slate-200/50">
                                            <div class="h-full bg-gradient-to-r from-teal-500 to-[#0d9488] transition-all duration-500" :style="`width: ${progress === null ? 5 : progress}%`"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-xs">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider capitalize"
                                          :class="{
                                              'bg-emerald-100 text-emerald-800 border border-emerald-300': ['completed', 'success'].includes((status || '').toLowerCase()),
                                              'bg-sky-100 text-sky-800 border border-sky-300 animate-pulse': ['processing', 'in progress'].includes((status || '').toLowerCase()),
                                              'bg-amber-100 text-amber-800 border border-amber-300': ['pending'].includes((status || '').toLowerCase()),
                                              'bg-rose-100 text-rose-800 border border-rose-300': ['canceled', 'cancelled'].includes((status || '').toLowerCase()),
                                              'bg-slate-100 text-slate-700 border border-slate-300': !['completed', 'success', 'processing', 'in progress', 'pending', 'canceled', 'cancelled'].includes((status || '').toLowerCase())
                                          }"
                                          x-text="status"></span>
                                    <div class="text-[10px] text-slate-400 mt-1 font-bold" x-show="startCount !== null">Start: <span x-text="startCount"></span></div>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-right text-xs">
                                    <template x-if="reported">
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[10px] font-black bg-amber-100 text-amber-800 border border-amber-300">
                                            🚩 Reported
                                        </span>
                                    </template>
                                    <template x-if="!reported && !['completed', 'received', 'success'].includes((status || '').toLowerCase())">
                                        <button 
                                            type="button" 
                                            @click="
                                                if (confirm('Report issue with this boosting order to support?')) {
                                                    submittingReport = true;
                                                    fetch('/orders/report', {
                                                        method: 'POST',
                                                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                                        body: JSON.stringify({ order_type: 'boost', order_id: {{ $order->id }}, reason: 'Boosting order issue reported.' })
                                                    })
                                                    .then(r => r.json())
                                                    .then(data => {
                                                        submittingReport = false;
                                                        if (data.success) {
                                                            reported = true;
                                                            alert(data.message);
                                                        } else {
                                                            alert(data.message || 'Could not report order.');
                                                        }
                                                    })
                                                    .catch(e => { submittingReport = false; alert('Error submitting report.'); });
                                                }
                                            "
                                            class="inline-flex items-center gap-1 text-[11px] font-black text-rose-700 hover:text-rose-900 bg-rose-50 hover:bg-rose-100 border border-rose-200 px-3 py-1.5 rounded-xl transition cursor-pointer"
                                            :disabled="submittingReport">
                                            <span x-show="!submittingReport">🚩 Report</span>
                                            <span x-show="submittingReport">Submitting...</span>
                                        </button>
                                    </template>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile App Cards View -->
            <div class="lg:hidden divide-y divide-slate-100">
                @foreach($orders as $order)
                    <div class="p-4 space-y-3" x-data="{
                        status: @js($order->status),
                        startCount: {{ $order->start_count ?? 'null' }},
                        remains: {{ $order->remains ?? 'null' }},
                        progress: null,
                        reported: @js((bool)($order->is_reported ?? false)),
                        submittingReport: false,
                        init() {
                            const isFinal = () => {
                                const s = (this.status || '').toLowerCase();
                                return s === 'completed' || s === 'canceled' || s === 'cancelled' || s === 'partial';
                            };
                            const calcProgress = () => {
                                if (this.remains === null) return null;
                                const qty = {{ (int) $order->quantity }};
                                if (!qty || qty <= 0) return null;
                                const done = qty - Math.max(0, Number(this.remains));
                                const pct = Math.round((done / qty) * 100);
                                return Math.min(100, Math.max(0, pct));
                            };
                            this.progress = calcProgress();
                            const poll = async () => {
                                try {
                                    const res = await fetch(`/api/boost/check/{{ $order->id }}`);
                                    if (!res.ok) return;
                                    const data = await res.json();
                                    if (data && data.status) {
                                        this.status = data.status;
                                        if (data.start_count !== undefined) this.startCount = data.start_count;
                                        if (data.remains !== undefined) this.remains = data.remains;
                                        this.progress = calcProgress();
                                    }
                                } catch (e) {}
                            };

                            const interval = setInterval(async () => {
                                if (isFinal()) { clearInterval(interval); return; }
                                await poll();
                            }, 10000);
                        }
                    }">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-black text-slate-900">Order #{{ $order->id }}</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-black uppercase"
                                          :class="{
                                              'bg-emerald-100 text-emerald-800': ['completed', 'success'].includes((status || '').toLowerCase()),
                                              'bg-sky-100 text-sky-800': ['processing', 'in progress'].includes((status || '').toLowerCase()),
                                              'bg-amber-100 text-amber-800': ['pending'].includes((status || '').toLowerCase()),
                                              'bg-rose-100 text-rose-800': ['canceled', 'cancelled'].includes((status || '').toLowerCase()),
                                              'bg-slate-100 text-slate-700': !['completed', 'success', 'processing', 'in progress', 'pending', 'canceled', 'cancelled'].includes((status || '').toLowerCase())
                                          }"
                                          x-text="status"></span>
                                </div>
                                <div class="text-xs font-black text-slate-800 mt-1">
                                    {{ $order->service->name ?? 'Service #' . $order->boost_service_id }}
                                </div>
                                <div class="text-[10px] font-bold text-slate-400 mt-0.5">
                                    {{ $order->created_at->format('M d, Y · H:i') }} &bull; {{ number_format($order->quantity) }} units
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-black text-[#0d9488]">
                                    {{ $currencySymbol ?? '$' }}{{ ($formatAmount ?? fn($x) => number_format($x, 2))($order->charge) }}
                                </div>
                            </div>
                        </div>

                        <!-- Progress Bar & Link -->
                        <div class="space-y-1.5 pt-1">
                            <div class="flex items-center justify-between text-[11px] font-bold">
                                <span class="text-slate-700" x-text="progress === null ? 'Delivery Starting...' : (`${progress}% Completed`)"></span>
                                <span x-show="remains !== null" class="text-slate-400 text-[10px]">Left: <span x-text="remains"></span></span>
                            </div>
                            <div class="h-2 bg-slate-100 rounded-full overflow-hidden border border-slate-200/50">
                                <div class="h-full bg-gradient-to-r from-teal-500 to-[#0d9488] transition-all duration-500" :style="`width: ${progress === null ? 5 : progress}%`"></div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-2 pt-1 border-t border-slate-100">
                            <div class="text-[10px] font-mono text-slate-400 truncate max-w-[200px]">
                                {{ $order->link }}
                            </div>

                            <template x-if="reported">
                                <span class="text-[10px] font-black text-amber-700 bg-amber-100 px-2.5 py-0.5 rounded-full">
                                    Reported
                                </span>
                            </template>
                            <template x-if="!reported && !['completed', 'received', 'success'].includes((status || '').toLowerCase())">
                                <button 
                                    type="button" 
                                    @click="
                                        if (confirm('Report issue with this order?')) {
                                            submittingReport = true;
                                            fetch('/orders/report', {
                                                method: 'POST',
                                                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                                body: JSON.stringify({ order_type: 'boost', order_id: {{ $order->id }}, reason: 'Boosting mobile report.' })
                                            })
                                            .then(r => r.json())
                                            .then(data => {
                                                submittingReport = false;
                                                if (data.success) { reported = true; alert(data.message); }
                                                else { alert(data.message || 'Error reporting.'); }
                                            })
                                            .catch(e => { submittingReport = false; alert('Network error.'); });
                                        }
                                    "
                                    class="text-[10px] font-black text-rose-700 bg-rose-50 border border-rose-200 px-2.5 py-1 rounded-lg"
                                    :disabled="submittingReport">
                                    🚩 Report
                                </button>
                            </template>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/70">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

</div>

@endsection

<script>
    window.__boostingServices = @json(($services ?? collect())->values());
    window.__boostingFavorites = @json(($favoriteBoostIds ?? collect())->values());
    window.__boostingInitialServiceId = @json((string) request()->query('boost_service_id', ''));
    window.__boostingInitialLink = @json((string) request()->query('link', ''));
    window.__boostingInitialQty = @json((string) request()->query('quantity', ''));
    window.__boostingCurrencyFx = {{ (float) ($currencyFx ?? 1) }};
    window.__boostingCurrencySymbol = @json($currencySymbol ?? '$');

    function boostingPageData() {
        return {
            services: Array.isArray(window.__boostingServices) ? window.__boostingServices : [],
            favorites: Array.isArray(window.__boostingFavorites) ? window.__boostingFavorites : [],
            favoritesOnly: false,
            activePlatform: 'all',
            category: '',
            search: '',
            serviceId: window.__boostingInitialServiceId || '',
            link: window.__boostingInitialLink || '',
            qty: window.__boostingInitialQty || '',
            submitting: false,
            fx: Number(window.__boostingCurrencyFx || 1),
            symbol: window.__boostingCurrencySymbol || '$',
            init() {
                if (this.favoritesOnly && (!this.favorites || this.favorites.length === 0)) {
                    this.favoritesOnly = false;
                }
            },
            toggleFavoritesOnly() {
                const next = !this.favoritesOnly;
                if (next && (!this.favorites || this.favorites.length === 0)) {
                    this.favoritesOnly = false;
                    return;
                }
                this.favoritesOnly = next;
            },
            format(v) { return Number(v).toFixed(this.fx === 1 ? 4 : 2); },
            get popularPlatforms() {
                const countMatches = (keys) => {
                    return this.services.filter(s => {
                        const txt = ((s.category || '') + ' ' + (s.name || '')).toLowerCase();
                        return keys.some(k => txt.includes(k));
                    }).length;
                };

                return [
                    { id: 'all', name: 'All Services', icon: '🌟', count: this.services.length },
                    { id: 'tiktok', name: 'TikTok', icon: '🎵', count: countMatches(['tiktok']) },
                    { id: 'instagram', name: 'Instagram', icon: '📸', count: countMatches(['instagram', 'insta']) },
                    { id: 'twitter', name: 'Twitter / X', icon: '🐦', count: countMatches(['twitter', 'x -', 'x - twitter']) },
                    { id: 'snapchat', name: 'Snapchat', icon: '👻', count: countMatches(['snapchat', 'snap ']) },
                    { id: 'youtube', name: 'YouTube', icon: '▶️', count: countMatches(['youtube']) },
                    { id: 'telegram', name: 'Telegram', icon: '✈️', count: countMatches(['telegram']) },
                    { id: 'facebook', name: 'Facebook', icon: '📘', count: countMatches(['facebook']) },
                    { id: 'spotify', name: 'Spotify', icon: '🎧', count: countMatches(['spotify']) },
                    { id: 'other', name: 'Other', icon: '🌐', count: 0 },
                ];
            },
            setPlatform(id) {
                this.activePlatform = id;
                this.category = '';
                this.serviceId = '';
            },
            get availableCategories() {
                const plat = (this.activePlatform || 'all').toLowerCase();
                const matched = this.services.filter(s => {
                    if (plat === 'all') return true;
                    const text = ((s.category || '') + ' ' + (s.name || '')).toLowerCase();
                    if (plat === 'tiktok') return text.includes('tiktok');
                    if (plat === 'instagram') return text.includes('instagram') || text.includes('insta');
                    if (plat === 'twitter') return text.includes('twitter') || text.includes('x -');
                    if (plat === 'snapchat') return text.includes('snapchat') || text.includes('snap ');
                    if (plat === 'youtube') return text.includes('youtube');
                    if (plat === 'telegram') return text.includes('telegram');
                    if (plat === 'facebook') return text.includes('facebook');
                    if (plat === 'spotify') return text.includes('spotify');
                    if (plat === 'other') {
                        const main = ['tiktok', 'instagram', 'insta', 'twitter', 'x -', 'snapchat', 'snap ', 'youtube', 'telegram', 'facebook', 'spotify'];
                        return !main.some(m => text.includes(m));
                    }
                    return true;
                });
                return Array.from(new Set(matched.map(s => s.category || 'Other'))).sort();
            },
            get categories() {
                return this.availableCategories;
            },
            get filteredServices() {
                const q = (this.search || '').toLowerCase();
                const plat = (this.activePlatform || 'all').toLowerCase();
                return this.services
                    .filter(s => !this.favoritesOnly || this.favorites.includes(Number(s.id)))
                    .filter(s => {
                        if (plat === 'all') return true;
                        const text = ((s.category || '') + ' ' + (s.name || '')).toLowerCase();
                        if (plat === 'tiktok') return text.includes('tiktok');
                        if (plat === 'instagram') return text.includes('instagram') || text.includes('insta');
                        if (plat === 'twitter') return text.includes('twitter') || text.includes('x -');
                        if (plat === 'snapchat') return text.includes('snapchat') || text.includes('snap ');
                        if (plat === 'youtube') return text.includes('youtube');
                        if (plat === 'telegram') return text.includes('telegram');
                        if (plat === 'facebook') return text.includes('facebook');
                        if (plat === 'spotify') return text.includes('spotify');
                        if (plat === 'other') {
                            const main = ['tiktok', 'instagram', 'insta', 'twitter', 'x -', 'snapchat', 'snap ', 'youtube', 'telegram', 'facebook', 'spotify'];
                            return !main.some(m => text.includes(m));
                        }
                        return true;
                    })
                    .filter(s => !this.category || s.category === this.category)
                    .filter(s => !q || (s.name || '').toLowerCase().includes(q));
            },
            get selectedService() {
                const id = parseInt(this.serviceId || '0', 10);
                return this.services.find(s => s.id === id) || null;
            },
            get totalUsd() {
                const s = this.selectedService;
                const qty = parseInt(this.qty || '0', 10);
                if (!s || !qty || qty < 1) return 0;
                return (qty / 1000) * parseFloat(s.rate);
            },
            get total() {
                return this.totalUsd * this.fx;
            },
            isFav(id) {
                return this.favorites.includes(Number(id));
            },
            async toggleFav() {
                const id = Number(this.serviceId || 0);
                if (!id) return;
                try {
                    const res = await fetch('/favorites/toggle', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                        body: JSON.stringify({ type: 'boost', key: String(id) })
                    });
                    const data = await res.json();
                    if (data && data.favorited === true) {
                        if (!this.favorites.includes(id)) this.favorites.push(id);
                    } else if (data && data.favorited === false) {
                        this.favorites = this.favorites.filter(x => Number(x) !== id);
                    }
                } catch (e) {}
            }
        };
    }
</script>
