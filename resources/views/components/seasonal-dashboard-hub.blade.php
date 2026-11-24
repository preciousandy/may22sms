@php
    $mode = $activeSeasonalMode ?? null;
    $user = auth()->user();
    $userPref = $seasonalUserPref ?? null;
    $dismissedIds = $userPref ? array_map('intval', (array) ($userPref->dismissed_announcements ?? [])) : [];

    $activeGifts = collect();
    $activeAnnouncements = collect();
    $claimedGiftIds = [];

    if ($mode) {
        $activeGifts = $mode->gifts
            ? $mode->gifts->filter(fn ($g) => $g->isCurrentlyAvailable() && $g->distribution_mode === 'manual_claim')
            : collect();

        $activeAnnouncements = $mode->announcements
            ? $mode->announcements->filter(fn ($a) => $a->isCurrentlyVisible() && $a->show_on_dashboard && ! in_array((int) $a->id, $dismissedIds, true))
            : collect();

        if ($user && \Illuminate\Support\Facades\Schema::hasTable('seasonal_gift_claims')) {
            $claimedGiftIds = \App\Models\SeasonalGiftClaim::where('user_id', $user->id)
                ->pluck('seasonal_gift_id')
                ->map(fn ($id) => (int) $id)
                ->toArray();
        }
    }
@endphp

