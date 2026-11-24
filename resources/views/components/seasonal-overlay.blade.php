@php
    $mode = $activeSeasonalMode ?? null;
    $userPref = $seasonalUserPref ?? null;
    $isPreview = $mode && (bool) ($mode->getAttribute('is_preview_mode') ?? false);

    // Determine current page key
    $pageKey = 'other';
    if (request()->is('/') || request()->is('home')) $pageKey = 'home';
    elseif (request()->is('dashboard')) $pageKey = 'dashboard';
    elseif (request()->is('sms-verify*')) $pageKey = 'sms';
    elseif (request()->is('social-boosting*')) $pageKey = 'boosting';
    elseif (request()->is('logs-store*')) $pageKey = 'logs';
    elseif (request()->is('funding*') || request()->is('wallet*')) $pageKey = 'funding';

    $shouldRenderOnPage = $mode && ($isPreview || $mode->shouldDisplayOnPage($pageKey));
    $serverAnimationsEnabled = $shouldRenderOnPage && (bool) $mode->animations_enabled && ($mode->animation_type !== 'none');
    $userDisabledAnim = $userPref ? (bool) $userPref->animations_disabled : false;
@endphp

@if($shouldRenderOnPage)
    <!-- Admin Safe Preview Top Bar -->
    @if($isPreview)
        <div class="fixed top-0 inset-x-0 z-[9999] bg-gradient-to-r from-amber-400 via-yellow-300 to-amber-500 text-slate-950 px-4 py-2 text-xs font-black flex flex-wrap items-center justify-between gap-2 shadow-lg">
            <div class="flex items-center gap-2">
                <span>👁️ ADMIN PREVIEW MODE:</span>
                <span class="px-2 py-0.5 rounded-full bg-slate-950 text-amber-300">{{ $mode->icon }} {{ $mode->name }}</span>
                <span class="hidden sm:inline font-bold text-slate-800">(Ordinary users do not see this until you click Activate in Admin)</span>
            </div>
            <div class="flex items-center gap-2">
                <a href="/admin/seasonal" class="px-3 py-1 rounded-lg bg-slate-950 text-white hover:bg-slate-800 transition">
                    &larr; Back to Seasonal Admin
                </a>
                <a href="{{ url()->current() }}" class="px-3 py-1 rounded-lg bg-white/80 text-slate-900 hover:bg-white transition">
                    Exit Preview
                </a>
            </div>
        </div>
    @endif

    <!-- Decorative Festive Top Garland / Ribbon Strip -->
    @if($mode->decorations_enabled)
        <div class="pointer-events-none fixed top-0 inset-x-0 z-[65] h-1.5"
             style="background: linear-gradient(90deg, {{ $mode->primary_color }}, {{ $mode->accent_color }}, {{ $mode->secondary_color }}, {{ $mode->primary_color }}); background-size: 200% 100%; animation: may22SeasonalGradient 6s linear infinite;">
        </div>
    @endif

    <!-- Non-Blocking Seasonal Particle Canvas & Floating Toggle Pill -->
    @if($serverAnimationsEnabled)
        <div x-data="{
                animOff: {{ $userDisabledAnim ? 'true' : 'false' }} || localStorage.getItem('may22_seasonal_anim_off') === '1',
                toggleAnim() {
                    this.animOff = !this.animOff;
                    try {
                        localStorage.setItem('may22_seasonal_anim_off', this.animOff ? '1' : '0');
                    } catch (e) {}
                    window.dispatchEvent(new CustomEvent('may22-seasonal-anim-toggle', { detail: { disabled: this.animOff } }));
                    @auth
                        fetch('/api/seasonal/preferences/animations', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                            },
                            body: JSON.stringify({ disabled: this.animOff })
                        }).catch(() => {});
                    @endauth
                }
             }">

            <!-- Non-blocking animation layer -->
            <div id="may22-seasonal-particles"
                 x-show="!animOff"
                 style="position: fixed; inset: 0; pointer-events: none; z-index: 55; overflow: hidden;"
                 aria-hidden="true"></div>

            <!-- Discreet Bottom-Left Seasonal Effect Toggle Pill -->
            <button type="button"
                    @click="toggleAnim()"
                    style="position: fixed; left: 12px; bottom: 76px; z-index: 45;"
                    class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-slate-900/80 hover:bg-slate-900 text-white backdrop-blur-md border border-white/15 text-[11px] font-extrabold shadow-md transition cursor-pointer"
                    :title="animOff ? 'Enable Seasonal Effects' : 'Pause Seasonal Effects'">
                <span>{{ $mode->icon ?: '✨' }}</span>
                <span x-text="animOff ? 'Effects Off' : '{{ $mode->badge_text ?: $mode->name }}'"></span>
            </button>
        </div>

        <style>
            @keyframes may22SeasonalGradient {
                0% { background-position: 0% 50%; }
                100% { background-position: 200% 50%; }
            }
            @keyframes may22FallDown {
                0% {
                    transform: translate3d(0, -10vh, 0) rotate(0deg);
                    opacity: 0;
                }
                12% {
                    opacity: var(--p-opacity, 0.85);
                }
                88% {
                    opacity: var(--p-opacity, 0.85);
                }
                100% {
                    transform: translate3d(var(--p-drift, 35px), 106vh, 0) rotate(var(--p-rot, 360deg));
                    opacity: 0;
                }
            }
            @keyframes may22FloatUp {
                0% {
                    transform: translate3d(0, 105vh, 0) scale(0.8) rotate(0deg);
                    opacity: 0;
                }
                15% {
                    opacity: var(--p-opacity, 0.85);
                }
                85% {
                    opacity: var(--p-opacity, 0.85);
                }
                100% {
                    transform: translate3d(var(--p-drift, -30px), -10vh, 0) scale(1.1) rotate(var(--p-rot, 25deg));
                    opacity: 0;
                }
            }
            .may22-seasonal-particle {
                position: absolute;
                top: -40px;
                left: 0;
                user-select: none;
                pointer-events: none;
                will-change: transform, opacity;
            }
            @media (prefers-reduced-motion: reduce) {
                #may22-seasonal-particles {
                    display: none !important;
                }
            }
        </style>

        <script>
            (function () {
                if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                    return;
                }

                var animType = @js($mode->animation_type);
                var intensity = @js($mode->animation_intensity ?? 'medium');
                var container = document.getElementById('may22-seasonal-particles');
                if (!container) return;

                var countMap = { low: 14, medium: 24, high: 38 };
                var totalParticles = countMap[intensity] || 22;
                if (window.innerWidth < 640) {
                    totalParticles = Math.max(10, Math.round(totalParticles * 0.6));
                }

                var palettes = {
                    snow: { symbols: ['❄', '❅', '❆', '•'], dir: 'down', colors: ['#ffffff', '#e0f2fe', '#bae6fd'] },
                    fireworks_confetti: { symbols: ['✨', '🎆', '🎊', '★', '✦'], dir: 'down', colors: ['#facc15', '#38bdf8', '#f43f5e', '#a855f7', '#4ade80'] },
                    hearts: { symbols: ['💖', '💗', '❤️', '💕', '🌹'], dir: 'up', colors: ['#f43f5e', '#fb7185', '#e11d48'] },
                    easter_eggs: { symbols: ['🥚', '🐣', '🌷', '🌸', '✨'], dir: 'up', colors: ['#c084fc', '#fde047', '#5eead4'] },
                    pumpkins: { symbols: ['🎃', '👻', '🦇', '🕸️', '✨'], dir: 'up', colors: ['#f97316', '#fb923c', '#a855f7'] },
                    green_white_confetti: { symbols: ['🇳🇬', '💚', '🤍', '✦', '★'], dir: 'down', colors: ['#008751', '#ffffff', '#10b981'] },
                    gold_sparkles: { symbols: ['✨', '⚡', '★', '🛍️', '💎'], dir: 'down', colors: ['#facc15', '#fde047', '#f59e0b'] },
                    lanterns_stars: { symbols: ['🌙', '✨', '🏮', '⭐', '✦'], dir: 'up', colors: ['#fbbf24', '#fde68a', '#34d399'] }
                };

                var cfg = palettes[animType] || palettes.gold_sparkles;
                var fragment = document.createDocumentFragment();

                for (var i = 0; i < totalParticles; i++) {
                    var el = document.createElement('span');
                    el.className = 'may22-seasonal-particle';
                    var sym = cfg.symbols[i % cfg.symbols.length];
                    el.textContent = sym;

                    var leftPct = (Math.random() * 98).toFixed(2);
                    var sizePx = (13 + Math.random() * 13).toFixed(1);
                    var durationSec = (7 + Math.random() * 9).toFixed(2);
                    var delaySec = (Math.random() * 10).toFixed(2);
                    var driftPx = ((Math.random() - 0.5) * 90).toFixed(0) + 'px';
                    var rotDeg = ((Math.random() > 0.5 ? 1 : -1) * (120 + Math.random() * 240)).toFixed(0) + 'deg';
                    var opacity = (0.45 + Math.random() * 0.45).toFixed(2);

                    el.style.left = leftPct + '%';
                    el.style.fontSize = sizePx + 'px';
                    el.style.color = cfg.colors[i % cfg.colors.length];
                    el.style.setProperty('--p-drift', driftPx);
                    el.style.setProperty('--p-rot', rotDeg);
                    el.style.setProperty('--p-opacity', opacity);
                    el.style.animation = (cfg.dir === 'up' ? 'may22FloatUp ' : 'may22FallDown ') + durationSec + 's linear ' + delaySec + 's infinite';

                    fragment.appendChild(el);
                }

                container.appendChild(fragment);
            })();
        </script>
    @endif
@endif

