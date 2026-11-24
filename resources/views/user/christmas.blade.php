@extends('layouts.dashboard')

@section('page_title', 'Christmas Wonderland Hub 🎄')

@section('dashboard_content')
@php
    $user = auth()->user();
    $treeCfg = $creation?->tree_config ?? [
        'topper' => '⭐',
        'lights' => 'warm_gold',
        'garland' => 'gold_tinsel',
        'slots' => ['🔴', '🔔', '🟡', '🎁', '🔵', '❄️', '🦌', '🧦'],
    ];
    $snowmanCfg = $creation?->snowman_config ?? [
        'name' => explode(' ', $user->name ?? 'Frosty')[0] . "'s Frosty",
        'hat' => '🎩',
        'scarf' => '#dc2626',
        'buttons' => '⚫',
        'eyes' => '😊',
        'nose' => '🥕',
        'accessory' => '🧹',
    ];
    $cardCfg = $creation?->greeting_card_config ?? [
        'template' => 'royal_red',
        'recipient' => 'My Dear Friend',
        'sender' => $user->name ?? 'May22SMS User',
        'message' => 'Wishing you joy, peace, prosperity, and instant digital success this Christmas season and throughout the New Year!',
    ];
    $visitedSpots = (array) ($creation?->village_Visited_spots ?? []);
@endphp

