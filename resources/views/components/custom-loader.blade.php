@props([
    'logo' => asset('logo/favicon-512.png'),
    'siteName' => $siteName ?? 'May22SMS',
    'initialLoad' => request()->is('/') || request()->is('home'),
    'initialTitle' => null,
    'initialSubtitle' => null,
])

<!-- Global Custom Logo App Loader -->
<div id="may22-app-loader" 
     class="fixed inset-0 z-[99999] flex items-center justify-center transition-all duration-500 ease-out {{ $initialLoad ? 'opacity-100 pointer-events-auto' : 'opacity-0 pointer-events-none' }}" 
     style="{{ $initialLoad ? 'display: flex;' : 'display: none;' }}" 
     aria-hidden="{{ $initialLoad ? 'false' : 'true' }}">
    <!-- Backdrop with Rich Dark Blur -->
    <div class="absolute inset-0 bg-slate-950/85 backdrop-blur-md transition-opacity duration-500" id="may22-loader-backdrop"></div>

    <!-- Central Glowing Loader Card -->
    <div class="relative z-10 flex flex-col items-center justify-center p-8 max-w-sm w-full mx-4 text-center transform transition-all duration-500 {{ $initialLoad ? 'scale-100' : 'scale-95' }}" id="may22-loader-card">
        
        <!-- Multi-Ring Orb with Logo Center -->
        <div class="relative w-32 h-32 flex items-center justify-center mb-6">
            <!-- Ambient Pulsing Aura -->
            <div class="absolute inset-0 rounded-full bg-gradient-to-tr from-[#0d9488]/40 via-teal-400/30 to-amber-400/30 blur-2xl may22-aura"></div>
            
            <!-- Outer Conic Spinning Ring -->
            <div class="absolute inset-0 rounded-full border-2 border-transparent border-t-[#0d9488] border-r-teal-400 may22-spin-slow"></div>
            
            <!-- Counter-Rotating Middle Ring -->
            <div class="absolute inset-2 rounded-full border-2 border-transparent border-b-[#facc15] border-l-amber-300 may22-spin-reverse opacity-80"></div>
            
            <!-- Innermost Dash Orbit -->
            <div class="absolute inset-3.5 rounded-full border border-dashed border-teal-200/40 may22-spin-fast"></div>

            <!-- Central Glassmorphism Logo Plaque -->
            <div class="relative w-20 h-20 rounded-2xl bg-white/95 backdrop-blur-xl p-3 shadow-2xl border border-white/40 flex items-center justify-center may22-logo-container">
                <img src="{{ $logo }}" 
                     alt="{{ $siteName }}" 
                     class="w-full h-full object-contain may22-logo-img"
                     loading="eager">
                
                <!-- Tiny corner live pulse dot -->
                <span class="absolute -top-1 -right-1 flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500 border-2 border-white"></span>
                </span>
            </div>
        </div>

        <!-- Typography & Dynamic Status -->
        <div class="space-y-2">
            <h4 class="text-xl font-black tracking-tight text-white flex items-center justify-center gap-1.5" id="may22-loader-title">
                <span class="bg-gradient-to-r from-white via-teal-100 to-amber-200 bg-clip-text text-transparent">{{ $initialTitle ?? $siteName }}</span>
            </h4>
            <p class="text-xs font-semibold text-teal-200/90 tracking-wide max-w-xs transition-all duration-200" id="may22-loader-subtitle">
                {{ $initialSubtitle ?? ($initialLoad ? 'Preparing your secure experience...' : 'Please wait a moment...') }}
            </p>
        </div>

        <!-- Animated Progress Shimmer Line -->
        <div class="w-48 h-1 bg-white/10 rounded-full overflow-hidden mt-5 relative">
            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-[#0d9488] to-amber-300 may22-shimmer"></div>
        </div>
    </div>
</div>

<style>
    @keyframes may22Spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    @keyframes may22SpinRev {
        from { transform: rotate(360deg); }
        to { transform: rotate(0deg); }
    }
    @keyframes may22PulseLogo {
        0%, 100% {
            transform: scale(1);
            filter: drop-shadow(0 0 12px rgba(13, 148, 136, 0.4));
        }
        50% {
            transform: scale(1.06);
            filter: drop-shadow(0 0 20px rgba(13, 148, 136, 0.75));
        }
    }
    @keyframes may22Aura {
        0%, 100% { opacity: 0.35; transform: scale(0.95); }
        50% { opacity: 0.8; transform: scale(1.15); }
    }
    @keyframes may22Shimmer {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }

    .may22-spin-slow {
        animation: may22Spin 2.4s cubic-bezier(0.4, 0, 0.2, 1) infinite;
    }
    .may22-spin-reverse {
        animation: may22SpinRev 1.8s linear infinite;
    }
    .may22-spin-fast {
        animation: may22Spin 1.2s linear infinite;
    }
    .may22-logo-container {
        animation: may22PulseLogo 2.2s ease-in-out infinite;
    }
    .may22-aura {
        animation: may22Aura 2.2s ease-in-out infinite;
    }
    .may22-shimmer {
        width: 100%;
        animation: may22Shimmer 1.5s ease-in-out infinite;
    }
</style>

<script>
(function() {
    let loaderTimeout = null;

    /**
     * Show the global application loader with custom title and subtitle.
     * @param {string} title 
     * @param {string} subtitle 
     */
    window.showAppLoader = function(title = '{{ $siteName }}', subtitle = 'Please wait a moment...') {
        const loader = document.getElementById('may22-app-loader');
        const card = document.getElementById('may22-loader-card');
        const titleEl = document.getElementById('may22-loader-title');
        const subEl = document.getElementById('may22-loader-subtitle');

        if (!loader) return;

        if (titleEl) {
            titleEl.innerHTML = `<span class="bg-gradient-to-r from-white via-teal-100 to-amber-200 bg-clip-text text-transparent">${title}</span>`;
        }
        if (subEl) {
            subEl.textContent = subtitle;
        }

        if (loaderTimeout) clearTimeout(loaderTimeout);

        loader.style.display = 'flex';
        // Force reflow
        void loader.offsetWidth;

        loader.classList.remove('opacity-0', 'pointer-events-none');
        loader.classList.add('opacity-100', 'pointer-events-auto');
        if (card) {
            card.classList.remove('scale-95');
            card.classList.add('scale-100');
        }
    };

    /**
     * Hide the global application loader smoothly.
     */
    window.hideAppLoader = function() {
        const loader = document.getElementById('may22-app-loader');
        const card = document.getElementById('may22-loader-card');

        if (!loader) return;

        loader.classList.remove('opacity-100', 'pointer-events-auto');
        loader.classList.add('opacity-0', 'pointer-events-none');
        if (card) {
            card.classList.remove('scale-100');
            card.classList.add('scale-95');
        }

        if (loaderTimeout) clearTimeout(loaderTimeout);
        loaderTimeout = setTimeout(() => {
            loader.style.display = 'none';
        }, 520);
    };

    // Auto-dismiss initial page loader when landing on home or preloader enabled
    const isInitialLoad = {{ $initialLoad ? 'true' : 'false' }};
    if (isInitialLoad) {
        const minDisplayDuration = 600; // minimum ms so the sleek branded animation is enjoyed smoothly
        const startTime = Date.now();

        function dismissInitialLoader() {
            const elapsed = Date.now() - startTime;
            const remaining = Math.max(0, minDisplayDuration - elapsed);
            setTimeout(() => {
                window.hideAppLoader();
            }, remaining);
        }

        if (document.readyState === 'complete') {
            dismissInitialLoader();
        } else {
            window.addEventListener('load', dismissInitialLoader);
            // Failsafe timeout: ensure the page reveals within 2.8s even on slow network
            setTimeout(dismissInitialLoader, 2800);
        }
    }

    // Auto-listen to forms that opt into loading indicator
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('form[data-loader]').forEach(function(form) {
            form.addEventListener('submit', function(e) {
                if (form.checkValidity && !form.checkValidity()) return;
                const title = form.getAttribute('data-loader-title') || 'Processing Request...';
                const subtitle = form.getAttribute('data-loader-subtitle') || 'Connecting securely to server';
                window.showAppLoader(title, subtitle);
            });
        });
    });

    // Handle bfcache (browser back/forward button restore)
    window.addEventListener('pageshow', function(event) {
        window.hideAppLoader();
    });

    // Auto-hook window navigation for smooth transitions on key navigation links
    document.addEventListener('click', function(e) {
        const link = e.target.closest('a[href]:not([target="_blank"]):not([href^="#"]):not([href^="javascript"]):not([download])');
        if (!link) return;

        // Skip modified clicks (Ctrl, Cmd, Shift, Alt)
        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

        const href = link.getAttribute('href');
        if (!href) return;

        // Skip same-page anchors
        if (href.includes('#')) {
            const [path] = href.split('#');
            if (path === window.location.pathname || path === '') return;
        }

        // Only trigger on internal site routes
        if (href.startsWith('/') || href.includes(window.location.host)) {
            // If link explicitly opts in or belongs to primary navigation
            if (link.hasAttribute('data-show-loader') || link.closest('nav') || link.classList.contains('load-nav-link')) {
                const label = link.getAttribute('data-loader-title') || 'Loading Page...';
                const destName = link.textContent.trim().replace(/\s+/g, ' ').split('\n')[0].slice(0, 30);
                const sub = link.getAttribute('data-loader-subtitle') || (destName ? `Opening ${destName}` : 'Taking you to destination');
                window.showAppLoader(label, sub);

                // Auto-hide fallback after 6s in case navigation is cancelled or slow
                setTimeout(() => {
                    window.hideAppLoader();
                }, 6000);
            }
        }
    });
})();
</script>
