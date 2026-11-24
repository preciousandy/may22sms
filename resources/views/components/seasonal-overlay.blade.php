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

    // Do not render user seasonal decorations inside the admin control panel itself
    $isAdminArea = request()->is('admin*');
    $shouldRenderOnPage = ! $isAdminArea && $mode && ($isPreview || $mode->shouldDisplayOnPage($pageKey));

    $effects = $mode ? (array) ($mode->visual_effects ?? []) : [];
    $isSnowTheme = $mode && ($mode->theme_type === 'christmas' || $mode->animation_type === 'snow');

    $serverAnimationsEnabled = $shouldRenderOnPage && (bool) $mode->animations_enabled && ($mode->animation_type !== 'none');
    $decorationsEnabled = $shouldRenderOnPage && (bool) $mode->decorations_enabled;

    $snowAccumulationEnabled = $decorationsEnabled && $isSnowTheme && (($effects['snow_accumulation'] ?? true) !== false);
    $icyBordersEnabled = $decorationsEnabled && $isSnowTheme && (($effects['icy_borders'] ?? true) !== false);
    $christmasOrnamentsEnabled = $decorationsEnabled && $isSnowTheme && (($effects['christmas_ornaments'] ?? true) !== false);

    $snowflakeSize = (string) ($effects['snowflake_size'] ?? 'medium'); // small, medium, large
    $snowflakeSpeed = (string) ($effects['snowflake_speed'] ?? 'normal'); // slow, normal, fast
    $snowCapThickness = (string) ($effects['snow_cap_thickness'] ?? 'medium'); // thin, medium, thick
    $snowTargets = (array) ($effects['snow_targets'] ?? ['cards', 'wallet', 'services', 'buttons', 'inputs', 'navbar', 'banners']);

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
    @if($decorationsEnabled)
        <div class="pointer-events-none fixed top-0 inset-x-0 z-[65] h-1.5"
             style="background: linear-gradient(90deg, {{ $mode->primary_color }}, {{ $mode->accent_color }}, {{ $mode->secondary_color }}, {{ $mode->primary_color }}); background-size: 200% 100%; animation: may22SeasonalGradient 6s linear infinite;">
        </div>
    @endif

    <!-- =========================================================================
         PREMIUM SNOW ACCUMULATION CAPS, ICY BORDERS & CHRISTMAS ORNAMENTS CSS
         ========================================================================= -->
    <style>
        @keyframes may22SeasonalGradient {
            0% { background-position: 0% 50%; }
            100% { background-position: 200% 50%; }
        }

        @keyframes may22Snowfall3D {
            0% {
                transform: translate3d(0, -8vh, 0) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: var(--p-opacity, 0.85);
            }
            50% {
                transform: translate3d(var(--p-sway-mid, 22px), 48vh, 0) rotate(var(--p-rot-mid, 180deg));
            }
            90% {
                opacity: var(--p-opacity, 0.85);
            }
            100% {
                transform: translate3d(var(--p-drift, -18px), 105vh, 0) rotate(var(--p-rot, 360deg));
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

        @keyframes may22OrnamentSwing {
            0%, 100% { transform: rotate(-4deg); }
            50% { transform: rotate(4deg); }
        }

        @keyframes may22IceSparkle {
            0%, 100% { opacity: 0.25; transform: scale(0.85); }
            50% { opacity: 0.95; transform: scale(1.15); }
        }

        .may22-seasonal-particle {
            position: absolute;
            top: -40px;
            left: 0;
            user-select: none;
            pointer-events: none;
            will-change: transform, opacity;
        }

        /* Snow Cap Overlay Element (Injected onto cards, buttons, navbars, inputs, banners) */
        .may22-snow-cap {
            position: absolute;
            left: 2px;
            right: 2px;
            top: -7px;
            height: var(--may22-cap-h, 18px);
            pointer-events: none !important;
            user-select: none !important;
            z-index: 25;
            background-repeat: repeat-x;
            background-size: var(--may22-cap-w, 220px) 100%;
            filter: drop-shadow(0 3px 4px rgba(15, 23, 42, 0.22)) drop-shadow(0 1px 1px rgba(186, 230, 253, 0.45));
            border-top-left-radius: inherit;
            border-top-right-radius: inherit;
            transition: opacity 0.25s ease;
        }

        /* 5 Distinct Organic Irregular Snowbank SVG Profiles (White + Icy Blue Highlights + Soft Drifts) */
        .may22-snow-variant-1 {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 240 28' preserveAspectRatio='none'%3E%3Cdefs%3E%3ClinearGradient id='g1' x1='0' y1='0' x2='0' y2='1'%3E%3Cstop offset='0%25' stop-color='%23ffffff'/%3E%3Cstop offset='68%25' stop-color='%23f0f9ff'/%3E%3Cstop offset='100%25' stop-color='%23bae6fd'/%3E%3C/linearGradient%3E%3C/defs%3E%3Cpath fill='url(%23g1)' d='M0,10 C18,2 34,2 52,7 C70,12 88,1 112,5 C135,9 152,2 176,6 C198,10 218,2 240,8 L240,17 C224,25 207,14 190,20 C172,26 156,14 136,19 C116,24 98,27 78,18 C58,10 42,25 22,19 C10,15 4,19 0,16 Z'/%3E%3Ccircle cx='36' cy='11' r='2.2' fill='%23ffffff' opacity='0.9'/%3E%3Ccircle cx='145' cy='10' r='1.8' fill='%23e0f2fe' opacity='0.85'/%3E%3C/svg%3E");
        }

        .may22-snow-variant-2 {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 260 28' preserveAspectRatio='none'%3E%3Cdefs%3E%3ClinearGradient id='g2' x1='0' y1='0' x2='0' y2='1'%3E%3Cstop offset='0%25' stop-color='%23ffffff'/%3E%3Cstop offset='65%25' stop-color='%23f8fafc'/%3E%3Cstop offset='100%25' stop-color='%23cbd5e1'/%3E%3C/linearGradient%3E%3C/defs%3E%3Cpath fill='url(%23g2)' d='M0,8 C25,1 48,11 74,5 C100,-1 124,9 152,4 C180,-1 210,10 260,6 L260,16 C238,21 220,27 198,18 C176,10 158,24 134,21 C110,18 92,12 68,22 C44,29 22,14 0,18 Z'/%3E%3Cpath fill='%23bae6fd' opacity='0.55' d='M0,14 C30,19 64,12 96,18 C130,24 166,12 204,19 C228,23 245,15 260,16 L260,18 C230,24 198,14 164,21 C128,27 92,15 58,22 C28,28 12,16 0,18 Z'/%3E%3C/svg%3E");
        }

        .may22-snow-variant-3 {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 200 28' preserveAspectRatio='none'%3E%3Cdefs%3E%3ClinearGradient id='g3' x1='0' y1='0' x2='0' y2='1'%3E%3Cstop offset='0%25' stop-color='%23ffffff'/%3E%3Cstop offset='72%25' stop-color='%23e0f2fe'/%3E%3Cstop offset='100%25' stop-color='%237dd3fc'/%3E%3C/linearGradient%3E%3C/defs%3E%3Cpath fill='url(%23g3)' d='M0,9 C16,2 38,3 58,8 C78,13 102,1 128,6 C154,11 176,2 200,7 L200,15 C186,23 172,14 156,19 C140,25 128,28 112,18 C96,10 78,24 58,20 C38,16 20,24 0,16 Z'/%3E%3C/svg%3E");
        }

        .may22-snow-variant-4 {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 220 30' preserveAspectRatio='none'%3E%3Cdefs%3E%3ClinearGradient id='g4' x1='0' y1='0' x2='0' y2='1'%3E%3Cstop offset='0%25' stop-color='%23ffffff'/%3E%3Cstop offset='60%25' stop-color='%23f0f9ff'/%3E%3Cstop offset='100%25' stop-color='%2393c5fd'/%3E%3C/linearGradient%3E%3C/defs%3E%3Cpath fill='url(%23g4)' d='M0,7 C22,0 44,9 68,4 C92,-1 118,8 146,3 C174,-1 196,8 220,5 L220,16 C206,20 198,28 188,20 C178,12 160,22 142,18 C124,14 108,27 92,19 C76,11 54,24 34,18 C18,13 8,21 0,16 Z'/%3E%3C/svg%3E");
        }

        /* Corner Fluffy Snow Cluster Accent (Top-Left & Top-Right Mounds) */
        .may22-snow-corner-cluster {
            position: absolute;
            top: -9px;
            right: 10px;
            width: 44px;
            height: 18px;
            pointer-events: none !important;
            z-index: 26;
            background-repeat: no-repeat;
            background-size: contain;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 26'%3E%3Cdefs%3E%3ClinearGradient id='gc' x1='0' y1='0' x2='0' y2='1'%3E%3Cstop offset='0%25' stop-color='%23ffffff'/%3E%3Cstop offset='75%25' stop-color='%23e0f2fe'/%3E%3Cstop offset='100%25' stop-color='%237dd3fc'/%3E%3C/linearGradient%3E%3C/defs%3E%3Cpath fill='url(%23gc)' d='M4,18 C4,9 13,3 22,6 C27,1 39,1 45,7 C53,5 61,11 60,18 C59,22 48,23 33,22 C18,21 5,23 4,18 Z'/%3E%3C/svg%3E");
            filter: drop-shadow(0 2px 3px rgba(15, 23, 42, 0.25));
        }

        /* Button & Input Compact Snow Cap */
        .may22-snow-cap-compact {
            top: -5px !important;
            left: 4px !important;
            right: 4px !important;
            height: 11px !important;
            background-size: 130px 100% !important;
        }

        /* Navbar Full-Width Snow Ledge */
        .may22-snow-cap-navbar {
            left: 0 !important;
            right: 0 !important;
            top: -6px !important;
            height: 14px !important;
            background-size: 260px 100% !important;
            border-radius: 0 !important;
        }

        /* Frosty / Icy Card Borders & Soft Winter Glow */
        .may22-icy-surface {
            box-shadow:
                0 0 0 1px rgba(186, 230, 253, 0.32),
                0 10px 28px -6px rgba(56, 189, 248, 0.14),
                inset 0 1px 0 0 rgba(255, 255, 255, 0.55) !important;
        }

        /* Hanging Mini Christmas Ornament / Sparkling Star Badge */
        .may22-xmas-ornament {
            position: absolute;
            top: 4px;
            right: 16px;
            pointer-events: none !important;
            user-select: none !important;
            z-index: 27;
            font-size: 15px;
            line-height: 1;
            transform-origin: top center;
            animation: may22OrnamentSwing 3.8s ease-in-out infinite;
            filter: drop-shadow(0 3px 5px rgba(0, 0, 0, 0.28));
        }

        .may22-ice-sparkle-dot {
            position: absolute;
            top: -4px;
            left: 22px;
            pointer-events: none !important;
            z-index: 27;
            font-size: 11px;
            color: #e0f2fe;
            animation: may22IceSparkle 2.8s ease-in-out infinite;
            text-shadow: 0 0 8px rgba(56, 189, 248, 0.9);
        }

        @media (max-width: 640px) {
            .may22-snow-cap {
                top: -5px;
                height: calc(var(--may22-cap-h, 16px) * 0.82);
            }
            .may22-snow-corner-cluster {
                width: 34px;
                height: 14px;
                top: -7px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            #may22-seasonal-particles,
            .may22-xmas-ornament,
            .may22-ice-sparkle-dot {
                animation: none !important;
            }
            #may22-seasonal-particles {
                display: none !important;
            }
        }
    </style>

    <!-- Non-Blocking Seasonal Particle Canvas & Floating Toggle Pill -->
    <div x-data="{
            animOff: {{ $userDisabledAnim ? 'true' : 'false' }} || localStorage.getItem('may22_seasonal_anim_off') === '1',
            toggleAnim() {
                this.animOff = !this.animOff;
                try {
                    localStorage.setItem('may22_seasonal_anim_off', this.animOff ? '1' : '0');
                } catch (e) {}
                document.documentElement.classList.toggle('may22-seasonal-paused', this.animOff);
                var pLayer = document.getElementById('may22-seasonal-particles');
                if (pLayer) pLayer.style.display = this.animOff ? 'none' : 'block';
                document.querySelectorAll('.may22-snow-cap, .may22-snow-corner-cluster, .may22-xmas-ornament, .may22-ice-sparkle-dot').forEach(function (el) {
                    el.style.display = this.animOff ? 'none' : '';
                }.bind(this));
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

        @if($serverAnimationsEnabled)
            <!-- Non-blocking 3D snowfall / seasonal particle layer -->
            <div id="may22-seasonal-particles"
                 x-show="!animOff"
                 style="position: fixed; inset: 0; pointer-events: none; z-index: 55; overflow: hidden;"
                 aria-hidden="true"></div>
        @endif

        <!-- Discreet Bottom-Left Seasonal Effect Toggle Pill -->
        <button type="button"
                @click="toggleAnim()"
                style="position: fixed; left: 12px; bottom: 76px; z-index: 45;"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-slate-900/85 hover:bg-slate-900 text-white backdrop-blur-md border border-white/20 text-[11px] font-extrabold shadow-lg transition cursor-pointer"
                :title="animOff ? 'Enable Seasonal Effects' : 'Pause Seasonal Effects'">
            <span>{{ $mode->icon ?: '🎄' }}</span>
            <span x-text="animOff ? 'Snow Off' : '{{ $mode->badge_text ?: $mode->name }}'"></span>
        </button>
    </div>

    <script>
        (function () {
            var isOff = {{ $userDisabledAnim ? 'true' : 'false' }} || localStorage.getItem('may22_seasonal_anim_off') === '1';
            var animEnabled = @js($serverAnimationsEnabled);
            var animType = @js($mode->animation_type);
            var intensity = @js($mode->animation_intensity ?? 'medium');
            var flakeSizeSetting = @js($snowflakeSize);
            var flakeSpeedSetting = @js($snowflakeSpeed);

            var snowAccumulationEnabled = @js($snowAccumulationEnabled);
            var icyBordersEnabled = @js($icyBordersEnabled);
            var christmasOrnamentsEnabled = @js($christmasOrnamentsEnabled);
            var snowCapThickness = @js($snowCapThickness);
            var snowTargets = @js($snowTargets);

            // ====================================================================
            // 1. REALISTIC 3D MULTI-LAYER FALLING SNOW & SEASONAL PARTICLES
            // ====================================================================
            function initFallingParticles() {
                if (!animEnabled) return;
                if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

                var container = document.getElementById('may22-seasonal-particles');
                if (!container) return;

                var countMap = { low: 18, medium: 32, high: 48 };
                var totalParticles = countMap[intensity] || 30;
                if (window.innerWidth < 640) {
                    totalParticles = Math.max(14, Math.round(totalParticles * 0.62));
                }

                var sizeMult = flakeSizeSetting === 'small' ? 0.75 : (flakeSizeSetting === 'large' ? 1.32 : 1.0);
                var speedMult = flakeSpeedSetting === 'slow' ? 1.38 : (flakeSpeedSetting === 'fast' ? 0.72 : 1.0);

                var palettes = {
                    snow: { symbols: ['❄', '❅', '❆', '•', '✦'], dir: 'down', colors: ['#ffffff', '#f0f9ff', '#e0f2fe', '#bae6fd'] },
                    fireworks_confetti: { symbols: ['✨', '🎆', '🎊', '★', '✦'], dir: 'down', colors: ['#facc15', '#38bdf8', '#f43f5e', '#a855f7', '#4ade80'] },
                    hearts: { symbols: ['💖', '💗', '❤️', '💕', '🌹'], dir: 'up', colors: ['#f43f5e', '#fb7185', '#e11d48'] },
                    easter_eggs: { symbols: ['🥚', '🐣', '🌷', '🌸', '✨'], dir: 'up', colors: ['#c084fc', '#fde047', '#5eead4'] },
                    pumpkins: { symbols: ['🎃', '👻', '🦇', '🕸️', '✨'], dir: 'up', colors: ['#f97316', '#fb923c', '#a855f7'] },
                    green_white_confetti: { symbols: ['🇳🇬', '💚', '🤍', '✦', '★'], dir: 'down', colors: ['#008751', '#ffffff', '#10b981'] },
                    gold_sparkles: { symbols: ['✨', '⚡', '★', '🛍️', '💎'], dir: 'down', colors: ['#facc15', '#fde047', '#f59e0b'] },
                    lanterns_stars: { symbols: ['🌙', '✨', '🏮', '⭐', '✦'], dir: 'up', colors: ['#fbbf24', '#fde68a', '#34d399'] }
                };

                var cfg = palettes[animType] || palettes.snow;
                var fragment = document.createDocumentFragment();

                for (var i = 0; i < totalParticles; i++) {
                    var el = document.createElement('span');
                    el.className = 'may22-seasonal-particle';

                    // 3D depth layer: 0 = distant (small, slow, soft), 1 = mid, 2 = foreground (crisp, larger)
                    var depthLayer = i % 3;
                    var sym = (animType === 'snow' && depthLayer === 0) ? '•' : cfg.symbols[i % cfg.symbols.length];
                    el.textContent = sym;

                    var baseSize = depthLayer === 0 ? (9 + Math.random() * 5) : (depthLayer === 1 ? (13 + Math.random() * 6) : (17 + Math.random() * 8));
                    var sizePx = (baseSize * sizeMult).toFixed(1);

                    var baseDur = depthLayer === 0 ? (12 + Math.random() * 7) : (depthLayer === 1 ? (8.5 + Math.random() * 5) : (6.5 + Math.random() * 4));
                    var durationSec = (baseDur * speedMult).toFixed(2);
                    var delaySec = (Math.random() * 11).toFixed(2);

                    var leftPct = (Math.random() * 98).toFixed(2);
                    var swayMidPx = ((Math.random() - 0.5) * 65).toFixed(0) + 'px';
                    var driftPx = ((Math.random() - 0.5) * 95).toFixed(0) + 'px';
                    var rotMidDeg = ((Math.random() > 0.5 ? 1 : -1) * (90 + Math.random() * 110)).toFixed(0) + 'deg';
                    var rotDeg = ((Math.random() > 0.5 ? 1 : -1) * (200 + Math.random() * 220)).toFixed(0) + 'deg';
                    var opacity = depthLayer === 0 ? (0.35 + Math.random() * 0.25).toFixed(2) : (0.65 + Math.random() * 0.30).toFixed(2);

                    el.style.left = leftPct + '%';
                    el.style.fontSize = sizePx + 'px';
                    el.style.color = cfg.colors[i % cfg.colors.length];
                    if (depthLayer === 2 && animType === 'snow') {
                        el.style.textShadow = '0 0 6px rgba(186, 230, 253, 0.75)';
                    } else if (depthLayer === 0 && animType === 'snow') {
                        el.style.filter = 'blur(0.6px)';
                    }
                    el.style.setProperty('--p-sway-mid', swayMidPx);
                    el.style.setProperty('--p-drift', driftPx);
                    el.style.setProperty('--p-rot-mid', rotMidDeg);
                    el.style.setProperty('--p-rot', rotDeg);
                    el.style.setProperty('--p-opacity', opacity);
                    el.style.animation = (cfg.dir === 'up' ? 'may22FloatUp ' : 'may22Snowfall3D ') + durationSec + 's ease-in-out ' + delaySec + 's infinite';

                    fragment.appendChild(el);
                }

                container.appendChild(fragment);
                if (isOff) {
                    container.style.display = 'none';
                }
            }

            // ====================================================================
            // 2. SNOW ACCUMULATION CAPS, ICY BORDERS & CHRISTMAS ORNAMENTS ENGINE
            // ====================================================================
            function attachSnowAccumulationCaps() {
                if (!snowAccumulationEnabled && !icyBordersEnabled && !christmasOrnamentsEnabled) return;

                var heightMap = { thin: '13px', medium: '18px', thick: '24px' };
                var capHeight = heightMap[snowCapThickness] || '18px';
                var variants = ['may22-snow-variant-1', 'may22-snow-variant-2', 'may22-snow-variant-3', 'may22-snow-variant-4'];
                var ornaments = ['🎄', '🔔', '⭐', '❄️', '🎁', '🦌'];

                var selectorGroups = [];

                // 1. Dashboard Cards & Widgets
                if (snowTargets.indexOf('cards') !== -1) {
                    selectorGroups.push({
                        sel: 'main .rounded-3xl, main .rounded-2xl, .auth-card, .service-card, .feature-card, .step-card, .testimonial-card, .faq-item, .hero-showcase-card',
                        type: 'card'
                    });
                }

                // 2. Wallet Balance Container (Sidebar & Dashboard Hero)
                if (snowTargets.indexOf('wallet') !== -1) {
                    selectorGroups.push({
                        sel: 'aside .bg-gradient-to-br, main .bg-gradient-to-br',
                        type: 'wallet'
                    });
                }

                // 3. Quick Services & Popular App Cards
                if (snowTargets.indexOf('services') !== -1) {
                    selectorGroups.push({
                        sel: '.app-card, #popularAppsGrid > button',
                        type: 'compact'
                    });
                }

                // 4. Primary Action Buttons
                if (snowTargets.indexOf('buttons') !== -1) {
                    selectorGroups.push({
                        sel: '#buyBtn, .btn-auth-submit, .btn-cta',
                        type: 'compact'
                    });
                }

                // 5. Input & Search Containers
                if (snowTargets.indexOf('inputs') !== -1) {
                    selectorGroups.push({
                        sel: '#priceTierSection, .input-field-modern, #serviceSearch',
                        type: 'compact'
                    });
                }

                // 6. Top & Mobile Bottom Navigation Bars
                if (snowTargets.indexOf('navbar') !== -1) {
                    selectorGroups.push({
                        sel: 'header.sticky, nav.fixed.bottom-0, .edus-header',
                        type: 'navbar'
                    });
                }

                // 7. Promotional & Celebration Banners
                if (snowTargets.indexOf('banners') !== -1) {
                    selectorGroups.push({
                        sel: '#seasonal-celebration-hub > div',
                        type: 'card'
                    });
                }

                var variantIdx = 0;

                selectorGroups.forEach(function (group) {
                    var nodes = document.querySelectorAll(group.sel);
                    nodes.forEach(function (node) {
                        if (!node || node.dataset.may22SnowAttached === '1') return;
                        // Skip hidden elements, modals, or tiny badges (< 80px wide or < 34px tall)
                        var rect = node.getBoundingClientRect();
                        if (rect.width > 0 && (rect.width < 85 || rect.height < 34)) return;
                        if (node.closest('#may22-app-loader') || node.closest('[x-cloak]')) return;

                        node.dataset.may22SnowAttached = '1';

                        var computedPos = window.getComputedStyle(node).position;
                        if (computedPos === 'static') {
                            node.style.position = 'relative';
                        }

                        // Ensure overflow-hidden on cards does not clip the top snow cap sitting at top: -7px
                        var computedOverflow = window.getComputedStyle(node).overflow;
                        if (computedOverflow === 'hidden' && group.type !== 'navbar') {
                            node.style.overflow = 'visible';
                        }

                        if (icyBordersEnabled && (group.type === 'card' || group.type === 'wallet')) {
                            node.classList.add('may22-icy-surface');
                        }

                        if (snowAccumulationEnabled) {
                            // Do not put a div inside <input> or <select> void elements
                            var tagName = node.tagName.toLowerCase();
                            var targetContainer = (tagName === 'input' || tagName === 'select' || tagName === 'img')
                                ? node.parentElement
                                : node;

                            if (targetContainer && !targetContainer.querySelector(':scope > .may22-snow-cap')) {
                                if (window.getComputedStyle(targetContainer).position === 'static') {
                                    targetContainer.style.position = 'relative';
                                }

                                var cap = document.createElement('div');
                                var vClass = variants[variantIdx % variants.length];
                                variantIdx++;

                                cap.className = 'may22-snow-cap ' + vClass;
                                if (group.type === 'compact') {
                                    cap.classList.add('may22-snow-cap-compact');
                                } else if (group.type === 'navbar') {
                                    cap.classList.add('may22-snow-cap-navbar');
                                } else {
                                    cap.style.setProperty('--may22-cap-h', capHeight);
                                    // Slightly vary background repeat width so adjacent cards never look identical
                                    var bgW = 180 + ((variantIdx * 37) % 110);
                                    cap.style.setProperty('--may22-cap-w', bgW + 'px');
                                }

                                if (isOff) cap.style.display = 'none';
                                targetContainer.appendChild(cap);

                                // Add a fluffy corner snow mound + ice sparkle on larger cards
                                if ((group.type === 'card' || group.type === 'wallet') && rect.width > 240 && (variantIdx % 2 === 0)) {
                                    var cluster = document.createElement('div');
                                    cluster.className = 'may22-snow-corner-cluster';
                                    if (isOff) cluster.style.display = 'none';
                                    targetContainer.appendChild(cluster);

                                    var sparkle = document.createElement('span');
                                    sparkle.className = 'may22-ice-sparkle-dot';
                                    sparkle.textContent = '✦';
                                    if (isOff) sparkle.style.display = 'none';
                                    targetContainer.appendChild(sparkle);
                                }
                            }
                        }

                        // Tasteful Christmas Ornament on primary cards/banners
                        if (christmasOrnamentsEnabled && (group.type === 'card' || group.type === 'wallet') && rect.width > 280 && (variantIdx % 3 === 0)) {
                            if (!node.querySelector(':scope > .may22-xmas-ornament')) {
                                var orn = document.createElement('span');
                                orn.className = 'may22-xmas-ornament';
                                orn.textContent = ornaments[variantIdx % ornaments.length];
                                if (isOff) orn.style.display = 'none';
                                node.appendChild(orn);
                            }
                        }
                    });
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function () {
                    initFallingParticles();
                    attachSnowAccumulationCaps();
                });
            } else {
                initFallingParticles();
                attachSnowAccumulationCaps();
            }
        })();
    </script>
@endif
