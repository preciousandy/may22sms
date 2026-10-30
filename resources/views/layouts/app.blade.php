<!DOCTYPE html>
<html lang="en">
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
    </style>

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

    @auth
        <!-- Glassmorphism Review Modal -->
        <x-review-modal />
    @endauth

</body>
</html>