<div x-data="christmasWonderlandApp({
        claimedAdventDays: @js($claimedAdventDays),
        claimedGiftIds: @js($claimedGiftIds),
        earnedBadgeIds: @js($earnedBadgeIds),
        treeConfig: @js($treeCfg),
        snowmanConfig: @js($snowmanCfg),
        cardConfig: @js($cardCfg),
        visitedSpots: @js($visitedSpots),
        currencySymbol: @js($currencySymbol ?? '$'),
        currencyFx: {{ (float) ($currencyFx ?? 1) }},
        countdownTarget: @js($christmasMode && $christmasMode->countdown_target_at ? $christmasMode->countdown_target_at->toIsoString() : now()->setMonth(12)->setDay(25)->startOfDay()->toIsoString()),
        popupsEnabled: @js((bool) ($featureSettings['xmas_popups_enabled'] ?? true)),
        musicEnabled: @js((bool) ($featureSettings['xmas_music_enabled'] ?? true))
     })"
     x-init="initWonderland()"
     class="space-y-6">

    <!-- Festive Interactive Pop-Up Toast Modal (Non-blocking, dismissible) -->
    <div x-show="popupAlert.open"
         x-cloak
         x-transition
         style="position: fixed; right: 20px; top: 82px; z-index: 80; max-width: 360px;"
         class="rounded-3xl p-4 text-white shadow-2xl border-2 border-amber-300/80"
         style="background: linear-gradient(135deg, #7f1d1d 0%, #991b1b 50%, #14532d 100%);">
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-start gap-3">
                <span class="text-3xl shrink-0" x-text="popupAlert.icon || '🎄'"></span>
                <div>
                    <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase bg-amber-400 text-slate-950" x-text="popupAlert.tag || 'Christmas Surprise'"></span>
                    <h4 class="font-black text-sm text-white mt-1" x-text="popupAlert.title"></h4>
                    <p class="text-xs text-white/90 mt-0.5" x-text="popupAlert.message"></p>
                </div>
            </div>
            <button type="button" @click="dismissPopup()" class="text-white/75 hover:text-white font-black text-lg leading-none cursor-pointer">&times;</button>
        </div>
    </div>

    <!-- =====================================================================
         1. HERO BANNER: CHRISTMAS COUNTDOWN, MUSIC CONTROLS & SNOWMAN PROFILE
         ===================================================================== -->
    <div class="rounded-3xl p-6 sm:p-8 text-white shadow-2xl relative overflow-hidden border border-white/20"
         style="background: linear-gradient(135deg, #7f1d1d 0%, #991b1b 45%, #14532d 100%);">
        <div class="absolute -right-12 -top-12 w-64 h-64 rounded-full bg-amber-400/15 blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="max-w-2xl">
                <div class="flex items-center gap-2.5 flex-wrap mb-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-amber-400 text-slate-950 shadow-sm">
                        <span>🎅</span> MAY22SMS Interactive Christmas Experience
                    </span>

                    <!-- Optional Christmas Music & Ambience Player (Disabled by default until user clicks Play) -->
                    @if($featureSettings['xmas_music_enabled'] ?? true)
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-black/35 backdrop-blur-md border border-white/20 text-xs font-bold">
                            <button type="button" @click="toggleMusic()" class="flex items-center gap-1.5 text-amber-300 hover:text-white transition cursor-pointer">
                                <span x-text="musicPlaying ? '⏸ Pause Carol Chimes' : '🎵 Play Christmas Chimes'"></span>
                            </button>
                            <span class="text-white/30">|</span>
                            <button type="button" @click="sfxMuted = !sfxMuted" class="text-[11px] text-white/80 hover:text-white cursor-pointer"
                                    x-text="sfxMuted ? '🔇 SFX Muted' : '🔔 SFX On'"></button>
                        </div>
                    @endif
                </div>

                <h1 class="text-2xl sm:text-3xl md:text-4xl font-black tracking-tight text-white">
                    {{ $christmasMode?->greeting_title ?: 'Welcome to the MAY22SMS Christmas Wonderland! 🎄✨' }}
                </h1>
                <p class="text-xs sm:text-sm text-white/90 mt-2 leading-relaxed">
                    Unwrap daily Advent Calendar boxes, decorate your personal Christmas tree, build your custom snowman, explore the snowy Christmas village, complete festive challenges, and send branded holiday greeting cards!
                </p>

                <!-- Live 4-Box Christmas Countdown -->
                <div class="mt-5 inline-grid grid-cols-4 gap-2.5 sm:gap-3 bg-black/30 backdrop-blur-md p-3 rounded-2xl border border-white/20">
                    <div class="text-center px-2 sm:px-3">
                        <span class="text-xl sm:text-2xl font-mono font-black text-amber-300 block" x-text="cd.days">00</span>
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-white/80">Days</span>
                    </div>
                    <div class="text-center px-2 sm:px-3 border-l border-white/15">
                        <span class="text-xl sm:text-2xl font-mono font-black text-white block" x-text="cd.hours">00</span>
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-white/80">Hours</span>
                    </div>
                    <div class="text-center px-2 sm:px-3 border-l border-white/15">
                        <span class="text-xl sm:text-2xl font-mono font-black text-white block" x-text="cd.mins">00</span>
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-white/80">Mins</span>
                    </div>
                    <div class="text-center px-2 sm:px-3 border-l border-white/15">
                        <span class="text-xl sm:text-2xl font-mono font-black text-emerald-300 block" x-text="cd.secs">00</span>
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-white/80">Secs</span>
                    </div>
                </div>
            </div>

            <!-- User's Festive Profile Card with Saved Snowman & Earned Badges Count -->
            <div class="bg-black/30 backdrop-blur-md border border-white/25 rounded-3xl p-5 flex items-center gap-4 shrink-0 min-w-[260px]">
                <!-- Live Mini Snowman Avatar -->
                <div class="relative w-16 h-20 rounded-2xl bg-gradient-to-b from-sky-900/80 to-slate-900/90 border border-white/20 flex flex-col items-center justify-center shrink-0">
                    <span class="text-lg -mb-2 z-10" x-text="snowman.hat"></span>
                    <div class="w-7 h-7 rounded-full bg-white text-slate-900 flex items-center justify-center text-[10px] font-bold relative shadow">
                        <span x-text="snowman.nose"></span>
                    </div>
                    <div class="w-8 h-2 rounded-full -my-0.5 z-10" :style="'background:' + snowman.scarf"></div>
                    <div class="w-10 h-9 rounded-full bg-white text-slate-900 flex flex-col items-center justify-center text-[8px] leading-none shadow">
                        <span x-text="snowman.buttons"></span>
                        <span x-text="snowman.buttons"></span>
                    </div>
                    <span class="absolute right-0.5 bottom-1 text-sm" x-text="snowman.accessory"></span>
                </div>

                <div>
                    <span class="text-[10px] font-black uppercase tracking-wider text-amber-300 block">Festive Profile Card</span>
                    <h3 class="font-black text-base text-white truncate">{{ $user->name }}</h3>
                    <div class="text-xs text-emerald-200 font-bold mt-0.5" x-text="snowman.name"></div>
                    <div class="mt-2 flex items-center gap-2 text-[11px] font-extrabold">
                        <span class="px-2 py-0.5 rounded-lg bg-white/15 text-white">
                            🎁 <span x-text="claimedAdvent.length"></span>/25 Advent
                        </span>
                        <span class="px-2 py-0.5 rounded-lg bg-amber-400 text-slate-950">
                            🏅 <span x-text="earnedBadges.length"></span> Badges
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- =====================================================================
         2. INTERACTIVE NAVIGATION PILLS
         ===================================================================== -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
        @if($featureSettings['xmas_advent_enabled'] ?? true)
            <button type="button" @click="tab = 'advent'"
                    :class="tab === 'advent' ? 'bg-red-600 text-white shadow-md' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50'"
                    class="px-4 py-2.5 rounded-2xl font-black text-xs whitespace-nowrap transition cursor-pointer flex items-center gap-1.5">
                <span>🗓️</span> <span>1. Advent Calendar (25 Days)</span>
            </button>
        @endif
        @if($featureSettings['xmas_tree_enabled'] ?? true)
            <button type="button" @click="tab = 'tree'"
                    :class="tab === 'tree' ? 'bg-emerald-600 text-white shadow-md' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50'"
                    class="px-4 py-2.5 rounded-2xl font-black text-xs whitespace-nowrap transition cursor-pointer flex items-center gap-1.5">
                <span>🎄</span> <span>2. Decorate Christmas Tree</span>
            </button>
        @endif
        @if($featureSettings['xmas_snowman_enabled'] ?? true)
            <button type="button" @click="tab = 'snowman'"
                    :class="tab === 'snowman' ? 'bg-sky-600 text-white shadow-md' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50'"
                    class="px-4 py-2.5 rounded-2xl font-black text-xs whitespace-nowrap transition cursor-pointer flex items-center gap-1.5">
                <span>⛄</span> <span>3. Snowman Builder</span>
            </button>
        @endif
        @if($featureSettings['xmas_village_enabled'] ?? true)
            <button type="button" @click="tab = 'village'"
                    :class="tab === 'village' ? 'bg-indigo-600 text-white shadow-md' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50'"
                    class="px-4 py-2.5 rounded-2xl font-black text-xs whitespace-nowrap transition cursor-pointer flex items-center gap-1.5">
                <span>🏘️</span> <span>4. Christmas Village</span>
            </button>
        @endif
        @if($featureSettings['xmas_challenges_enabled'] ?? true)
            <button type="button" @click="tab = 'challenges'"
                    :class="tab === 'challenges' ? 'bg-amber-500 text-slate-950 shadow-md' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50'"
                    class="px-4 py-2.5 rounded-2xl font-black text-xs whitespace-nowrap transition cursor-pointer flex items-center gap-1.5">
                <span>🎯</span> <span>5. Challenges & Badges</span>
            </button>
        @endif
        @if($featureSettings['xmas_greeting_cards_enabled'] ?? true)
            <button type="button" @click="tab = 'cards'"
                    :class="tab === 'cards' ? 'bg-rose-600 text-white shadow-md' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50'"
                    class="px-4 py-2.5 rounded-2xl font-black text-xs whitespace-nowrap transition cursor-pointer flex items-center gap-1.5">
                <span>💌</span> <span>6. Greeting Card Studio</span>
            </button>
        @endif
        @if($featureSettings['xmas_mystery_gifts_enabled'] ?? true)
            <button type="button" @click="tab = 'gifts'"
                    :class="tab === 'gifts' ? 'bg-[#0d9488] text-white shadow-md' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50'"
                    class="px-4 py-2.5 rounded-2xl font-black text-xs whitespace-nowrap transition cursor-pointer flex items-center gap-1.5">
                <span>🎁</span> <span>7. Festive Gift Boxes</span>
            </button>
        @endif
    </div>

    <!-- Global Status Banner -->
    <template x-if="statusBanner.msg !== ''">
        <div :class="statusBanner.ok ? 'bg-emerald-500/20 border-emerald-400 text-emerald-200' : 'bg-rose-500/20 border-rose-400 text-rose-200'"
             class="rounded-2xl border px-5 py-3.5 text-xs sm:text-sm font-black flex items-center justify-between">
            <span x-text="statusBanner.msg"></span>
            <button type="button" @click="statusBanner.msg = ''" class="font-bold text-lg leading-none">&times;</button>
        </div>
    </template>

    <!-- =====================================================================
         SECTION 1: 25-DAY CHRISTMAS ADVENT CALENDAR
         ===================================================================== -->
    @if($featureSettings['xmas_advent_enabled'] ?? true)
    <div x-show="tab === 'advent'" class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-200 shadow-sm space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-gray-100 pb-4">
            <div>
                <h2 class="text-xl font-black text-gray-900 flex items-center gap-2">
                    <span>🗓️ 25-Day Christmas Advent Calendar</span>
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">Unlock each numbered Christmas gift box as December progresses for free wallet credits, SMS credits, and discount codes!</p>
            </div>
            <div class="px-3.5 py-2 rounded-2xl bg-red-50 border border-red-200 text-red-700 text-xs font-black">
                Unwrapped: <span x-text="claimedAdvent.length"></span> / 25 Boxes
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3.5">
            @foreach($adventDays as $day)
                @php
                    $unlocked = $day->isUnlockedNow();
                @endphp
                <div class="rounded-2xl border-2 p-4 flex flex-col justify-between transition relative overflow-hidden {{ $day->day_number === 25 ? 'sm:col-span-2 md:col-span-1 border-amber-400 bg-gradient-to-br from-red-900 via-red-800 to-emerald-900 text-white' : ($unlocked ? 'border-emerald-500/50 bg-emerald-50/20' : 'border-gray-200 bg-gray-50 opacity-75') }}">
                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black {{ $day->day_number === 25 ? 'bg-amber-400 text-slate-950' : 'bg-red-600 text-white' }}">
                            Day {{ $day->day_number }}
                        </span>
                        <span class="text-2xl">{{ $day->icon ?: '🎁' }}</span>
                    </div>

                    <div class="my-3">
                        <h4 class="font-black text-xs sm:text-sm {{ $day->day_number === 25 ? 'text-white' : 'text-gray-900' }}">{{ $day->title }}</h4>
                        <p class="text-[11px] {{ $day->day_number === 25 ? 'text-amber-200' : 'text-gray-500' }} mt-0.5 line-clamp-2">{{ $day->description }}</p>
                        <div class="mt-2 text-xs font-black {{ $day->day_number === 25 ? 'text-amber-300' : 'text-[#0d9488]' }}">
                            @if($day->reward_value_usd > 0)
                                +{{ $currencySymbol ?? '$' }}{{ number_format($day->reward_value_usd * ($currencyFx ?? 1), 2) }}
                            @elseif($day->discount_percent > 0)
                                {{ $day->discount_percent }}% OFF Coupon
                            @else
                                Mystery Gift
                            @endif
                        </div>
                    </div>

                    <div>
                        <template x-if="claimedAdvent.includes({{ $day->day_number }})">
                            <span class="w-full py-2 rounded-xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-700 font-black text-xs flex items-center justify-center gap-1">
                                ✅ Unwrapped
                            </span>
                        </template>

                        <template x-if="!claimedAdvent.includes({{ $day->day_number }})">
                            @if($unlocked)
                                <button type="button"
                                        @click="claimAdventBox({{ $day->day_number }})"
                                        :disabled="claimingAdventDay === {{ $day->day_number }}"
                                        class="w-full py-2 rounded-xl bg-gradient-to-r from-red-600 to-amber-500 hover:from-red-500 hover:to-amber-400 text-white font-black text-xs shadow-md transition cursor-pointer active:scale-95">
                                    <span x-text="claimingAdventDay === {{ $day->day_number }} ? 'Unwrapping...' : '🎁 Unwrap Box'"></span>
                                </button>
                            @else
                                <span class="w-full py-2 rounded-xl bg-gray-200/70 text-gray-500 font-bold text-[11px] flex items-center justify-center gap-1">
                                    🔒 Dec {{ $day->day_number }}
                                </span>
                            @endif
                        </template>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- =====================================================================
         SECTION 2: INTERACTIVE CHRISTMAS TREE DECORATOR
         ===================================================================== -->
    @if($featureSettings['xmas_tree_enabled'] ?? true)
    <div x-show="tab === 'tree'" style="display: none;" class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-200 shadow-sm">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
            <!-- Left: Interactive Visual Pine Tree Stage -->
            <div class="rounded-3xl p-6 flex flex-col items-center justify-center min-h-[430px] relative overflow-hidden border border-emerald-500/30"
                 style="background: radial-gradient(circle at 50% 25%, #064e3b 0%, #022c22 70%, #090d16 100%);">

                <div class="text-xs font-black uppercase tracking-wider text-amber-300 mb-3">
                    ✨ Click any ornament on the tree or pick below to customize
                </div>

                <!-- Tree Topper -->
                <div class="text-4xl sm:text-5xl mb-1 animate-bounce cursor-pointer select-none"
                     @click="cycleTreeTopper()"
                     title="Click to change tree topper"
                     x-text="tree.topper"></div>

                <!-- 3-Tier Stylized Evergreen Pine Tree -->
                <div class="relative flex flex-col items-center select-none">
                    <!-- Top Pine Tier -->
                    <div class="w-36 h-24 bg-gradient-to-b from-emerald-500 to-emerald-700 rounded-t-full flex items-center justify-around px-4 shadow-lg border-b-4 border-white/80">
                        <button type="button" @click="placeOrnamentInSlot(0)" class="text-2xl hover:scale-125 transition cursor-pointer" x-text="tree.slots[0]"></button>
                        <button type="button" @click="placeOrnamentInSlot(1)" class="text-2xl hover:scale-125 transition cursor-pointer" x-text="tree.slots[1]"></button>
                    </div>
                    <!-- Middle Pine Tier -->
                    <div class="w-52 h-28 -mt-4 bg-gradient-to-b from-emerald-600 to-emerald-800 rounded-t-3xl flex items-center justify-around px-5 shadow-xl border-b-4 border-white/80">
                        <button type="button" @click="placeOrnamentInSlot(2)" class="text-2xl hover:scale-125 transition cursor-pointer" x-text="tree.slots[2]"></button>
                        <button type="button" @click="placeOrnamentInSlot(3)" class="text-2xl hover:scale-125 transition cursor-pointer" x-text="tree.slots[3]"></button>
                        <button type="button" @click="placeOrnamentInSlot(4)" class="text-2xl hover:scale-125 transition cursor-pointer" x-text="tree.slots[4]"></button>
                    </div>
                    <!-- Bottom Pine Tier -->
                    <div class="w-68 sm:w-72 h-32 -mt-4 bg-gradient-to-b from-emerald-700 to-emerald-950 rounded-t-3xl flex items-center justify-around px-6 shadow-2xl border-b-4 border-white/90">
                        <button type="button" @click="placeOrnamentInSlot(5)" class="text-2xl hover:scale-125 transition cursor-pointer" x-text="tree.slots[5]"></button>
                        <button type="button" @click="placeOrnamentInSlot(6)" class="text-2xl hover:scale-125 transition cursor-pointer" x-text="tree.slots[6]"></button>
                        <button type="button" @click="placeOrnamentInSlot(7)" class="text-2xl hover:scale-125 transition cursor-pointer" x-text="tree.slots[7]"></button>
                    </div>
                    <!-- Tree Trunk & Gifts Under Tree -->
                    <div class="w-12 h-10 bg-amber-900 rounded-b-xl shadow-inner"></div>
                    <div class="flex items-center gap-3 -mt-3 text-2xl">
                        <span>🎁</span><span>🧸</span><span>🎁</span>
                    </div>
                </div>
            </div>

            <!-- Right: Ornament Palette & Controls -->
            <div class="space-y-5">
                <div>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-emerald-100 text-emerald-800">Interactive Decorator</span>
                    <h2 class="text-xl font-black text-gray-900 mt-1">Decorate Your Personal MAY22SMS Christmas Tree</h2>
                    <p class="text-xs text-gray-500 mt-1">Select an ornament brush below, then click any spot on the tree (or click Randomize) and save your creation to earn the <strong>Christmas Tree Master</strong> badge!</p>
                </div>

                <div>
                    <label class="text-xs font-black uppercase text-gray-500 block mb-2">1. Choose Tree Topper</label>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="topIcon in ['⭐', '🌟', '👼', '❄️', '👑', '🎀']" :key="topIcon">
                            <button type="button" @click="tree.topper = topIcon; playChimeNota(784)"
                                    :class="tree.topper === topIcon ? 'border-emerald-600 bg-emerald-50 ring-2 ring-emerald-500/30' : 'border-gray-200 bg-gray-50'"
                                    class="w-11 h-11 rounded-xl border-2 text-xl flex items-center justify-center transition cursor-pointer"
                                    x-text="topIcon"></button>
                        </template>
                    </div>
                </div>

                <div>
                    <label class="text-xs font-black uppercase text-gray-500 block mb-2">
                        2. Pick Active Ornament Brush: <span class="text-lg" x-text="selectedOrnamentBrush"></span>
                    </label>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="orn in ['🔴', '🟡', '🔵', '🔔', '🎁', '❄️', '🦌', '🧦', '🍬', '🕯️', '🍪', '🎀']" :key="orn">
                            <button type="button" @click="selectedOrnamentBrush = orn; playChimeNota(659)"
                                    :class="selectedOrnamentBrush === orn ? 'border-red-600 bg-red-50 ring-2 ring-red-500/30' : 'border-gray-200 bg-gray-50'"
                                    class="w-11 h-11 rounded-xl border-2 text-xl flex items-center justify-center transition cursor-pointer"
                                    x-text="orn"></button>
                        </template>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3 pt-3">
                    <button type="button" @click="randomizeTree()"
                            class="px-4 py-2.5 rounded-xl border border-gray-200 hover:bg-gray-100 text-gray-700 font-bold text-xs transition cursor-pointer">
                        🎲 Randomize Ornaments
                    </button>
                    <button type="button" @click="saveCreation('tree', tree)"
                            :disabled="savingCreation"
                            class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs shadow-md transition cursor-pointer">
                        <span x-text="savingCreation ? 'Saving Tree...' : '💾 Save My Christmas Tree'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- =====================================================================
         SECTION 3: INTERACTIVE SNOWMAN BUILDER
         ===================================================================== -->
    @if($featureSettings['xmas_snowman_enabled'] ?? true)
    <div x-show="tab === 'snowman'" style="display: none;" class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-200 shadow-sm">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
            <!-- Left: Live Snowman Stage -->
            <div class="rounded-3xl p-6 flex flex-col items-center justify-center min-h-[420px] relative overflow-hidden border border-sky-500/30"
                 style="background: linear-gradient(180deg, #0c4a6e 0%, #082f49 70%, #e0f2fe 100%);">

                <div class="px-3 py-1 rounded-full bg-white/15 text-white text-xs font-black mb-4" x-text="snowman.name"></div>

                <!-- Snowman Character -->
                <div class="relative flex flex-col items-center select-none">
                    <!-- Hat -->
                    <div class="text-5xl -mb-4 z-20 transition-transform hover:scale-110" x-text="snowman.hat"></div>
                    <!-- Head -->
                    <div class="w-24 h-24 rounded-full bg-gradient-to-b from-white to-sky-100 shadow-lg flex flex-col items-center justify-center relative border border-sky-200">
                        <span class="text-xl leading-none" x-text="snowman.eyes"></span>
                        <span class="text-lg leading-none mt-0.5" x-text="snowman.nose"></span>
                    </div>
                    <!-- Scarf -->
                    <div class="w-28 h-5 rounded-full -my-1.5 z-10 shadow-md border border-white/40" :style="'background:' + snowman.scarf"></div>
                    <!-- Middle Torso -->
                    <div class="w-32 h-28 rounded-full bg-gradient-to-b from-white to-sky-100 shadow-xl flex flex-col items-center justify-center gap-2 relative border border-sky-200">
                        <span class="text-base leading-none" x-text="snowman.buttons"></span>
                        <span class="text-base leading-none" x-text="snowman.buttons"></span>
                        <span class="text-base leading-none" x-text="snowman.buttons"></span>
                        <!-- Held Accessory -->
                        <span class="absolute -right-7 top-4 text-4xl" x-text="snowman.accessory"></span>
                    </div>
                    <!-- Base Snowball -->
                    <div class="w-40 h-24 -mt-3 rounded-full bg-gradient-to-b from-white to-sky-200 shadow-2xl border border-sky-200"></div>
                </div>
            </div>

            <!-- Right: Snowman Customization Controls -->
            <div class="space-y-4">
                <div>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-sky-100 text-sky-800">Snowman Studio</span>
                    <h2 class="text-xl font-black text-gray-900 mt-1">Build & Customize Your Snowman</h2>
                    <p class="text-xs text-gray-500 mt-1">Your saved snowman appears on your Christmas Profile Card at the top of the Wonderland Hub!</p>
                </div>

                <div>
                    <label class="text-xs font-black uppercase text-gray-500 block mb-1">Snowman Name</label>
                    <input type="text" x-model="snowman.name" maxlength="40"
                           class="w-full rounded-xl border border-gray-300 px-3.5 py-2 text-xs font-bold">
                </div>

                <div>
                    <label class="text-xs font-black uppercase text-gray-500 block mb-1.5">Choose Hat</label>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="h in ['🎩', '🎅', '👑', '🧢', '🎓', '🪖', '🎀']" :key="h">
                            <button type="button" @click="snowman.hat = h; playChimeNota(740)"
                                    :class="snowman.hat === h ? 'border-sky-600 bg-sky-50 ring-2 ring-sky-500/30' : 'border-gray-200 bg-gray-50'"
                                    class="w-10 h-10 rounded-xl border-2 text-xl flex items-center justify-center cursor-pointer" x-text="h"></button>
                        </template>
                    </div>
                </div>

                <div>
                    <label class="text-xs font-black uppercase text-gray-500 block mb-1.5">Choose Scarf Color</label>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="clr in ['#dc2626', '#16a34a', '#2563eb', '#eab308', '#9333ea', '#db2777']" :key="clr">
                            <button type="button" @click="snowman.scarf = clr; playChimeNota(698)"
                                    :class="snowman.scarf === clr ? 'ring-4 ring-offset-1 ring-sky-500' : ''"
                                    class="w-8 h-8 rounded-full border border-white shadow cursor-pointer"
                                    :style="'background:' + clr"></button>
                        </template>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-black uppercase text-gray-500 block mb-1.5">Buttons</label>
                        <div class="flex flex-wrap gap-1.5">
                            <template x-for="b in ['⚫', '🔴', '⭐', '💎', '❤️']" :key="b">
                                <button type="button" @click="snowman.buttons = b"
                                        :class="snowman.buttons === b ? 'border-sky-600 bg-sky-50' : 'border-gray-200'"
                                        class="w-9 h-9 rounded-xl border text-base flex items-center justify-center cursor-pointer" x-text="b"></button>
                            </template>
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-black uppercase text-gray-500 block mb-1.5">Accessory</label>
                        <div class="flex flex-wrap gap-1.5">
                            <template x-for="acc in ['🧹', '🎁', '🔔', '🎸', '🍭', '🕯️']" :key="acc">
                                <button type="button" @click="snowman.accessory = acc"
                                        :class="snowman.accessory === acc ? 'border-sky-600 bg-sky-50' : 'border-gray-200'"
                                        class="w-9 h-9 rounded-xl border text-base flex items-center justify-center cursor-pointer" x-text="acc"></button>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="button" @click="saveCreation('snowman', snowman)"
                            :disabled="savingCreation"
                            class="px-6 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-black text-xs shadow-md transition cursor-pointer">
                        <span x-text="savingCreation ? 'Saving Snowman...' : '💾 Save Snowman to Profile Card'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- =====================================================================
         SECTION 4: INTERACTIVE ANIMATED CHRISTMAS VILLAGE
         ===================================================================== -->
    @if($featureSettings['xmas_village_enabled'] ?? true)
    <div x-show="tab === 'village'" style="display: none;" class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-200 shadow-sm space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 class="text-xl font-black text-gray-900">🏘️ Interactive MAY22SMS Christmas Village</h2>
                <p class="text-xs text-gray-500">Click any glowing snow-covered building or landmark in the village below to discover holiday secrets and complete your Snow Explorer challenge!</p>
            </div>
            <span class="px-3 py-1.5 rounded-xl bg-indigo-50 text-indigo-700 font-black text-xs">
                Explored: <span x-text="villageSpots.length"></span> / 4 Landmarks
            </span>
        </div>

        <!-- Animated Starry Winter Night Village Canvas -->
        <div class="rounded-3xl p-6 sm:p-8 text-white relative overflow-hidden border border-indigo-500/30"
             style="background: linear-gradient(180deg, #090d16 0%, #1e1b4b 60%, #0f172a 100%);">

            <!-- Twinkling Night Sky Stars -->
            <div class="flex items-center justify-between text-amber-300 text-sm opacity-80 mb-6 select-none">
                <span class="animate-pulse">✦</span>
                <span>⭐</span>
                <span class="animate-ping">✦</span>
                <span class="text-2xl">🌕</span>
                <span class="animate-pulse">★</span>
                <span>✦</span>
            </div>

            <!-- 4 Interactive Snow-Covered Village Landmarks -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 relative z-10">
                <!-- Spot 1: Santa's OTP Workshop -->
                <div @click="inspectVillageSpot('otp_workshop', 'Santa’s Instant OTP Workshop 🎅', 'Did you know? Picking Fast Priority on the SMS Verification page routes your code through our highest-priority telecom lines!')"
                     class="rounded-2xl bg-white/10 hover:bg-white/20 backdrop-blur-md border border-white/20 p-5 text-center cursor-pointer transition transform hover:-translate-y-1">
                    <div class="text-4xl mb-2">🏠🎄</div>
                    <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-400 text-slate-950 mb-1">
                        Glowing Windows
                    </div>
                    <h4 class="font-black text-sm text-white">Santa's OTP Workshop</h4>
                    <p class="text-[11px] text-indigo-200 mt-1">Tap to peek inside the workshop</p>
                </div>

                <!-- Spot 2: Grand Village Pine & Street Lamp -->
                <div @click="inspectVillageSpot('grand_pine', 'Village Town Square Tree 🎄', 'Merry Christmas from the entire MAY22SMS team! Remember to unwrap your daily Advent Calendar gift box before midnight!')"
                     class="rounded-2xl bg-white/10 hover:bg-white/20 backdrop-blur-md border border-white/20 p-5 text-center cursor-pointer transition transform hover:-translate-y-1">
                    <div class="text-4xl mb-2">🎄🕯️</div>
                    <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-400 text-slate-950 mb-1">
                        Street Lights On
                    </div>
                    <h4 class="font-black text-sm text-white">Town Square Pine</h4>
                    <p class="text-[11px] text-indigo-200 mt-1">Tap to read the village greeting</p>
                </div>

                <!-- Spot 3: Reindeer Boosting Stable -->
                <div @click="inspectVillageSpot('reindeer_stable', 'Reindeer Social Boosting Barn 🦌', 'Grow your Instagram, TikTok, and Telegram channels at warp speed this holiday season in the Social Boosting hub!')"
                     class="rounded-2xl bg-white/10 hover:bg-white/20 backdrop-blur-md border border-white/20 p-5 text-center cursor-pointer transition transform hover:-translate-y-1">
                    <div class="text-4xl mb-2">🦌🏡</div>
                    <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-400 text-slate-950 mb-1">
                        Festive Barn
                    </div>
                    <h4 class="font-black text-sm text-white">Reindeer Boosting Barn</h4>
                    <p class="text-[11px] text-indigo-200 mt-1">Tap to discover boosting tips</p>
                </div>

                <!-- Spot 4: Save Small Small Winter Vault -->
                <div @click="inspectVillageSpot('winter_vault', 'Holiday Savings Treasury 🏦', 'Lock in your festive savings with Save Small Small and earn automated daily interest into the New Year!')"
                     class="rounded-2xl bg-white/10 hover:bg-white/20 backdrop-blur-md border border-white/20 p-5 text-center cursor-pointer transition transform hover:-translate-y-1">
                    <div class="text-4xl mb-2">🏦❄️</div>
                    <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-sky-400 text-slate-950 mb-1">
                        Snowy Roof
                    </div>
                    <h4 class="font-black text-sm text-white">Winter Treasury Lodge</h4>
                    <p class="text-[11px] text-indigo-200 mt-1">Tap to unlock holiday wisdom</p>
                </div>
            </div>

            <!-- Active Discovery Story Box -->
            <template x-if="activeVillageStory.title !== ''">
                <div class="mt-6 rounded-2xl bg-black/45 border border-amber-400/50 p-4 flex items-center justify-between gap-4">
                    <div>
                        <h4 class="font-black text-amber-300 text-sm" x-text="activeVillageStory.title"></h4>
                        <p class="text-xs text-white/90 mt-1" x-text="activeVillageStory.text"></p>
                    </div>
                    <a href="/sms-verify" class="px-3.5 py-2 rounded-xl bg-amber-400 text-slate-950 font-black text-xs shrink-0">
                        Explore Services &rarr;
                    </a>
                </div>
            </template>
        </div>
    </div>
    @endif

    <!-- =====================================================================
         SECTION 5: CHRISTMAS CHALLENGE BOARD & SEASONAL BADGES
         ===================================================================== -->
    @if(($featureSettings['xmas_challenges_enabled'] ?? true) || ($featureSettings['xmas_badges_enabled'] ?? true))
    <div x-show="tab === 'challenges'" style="display: none;" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Challenge Board (2 cols) -->
        <div class="lg:col-span-2 bg-white rounded-3xl p-6 border border-gray-200 shadow-sm space-y-4">
            <div>
                <h2 class="text-lg font-black text-gray-900">🎯 Daily & Weekly Christmas Challenge Board</h2>
                <p class="text-xs text-gray-500">Complete fun festive activities around MAY22SMS to claim instant bonus wallet credits!</p>
            </div>

            <div class="space-y-3">
                @foreach($challenges as $ch)
                    @php
                        $uc = $userChallengesMap->get($ch->id);
                        $prog = $uc ? (int) $uc->progress_count : 0;
                        $isDone = $uc ? (bool) $uc->is_completed : false;
                        $isClaimed = $uc ? (bool) $uc->is_reward_claimed : false;
                        $pct = $ch->target_count > 0 ? min(100, round(($prog / $ch->target_count) * 100)) : 0;
                    @endphp
                    <div class="rounded-2xl border border-gray-200 p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="flex items-start gap-3.5">
                            <div class="w-11 h-11 rounded-2xl bg-amber-50 border border-amber-200 flex items-center justify-center text-2xl shrink-0">
                                {{ $ch->icon ?: '🎄' }}
                            </div>
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase bg-gray-100 text-gray-700">{{ $ch->period_type }}</span>
                                    <h4 class="font-black text-gray-900 text-sm">{{ $ch->title }}</h4>
                                </div>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $ch->description }}</p>
                                <div class="mt-2 flex items-center gap-3">
                                    <div class="w-36 h-2 rounded-full bg-gray-100 overflow-hidden">
                                        <div class="h-full bg-emerald-500 rounded-full" style="width: {{ $pct }}%;"></div>
                                    </div>
                                    <span class="text-[11px] font-bold text-gray-500">{{ $prog }} / {{ $ch->target_count }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between sm:flex-col sm:items-end gap-2 shrink-0">
                            <span class="text-sm font-black text-[#0d9488]">
                                +{{ $currencySymbol ?? '$' }}{{ number_format($ch->reward_value_usd * ($currencyFx ?? 1), 2) }}
                            </span>

                            @if($isClaimed)
                                <span class="px-3 py-1.5 rounded-xl bg-emerald-100 text-emerald-800 font-black text-xs">✅ Reward Claimed</span>
                            @elseif($isDone)
                                <button type="button"
                                        @click="claimChallengeReward({{ $ch->id }})"
                                        class="px-4 py-2 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-950 font-black text-xs shadow transition cursor-pointer">
                                    🎁 Claim Reward
                                </button>
                            @else
                                <span class="px-3 py-1.5 rounded-xl bg-gray-100 text-gray-500 font-bold text-xs">In Progress</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Seasonal Badges Showcase (1 col) -->
        <div class="bg-white rounded-3xl p-6 border border-gray-200 shadow-sm space-y-4">
            <div>
                <h2 class="text-lg font-black text-gray-900">🏅 Seasonal Achievements</h2>
                <p class="text-xs text-gray-500">Collect exclusive Christmas badges on your profile.</p>
            </div>

            <div class="grid grid-cols-1 gap-3">
                @foreach($badges as $badge)
                    <div class="rounded-2xl border p-3.5 flex items-center gap-3 transition"
                         :class="earnedBadges.includes({{ $badge->id }}) ? 'border-amber-400 bg-amber-50/30' : 'border-gray-200 opacity-55'">
                        <div class="w-11 h-11 rounded-2xl bg-white border border-gray-200 flex items-center justify-center text-2xl shrink-0 shadow-xs">
                            {{ $badge->icon ?: '⭐' }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-1">
                                <h4 class="font-black text-gray-900 text-xs truncate">{{ $badge->name }}</h4>
                                <span x-show="earnedBadges.includes({{ $badge->id }})" class="px-2 py-0.5 rounded-full text-[9px] font-black bg-emerald-500 text-white">UNLOCKED</span>
                            </div>
                            <p class="text-[11px] text-gray-500 mt-0.5">{{ $badge->description }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- =====================================================================
         SECTION 6: CHRISTMAS GREETING CARD GENERATOR
         ===================================================================== -->
    @if($featureSettings['xmas_greeting_cards_enabled'] ?? true)
    <div x-show="tab === 'cards'" style="display: none;" class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-200 shadow-sm">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
            <!-- Left: Live Greeting Card Preview -->
            <div id="may22-greeting-card-preview"
                 class="rounded-3xl p-7 sm:p-9 text-white shadow-2xl relative overflow-hidden border-4 border-amber-300/70 min-h-[340px] flex flex-col justify-between"
                 :style="cardTemplateStyle()">
                <div class="flex items-center justify-between">
                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase bg-black/30 text-amber-300">
                        🎄 Season's Greetings
                    </span>
                    <span class="text-2xl">❄️✨</span>
                </div>

                <div class="my-6 space-y-2">
                    <div class="text-xs font-bold text-amber-200">Dear <span x-text="card.recipient || 'Friend'"></span>,</div>
                    <p class="text-base sm:text-lg font-black text-white leading-relaxed" x-text="card.message"></p>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-white/20 text-xs">
                    <div>
                        <span class="text-white/70 block text-[10px]">Warmest wishes from</span>
                        <strong class="text-amber-300 font-black text-sm" x-text="card.sender"></strong>
                    </div>
                    <div class="text-right">
                        <span class="font-black tracking-tight text-white/90 text-xs">MAY22SMS<span class="text-amber-400">.</span></span>
                        <span class="block text-[9px] text-white/70">Automated Digital Hub</span>
                    </div>
                </div>
            </div>

            <!-- Right: Card Editor & Download PNG / Share Controls -->
            <div class="space-y-4">
                <div>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-rose-100 text-rose-800">Festive Card Studio</span>
                    <h2 class="text-xl font-black text-gray-900 mt-1">Create & Download a Personalized Christmas Card</h2>
                    <p class="text-xs text-gray-500 mt-1">Customize your holiday message and download a high-resolution PNG card to share on WhatsApp, Telegram, or Instagram!</p>
                </div>

                <div>
                    <label class="text-xs font-black uppercase text-gray-500 block mb-1.5">Choose Card Theme</label>
                    <div class="grid grid-cols-3 gap-2 text-xs">
                        <button type="button" @click="card.template = 'royal_red'"
                                :class="card.template === 'royal_red' ? 'ring-2 ring-red-600 font-black' : ''"
                                class="p-2.5 rounded-xl bg-red-800 text-white text-center cursor-pointer">🎅 Royal Crimson</button>
                        <button type="button" @click="card.template = 'emerald_pine'"
                                :class="card.template === 'emerald_pine' ? 'ring-2 ring-emerald-600 font-black' : ''"
                                class="p-2.5 rounded-xl bg-emerald-800 text-white text-center cursor-pointer">🎄 Emerald Pine</button>
                        <button type="button" @click="card.template = 'midnight_gold'"
                                :class="card.template === 'midnight_gold' ? 'ring-2 ring-amber-500 font-black' : ''"
                                class="p-2.5 rounded-xl bg-slate-900 text-amber-300 text-center cursor-pointer">✨ Midnight Gold</button>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-bold text-gray-700 block mb-1">Recipient Name</label>
                        <input type="text" x-model="card.recipient" maxlength="50" class="w-full rounded-xl border border-gray-300 px-3.5 py-2 text-xs font-semibold">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-700 block mb-1">Your Display Name</label>
                        <input type="text" x-model="card.sender" maxlength="50" class="w-full rounded-xl border border-gray-300 px-3.5 py-2 text-xs font-semibold">
                    </div>
                </div>

                <div>
                    <label class="text-xs font-bold text-gray-700 block mb-1">Festive Greeting Message</label>
                    <textarea x-model="card.message" rows="3" maxlength="240" class="w-full rounded-xl border border-gray-300 px-3.5 py-2 text-xs font-semibold"></textarea>
                </div>

                <div class="flex flex-wrap items-center gap-3 pt-2">
                    <button type="button" @click="downloadGreetingCardPng()"
                            class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-black text-xs shadow-md transition cursor-pointer">
                        📥 Download Card Image (PNG)
                    </button>
                    <button type="button" @click="shareGreetingCardWhatsApp()"
                            class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs shadow-md transition cursor-pointer">
                        💬 Share on WhatsApp
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- =====================================================================
         SECTION 7: TRANSPARENT PREDEFINED FESTIVE GIFT BOXES
         ===================================================================== -->
    @if($featureSettings['xmas_mystery_gifts_enabled'] ?? true)
    <div x-show="tab === 'gifts'" style="display: none;" class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-200 shadow-sm space-y-5">
        <div>
            <h2 class="text-xl font-black text-gray-900">🎁 Claimable Christmas Celebration Gift Boxes</h2>
            <p class="text-xs text-gray-500">Transparent, predefined seasonal gift rewards configured by MAY22SMS administrators.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse($mysteryGifts as $g)
                <div class="rounded-2xl border border-gray-200 p-5 flex flex-col justify-between gap-4">
                    <div class="flex items-start gap-3.5">
                        <div class="w-13 h-13 rounded-2xl bg-red-50 border border-red-200 flex items-center justify-center text-3xl shrink-0">
                            {{ $g->icon_or_image && !str_starts_with($g->icon_or_image, '/') ? $g->icon_or_image : '🎁' }}
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <h4 class="font-black text-gray-900 text-base">{{ $g->name }}</h4>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-emerald-100 text-emerald-800">
                                    @if($g->reward_value_usd > 0)
                                        +{{ $currencySymbol ?? '$' }}{{ number_format($g->reward_value_usd * ($currencyFx ?? 1), 2) }}
                                    @elseif($g->discount_percent > 0)
                                        {{ $g->discount_percent }}% OFF
                                    @else
                                        Special Gift
                                    @endif
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">{{ $g->description }}</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                        <span class="text-[11px] font-bold text-gray-400 uppercase">{{ str_replace('_', ' ', $g->gift_type) }}</span>
                        <button type="button"
                                @click="claimSpecialGift({{ $g->id }})"
                                :disabled="claimedGifts.includes({{ $g->id }})"
                                :class="claimedGifts.includes({{ $g->id }}) ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-[#0d9488] hover:bg-teal-700 text-white shadow cursor-pointer'"
                                class="px-5 py-2 rounded-xl font-black text-xs transition">
                            <span x-text="claimedGifts.includes({{ $g->id }}) ? '✅ Already Claimed' : '{{ $g->claim_button_text ?: 'Claim Gift 🎁' }}'"></span>
                        </button>
                    </div>
                </div>
            @empty
                <div class="col-span-2 p-8 text-center text-gray-400 text-xs">No active seasonal gift boxes right now.</div>
            @endforelse
        </div>
    </div>
    @endif
</div>

<script>
    function christmasWonderlandApp(cfg) {
        return {
            tab: 'advent',
            claimedAdvent: cfg.claimedAdventDays || [],
            claimedGifts: cfg.claimedGiftIds || [],
            earnedBadges: cfg.earnedBadgeIds || [],
            claimingAdventDay: null,
            savingCreation: false,
            selectedOrnamentBrush: '🔴',
            tree: cfg.treeConfig,
            snowman: cfg.snowmanConfig,
            card: cfg.cardConfig,
            villageSpots: cfg.visitedSpots || [],
            activeVillageStory: { title: '', text: '' },
            statusBanner: { msg: '', ok: true },
            popupAlert: { open: false, icon: '🎄', tag: 'Festive Surprise', title: '', message: '' },
            cd: { days: '00', hours: '00', mins: '00', secs: '00' },
            musicPlaying: false,
            sfxMuted: false,
            musicTimer: null,

            initWonderland() {
                this.updateCountdown();
                setInterval(() => this.updateCountdown(), 1000);

                // Show non-intrusive welcome pop-up once per session if enabled
                if (cfg.popupsEnabled && !sessionStorage.getItem('may22_xmas_welcome_shown')) {
                    sessionStorage.setItem('may22_xmas_welcome_shown', '1');
                    setTimeout(() => {
                        this.triggerPopup('🎅', 'Merry Christmas!', 'Welcome to Christmas Wonderland', 'Unwrap today’s Advent Calendar box and decorate your tree to earn bonus rewards!');
                    }, 900);
                }
            },

            updateCountdown() {
                var targetMs = new Date(cfg.countdownTarget).getTime();
                var diff = Math.max(0, Math.floor((targetMs - Date.now()) / 1000));
                this.cd.days = String(Math.floor(diff / 86400)).padStart(2, '0');
                this.cd.hours = String(Math.floor((diff % 86400) / 3600)).padStart(2, '0');
                this.cd.mins = String(Math.floor((diff % 3600) / 60)).padStart(2, '0');
                this.cd.secs = String(diff % 60).padStart(2, '0');
            },

            triggerPopup(icon, tag, title, message) {
                if (!cfg.popupsEnabled) return;
                this.popupAlert = { open: true, icon: icon, tag: tag, title: title, message: message };
                setTimeout(() => { this.popupAlert.open = false; }, 6500);
            },

            dismissPopup() {
                this.popupAlert.open = false;
            },

            // WebAudio Synthesizer for Gentle Carol Chimes & Gift Unwrap SFX (Zero external MP3 dependency, 100% licensed)
            playChimeNota(freq, duration = 0.25) {
                if (this.sfxMuted) return;
                try {
                    var AudioCtx = window.AudioContext || window.webkitAudioContext;
                    if (!AudioCtx) return;
                    if (!window.__may22AudioCtx) window.__may22AudioCtx = new AudioCtx();
                    var ctx = window.__may22AudioCtx;
                    var osc = ctx.createOscillator();
                    var gain = ctx.createGain();
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(freq, ctx.currentTime);
                    gain.gain.setValueAtTime(0.06, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.0008, ctx.currentTime + duration);
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.start();
                    osc.stop(ctx.currentTime + duration);
                } catch (e) {}
            },

            playGiftFanfare() {
                this.playChimeNota(523.25, 0.18);
                setTimeout(() => this.playChimeNota(659.25, 0.18), 120);
                setTimeout(() => this.playChimeNota(783.99, 0.22), 240);
                setTimeout(() => this.playChimeNota(1046.50, 0.35), 360);
            },

            toggleMusic() {
                if (this.musicPlaying) {
                    this.musicPlaying = false;
                    if (this.musicTimer) clearInterval(this.musicTimer);
                    this.musicTimer = null;
                    return;
                }
                this.musicPlaying = true;
                this.sfxMuted = false;
                // Gentle Jingle Bells melody notes in Hz
                var notes = [659.25, 659.25, 659.25, 659.25, 659.25, 659.25, 659.25, 783.99, 523.25, 587.33, 659.25];
                var idx = 0;
                this.playChimeNota(notes[0], 0.35);
                this.musicTimer = setInterval(() => {
                    if (!this.musicPlaying) return;
                    idx = (idx + 1) % notes.length;
                    this.playChimeNota(notes[idx], 0.35);
                }, 550);
            },

            async claimAdventBox(dayNumber) {
                if (this.claimingAdventDay) return;
                this.claimingAdventDay = dayNumber;
                try {
                    var csrf = document.querySelector('meta[name=csrf-token]')?.content || '';
                    var res = await fetch('/api/seasonal/advent/' + dayNumber + '/claim', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
                    });
                    var data = await res.json();
                    if (res.ok && data.success) {
                        this.claimedAdvent.push(dayNumber);
                        this.playGiftFanfare();
                        this.statusBanner = { msg: data.message, ok: true };
                        this.triggerPopup('🎁', 'Advent Day ' + dayNumber, 'Gift Box Unwrapped!', data.message);
                    } else {
                        this.statusBanner = { msg: data.message || 'Could not unwrap Advent box.', ok: false };
                    }
                } catch (e) {
                    this.statusBanner = { msg: 'Network error while unwrapping box.', ok: false };
                } finally {
                    this.claimingAdventDay = null;
                }
            },

            cycleTreeTopper() {
                var tops = ['⭐', '🌟', '👼', '❄️', '👑', '🎀'];
                var next = (tops.indexOf(this.tree.topper) + 1) % tops.length;
                this.tree.topper = tops[next];
                this.playChimeNota(880);
            },

            placeOrnamentInSlot(slotIdx) {
                this.tree.slots[slotIdx] = this.selectedOrnamentBrush;
                this.playChimeNota(587.33);
            },

            randomizeTree() {
                var pool = ['🔴', '🟡', '🔵', '🔔', '🎁', '❄️', '🦌', '🧦', '🍬', '🕯️', '🍪', '🎀'];
                this.tree.slots = this.tree.slots.map(() => pool[Math.floor(Math.random() * pool.length)]);
                this.playGiftFanfare();
            },

            async saveCreation(type, configObj) {
                this.savingCreation = true;
                try {
                    var csrf = document.querySelector('meta[name=csrf-token]')?.content || '';
                    var res = await fetch('/api/seasonal/creations/save', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                        body: JSON.stringify({ type: type, config: configObj })
                    });
                    var data = await res.json();
                    if (res.ok && data.success) {
                        this.playGiftFanfare();
                        this.statusBanner = { msg: data.message, ok: true };
                        if (data.earned_badges && data.earned_badges.length > 0) {
                            data.earned_badges.forEach(b => {
                                if (!this.earnedBadges.includes(b.id)) this.earnedBadges.push(b.id);
                                this.triggerPopup(b.icon || '🏅', 'Badge Unlocked!', b.name, b.description);
                            });
                        }
                    }
                } catch (e) {
                    this.statusBanner = { msg: 'Could not save creation.', ok: false };
                } finally {
                    this.savingCreation = false;
                }
            },

            async inspectVillageSpot(spotKey, title, text) {
                this.activeVillageStory = { title: title, text: text };
                this.playChimeNota(784);
                if (!this.villageSpots.includes(spotKey)) {
                    this.villageSpots.push(spotKey);
                }
                await this.saveCreation('village_visit', { spot: spotKey });
            },

            async claimChallengeReward(challengeId) {
                try {
                    var csrf = document.querySelector('meta[name=csrf-token]')?.content || '';
                    var res = await fetch('/api/seasonal/challenges/' + challengeId + '/claim', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
                    });
                    var data = await res.json();
                    if (res.ok && data.success) {
                        this.playGiftFanfare();
                        this.statusBanner = { msg: data.message, ok: true };
                        setTimeout(() => window.location.reload(), 1200);
                    } else {
                        this.statusBanner = { msg: data.message || 'Could not claim challenge reward.', ok: false };
                    }
                } catch (e) {}
            },

            async claimSpecialGift(giftId) {
                try {
                    var csrf = document.querySelector('meta[name=csrf-token]')?.content || '';
                    var res = await fetch('/api/seasonal/gifts/' + giftId + '/claim', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
                    });
                    var data = await res.json();
                    if (res.ok && data.success) {
                        this.claimedGifts.push(giftId);
                        this.playGiftFanfare();
                        this.statusBanner = { msg: data.message, ok: true };
                    } else {
                        this.statusBanner = { msg: data.message || 'Could not claim gift.', ok: false };
                    }
                } catch (e) {}
            },

            cardTemplateStyle() {
                if (this.card.template === 'emerald_pine') {
                    return 'background: linear-gradient(135deg, #022c22 0%, #065f46 55%, #0d9488 100%);';
                }
                if (this.card.template === 'midnight_gold') {
                    return 'background: linear-gradient(135deg, #090d16 0%, #1e1b4b 55%, #78350f 100%);';
                }
                return 'background: linear-gradient(135deg, #7f1d1d 0%, #b91c1c 55%, #14532d 100%);';
            },

            async downloadGreetingCardPng() {
                await this.saveCreation('greeting_card', this.card);
                var canvas = document.createElement('canvas');
                canvas.width = 900;
                canvas.height = 520;
                var ctx = canvas.getContext('2d');

                var grad = ctx.createLinearGradient(0, 0, 900, 520);
                if (this.card.template === 'emerald_pine') {
                    grad.addColorStop(0, '#022c22'); grad.addColorStop(1, '#065f46');
                } else if (this.card.template === 'midnight_gold') {
                    grad.addColorStop(0, '#090d16'); grad.addColorStop(1, '#78350f');
                } else {
                    grad.addColorStop(0, '#7f1d1d'); grad.addColorStop(0.55, '#991b1b'); grad.addColorStop(1, '#14532d');
                }
                ctx.fillStyle = grad;
                ctx.fillRect(0, 0, 900, 520);

                ctx.strokeStyle = '#facc15';
                ctx.lineWidth = 8;
                ctx.strokeRect(20, 20, 860, 480);

                ctx.fillStyle = '#fde047';
                ctx.font = 'bold 24px sans-serif';
                ctx.fillText('🎄 MAY22SMS SEASON’S GREETINGS ❄️', 55, 78);

                ctx.fillStyle = '#fef08a';
                ctx.font = 'bold 22px sans-serif';
                ctx.fillText('Dear ' + (this.card.recipient || 'Friend') + ',', 55, 145);

                ctx.fillStyle = '#ffffff';
                ctx.font = 'bold 28px sans-serif';
                var words = (this.card.message || '').split(' ');
                var line = '';
                var y = 210;
                for (var i = 0; i < words.length; i++) {
                    var testLine = line + words[i] + ' ';
                    if (ctx.measureText(testLine).width > 780 && i > 0) {
                        ctx.fillText(line, 55, y);
                        line = words[i] + ' ';
                        y += 42;
                    } else {
                        line = testLine;
                    }
                }
                ctx.fillText(line, 55, y);

                ctx.fillStyle = '#facc15';
                ctx.font = 'bold 22px sans-serif';
                ctx.fillText('Warmest wishes from ' + (this.card.sender || 'May22SMS User'), 55, 445);

                ctx.fillStyle = '#ffffff';
                ctx.font = 'bold 18px sans-serif';
                ctx.fillText('may22sim.com • Automated Digital Hub', 530, 445);

                var link = document.createElement('a');
                link.download = 'May22SMS-Christmas-Card.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
            },

            async shareGreetingCardWhatsApp() {
                await this.saveCreation('greeting_card', this.card);
                var txt = '🎄 *Season’s Greetings from ' + (this.card.sender || 'me') + '!* ❄️\n\n' +
                    'Dear ' + (this.card.recipient || 'Friend') + ',\n' +
                    (this.card.message || '') + '\n\n' +
                    '✨ Sent via *MAY22SMS Christmas Wonderland*: ' + window.location.origin;
                window.open('https://wa.me/?text=' + encodeURIComponent(txt), '_blank');
            }
        };
    }
</script>
@endsection
