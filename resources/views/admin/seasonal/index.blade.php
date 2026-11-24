@extends('layouts.admin')

@push('header_title', 'Seasonal Modes, Animations & Celebration Gifts')

@section('admin_content')
<div x-data="{
    activeTab: '{{ request('tab', 'modes') }}',
    showCreateModeModal: false,
    showCreateGiftModal: false,
    showCreateAnnouncementModal: false,
    editMode: null,
    editGift: null,
    distributeGiftModal: null
}" class="space-y-6">

    <!-- Flash Alerts -->
    @if(session('success'))
        <div class="rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-4 text-sm font-bold flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2.5">
                <span class="text-lg">🎉</span>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 px-5 py-4 text-sm font-bold flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2.5">
                <span class="text-lg">⚠️</span>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 px-5 py-4 text-xs font-bold">
            <p class="mb-1 font-black">Please fix the following errors:</p>
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Top Live Status Banner & Quick Controls -->
    <div class="rounded-3xl p-6 sm:p-7 text-white shadow-xl relative overflow-hidden border border-teal-500/30"
         style="background: {{ $activeMode ? ($activeMode->banner_gradient ?: 'linear-gradient(135deg, #0f172a 0%, #0d9488 100%)') : 'linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #0d9488 100%)' }};">
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
            <div>
                <div class="flex items-center gap-2.5 flex-wrap mb-2">
                    @if($activeMode)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-emerald-400 text-slate-950 shadow-sm">
                            <span class="w-2 h-2 rounded-full bg-slate-950 animate-ping"></span>
                            LIVE SEASONAL MODE: {{ $activeMode->icon }} {{ $activeMode->name }}
                        </span>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-white/15 text-white">
                            Animation: {{ ucfirst(str_replace('_', ' ', $activeMode->animation_type)) }} ({{ ucfirst($activeMode->animation_intensity) }})
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-white/15 text-teal-200">
                            Standard MAY22SMS Mode Active (No Seasonal Overlay)
                        </span>
                    @endif
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
                    {{ $activeMode ? $activeMode->greeting_title : 'MAY22SMS Seasonal Celebrations & User Gifts Engine' }}
                </h1>
                <p class="text-xs sm:text-sm text-white/85 mt-1 max-w-2xl">
                    {{ $activeMode ? $activeMode->greeting_subtitle : 'Activate Christmas, New Year, Valentine, Easter, Independence Day, Black Friday, Ramadan/Eid, or Custom themes with live animations and claimable user gifts.' }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                @if($activeMode)
                    <a href="/dashboard?seasonal_preview={{ $activeMode->slug }}" target="_blank"
                       class="px-4 py-2.5 rounded-xl bg-white/15 hover:bg-white/25 border border-white/25 text-white font-extrabold text-xs transition flex items-center gap-1.5">
                        <span>👁️ View Live on Dashboard</span>
                    </a>
                    <form action="{{ route('admin.seasonal.modes.deactivate-all') }}" method="POST" onsubmit="return confirm('Deactivate active seasonal mode and restore normal MAY22SMS appearance?');">
                        @csrf
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-black text-xs shadow-md transition cursor-pointer">
                            ⏹ Restore Normal Appearance
                        </button>
                    </form>
                @endif
                <button type="button" @click="showCreateModeModal = true"
                        class="px-4 py-2.5 rounded-xl bg-[#facc15] hover:bg-yellow-400 text-slate-950 font-black text-xs shadow-md transition cursor-pointer">
                    + New Seasonal Mode
                </button>
                <button type="button" @click="showCreateGiftModal = true"
                        class="px-4 py-2.5 rounded-xl bg-emerald-400 hover:bg-emerald-300 text-slate-950 font-black text-xs shadow-md transition cursor-pointer">
                    🎁 + Create User Gift
                </button>
            </div>
        </div>
    </div>

    <!-- 6-Card Gift Distribution & Campaign Metrics Report -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-white rounded-2xl p-4 border border-gray-200 shadow-xs">
            <span class="text-[11px] font-extrabold uppercase tracking-wider text-gray-400 block">Total Gifts Created</span>
            <span class="text-2xl font-black text-gray-900 mt-1 block">{{ number_format($report['total_gifts_created'] ?? 0) }}</span>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200 shadow-xs">
            <span class="text-[11px] font-extrabold uppercase tracking-wider text-gray-400 block">Eligible Active Users</span>
            <span class="text-2xl font-black text-[#0d9488] mt-1 block">{{ number_format($report['total_eligible_users'] ?? 0) }}</span>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200 shadow-xs">
            <span class="text-[11px] font-extrabold uppercase tracking-wider text-gray-400 block">Total Gifts Claimed</span>
            <span class="text-2xl font-black text-emerald-600 mt-1 block">{{ number_format($report['total_gifts_claimed'] ?? 0) }}</span>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200 shadow-xs">
            <span class="text-[11px] font-extrabold uppercase tracking-wider text-gray-400 block">Auto / Bulk Distributed</span>
            <span class="text-2xl font-black text-indigo-600 mt-1 block">{{ number_format($report['total_gifts_distributed'] ?? 0) }}</span>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200 shadow-xs">
            <span class="text-[11px] font-extrabold uppercase tracking-wider text-gray-400 block">Total Reward Value</span>
            <span class="text-2xl font-black text-amber-600 mt-1 block">${{ number_format((float)($report['total_reward_value_usd'] ?? 0), 2) }}</span>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200 shadow-xs">
            <span class="text-[11px] font-extrabold uppercase tracking-wider text-gray-400 block">Remaining Claims Pool</span>
            <span class="text-2xl font-black text-gray-900 mt-1 block">{{ number_format($report['remaining_available_claims'] ?? 0) }}</span>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex flex-wrap items-center gap-2 bg-white p-2 rounded-2xl border border-gray-200 shadow-xs">
        <button type="button" @click="activeTab = 'modes'"
                :class="activeTab === 'modes' ? 'bg-[#0d9488] text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100'"
                class="px-4 py-2.5 rounded-xl font-extrabold text-xs uppercase tracking-wider transition cursor-pointer">
            🎄 1. Seasonal Modes ({{ $modes->count() }})
        </button>
        <button type="button" @click="activeTab = 'gifts'"
                :class="activeTab === 'gifts' ? 'bg-[#0d9488] text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100'"
                class="px-4 py-2.5 rounded-xl font-extrabold text-xs uppercase tracking-wider transition cursor-pointer">
            🎁 2. Seasonal Gifts & Distribution ({{ $gifts->count() }})
        </button>
        <button type="button" @click="activeTab = 'announcements'"
                :class="activeTab === 'announcements' ? 'bg-[#0d9488] text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100'"
                class="px-4 py-2.5 rounded-xl font-extrabold text-xs uppercase tracking-wider transition cursor-pointer">
            📣 3. Announcements & Notifications ({{ $announcements->count() }})
        </button>
        <button type="button" @click="activeTab = 'claims'"
                :class="activeTab === 'claims' ? 'bg-[#0d9488] text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100'"
                class="px-4 py-2.5 rounded-xl font-extrabold text-xs uppercase tracking-wider transition cursor-pointer">
            📜 4. Claim Ledger & Audit Logs
        </button>
    </div>

    <!-- ==========================================
         TAB 1: SEASONAL MODES & THEMES
         ========================================== -->
    <div x-show="activeTab === 'modes'" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            @foreach($modes as $mode)
                <div class="bg-white rounded-3xl border-2 {{ $mode->is_active ? 'border-[#0d9488] ring-4 ring-[#0d9488]/15' : 'border-gray-200' }} overflow-hidden shadow-sm flex flex-col justify-between">
                    <div>
                        <!-- Theme Header Preview Strip -->
                        <div class="p-5 text-white relative overflow-hidden"
                             style="background: {{ $mode->banner_gradient ?: "linear-gradient(135deg, {$mode->secondary_color} 0%, {$mode->primary_color} 100%)" }};">
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-black uppercase bg-black/30 backdrop-blur-xs text-white">
                                    <span>{{ $mode->icon ?: '🎉' }}</span>
                                    <span>{{ $mode->badge_text ?: $mode->name }}</span>
                                </span>
                                @if($mode->is_active)
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-400 text-slate-950 animate-pulse">
                                        ● LIVE NOW
                                    </span>
                                @elseif($mode->is_scheduled && $mode->starts_at)
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-amber-300 text-slate-950">
                                        ⏰ Scheduled
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-white/15 text-white/80">
                                        Inactive
                                    </span>
                                @endif
                            </div>
                            <h3 class="text-lg font-black text-white leading-snug">{{ $mode->name }}</h3>
                            <p class="text-xs text-white/85 mt-1 line-clamp-2">{{ $mode->greeting_title }}</p>
                        </div>

                        <!-- Theme Details -->
                        <div class="p-5 space-y-3 text-xs">
                            <p class="text-gray-600 line-clamp-2">{{ $mode->greeting_subtitle }}</p>

                            <div class="grid grid-cols-2 gap-2 pt-2 border-t border-gray-100">
                                <div>
                                    <span class="text-[10px] font-bold uppercase text-gray-400 block">Animation</span>
                                    <span class="font-extrabold text-gray-800">
                                        {{ $mode->animations_enabled ? ucfirst(str_replace('_', ' ', $mode->animation_type)) . ' (' . ucfirst($mode->animation_intensity) . ')' : 'Disabled' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-[10px] font-bold uppercase text-gray-400 block">Gifts / Claims</span>
                                    <span class="font-extrabold text-[#0d9488]">{{ $mode->gifts_count }} Gift(s) &bull; {{ $mode->claims_count }} Claims</span>
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[10px] font-bold uppercase text-gray-400">Colors:</span>
                                    <span class="w-4 h-4 rounded-full border border-gray-300" style="background: {{ $mode->primary_color }};" title="Primary: {{ $mode->primary_color }}"></span>
                                    <span class="w-4 h-4 rounded-full border border-gray-300" style="background: {{ $mode->secondary_color }};" title="Secondary: {{ $mode->secondary_color }}"></span>
                                    <span class="w-4 h-4 rounded-full border border-gray-300" style="background: {{ $mode->accent_color }};" title="Accent: {{ $mode->accent_color }}"></span>
                                </div>
                                <div class="text-[11px] font-semibold text-gray-500">
                                    @if($mode->starts_at || $mode->ends_at)
                                        {{ optional($mode->starts_at)->format('M d, H:i') ?? 'Now' }} &rarr; {{ optional($mode->ends_at)->format('M d, H:i') ?? 'No End' }}
                                    @else
                                        Manual Control
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card Actions Footer -->
                    <div class="px-5 py-3.5 bg-gray-50 border-t border-gray-100 flex flex-wrap items-center justify-between gap-2">
                        <div class="flex items-center gap-1.5">
                            <!-- Activate / Deactivate Form -->
                            <form action="{{ route('admin.seasonal.modes.toggle', $mode->id) }}" method="POST">
                                @csrf
                                @if($mode->is_active)
                                    <button type="submit" class="px-3.5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-black text-xs transition cursor-pointer">
                                        Deactivate
                                    </button>
                                @else
                                    <button type="submit" class="px-3.5 py-2 rounded-xl bg-[#0d9488] hover:bg-teal-700 text-white font-black text-xs transition cursor-pointer shadow-xs">
                                        ⚡ Activate Mode
                                    </button>
                                @endif
                            </form>

                            <!-- Safe Admin Preview Link -->
                            <a href="/dashboard?seasonal_preview={{ $mode->slug }}" target="_blank"
                               class="px-3 py-2 rounded-xl bg-white hover:bg-teal-50 text-gray-700 hover:text-[#0d9488] border border-gray-200 font-extrabold text-xs transition"
                               title="Preview on User Dashboard without affecting live users">
                                👁️ Preview
                            </a>
                        </div>

                        <div class="flex items-center gap-1.5">
                            <button type="button"
                                    @click="editMode = @js($mode)"
                                    class="px-3 py-2 rounded-xl bg-white hover:bg-gray-100 text-gray-700 border border-gray-200 font-bold text-xs transition cursor-pointer">
                                Edit
                            </button>

                            @if(!$mode->is_system_default)
                                <form action="{{ route('admin.seasonal.modes.destroy', $mode->id) }}" method="POST" onsubmit="return confirm('Delete this seasonal mode?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 rounded-xl text-rose-600 hover:bg-rose-50 transition cursor-pointer" title="Delete">
                                        🗑️
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- ==========================================
         TAB 2: SEASONAL GIFTS & DISTRIBUTION
         ========================================== -->
    <div x-show="activeTab === 'gifts'" style="display: none;" class="space-y-4">
        <div class="flex items-center justify-between bg-white p-4 rounded-2xl border border-gray-200">
            <div>
                <h3 class="text-base font-black text-gray-900">Seasonal User Gifts & Reward Campaigns</h3>
                <p class="text-xs text-gray-500">Create claimable wallet credits, SMS credits, boosting credits, or promo coupons with eligibility rules.</p>
            </div>
            <button type="button" @click="showCreateGiftModal = true"
                    class="px-4 py-2.5 rounded-xl bg-[#0d9488] hover:bg-teal-700 text-white font-black text-xs shadow-sm transition cursor-pointer">
                + Create Seasonal Gift
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            @forelse($gifts as $gift)
                <div class="bg-white rounded-3xl border border-gray-200 p-5 shadow-xs flex flex-col justify-between gap-4">
                    <div>
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-2xl bg-teal-50 border border-teal-200 flex items-center justify-center text-2xl shrink-0">
                                    @if($gift->icon_or_image && str_starts_with($gift->icon_or_image, '/'))
                                        <img src="{{ asset($gift->icon_or_image) }}" alt="{{ $gift->name }}" class="w-9 h-9 object-contain">
                                    @else
                                        {{ $gift->icon_or_image ?: '🎁' }}
                                    @endif
                                </div>
                                <div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h4 class="font-black text-gray-900 text-base">{{ $gift->name }}</h4>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase {{ $gift->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-500' }}">
                                            {{ $gift->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-gray-500 font-semibold mt-0.5">
                                        Season: <strong class="text-gray-800">{{ $gift->mode?->icon }} {{ $gift->mode?->name ?? 'All Celebrations' }}</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="text-right">
                                <span class="text-lg font-black text-[#0d9488] block">
                                    @if($gift->reward_value_usd > 0)
                                        ${{ number_format($gift->reward_value_usd, 2) }} USD
                                    @elseif($gift->discount_percent > 0)
                                        {{ $gift->discount_percent }}% OFF
                                    @else
                                        {{ $gift->promo_code ?: 'Special' }}
                                    @endif
                                </span>
                                <span class="text-[10px] font-extrabold uppercase text-gray-400">{{ str_replace('_', ' ', $gift->gift_type) }}</span>
                            </div>
                        </div>

                        @if($gift->description)
                            <p class="text-xs text-gray-600 mt-3">{{ $gift->description }}</p>
                        @endif

                        <!-- Gift Quota & Eligibility Rules Summary -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 mt-4 p-3 rounded-2xl bg-gray-50 border border-gray-100 text-xs">
                            <div>
                                <span class="text-[10px] font-bold uppercase text-gray-400 block">Claims</span>
                                <span class="font-black text-gray-900">
                                    {{ number_format($gift->claimed_count) }} / {{ $gift->max_total_claims > 0 ? number_format($gift->max_total_claims) : '∞' }}
                                </span>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold uppercase text-gray-400 block">Distributed</span>
                                <span class="font-black text-emerald-700">${{ number_format($gift->total_distributed_usd, 2) }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold uppercase text-gray-400 block">Distribution</span>
                                <span class="font-bold text-gray-800 capitalize">{{ str_replace('_', ' ', $gift->distribution_mode) }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold uppercase text-gray-400 block">Eligibility</span>
                                <span class="font-bold text-gray-700">
                                    @if($gift->min_account_age_days > 0 || $gift->min_wallet_funding_usd > 0 || $gift->min_transactions_count > 0)
                                        Age≥{{ $gift->min_account_age_days }}d, Fund≥${{ number_format($gift->min_wallet_funding_usd, 0) }}, Tx≥{{ $gift->min_transactions_count }}
                                    @else
                                        All Active Users
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-gray-100 flex flex-wrap items-center justify-between gap-2">
                        <div class="flex items-center gap-1.5">
                            <form action="{{ route('admin.seasonal.gifts.toggle', $gift->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 rounded-xl text-xs font-extrabold border {{ $gift->is_active ? 'border-amber-200 bg-amber-50 text-amber-800 hover:bg-amber-100' : 'border-emerald-200 bg-emerald-50 text-emerald-800 hover:bg-emerald-100' }} transition cursor-pointer">
                                    {{ $gift->is_active ? 'Pause Gift' : 'Enable Gift' }}
                                </button>
                            </form>

                            <button type="button"
                                    @click="distributeGiftModal = @js($gift)"
                                    class="px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 text-xs font-extrabold transition cursor-pointer">
                                🚀 Distribute Now
                            </button>
                        </div>

                        <div class="flex items-center gap-1.5">
                            <button type="button"
                                    @click="editGift = @js($gift)"
                                    class="px-3 py-1.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold transition cursor-pointer">
                                Edit
                            </button>
                            <form action="{{ route('admin.seasonal.gifts.destroy', $gift->id) }}" method="POST" onsubmit="return confirm('Delete this seasonal gift?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-2.5 py-1.5 rounded-xl text-rose-600 hover:bg-rose-50 text-xs font-bold transition cursor-pointer">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-2 bg-white rounded-2xl p-10 text-center text-gray-500">
                    No seasonal gifts created yet. Click <strong>+ Create Seasonal Gift</strong> to add one.
                </div>
            @endforelse
        </div>
    </div>

    <!-- ==========================================
         TAB 3: SEASONAL ANNOUNCEMENTS
         ========================================== -->
    <div x-show="activeTab === 'announcements'" style="display: none;" class="space-y-4">
        <div class="flex items-center justify-between bg-white p-4 rounded-2xl border border-gray-200">
            <div>
                <h3 class="text-base font-black text-gray-900">Seasonal Announcements & Broadcasts</h3>
                <p class="text-xs text-gray-500">Display dismissible celebration banners on the dashboard/homepage or broadcast in-app and email notifications.</p>
            </div>
            <button type="button" @click="showCreateAnnouncementModal = true"
                    class="px-4 py-2.5 rounded-xl bg-[#0d9488] hover:bg-teal-700 text-white font-black text-xs shadow-sm transition cursor-pointer">
                + New Seasonal Announcement
            </button>
        </div>

        <div class="space-y-3">
            @forelse($announcements as $ann)
                <div class="bg-white rounded-2xl border border-gray-200 p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 flex-wrap mb-1">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase {{ $ann->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-500' }}">
                                {{ $ann->is_active ? 'Active' : 'Paused' }}
                            </span>
                            @if($ann->mode)
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-teal-50 text-[#0d9488]">
                                    {{ $ann->mode->icon }} {{ $ann->mode->name }}
                                </span>
                            @endif
                            @if($ann->is_dismissible)
                                <span class="text-[11px] text-gray-400 font-semibold">&bull; Dismissible</span>
                            @endif
                        </div>
                        <h4 class="font-black text-gray-900 text-sm sm:text-base">{{ $ann->title }}</h4>
                        <p class="text-xs text-gray-600 mt-1">{{ $ann->message }}</p>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <form action="{{ route('admin.seasonal.announcements.broadcast', $ann->id) }}" method="POST" onsubmit="return confirm('Send this announcement as an in-app notification to all active users?');">
                            @csrf
                            <button type="submit" class="px-3 py-2 rounded-xl bg-teal-50 hover:bg-teal-100 text-[#0d9488] font-extrabold text-xs transition cursor-pointer">
                                🔔 Broadcast In-App
                            </button>
                        </form>
                        <form action="{{ route('admin.seasonal.announcements.toggle', $ann->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-3 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs transition cursor-pointer">
                                {{ $ann->is_active ? 'Disable' : 'Enable' }}
                            </button>
                        </form>
                        <form action="{{ route('admin.seasonal.announcements.destroy', $ann->id) }}" method="POST" onsubmit="return confirm('Delete this announcement?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3 py-2 rounded-xl text-rose-600 hover:bg-rose-50 font-bold text-xs transition cursor-pointer">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl p-10 text-center text-gray-500">
                    No seasonal announcements yet.
                </div>
            @endforelse
        </div>
    </div>

    <!-- ==========================================
         TAB 4: CLAIM LEDGER & AUDIT LOGS
         ========================================== -->
    <div x-show="activeTab === 'claims'" style="display: none;" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent User Gift Claims -->
        <div class="bg-white rounded-3xl border border-gray-200 overflow-hidden shadow-xs">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h3 class="text-sm font-black text-gray-900 uppercase tracking-wider">Recent User Gift Claims</h3>
            </div>
            <div class="divide-y divide-gray-100 max-h-[520px] overflow-y-auto">
                @forelse($recentClaims as $claim)
                    <div class="px-6 py-3.5 flex items-center justify-between text-xs">
                        <div>
                            <div class="font-black text-gray-900">
                                {{ $claim->user?->name ?? 'User #'.$claim->user_id }}
                                <span class="text-gray-400 font-normal">({{ $claim->user?->email }})</span>
                            </div>
                            <div class="text-gray-500 mt-0.5">
                                {{ $claim->mode?->icon ?? '🎁' }} <strong>{{ $claim->gift?->name ?? 'Seasonal Gift' }}</strong>
                                &bull; <span class="uppercase text-[10px] font-bold">{{ str_replace('_', ' ', $claim->distribution_method) }}</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="font-black text-emerald-600 text-sm block">
                                @if($claim->reward_value_usd > 0)
                                    +${{ number_format($claim->reward_value_usd, 2) }}
                                @else
                                    {{ $claim->reward_code }}
                                @endif
                            </span>
                            <span class="text-[10px] text-gray-400">{{ optional($claim->claimed_at)->diffForHumans() }}</span>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-xs text-gray-400">No gift claims recorded yet.</div>
                @endforelse
            </div>
        </div>

        <!-- Admin Seasonal Audit Trail -->
        <div class="bg-white rounded-3xl border border-gray-200 overflow-hidden shadow-xs">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h3 class="text-sm font-black text-gray-900 uppercase tracking-wider">Seasonal Security & Audit Logs</h3>
            </div>
            <div class="divide-y divide-gray-100 max-h-[520px] overflow-y-auto">
                @forelse($auditLogs as $log)
                    <div class="px-6 py-3.5 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="font-black text-gray-900">{{ $log->actor_name ?: 'System' }}</span>
                            <span class="text-[10px] text-gray-400">{{ optional($log->created_at)->format('M d, H:i:s') }}</span>
                        </div>
                        <p class="text-gray-600 mt-0.5">{{ $log->summary }}</p>
                    </div>
                @empty
                    <div class="p-8 text-center text-xs text-gray-400">No seasonal audit events yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- ======================================================
         MODAL: CREATE / EDIT SEASONAL MODE
         ====================================================== -->
    <div x-show="showCreateModeModal || editMode !== null" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs overflow-y-auto">
        <div class="bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl border border-gray-200 my-8 max-h-[90vh] overflow-y-auto"
             @click.away="showCreateModeModal = false; editMode = null">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                <h3 class="text-lg font-black text-gray-900" x-text="editMode ? ('Edit ' + editMode.name) : 'Create New Seasonal Celebration Mode'"></h3>
                <button type="button" @click="showCreateModeModal = false; editMode = null" class="text-gray-400 hover:text-gray-700 text-xl font-bold cursor-pointer">&times;</button>
            </div>

            <form :action="editMode ? ('/admin/seasonal/modes/' + editMode.id) : '{{ route('admin.seasonal.modes.store') }}'"
                  method="POST" enctype="multipart/form-data" class="space-y-4 mt-4 text-xs">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label class="font-bold text-gray-700 block mb-1">Seasonal Event Name *</label>
                        <input type="text" name="name" :value="editMode ? editMode.name : ''" required placeholder="e.g. Christmas Mode 2026"
                               class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 font-semibold">
                    </div>
                    <div>
                        <label class="font-bold text-gray-700 block mb-1">Emoji / Icon</label>
                        <input type="text" name="icon" :value="editMode ? editMode.icon : '🎄'" placeholder="🎄"
                               class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 font-semibold">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="font-bold text-gray-700 block mb-1">Celebration Category *</label>
                        <select name="theme_type" :value="editMode ? editMode.theme_type : 'christmas'" class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 font-semibold">
                            <option value="christmas">Christmas</option>
                            <option value="new_year">New Year</option>
                            <option value="valentines">Valentine's Day</option>
                            <option value="easter">Easter</option>
                            <option value="halloween">Halloween</option>
                            <option value="independence">Nigeria Independence Day</option>
                            <option value="black_friday">Black Friday</option>
                            <option value="anniversary">May22SMS Anniversary</option>
                            <option value="ramadan">Ramadan / Eid</option>
                            <option value="custom">Custom Celebration</option>
                        </select>
                    </div>
                    <div>
                        <label class="font-bold text-gray-700 block mb-1">Animation Effect *</label>
                        <select name="animation_type" :value="editMode ? editMode.animation_type : 'snow'" class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 font-semibold">
                            <option value="snow">❄️ Falling Snowflakes (Christmas)</option>
                            <option value="fireworks_confetti">🎆 Fireworks & Confetti (New Year / Anniversary)</option>
                            <option value="hearts">💖 Floating Hearts (Valentine's Day)</option>
                            <option value="easter_eggs">🥚 Floating Easter Eggs (Easter)</option>
                            <option value="pumpkins">🎃 Floating Pumpkins (Halloween)</option>
                            <option value="green_white_confetti">🇳🇬 Nigerian Green & White Ribbons (Independence)</option>
                            <option value="gold_sparkles">✨ Golden Sparkles (Black Friday / VIP)</option>
                            <option value="lanterns_stars">🌙 Crescent Moons & Lantern Stars (Ramadan / Eid)</option>
                            <option value="none">None (Banner Only)</option>
                        </select>
                    </div>
                    <div>
                        <label class="font-bold text-gray-700 block mb-1">Animation Intensity *</label>
                        <select name="animation_intensity" :value="editMode ? editMode.animation_intensity : 'medium'" class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 font-semibold">
                            <option value="low">Low (Subtle)</option>
                            <option value="medium">Medium (Balanced)</option>
                            <option value="high">High (Festive Burst)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label class="font-bold text-gray-700 block mb-1">Greeting Banner Headline *</label>
                        <input type="text" name="greeting_title" :value="editMode ? editMode.greeting_title : ''" required placeholder="e.g. Merry Christmas from MAY22SMS! 🎄"
                               class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 font-semibold">
                    </div>
                    <div>
                        <label class="font-bold text-gray-700 block mb-1">Badge Pill Label</label>
                        <input type="text" name="badge_text" :value="editMode ? editMode.badge_text : 'Festive Celebration'"
                               class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 font-semibold">
                    </div>
                </div>

                <div>
                    <label class="font-bold text-gray-700 block mb-1">Greeting Subtitle / Announcement Message</label>
                    <textarea name="greeting_subtitle" rows="2" :value="editMode ? editMode.greeting_subtitle : ''"
                              class="w-full rounded-xl border border-gray-300 px-3.5 py-2 font-semibold"></textarea>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="font-bold text-gray-700 block mb-1">Primary Color</label>
                        <input type="color" name="primary_color" :value="editMode ? editMode.primary_color : '#dc2626'"
                               class="w-full h-10 rounded-xl border border-gray-300 p-1 cursor-pointer">
                    </div>
                    <div>
                        <label class="font-bold text-gray-700 block mb-1">Secondary Color</label>
                        <input type="color" name="secondary_color" :value="editMode ? editMode.secondary_color : '#15803d'"
                               class="w-full h-10 rounded-xl border border-gray-300 p-1 cursor-pointer">
                    </div>
                    <div>
                        <label class="font-bold text-gray-700 block mb-1">Accent Color</label>
                        <input type="color" name="accent_color" :value="editMode ? editMode.accent_color : '#facc15'"
                               class="w-full h-10 rounded-xl border border-gray-300 p-1 cursor-pointer">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="font-bold text-gray-700 block mb-1">Custom Banner Image (Optional)</label>
                        <input type="file" name="banner_image_file" accept="image/*" class="w-full text-xs">
                    </div>
                    <div>
                        <label class="font-bold text-gray-700 block mb-1">Custom Background Image (Optional)</label>
                        <input type="file" name="background_image_file" accept="image/*" class="w-full text-xs">
                    </div>
                </div>

                <!-- Scheduling & Countdown -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-3.5 rounded-2xl bg-gray-50 border border-gray-200">
                    <div>
                        <label class="font-bold text-gray-700 block mb-1">Auto-Start Date & Time</label>
                        <input type="datetime-local" name="starts_at" :value="editMode && editMode.starts_at ? editMode.starts_at.slice(0,16) : ''"
                               class="w-full rounded-xl border border-gray-300 px-3 py-2">
                    </div>
                    <div>
                        <label class="font-bold text-gray-700 block mb-1">Auto-End Date & Time</label>
                        <input type="datetime-local" name="ends_at" :value="editMode && editMode.ends_at ? editMode.ends_at.slice(0,16) : ''"
                               class="w-full rounded-xl border border-gray-300 px-3 py-2">
                    </div>
                    <div>
                        <label class="font-bold text-gray-700 block mb-1">Countdown Target Date</label>
                        <input type="datetime-local" name="countdown_target_at" :value="editMode && editMode.countdown_target_at ? editMode.countdown_target_at.slice(0,16) : ''"
                               class="w-full rounded-xl border border-gray-300 px-3 py-2">
                    </div>
                </div>

                <!-- Checkboxes for Visual Effects & Pages -->
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 pt-2">
                    <label class="inline-flex items-center gap-2 font-bold text-gray-700 cursor-pointer">
                        <input type="checkbox" name="animations_enabled" value="1" :checked="!editMode || editMode.animations_enabled" class="rounded text-[#0d9488]">
                        <span>Enable Animations</span>
                    </label>
                    <label class="inline-flex items-center gap-2 font-bold text-gray-700 cursor-pointer">
                        <input type="checkbox" name="decorations_enabled" value="1" :checked="!editMode || editMode.decorations_enabled" class="rounded text-[#0d9488]">
                        <span>Enable Decorations</span>
                    </label>
                    <label class="inline-flex items-center gap-2 font-bold text-gray-700 cursor-pointer">
                        <input type="checkbox" name="banner_enabled" value="1" :checked="!editMode || editMode.banner_enabled" class="rounded text-[#0d9488]">
                        <span>Show Dashboard Banner</span>
                    </label>
                    <label class="inline-flex items-center gap-2 font-bold text-gray-700 cursor-pointer">
                        <input type="checkbox" name="countdown_enabled" value="1" :checked="editMode && editMode.countdown_enabled" class="rounded text-[#0d9488]">
                        <span>Show Live Countdown</span>
                    </label>
                    <label class="inline-flex items-center gap-2 font-bold text-gray-700 cursor-pointer">
                        <input type="checkbox" name="is_scheduled" value="1" :checked="editMode && editMode.is_scheduled" class="rounded text-[#0d9488]">
                        <span>Enable Auto-Schedule</span>
                    </label>
                </div>

                <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                    <button type="button" @click="showCreateModeModal = false; editMode = null"
                            class="px-4 py-2.5 rounded-xl border border-gray-200 text-gray-600 font-bold">
                        Cancel
                    </button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#0d9488] hover:bg-teal-700 text-white font-black shadow-md cursor-pointer">
                        Save Seasonal Mode
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ======================================================
         MODAL: CREATE / EDIT SEASONAL GIFT
         ====================================================== -->
    <div x-show="showCreateGiftModal || editGift !== null" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs overflow-y-auto">
        <div class="bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl border border-gray-200 my-8 max-h-[90vh] overflow-y-auto"
             @click.away="showCreateGiftModal = false; editGift = null">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                <h3 class="text-lg font-black text-gray-900" x-text="editGift ? ('Edit Gift: ' + editGift.name) : 'Create Seasonal User Gift'"></h3>
                <button type="button" @click="showCreateGiftModal = false; editGift = null" class="text-gray-400 hover:text-gray-700 text-xl font-bold cursor-pointer">&times;</button>
            </div>

            <form :action="editGift ? ('/admin/seasonal/gifts/' + editGift.id) : '{{ route('admin.seasonal.gifts.store') }}'"
                  method="POST" enctype="multipart/form-data" class="space-y-4 mt-4 text-xs">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="font-bold text-gray-700 block mb-1">Gift Name *</label>
                        <input type="text" name="name" :value="editGift ? editGift.name : ''" required placeholder="e.g. Christmas Wallet Bonus"
                               class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 font-semibold">
                    </div>
                    <div>
                        <label class="font-bold text-gray-700 block mb-1">Associated Seasonal Event</label>
                        <select name="seasonal_mode_id" :value="editGift ? editGift.seasonal_mode_id : '{{ $activeMode?->id }}'"
                                class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 font-semibold">
                            <option value="">All Seasonal Celebrations</option>
                            @foreach($modes as $m)
                                <option value="{{ $m->id }}">{{ $m->icon }} {{ $m->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="font-bold text-gray-700 block mb-1">Gift Description</label>
                    <input type="text" name="description" :value="editGift ? editGift.description : ''"
                           placeholder="e.g. Unwrap your festive wallet credit from MAY22SMS!"
                           class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 font-semibold">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="font-bold text-gray-700 block mb-1">Gift Type *</label>
                        <select name="gift_type" :value="editGift ? editGift.gift_type : 'wallet_credit'"
                                class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 font-semibold">
                            <option value="wallet_credit">💰 Free Wallet Credit (USD)</option>
                            <option value="sms_credit">📱 Free SMS Verification Credit</option>
                            <option value="boosting_credit">🚀 Free Social Boosting Credit</option>
                            <option value="service_credit">⚡ Special Service Credit</option>
                            <option value="discount_coupon">🎟️ Discount Coupon (%)</option>
                            <option value="promo_code">🏷️ Promotional Code</option>
                            <option value="custom_reward">✨ Custom Promotional Reward</option>
                        </select>
                    </div>
                    <div>
                        <label class="font-bold text-gray-700 block mb-1">Reward Value (USD) *</label>
                        <input type="number" step="0.01" min="0" name="reward_value_usd" :value="editGift ? editGift.reward_value_usd : '0.15'" required
                               class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 font-semibold">
                    </div>
                    <div>
                        <label class="font-bold text-gray-700 block mb-1">Distribution Mode *</label>
                        <select name="distribution_mode" :value="editGift ? editGift.distribution_mode : 'manual_claim'"
                                class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 font-semibold">
                            <option value="manual_claim">Option A: Manual Claim Button</option>
                            <option value="auto_distribute">Option B: Automatic Distribution</option>
                            <option value="selected_users">Option C: Selected Users Only</option>
                            <option value="all_eligible">Option D: All Eligible Users</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="font-bold text-gray-700 block mb-1">Claim Button Text *</label>
                        <input type="text" name="claim_button_text" :value="editGift ? editGift.claim_button_text : 'Claim Your Christmas Gift 🎁'" required
                               class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 font-semibold">
                    </div>
                    <div>
                        <label class="font-bold text-gray-700 block mb-1">Max Total Claims (0 = Unlimited)</label>
                        <input type="number" min="0" name="max_total_claims" :value="editGift ? editGift.max_total_claims : '500'" required
                               class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 font-semibold">
                    </div>
                    <div>
                        <label class="font-bold text-gray-700 block mb-1">Max Claims Per User</label>
                        <input type="number" min="1" max="50" name="max_claims_per_user" :value="editGift ? editGift.max_claims_per_user : '1'" required
                               class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 font-semibold">
                    </div>
                </div>

                <!-- Eligibility Rules -->
                <div class="p-3.5 rounded-2xl bg-gray-50 border border-gray-200 space-y-3">
                    <div class="font-black text-gray-800 uppercase tracking-wider text-[11px]">User Eligibility Requirements</div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="font-bold text-gray-600 block mb-1">Min Account Age (Days)</label>
                            <input type="number" min="0" name="min_account_age_days" :value="editGift ? editGift.min_account_age_days : '0'"
                                   class="w-full rounded-xl border border-gray-300 px-3 py-2">
                        </div>
                        <div>
                            <label class="font-bold text-gray-600 block mb-1">Min Wallet Funding (USD)</label>
                            <input type="number" step="0.01" min="0" name="min_wallet_funding_usd" :value="editGift ? editGift.min_wallet_funding_usd : '0'"
                                   class="w-full rounded-xl border border-gray-300 px-3 py-2">
                        </div>
                        <div>
                            <label class="font-bold text-gray-600 block mb-1">Min Successful Orders</label>
                            <input type="number" min="0" name="min_transactions_count" :value="editGift ? editGift.min_transactions_count : '0'"
                                   class="w-full rounded-xl border border-gray-300 px-3 py-2">
                        </div>
                    </div>
                    <div>
                        <label class="font-bold text-gray-600 block mb-1">Selected User IDs or Emails (Comma-separated, for Option C)</label>
                        <input type="text" name="eligible_user_ids_csv"
                               :value="editGift && editGift.eligible_user_ids ? editGift.eligible_user_ids.join(', ') : ''"
                               placeholder="e.g. 12, 45, user@example.com"
                               class="w-full rounded-xl border border-gray-300 px-3 py-2">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="font-bold text-gray-700 block mb-1">Emoji Icon (or upload image)</label>
                        <input type="text" name="icon_or_image" :value="editGift ? editGift.icon_or_image : '🎁'"
                               class="w-full rounded-xl border border-gray-300 px-3.5 py-2">
                    </div>
                    <div>
                        <label class="font-bold text-gray-700 block mb-1">Start Date (Optional)</label>
                        <input type="datetime-local" name="starts_at" :value="editGift && editGift.starts_at ? editGift.starts_at.slice(0,16) : ''"
                               class="w-full rounded-xl border border-gray-300 px-3 py-2">
                    </div>
                    <div>
                        <label class="font-bold text-gray-700 block mb-1">Expiration Date (Optional)</label>
                        <input type="datetime-local" name="expires_at" :value="editGift && editGift.expires_at ? editGift.expires_at.slice(0,16) : ''"
                               class="w-full rounded-xl border border-gray-300 px-3 py-2">
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-4 pt-1">
                    <label class="inline-flex items-center gap-2 font-bold text-gray-700 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" :checked="!editGift || editGift.is_active" class="rounded text-[#0d9488]">
                        <span>Gift is Active</span>
                    </label>
                    <label x-show="!editGift" class="inline-flex items-center gap-2 font-bold text-indigo-700 cursor-pointer">
                        <input type="checkbox" name="distribute_immediately" value="1" class="rounded text-indigo-600">
                        <span>If Option B/C/D, distribute immediately upon saving</span>
                    </label>
                </div>

                <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                    <button type="button" @click="showCreateGiftModal = false; editGift = null"
                            class="px-4 py-2.5 rounded-xl border border-gray-200 text-gray-600 font-bold">
                        Cancel
                    </button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#0d9488] hover:bg-teal-700 text-white font-black shadow-md cursor-pointer">
                        Save Seasonal Gift
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ======================================================
         MODAL: DISTRIBUTE GIFT BATCH (SELECTED OR ALL ELIGIBLE)
         ====================================================== -->
    <div x-show="distributeGiftModal !== null" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-gray-200"
             @click.away="distributeGiftModal = null">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <h3 class="text-base font-black text-gray-900">🚀 Distribute Gift: <span x-text="distributeGiftModal ? distributeGiftModal.name : ''"></span></h3>
                <button type="button" @click="distributeGiftModal = null" class="text-gray-400 hover:text-gray-700 text-xl font-bold cursor-pointer">&times;</button>
            </div>

            <form :action="distributeGiftModal ? ('/admin/seasonal/gifts/' + distributeGiftModal.id + '/distribute') : '#'"
                  method="POST" class="space-y-4 mt-4 text-xs">
                @csrf
                <div>
                    <label class="font-bold text-gray-700 block mb-1">Distribution Target *</label>
                    <select name="target_mode" class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 font-semibold">
                        <option value="all_eligible">Option D: All Eligible Users (Meets Age/Funding/Tx Rules)</option>
                        <option value="selected_users">Option C: Selected Users Only (Enter IDs or Emails below)</option>
                    </select>
                </div>
                <div>
                    <label class="font-bold text-gray-700 block mb-1">Selected User IDs or Emails (For Option C)</label>
                    <textarea name="user_ids_csv" rows="3" placeholder="e.g. 1, 25, customer@gmail.com"
                              class="w-full rounded-xl border border-gray-300 px-3.5 py-2 font-semibold"></textarea>
                    <p class="text-[11px] text-gray-400 mt-1">Users who already claimed this gift are automatically skipped to prevent duplicate credits.</p>
                </div>

                <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-3">
                    <button type="button" @click="distributeGiftModal = null" class="px-4 py-2 rounded-xl border border-gray-200 text-gray-600 font-bold">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-black shadow-md cursor-pointer">
                        Distribute Reward Now
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ======================================================
         MODAL: CREATE SEASONAL ANNOUNCEMENT
         ====================================================== -->
    <div x-show="showCreateAnnouncementModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-gray-200"
             @click.away="showCreateAnnouncementModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <h3 class="text-base font-black text-gray-900">📣 Create Seasonal Announcement</h3>
                <button type="button" @click="showCreateAnnouncementModal = false" class="text-gray-400 hover:text-gray-700 text-xl font-bold cursor-pointer">&times;</button>
            </div>

            <form action="{{ route('admin.seasonal.announcements.store') }}" method="POST" class="space-y-4 mt-4 text-xs">
                @csrf
                <div>
                    <label class="font-bold text-gray-700 block mb-1">Associated Seasonal Event</label>
                    <select name="seasonal_mode_id" class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 font-semibold">
                        <option value="">All Celebrations</option>
                        @foreach($modes as $m)
                            <option value="{{ $m->id }}" {{ $activeMode && $activeMode->id === $m->id ? 'selected' : '' }}>{{ $m->icon }} {{ $m->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="font-bold text-gray-700 block mb-1">Headline *</label>
                    <input type="text" name="title" required placeholder="e.g. 🎄 Merry Christmas from MAY22SMS!"
                           class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 font-semibold">
                </div>
                <div>
                    <label class="font-bold text-gray-700 block mb-1">Message *</label>
                    <textarea name="message" rows="3" required placeholder="Celebrate with a special gift from us..."
                              class="w-full rounded-xl border border-gray-300 px-3.5 py-2 font-semibold"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="font-bold text-gray-700 block mb-1">CTA Button Text</label>
                        <input type="text" name="cta_text" placeholder="Claim Gift" class="w-full rounded-xl border border-gray-300 px-3 py-2">
                    </div>
                    <div>
                        <label class="font-bold text-gray-700 block mb-1">CTA Link</label>
                        <input type="text" name="cta_link" placeholder="/dashboard#seasonal-celebration-hub" class="w-full rounded-xl border border-gray-300 px-3 py-2">
                    </div>
                </div>
                <div class="space-y-2 pt-1">
                    <label class="flex items-center gap-2 font-bold text-gray-700 cursor-pointer">
                        <input type="checkbox" name="is_dismissible" value="1" checked class="rounded text-[#0d9488]">
                        <span>Allow users to dismiss banner</span>
                    </label>
                    <label class="flex items-center gap-2 font-bold text-gray-700 cursor-pointer">
                        <input type="checkbox" name="send_in_app_notification" value="1" class="rounded text-[#0d9488]">
                        <span>Send In-App Notification to all active users now</span>
                    </label>
                    <label class="flex items-center gap-2 font-bold text-gray-700 cursor-pointer">
                        <input type="checkbox" name="send_email_notification" value="1" class="rounded text-[#0d9488]">
                        <span>Send Email Notification using MAY22SMS mailer</span>
                    </label>
                </div>

                <div class="pt-3 border-t border-gray-100 flex items-center justify-end gap-3">
                    <button type="button" @click="showCreateAnnouncementModal = false" class="px-4 py-2 rounded-xl border border-gray-200 text-gray-600 font-bold">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#0d9488] hover:bg-teal-700 text-white font-black shadow-md cursor-pointer">
                        Publish Announcement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

