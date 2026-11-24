<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicons -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('logo/favicon-32.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('logo/favicon-192.png') }}">
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('logo/favicon-512.png') }}">
    <link rel="apple-touch-icon" type="image/png" sizes="64x64" href="{{ asset('logo/favicon-64.png') }}">
    <link rel="apple-touch-icon" type="image/png" sizes="192x192" href="{{ asset('logo/favicon-192.png') }}">
    <link rel="apple-touch-icon" type="image/png" sizes="512x512" href="{{ asset('logo/favicon-512.png') }}">

    @if(!empty($seoTitle))
        <title>{{ $seoTitle }}</title>
    @else
        <title>{{ $siteName ?? 'May22SMS' }}</title>
    @endif

    @if(!empty($seoDescription))
        <meta name="description" content="{{ $seoDescription }}">
    @endif

    @if(!empty($seoKeywords))
        <meta name="keywords" content="{{ $seoKeywords }}">
    @endif

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $siteName ?? 'May22SMS' }}">
    <meta property="og:url" content="{{ url()->current() }}">
    @if(!empty($seoOgTitle))
        <meta property="og:title" content="{{ $seoOgTitle }}">
    @elseif(!empty($seoTitle))
        <meta property="og:title" content="{{ $seoTitle }}">
    @else
        <meta property="og:title" content="{{ $siteName ?? 'May22SMS' }}">
    @endif
    @if(!empty($seoOgDescription))
        <meta property="og:description" content="{{ $seoOgDescription }}">
    @elseif(!empty($seoDescription))
        <meta property="og:description" content="{{ $seoDescription }}">
    @endif
    @if(!empty($seoOgImage))
        <meta property="og:image" content="{{ $seoOgImage }}">
    @endif

    <!-- Twitter -->
    <meta name="twitter:card" content="{{ $seoTwitterCard ?? 'summary_large_image' }}">
    @if(!empty($seoOgTitle))
        <meta name="twitter:title" content="{{ $seoOgTitle }}">
    @elseif(!empty($seoTitle))
        <meta name="twitter:title" content="{{ $seoTitle }}">
    @endif
    @if(!empty($seoOgDescription))
        <meta name="twitter:description" content="{{ $seoOgDescription }}">
    @elseif(!empty($seoDescription))
        <meta name="twitter:description" content="{{ $seoDescription }}">
    @endif
    @if(!empty($seoOgImage))
        <meta name="twitter:image" content="{{ $seoOgImage }}">
    @endif

    <style>
        [x-cloak] { display: none !important; }

        /* ==============================================================
           GLOBAL DARK MODE SYSTEM (Covers Home, Auth, Dashboard & Admin)
           ============================================================== */
        html.dark {
            color-scheme: dark;
            background-color: #0b1120;
            color: #f1f5f9;
        }
        html.dark body {
            background-color: #0b1120 !important;
            color: #f1f5f9 !important;
        }

        /* 1. Landing Page (.edus-theme) & Auth Pages (.auth-wrapper) CSS Variables */
        html.dark .edus-theme {
            --text-main: #f1f5f9 !important;
            --text-muted: #94a3b8 !important;
            --bg-body: #0b1120 !important;
            --bg-alt: #0f172a !important;
            --white: #1e293b !important;
            --glass: rgba(15, 23, 42, 0.92) !important;
            background-color: #0b1120 !important;
            color: #f1f5f9 !important;
        }
        html.dark .edus-header.scrolled {
            background: rgba(15, 23, 42, 0.96) !important;
            border-bottom-color: rgba(45, 212, 191, 0.18) !important;
        }
        html.dark .edus-logo-icon,
        html.dark .hero-showcase-card,
        html.dark .step-card,
        html.dark .testimonial-card,
        html.dark .faq-item,
        html.dark .edus-footer,
        html.dark .mobile-drawer,
        html.dark .about-card-visual {
            background: #1e293b !important;
            border-color: #334155 !important;
            color: #f1f5f9 !important;
        }
        html.dark .auth-wrapper {
            --text-main: #f1f5f9 !important;
            --text-muted: #94a3b8 !important;
            --bg-body: #0b1120 !important;
            background-color: #0b1120 !important;
            color: #f1f5f9 !important;
        }
        html.dark .auth-card {
            background: #1e293b !important;
            border-color: #334155 !important;
            color: #f1f5f9 !important;
        }
        html.dark .auth-tabs {
            background: #0f172a !important;
            border-color: #334155 !important;
        }
        html.dark .auth-tab.active {
            background: #1e293b !important;
            color: #2dd4bf !important;
        }
        html.dark .input-group-modern label {
            color: #cbd5e1 !important;
        }
        html.dark .input-field-modern,
        html.dark .otp-digit-box {
            background: #0f172a !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }
        html.dark .input-field-modern:focus,
        html.dark .otp-digit-box:focus {
            background: #0b1120 !important;
            border-color: #14b8a6 !important;
        }

        /* 2. Tailwind Surface & Background Remapping in Dark Mode */
        html.dark .bg-\[\#f8fafc\],
        html.dark .bg-gray-50,
        html.dark .bg-slate-50,
        html.dark .bg-gray-100 {
            background-color: #0b1120 !important;
        }
        html.dark .bg-gray-50\/60,
        html.dark .bg-gray-50\/70,
        html.dark .bg-gray-50\/80,
        html.dark .bg-slate-50\/70,
        html.dark .bg-slate-50\/90,
        html.dark .bg-slate-100,
        html.dark .bg-slate-100\/80,
        html.dark .bg-gray-100\/80 {
            background-color: #0f172a !important;
        }
        html.dark .bg-white {
            background-color: #1e293b !important;
        }
        html.dark .bg-white\/95,
        html.dark .bg-white\/90,
        html.dark .bg-white\/80 {
            background-color: rgba(30, 41, 59, 0.95) !important;
        }
        html.dark .from-gray-50\/80 {
            --tw-gradient-from: #0f172a var(--tw-gradient-from-position) !important;
        }
        html.dark .via-white {
            --tw-gradient-via: #1e293b var(--tw-gradient-via-position) !important;
        }
        html.dark .to-teal-50\/30 {
            --tw-gradient-to: rgba(13, 148, 136, 0.12) var(--tw-gradient-to-position) !important;
        }

        /* Soft pastel alert/badge backgrounds in dark mode */
        html.dark .bg-teal-50,
        html.dark .bg-teal-50\/30,
        html.dark .bg-teal-50\/40,
        html.dark .bg-teal-50\/60,
        html.dark .bg-teal-50\/80 {
            background-color: rgba(13, 148, 136, 0.16) !important;
        }
        html.dark .bg-emerald-50,
        html.dark .bg-emerald-50\/80 {
            background-color: rgba(16, 185, 129, 0.15) !important;
        }
        html.dark .bg-amber-50,
        html.dark .bg-amber-50\/90 {
            background-color: rgba(245, 158, 11, 0.15) !important;
        }
        html.dark .bg-rose-50,
        html.dark .bg-red-50 {
            background-color: rgba(244, 63, 94, 0.15) !important;
        }
        html.dark .from-teal-50,
        html.dark .from-amber-50 {
            --tw-gradient-from: rgba(15, 23, 42, 0.9) var(--tw-gradient-from-position) !important;
        }
        html.dark .to-orange-50,
        html.dark .to-amber-50\/50 {
            --tw-gradient-to: rgba(30, 41, 59, 0.9) var(--tw-gradient-to-position) !important;
        }

        /* 3. Typography & Text Colors in Dark Mode */
        html.dark .text-gray-900,
        html.dark .text-slate-900,
        html.dark .text-gray-800,
        html.dark .text-slate-800 {
            color: #f1f5f9 !important;
        }
        html.dark .text-gray-700,
        html.dark .text-slate-700,
        html.dark .text-gray-600,
        html.dark .text-slate-600 {
            color: #cbd5e1 !important;
        }
        html.dark .text-gray-500,
        html.dark .text-slate-500,
        html.dark .text-gray-400,
        html.dark .text-slate-400 {
            color: #94a3b8 !important;
        }
        html.dark .text-amber-900,
        html.dark .text-amber-800 {
            color: #fcd34d !important;
        }
        html.dark .text-emerald-800,
        html.dark .text-emerald-700,
        html.dark .text-green-700 {
            color: #6ee7b7 !important;
        }
        html.dark .text-teal-900,
        html.dark .text-teal-800,
        html.dark .text-teal-700 {
            color: #5eead4 !important;
        }
        html.dark .text-rose-800,
        html.dark .text-rose-700,
        html.dark .text-red-700 {
            color: #fda4af !important;
        }

        /* 4. Borders & Dividers in Dark Mode */
        html.dark .border-gray-100,
        html.dark .border-slate-100,
        html.dark .border-gray-200,
        html.dark .border-gray-200\/60,
        html.dark .border-gray-200\/80,
        html.dark .border-slate-200,
        html.dark .border-slate-200\/70,
        html.dark .border-slate-200\/80,
        html.dark .border-slate-200\/90,
        html.dark .border-gray-300,
        html.dark .border-slate-300 {
            border-color: #334155 !important;
        }
        html.dark .divide-gray-100 > :not([hidden]) ~ :not([hidden]),
        html.dark .divide-gray-200 > :not([hidden]) ~ :not([hidden]),
        html.dark .divide-slate-100 > :not([hidden]) ~ :not([hidden]),
        html.dark .divide-slate-200 > :not([hidden]) ~ :not([hidden]) {
            border-color: #334155 !important;
        }

        /* 5. Form Inputs, Selects & Textareas in Dark Mode */
        html.dark input:not([type="checkbox"]):not([type="radio"]):not([type="submit"]):not([type="button"]),
        html.dark select,
        html.dark textarea {
            background-color: #0f172a !important;
            color: #f8fafc !important;
            border-color: #334155 !important;
        }
        html.dark input::placeholder,
        html.dark textarea::placeholder {
            color: #64748b !important;
        }
        html.dark input:disabled,
        html.dark select:disabled {
            background-color: #1e293b !important;
            color: #64748b !important;
        }

        /* 6. Hover State Refinements in Dark Mode */
        html.dark .hover\:bg-gray-50:hover,
        html.dark .hover\:bg-gray-50\/50:hover,
        html.dark .hover\:bg-slate-50:hover,
        html.dark .hover\:bg-gray-100:hover,
        html.dark .hover\:bg-slate-100:hover {
            background-color: #334155 !important;
        }
        html.dark .hover\:bg-teal-50:hover,
        html.dark .hover\:bg-teal-50\/30:hover,
        html.dark .hover\:bg-teal-50\/80:hover {
            background-color: rgba(13, 148, 136, 0.22) !important;
        }
    </style>

    <!-- Instant Theme Initializer (Prevents Flash of Wrong Theme + Global Toggle API) -->
    <script>
        (function () {
            var STORAGE_KEY = 'may22_theme_v2';
            function getSavedTheme() {
                try {
                    return localStorage.getItem(STORAGE_KEY) || 'dark';
                } catch (e) {
                    return 'dark';
                }
            }
            function applyTheme(theme) {
                var root = document.documentElement;
                if (theme === 'dark') {
                    root.classList.add('dark');
                } else {
                    root.classList.remove('dark');
                }
                window.__may22Theme = theme;
                window.dispatchEvent(new CustomEvent('may22-theme-changed', { detail: { theme: theme } }));
            }
            applyTheme(getSavedTheme());

            window.toggleSiteTheme = function () {
                var next = document.documentElement.classList.contains('dark') ? 'light' : 'dark';
                try {
                    localStorage.setItem(STORAGE_KEY, next);
                } catch (e) {}
                applyTheme(next);
                return next;
            };
        })();
    </script>

    @php
        $viteEntries = ['resources/css/app.css', 'resources/js/app.js'];
        $hotFilePath = public_path('hot');
        $manifestPath = public_path('build/manifest.json');
        $manifest = null;

        if (is_file($manifestPath)) {
            $decodedManifest = json_decode(file_get_contents($manifestPath), true);
            $manifest = is_array($decodedManifest) ? $decodedManifest : null;
        }
    @endphp

    @if(is_array($manifest))
        @php
            $cssFile = $manifest['resources/css/app.css']['file'] ?? null;
            $jsFile = $manifest['resources/js/app.js']['file'] ?? null;
        @endphp

        @if($cssFile)
            <link rel="stylesheet" href="{{ asset('build/' . $cssFile) }}">
        @endif

        @if($jsFile)
            <script type="module" src="{{ asset('build/' . $jsFile) }}"></script>
        @endif
    @else
        @vite($viteEntries)
    @endif
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 text-gray-900 font-sans antialiased">
    <!-- Custom Branded Logo Loader (Initial Preloader & Global State) -->
    <x-custom-loader />

    @yield('content')

    @php
        $sitePopupTitleText = trim(strip_tags((string) ($sitePopupTitle ?? '')));
        $sitePopupMessageText = trim(strip_tags((string) ($sitePopupMessage ?? '')));
        $sitePopupButtonTextValue = trim((string) ($sitePopupButtonText ?? ''));
        $sitePopupButtonLinkValue = trim((string) ($sitePopupButtonLink ?? ''));
        $shouldRenderSitePopup = (bool) ($sitePopupEnabled ?? false) && ($sitePopupTitleText !== '' || $sitePopupMessageText !== '');
        $sitePopupHref = $sitePopupButtonLinkValue;

        if ($sitePopupHref !== '' && !preg_match('/^(https?:)?\/\//i', $sitePopupHref) && !str_starts_with($sitePopupHref, '/')) {
            $sitePopupHref = '/' . ltrim($sitePopupHref, '/');
        }
    @endphp

    @if($shouldRenderSitePopup)
        <div
            x-data="{
                open: false,
                storageKey: @js($sitePopupStorageKey ?? ('site-popup-dismissed:' . ($sitePopupVersion ?? 'default'))),
                init() {
                    try {
                        const localDismissed = window.localStorage.getItem(this.storageKey) === '1';
                        const sessionDismissed = window.sessionStorage.getItem('site_popup_dismissed_session') === '1';
                        this.open = !localDismissed && !sessionDismissed;
                    } catch (error) {
                        this.open = true;
                    }
                },
                dismiss() {
                    this.open = false;
                    try {
                        window.localStorage.setItem(this.storageKey, '1');
                        window.sessionStorage.setItem('site_popup_dismissed_session', '1');
                    } catch (error) {}
                }
            }"
            id="site-notice-popup-backdrop"
            x-init="init()"
            x-show="open"
            x-cloak
            @keydown.escape.window="dismiss(); const el = document.getElementById('site-notice-popup-backdrop'); if(el) el.style.display='none';"
            class="fixed inset-0 z-[70] flex items-center justify-center px-4 py-6 sm:px-6"
        >
            <div class="absolute inset-0 bg-gray-950/75 backdrop-blur-sm" @click="dismiss()" onclick="const el = document.getElementById('site-notice-popup-backdrop'); if(el) el.style.display='none';"></div>

            <div class="relative w-full max-w-lg overflow-hidden rounded-3xl border border-white/10 bg-white shadow-2xl">
                <div class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-[#0d9488] via-teal-500 to-[#facc15]"></div>

                <button
                    type="button"
                    class="absolute right-4 top-4 inline-flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 text-gray-500 transition hover:bg-gray-200 hover:text-gray-700 cursor-pointer"
                    @click="dismiss()"
                    onclick="const el = document.getElementById('site-notice-popup-backdrop'); if(el) el.style.display='none';"
                    aria-label="Close popup"
                >
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>

                <div class="p-6 sm:p-8">
                    <div class="mb-4 inline-flex items-center rounded-full bg-[#0d9488]/10 px-3 py-1 text-xs font-extrabold uppercase tracking-[0.2em] text-[#0d9488]">
                        {{ $siteName ?? 'May22SMS' }}
                    </div>

                    @if($sitePopupTitleText !== '')
                        <h2 class="pr-10 text-2xl font-black tracking-tight text-gray-900 sm:text-3xl">{{ $sitePopupTitleText }}</h2>
                    @endif

                    @if($sitePopupMessageText !== '')
                        <div class="mt-4 text-sm leading-7 text-gray-600 sm:text-base">
                            {!! nl2br(e($sitePopupMessageText)) !!}
                        </div>
                    @endif

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        @if($sitePopupButtonTextValue !== '' && $sitePopupHref !== '')
                            <a
                                href="{{ $sitePopupHref }}"
                                class="inline-flex items-center justify-center rounded-2xl bg-[#0d9488] px-5 py-3 text-sm font-extrabold text-white transition hover:bg-[#0f766e]"
                                @click="dismiss()"
                                onclick="const el = document.getElementById('site-notice-popup-backdrop'); if(el) el.style.display='none';"
                            >
                                {{ $sitePopupButtonTextValue }}
                            </a>
                        @endif

                        <button
                            type="button"
                            class="inline-flex items-center justify-center rounded-2xl border border-gray-200 px-5 py-3 text-sm font-bold text-gray-700 transition hover:bg-gray-50 cursor-pointer"
                            @click="dismiss()"
                            onclick="const el = document.getElementById('site-notice-popup-backdrop'); if(el) el.style.display='none';"
                        >
                            Dismiss
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Floating Action Buttons -->
    <div class="fixed bottom-20 sm:bottom-6 right-4 sm:right-6 flex flex-col gap-3 z-40">
        <!-- WhatsApp -->
        <a href="https://chat.whatsapp.com/FCD1Iqs14iPBG4DpiT4Vec?mode=gi_t" target="_blank" rel="noopener noreferrer" class="w-14 h-14 bg-green-500 hover:bg-green-600 rounded-full flex items-center justify-center shadow-lg transition-all duration-300 hover:scale-110">
            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.488-.494-.67-.503-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
            </svg>
        </a>
        <!-- Telegram -->
        <a href="https://t.me/may22sms" target="_blank" rel="noopener noreferrer" class="w-14 h-14 bg-blue-500 hover:bg-blue-600 rounded-full flex items-center justify-center shadow-lg transition-all duration-300 hover:scale-110">
            <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24">
                <path d="M16.658 9.931l-3.376 16.34c-.535 1.625-2.276 1.496-3.003.604l-6.665-8.647-4.192-1.406c-1.657-.585-1.675-1.647.355-2.37l19.405-7.927c1.81-.724 3.298-.234 3.892 1.403.775 2.122 3.663 13.685 3.663 13.685.22 1.276-1.086 1.82-2.093 1.28l-8.872-5.883z"/>
            </svg>
        </a>
    </div>
    <!-- Live Activity Social Proof Toast -->
    <x-live-activity-toast />

    <!-- Global Seasonal Celebration Animations & Decorations Overlay -->
    <x-seasonal-overlay />

    @auth
        <!-- Glassmorphism Review Modal -->
        <x-review-modal />
    @endauth

</body>
</html>