@if($mode && $mode->banner_enabled && $mode->shouldDisplayOnPage('dashboard'))
<div id="seasonal-celebration-hub"
     x-data="seasonalCelebrationHub({
        countdownTarget: @js($mode->countdown_enabled && $mode->countdown_target_at ? $mode->countdown_target_at->toIsoString() : null),
        currencySymbol: @js($currencySymbol ?? '$'),
        currencyFx: {{ (float) ($currencyFx ?? 1) }}
     })"
     x-init="initHub()"
     class="space-y-4">

    <!-- 1. Dismissible Seasonal Announcements Strip -->
    @foreach($activeAnnouncements as $ann)
        <div x-show="!dismissedAnnouncements.includes({{ $ann->id }})"
             x-transition
             class="rounded-2xl p-4 text-white shadow-md flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border border-white/20"
             style="background: {{ $mode->banner_gradient ?: "linear-gradient(135deg, {$mode->secondary_color} 0%, {$mode->primary_color} 100%)" }};">
            <div class="flex items-start sm:items-center gap-3">
                <span class="text-2xl shrink-0">{{ $mode->icon ?: '🎉' }}</span>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        @if($ann->badge_text)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-amber-400 text-slate-950">{{ $ann->badge_text }}</span>
                        @endif
                        <h4 class="font-black text-sm sm:text-base text-white">{{ $ann->title }}</h4>
                    </div>
                    <p class="text-xs text-white/90 mt-0.5">{{ $ann->message }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                @if($ann->cta_text && $ann->cta_link)
                    <a href="{{ $ann->cta_link }}" class="px-3.5 py-2 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-950 font-black text-xs transition">
                        {{ $ann->cta_text }} &rarr;
                    </a>
                @endif
                @if($ann->is_dismissible)
                    <button type="button"
                            @click="dismissAnnouncement({{ $ann->id }})"
                            class="p-1.5 rounded-lg bg-black/25 hover:bg-black/40 text-white/80 hover:text-white text-xs font-bold transition cursor-pointer"
                            title="Dismiss announcement">
                        &times;
                    </button>
                @endif
            </div>
        </div>
    @endforeach

    <!-- 2. Main Seasonal Celebration & Gift Claim Card -->
    <div class="rounded-3xl p-5 sm:p-7 text-white shadow-xl relative overflow-hidden border border-white/20"
         style="background: {{ $mode->banner_gradient ?: "linear-gradient(135deg, {$mode->secondary_color} 0%, {$mode->primary_color} 100%)" }};">

        @if($mode->banner_image)
            <div class="absolute inset-0 opacity-20 bg-cover bg-center pointer-events-none"
                 style="background-image: url('{{ asset($mode->banner_image) }}');"></div>
        @endif

        <!-- Ambient Holiday Orbs -->
        <div class="absolute -right-12 -top-12 w-56 h-56 rounded-full bg-white/10 blur-3xl pointer-events-none"></div>
        <div class="absolute -left-12 -bottom-12 w-56 h-56 rounded-full bg-amber-400/15 blur-3xl pointer-events-none"></div>

        <div class="relative z-10 space-y-5">
            <!-- Header & Countdown Row -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 flex-wrap mb-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-amber-400 text-slate-950 shadow-sm">
                            <span>{{ $mode->icon ?: '🎉' }}</span>
                            <span>{{ $mode->badge_text ?: $mode->name }}</span>
                        </span>
                        <button type="button"
                                @click="openHistoryModal()"
                                class="px-3 py-1 rounded-full text-[11px] font-bold bg-white/15 hover:bg-white/25 text-white border border-white/20 transition cursor-pointer">
                            📜 My Claimed Gifts History
                        </button>
                        @if($seasonalFeatureSettings['xmas_hub_button_enabled'] ?? true)
                            <a href="/christmas-wonderland"
                               class="px-3.5 py-1 rounded-full text-[11px] font-black bg-amber-400 hover:bg-amber-300 text-slate-950 shadow-sm transition flex items-center gap-1">
                                <span>🎄 Open Interactive Christmas Wonderland (Advent, Tree, Snowman &amp; Village) &rarr;</span>
                            </a>
                        @endif
                    </div>

                    <h2 class="text-xl sm:text-2xl md:text-3xl font-black tracking-tight text-white">
                        {{ $mode->greeting_title ?: $mode->name }}
                    </h2>
                    @if($mode->greeting_subtitle)
                        <p class="text-xs sm:text-sm text-white/90 mt-1 max-w-2xl leading-relaxed">
                            {{ $mode->greeting_subtitle }}
                        </p>
                    @endif
                </div>

                <!-- Live Countdown Clock (if enabled) -->
                <template x-if="countdownText !== ''">
                    <div class="bg-black/30 backdrop-blur-md border border-white/20 rounded-2xl px-4 py-3 text-center shrink-0">
                        <span class="text-[10px] font-black uppercase tracking-widest text-amber-300 block">Celebration Countdown</span>
                        <span class="text-lg sm:text-xl font-mono font-black text-white tracking-wider mt-0.5 block" x-text="countdownText"></span>
                    </div>
                </template>
            </div>

            <!-- Claimable Seasonal Gift Cards Grid -->
            @if($activeGifts->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                    @foreach($activeGifts as $gift)
                        @php
                            $alreadyClaimed = in_array((int) $gift->id, $claimedGiftIds, true);
                            $remaining = $gift->remainingClaims();
                        @endphp
                        <div class="rounded-2xl bg-black/25 backdrop-blur-md border border-white/20 p-4 sm:p-5 flex flex-col justify-between gap-4 hover:border-amber-300/60 transition">
                            <div class="flex items-start gap-3.5">
                                <div class="w-13 h-13 rounded-2xl bg-white/15 border border-white/25 flex items-center justify-center text-2xl sm:text-3xl shrink-0 shadow-inner">
                                    @if($gift->icon_or_image && str_starts_with($gift->icon_or_image, '/'))
                                        <img src="{{ asset($gift->icon_or_image) }}" alt="{{ $gift->name }}" class="w-9 h-9 object-contain">
                                    @else
                                        {{ $gift->icon_or_image ?: ($mode->icon ?: '🎁') }}
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2 flex-wrap">
                                        <h3 class="font-black text-white text-sm sm:text-base truncate">{{ $gift->name }}</h3>
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-amber-400 text-slate-950 shrink-0">
                                            @if($gift->reward_value_usd > 0)
                                                @php
                                                    $giftNgn = $gift->reward_value_usd * ($ngnPerUsd ?? 1500);
                                                    $displayNgn = ($giftNgn > 0 && $giftNgn <= 2.5) ? max(1, min(2, round($giftNgn))) : $giftNgn;
                                                @endphp
                                                ₦{{ number_format($displayNgn, 2) }}
                                            @elseif($gift->discount_percent > 0)
                                                {{ $gift->discount_percent }}% OFF
                                            @else
                                                FREE GIFT
                                            @endif
                                        </span>
                                    </div>
                                    <p class="text-xs text-white/85 mt-1">
                                        {{ $gift->description ?: 'Special celebration reward for MAY22SMS users!' }}
                                    </p>
                                    @if($remaining !== null)
                                        <div class="text-[11px] font-bold text-amber-300 mt-1.5">
                                            🔥 {{ number_format($remaining) }} gift claim(s) remaining in pool
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center justify-between gap-3 pt-2 border-t border-white/10">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-white/75">
                                    {{ str_replace('_', ' ', $gift->gift_type) }}
                                </span>

                                @if($seasonalFeatureSettings['xmas_claim_buttons_enabled'] ?? true)
                                    <button type="button"
                                            @click="claimSeasonalGift({{ $gift->id }})"
                                            :disabled="claimingGiftId === {{ $gift->id }} || claimedIds.includes({{ $gift->id }})"
                                            :class="claimedIds.includes({{ $gift->id }})
                                                ? 'bg-emerald-500/40 text-emerald-100 border border-emerald-400/40 cursor-not-allowed'
                                                : 'bg-gradient-to-r from-amber-400 to-yellow-300 hover:from-amber-300 hover:to-yellow-200 text-slate-950 shadow-lg cursor-pointer active:scale-95'"
                                            class="px-5 py-2.5 rounded-xl font-black text-xs transition flex items-center gap-2">
                                        <template x-if="claimingGiftId === {{ $gift->id }}">
                                            <span>Claiming Gift...</span>
                                        </template>
                                        <template x-if="claimingGiftId !== {{ $gift->id }} && claimedIds.includes({{ $gift->id }})">
                                            <span>✅ Gift Claimed</span>
                                        </template>
                                        <template x-if="claimingGiftId !== {{ $gift->id }} && !claimedIds.includes({{ $gift->id }})">
                                            <span>{{ $gift->claim_button_text ?: 'Claim Your Gift 🎁' }}</span>
                                        </template>
                                    </button>
                                @else
                                    <button type="button" disabled
                                            class="px-4 py-2 rounded-xl bg-white/10 text-white/60 border border-white/15 font-bold text-xs cursor-not-allowed">
                                        🔒 Claim Disabled
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Dynamic Claim Feedback Alert -->
            <template x-if="feedbackMsg !== ''">
                <div :class="feedbackSuccess ? 'bg-emerald-500/30 border-emerald-300 text-white' : 'bg-rose-500/30 border-rose-300 text-white'"
                     class="rounded-2xl border px-4 py-3 text-xs sm:text-sm font-black flex items-center justify-between gap-3">
                    <span x-text="feedbackMsg"></span>
                    <button type="button" @click="feedbackMsg = ''" class="text-white/80 hover:text-white font-bold">&times;</button>
                </div>
            </template>
        </div>
    </div>

    <!-- 3. User Seasonal Gift Claim History Modal -->
    <div x-show="historyOpen" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200"
             @click.away="historyOpen = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-base font-black text-slate-900">🎁 My Claimed Seasonal Gifts</h3>
                <button type="button" @click="historyOpen = false" class="text-slate-400 hover:text-slate-700 text-xl font-bold cursor-pointer">&times;</button>
            </div>

            <div class="divide-y divide-slate-100 max-h-80 overflow-y-auto my-3">
                <template x-if="historyLoading">
                    <div class="p-6 text-center text-xs text-slate-400 font-bold">Loading your gift history...</div>
                </template>
                <template x-if="!historyLoading && historyItems.length === 0">
                    <div class="p-6 text-center text-xs text-slate-400 font-bold">You haven't claimed any seasonal gifts yet.</div>
                </template>
                <template x-for="item in historyItems" :key="item.id">
                    <div class="py-3 flex items-center justify-between text-xs">
                        <div>
                            <div class="font-black text-slate-900">
                                <span x-text="item.mode_icon"></span>
                                <span x-text="item.gift_name"></span>
                            </div>
                            <div class="text-slate-400 text-[11px] mt-0.5" x-text="item.mode_name + ' • ' + item.claimed_at"></div>
                        </div>
                        <div class="text-right">
                            <span class="font-black text-emerald-600 block"
                                  x-text="item.reward_value_usd > 0 ? ('+' + currencySymbol + (item.reward_value_usd * currencyFx).toFixed(2)) : item.reward_code"></span>
                            <span class="text-[10px] text-slate-400 font-mono" x-text="item.reference"></span>
                        </div>
                    </div>
                </template>
            </div>

            <div class="pt-3 border-t border-slate-100 text-right">
                <button type="button" @click="historyOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs cursor-pointer">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function seasonalCelebrationHub(cfg) {
        return {
            claimedIds: @js($claimedGiftIds),
            dismissedAnnouncements: @js($dismissedIds),
            claimingGiftId: null,
            feedbackMsg: '',
            feedbackSuccess: true,
            countdownText: '',
            historyOpen: false,
            historyLoading: false,
            historyItems: [],
            currencySymbol: cfg.currencySymbol || '$',
            currencyFx: Number(cfg.currencyFx || 1),

            initHub() {
                if (cfg.countdownTarget) {
                    this.updateCountdown(cfg.countdownTarget);
                    setInterval(() => this.updateCountdown(cfg.countdownTarget), 1000);
                }
            },

            updateCountdown(targetIso) {
                var targetMs = new Date(targetIso).getTime();
                var diffSec = Math.max(0, Math.floor((targetMs - Date.now()) / 1000));
                if (diffSec <= 0) {
                    this.countdownText = 'Celebration Live! 🎉';
                    return;
                }
                var d = Math.floor(diffSec / 86400);
                var h = Math.floor((diffSec % 86400) / 3600);
                var m = Math.floor((diffSec % 3600) / 60);
                var s = diffSec % 60;
                this.countdownText = (d > 0 ? d + 'd ' : '') +
                    String(h).padStart(2, '0') + 'h ' +
                    String(m).padStart(2, '0') + 'm ' +
                    String(s).padStart(2, '0') + 's';
            },

            async claimSeasonalGift(giftId) {
                if (this.claimingGiftId || this.claimedIds.includes(giftId)) return;
                this.claimingGiftId = giftId;
                this.feedbackMsg = '';

                try {
                    var csrf = document.querySelector('meta[name=csrf-token]')?.content || '';
                    var res = await fetch('/api/seasonal/gifts/' + giftId + '/claim', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf
                        }
                    });
                    var data = await res.json();
                    if (res.ok && data.success) {
                        this.claimedIds.push(giftId);
                        this.feedbackSuccess = true;
                        this.feedbackMsg = data.message || '🎉 Seasonal gift claimed successfully!';
                        if (data.reward_usd && data.reward_usd > 0) {
                            setTimeout(() => window.location.reload(), 1600);
                        }
                    } else {
                        this.feedbackSuccess = false;
                        this.feedbackMsg = data.message || 'Could not claim this seasonal gift.';
                    }
                } catch (err) {
                    this.feedbackSuccess = false;
                    this.feedbackMsg = 'Network error while claiming gift. Please try again.';
                } finally {
                    this.claimingGiftId = null;
                }
            },

            async dismissAnnouncement(annId) {
                this.dismissedAnnouncements.push(annId);
                try {
                    var csrf = document.querySelector('meta[name=csrf-token]')?.content || '';
                    await fetch('/api/seasonal/announcements/' + annId + '/dismiss', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf
                        }
                    });
                } catch (e) {}
            },

            async openHistoryModal() {
                this.historyOpen = true;
                this.historyLoading = true;
                try {
                    var res = await fetch('/api/seasonal/claims/history', {
                        headers: { 'Accept': 'application/json' }
                    });
                    var data = await res.json();
                    this.historyItems = data.claims || [];
                } catch (e) {
                    this.historyItems = [];
                } finally {
                    this.historyLoading = false;
                }
            }
        };
    }
</script>
@endif

