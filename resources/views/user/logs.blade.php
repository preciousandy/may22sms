@extends('layouts.dashboard')

@section('page_title', 'Social Media Logs Store')

@section('dashboard_content')

@if(session('success'))
    <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-4 rounded-2xl shadow-sm flex items-center gap-3 animate-fade-in">
        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span class="font-extrabold text-sm">{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div class="mb-6 bg-rose-50 border border-rose-200 text-rose-800 px-5 py-4 rounded-2xl shadow-sm flex items-center gap-3 animate-fade-in">
        <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span class="font-extrabold text-sm">{{ session('error') }}</span>
    </div>
@endif

@if($errors->any())
    <div class="mb-6 bg-rose-50 border border-rose-200 text-rose-800 px-5 py-4 rounded-2xl shadow-sm">
        <ul class="list-disc pl-5 font-bold text-sm space-y-1">
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
    showVideoModal: false 
}" class="space-y-8">

    <!-- Hero Banner with High-Contrast Elegant Styling -->
    <div class="relative overflow-hidden rounded-3xl p-6 sm:p-10 text-white shadow-xl border border-slate-800 bg-slate-900" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0d9488 100%);">
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="space-y-3 max-w-2xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-800/80 border border-slate-700 text-[#facc15] text-xs font-black uppercase tracking-widest">
                    <span>⚡ Instant Delivery</span>
                    <span>•</span>
                    <span>100% Verified Logs</span>
                </div>
                <h2 class="text-3xl sm:text-4xl font-black text-white leading-tight tracking-tight">
                    <span class="text-[#facc15]">Premium</span> Social Media Accounts & Logs
                </h2>
                <p class="text-slate-200 text-sm sm:text-base font-medium leading-relaxed">
                    Aged accounts, cookies, and verified tokens for Facebook, TikTok, Instagram, Twitter, and more. Instant delivery after payment.
                </p>
                <div class="pt-2 flex flex-wrap items-center gap-3">
                    <a href="/logs-store/cart" class="inline-flex items-center gap-2 bg-[#facc15] hover:bg-yellow-400 text-slate-950 font-black text-xs uppercase tracking-wider px-5 py-3 rounded-2xl shadow-lg transition transform hover:-translate-y-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"></path></svg>
                        View Shopping Cart
                        @if(isset($cartCount) && $cartCount > 0)
                            <span class="ml-1 bg-slate-950 text-[#facc15] text-[11px] font-black px-2 py-0.5 rounded-full">{{ $cartCount }}</span>
                        @endif
                    </a>
                    <a href="/tickets?sell_logs=1" class="inline-flex items-center gap-2 bg-slate-800 hover:bg-slate-700 border border-slate-600 text-white font-extrabold text-xs uppercase tracking-wider px-5 py-3 rounded-2xl shadow-md transition">
                        <span>💼 Sell Your Logs</span>
                    </a>
                </div>
            </div>

            <!-- Stats Badge Card -->
            <div class="grid grid-cols-2 gap-3 lg:w-72 bg-slate-800/70 p-4 rounded-2xl border border-slate-700 shrink-0">
                <div class="p-3 bg-slate-900/60 rounded-xl border border-slate-700/60">
                    <div class="text-[10px] font-extrabold text-slate-300 uppercase">Categories</div>
                    <div class="text-xl font-black text-[#facc15] mt-1">{{ count($logItems) }}</div>
                </div>
                <div class="p-3 bg-slate-900/60 rounded-xl border border-slate-700/60">
                    <div class="text-[10px] font-extrabold text-slate-300 uppercase">In Stock</div>
                    <div class="text-xl font-black text-teal-300 mt-1">
                        @php
                            $totalStock = 0;
                            foreach($logItems as $items) {
                                $totalStock += $items->sum('available_stock');
                            }
                        @endphp
                        {{ number_format($totalStock) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Pills & Search Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-4 rounded-2xl border border-gray-100 shadow-sm">
        <!-- Category Filter Pills -->
        <div class="flex items-center gap-2 overflow-x-auto pb-2 md:pb-0 scrollbar-none">
            <button 
                @click="activeCategory = 'all'" 
                :class="activeCategory === 'all' ? 'bg-gray-900 text-white shadow-md' : 'bg-gray-50 text-gray-700 hover:bg-gray-100 border border-gray-200'"
                class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition whitespace-nowrap">
                All Logs
            </button>
            @foreach($logItems as $platform => $items)
                <button 
                    @click="activeCategory = '{{ strtolower($platform) }}'" 
                    :class="activeCategory === '{{ strtolower($platform) }}' ? 'bg-[#0d9488] text-white shadow-md' : 'bg-gray-50 text-gray-700 hover:bg-gray-100 border border-gray-200'"
                    class="px-3.5 py-2 rounded-xl text-xs font-extrabold capitalize transition whitespace-nowrap flex items-center gap-1.5">
                    <span>
                        @if($platform == 'facebook') 📘
                        @elseif($platform == 'instagram') 📸
                        @elseif($platform == 'twitter') 🐦
                        @elseif($platform == 'youtube') ▶️
                        @elseif($platform == 'tiktok') 🎵
                        @elseif($platform == 'discord') 💬
                        @else 📦 @endif
                    </span>
                    {{ $platform }}
                    <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px] font-black bg-black/10 text-current">{{ $items->count() }}</span>
                </button>
            @endforeach
        </div>

        <!-- Search Input -->
        <div class="relative min-w-[240px]">
            <input 
                type="text" 
                x-model="searchQuery" 
                placeholder="Search log title or keyword..." 
                class="w-full pl-9 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-800 placeholder-gray-400 focus:bg-white focus:ring-[#0d9488] focus:border-[#0d9488] transition">
            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </div>
    </div>

    <!-- Category Video Guides / Links Section (If videos exist for categories) -->
    @if(isset($categoryVideos) && $categoryVideos->count() > 0)
        <div class="space-y-4">
            @foreach($categoryVideos as $plat => $vids)
                <div x-show="(activeCategory === 'all' || activeCategory === '{{ strtolower($plat) }}') && {{ $vids->count() }} > 0" class="bg-gradient-to-r from-amber-500/10 via-yellow-500/5 to-teal-500/10 rounded-3xl p-5 sm:p-6 border border-amber-200/60 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2.5">
                            <span class="w-9 h-9 rounded-xl bg-amber-400 text-gray-950 font-black flex items-center justify-center text-base shadow-sm">
                                🎬
                            </span>
                            <div>
                                <h4 class="font-black text-gray-900 text-base capitalize flex items-center gap-1.5">
                                    {{ $plat }} Video Guides & Tutorials
                                </h4>
                                <p class="text-xs text-gray-600 font-semibold">Watch setup guides & tutorial links uploaded by admin for {{ $plat }} logs.</p>
                            </div>
                        </div>
                        <span class="text-xs font-black uppercase tracking-wider text-amber-700 bg-amber-100 border border-amber-200 px-3 py-1 rounded-full">
                            {{ $vids->count() }} {{ Str::plural('Guide', $vids->count()) }}
                        </span>
                    </div>

                    <!-- Video Cards Horizontal Scroll / Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($vids as $vid)
                            <div class="bg-white rounded-2xl border border-amber-200/80 p-4 shadow-sm hover:shadow-md transition flex flex-col justify-between space-y-3">
                                <div>
                                    <div class="flex items-center justify-between gap-2 mb-2">
                                        <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-md bg-gray-900 text-amber-300">
                                            @if($plat == 'facebook') 📘 Facebook
                                            @elseif($plat == 'tiktok') 🎵 TikTok
                                            @elseif($plat == 'instagram') 📸 Instagram
                                            @elseif($plat == 'twitter') 🐦 Twitter
                                            @elseif($plat == 'youtube') ▶️ YouTube
                                            @else 📦 {{ ucfirst($plat) }} @endif
                                        </span>
                                        <span class="text-[10px] font-bold text-gray-400">Tutorial</span>
                                    </div>
                                    <h5 class="font-black text-gray-900 text-sm leading-snug line-clamp-2">{{ $vid->title }}</h5>
                                </div>

                                <div class="pt-2 border-t border-gray-100 flex items-center justify-between gap-2">
                                    <button 
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
                                        class="w-full inline-flex items-center justify-center gap-2 bg-gradient-to-r from-amber-400 to-yellow-500 hover:from-amber-500 hover:to-yellow-600 text-gray-950 font-black text-xs px-4 py-2.5 rounded-xl shadow-sm transition">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M6.3 2.841A1.5 1.5 0 004 4.11v11.78a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/></svg>
                                        Watch Video Guide
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Log Items Grid Grouped by Platform -->
    @forelse($logItems as $platform => $items)
        <div 
            x-show="activeCategory === 'all' || activeCategory === '{{ strtolower($platform) }}'"
            class="space-y-4">
            
            <div class="flex items-center justify-between pb-2 border-b border-gray-200">
                <h3 class="text-xl font-black text-gray-900 capitalize flex items-center gap-2">
                    <span class="text-2xl">
                        @if($platform == 'facebook') 📘
                        @elseif($platform == 'instagram') 📸
                        @elseif($platform == 'twitter') 🐦
                        @elseif($platform == 'youtube') ▶️
                        @elseif($platform == 'tiktok') 🎵
                        @elseif($platform == 'discord') 💬
                        @else 📦 @endif
                    </span>
                    {{ $platform }} Accounts & Logs
                </h3>
                <span class="text-xs font-bold text-gray-500 bg-gray-100 px-3 py-1 rounded-full">
                    {{ $items->count() }} {{ Str::plural('type', $items->count()) }} available
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($items as $item)
                    <div 
                        x-show="searchQuery === '' || @js(strtolower($item->title)).includes(searchQuery.toLowerCase()) || @js(strtolower($item->description ?? '')).includes(searchQuery.toLowerCase())"
                        class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden flex flex-col hover:shadow-xl hover:-translate-y-1 transition duration-300 group">
                        
                        <div class="p-6 flex-1 flex flex-col justify-between space-y-4">
                            <div>
                                <div class="flex justify-between items-start gap-2 mb-3">
                                    <h4 class="font-black text-gray-900 text-lg leading-tight group-hover:text-[#0d9488] transition">
                                        {{ $item->title }}
                                    </h4>
                                    <span class="bg-teal-50 text-[#0d9488] border border-teal-200 font-black px-2.5 py-1 rounded-xl text-sm shrink-0 shadow-sm">
                                        {{ $currencySymbol ?? '$' }}{{ ($formatAmount ?? fn($x) => number_format($x, 2))($item->price) }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 font-medium leading-relaxed mb-3">
                                    {{ $item->description ?: 'High quality verified log account. Delivered instantly upon purchase.' }}
                                </p>
                            </div>

                            <div class="flex items-center justify-between pt-2 border-t border-gray-50">
                                <div class="text-xs font-extrabold px-3 py-1.5 rounded-xl inline-flex items-center gap-1.5 {{ $item->available_stock > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                    <span class="w-2 h-2 rounded-full {{ $item->available_stock > 0 ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500' }}"></span>
                                    {{ $item->available_stock > 0 ? $item->available_stock . ' in stock' : 'Out of Stock' }}
                                </div>
                            </div>
                        </div>

                        <!-- Card Action Footer -->
                        <div class="px-6 py-4 bg-gray-50/80 border-t border-gray-100">
                            @if($item->available_stock > 0)
                                <div x-data="{ open: false, qty: 1, fx: {{ (float) ($currencyFx ?? 1) }}, symbol: @js($currencySymbol ?? '$') }">
                                    <button 
                                        @click="open = true" 
                                        x-show="!open" 
                                        class="w-full bg-[#facc15] hover:bg-yellow-400 text-gray-950 font-black py-2.5 px-4 rounded-2xl shadow-sm transition uppercase tracking-wider text-xs flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                        Add to Cart
                                    </button>

                                    <form action="/logs-store/cart/{{ $item->id }}" method="POST" x-show="open" style="display: none;" class="flex items-center space-x-2">
                                        @csrf
                                        <div class="relative flex-1">
                                            <input 
                                                type="number" 
                                                name="quantity" 
                                                x-model="qty" 
                                                min="1" 
                                                max="{{ $item->available_stock }}" 
                                                class="w-full rounded-xl border-gray-300 py-2 pl-3 pr-8 text-xs font-bold focus:ring-[#0d9488] focus:border-[#0d9488]" 
                                                required>
                                            <span class="absolute right-2.5 top-2.5 text-gray-400 text-[10px] font-black uppercase">Qty</span>
                                        </div>
                                        <button 
                                            type="submit" 
                                            class="bg-[#0d9488] hover:bg-[#0f766e] text-white px-4 py-2 rounded-xl text-xs font-black shadow-sm transition whitespace-nowrap">
                                            Add (<span x-text="symbol"></span><span x-text="(qty * {{ $item->price }} * fx).toFixed(2)"></span>)
                                        </button>
                                    </form>
                                </div>
                            @else
                                <button disabled class="w-full bg-gray-200 text-gray-400 font-extrabold py-2.5 px-4 rounded-2xl text-xs cursor-not-allowed uppercase tracking-wider">
                                    Sold Out
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="bg-white p-16 rounded-3xl shadow-sm text-center border border-gray-100">
            <div class="text-6xl mb-4">🛒</div>
            <h3 class="text-2xl font-black text-gray-900 mb-2">Logs Store is Empty</h3>
            <p class="text-gray-500 font-medium max-w-md mx-auto">No social media log accounts are currently listed. Please check back soon or contact support.</p>
        </div>
    @endforelse

    <!-- Video Modal Player -->
    <div 
        x-show="showVideoModal" 
        class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-md flex items-center justify-center p-4" 
        style="display: none;" 
        @keydown.escape.window="showVideoModal = false">
        
        <div class="bg-slate-900 rounded-3xl max-w-3xl w-full p-6 text-white shadow-2xl relative border border-white/10" @click.away="showVideoModal = false">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-white/10">
                <div class="flex items-center gap-3">
                    <span class="p-2 rounded-xl bg-amber-400 text-gray-950 font-black">🎬</span>
                    <h3 class="text-lg font-black text-white" x-text="activeVideo ? activeVideo.title : 'Watch Video Guide'"></h3>
                </div>
                <button @click="showVideoModal = false" class="text-gray-400 hover:text-white transition">
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
                        <div class="p-8 text-center space-y-4">
                            <p class="text-sm font-semibold text-gray-300">This guide link points to an external link:</p>
                            <p class="text-xs font-mono text-amber-400 bg-white/5 p-3 rounded-xl break-all" x-text="activeVideo.raw_url"></p>
                            <a :href="activeVideo.raw_url" target="_blank" class="inline-flex items-center gap-2 bg-[#facc15] hover:bg-yellow-400 text-gray-950 font-black text-xs px-6 py-3 rounded-xl shadow-lg transition">
                                Open Tutorial Page ↗
                            </a>
                        </div>
                    </template>
                </div>
            </template>

            <div class="mt-4 pt-3 border-t border-white/10 flex justify-end">
                <button @click="showVideoModal = false" class="px-5 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white font-extrabold text-xs transition">
                    Close Player
                </button>
            </div>
        </div>
    </div>

    <!-- Purchase History Section -->
    <div class="mt-12 bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white flex items-center justify-between">
            <h3 class="text-lg font-black text-gray-900 flex items-center gap-2">
                <span>📦</span> My Purchased Logs & Accounts
            </h3>
            <span class="text-xs font-bold text-gray-500">History & Credentials</span>
        </div>
        
        @if(isset($orders) && $orders->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3.5 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3.5 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Item Purchased</th>
                            <th class="px-6 py-3.5 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Qty</th>
                            <th class="px-6 py-3.5 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3.5 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Account Credentials</th>
                            <th class="px-6 py-3.5 text-right text-xs font-black text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($orders as $order)
                            <tr class="hover:bg-gray-50/60 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-xs font-semibold text-gray-500">
                                    {{ $order->created_at->format('M d, Y H:i') }}
                                </td>
                                <td class="px-6 py-4 text-xs font-black text-gray-900">
                                    {{ $order->credentials->first()->item->title ?? 'Log Order #' . $order->id }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-gray-700">
                                    {{ $order->total_items }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-[#0d9488] font-black">
                                    {{ $currencySymbol ?? '$' }}{{ ($formatAmount ?? fn($x) => number_format($x, 2))($order->total_amount) }}
                                </td>
                                <td class="px-6 py-4 text-xs text-gray-700 min-w-[320px]">
                                    @php
                                        $allCredsText = $order->credentials->pluck('data')->implode("\n");
                                    @endphp
                                    <div x-data="{ copied: false, text: @js($allCredsText) }" class="space-y-2">
                                        <div class="bg-gray-50 border border-gray-200 rounded-2xl p-3.5 font-mono text-xs text-gray-900 font-bold max-h-36 overflow-y-auto whitespace-pre-wrap select-all shadow-inner border-l-4 border-l-[#0d9488]">
                                            @foreach($order->credentials as $cred)
                                                <div class="mb-1.5 pb-1.5 border-b border-gray-200/70 last:border-0 last:mb-0 last:pb-0 font-mono tracking-tight text-[#0f172a]">
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
                                            class="inline-flex items-center gap-1.5 text-[11px] font-black px-3 py-1.5 rounded-xl transition shadow-sm bg-teal-50 hover:bg-teal-100 text-[#0d9488] border border-teal-200">
                                            <svg x-show="!copied" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                            <svg x-show="copied" style="display:none;" class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            <span x-text="copied ? '✓ Credentials Copied!' : '📋 Copy Credentials'"></span>
                                        </button>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-xs" x-data="{ reported: @js((bool)($order->is_reported ?? false)), submitting: false }">
                                    <template x-if="reported">
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[10px] font-black bg-amber-100 text-amber-800 border border-amber-300">
                                            🚩 Issue Reported
                                        </span>
                                    </template>
                                    <template x-if="!reported">
                                        <button 
                                            type="button" 
                                            @click="
                                                if (confirm('Report issue with this log order to admin?')) {
                                                    submitting = true;
                                                    fetch('/orders/report', {
                                                        method: 'POST',
                                                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                                        body: JSON.stringify({ order_type: 'logs', order_id: {{ $order->id }}, reason: 'Log credentials issue reported from Logs page.' })
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
                                            class="inline-flex items-center gap-1 text-[11px] font-black text-rose-700 hover:text-rose-900 bg-rose-50 hover:bg-rose-100 border border-rose-200 px-3 py-1.5 rounded-xl transition"
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
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                {{ $orders->links() }}
            </div>
        @else
            <div class="p-10 text-center text-gray-500 font-medium text-xs">
                You haven't purchased any log accounts yet.
            </div>
        @endif
    </div>

</div>

@endsection
