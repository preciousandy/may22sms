@extends('layouts.admin')

@section('admin_content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">Global Settings</h2>
    <p class="text-gray-500 text-sm">Manage system configurations and API pricing markups.</p>
</div>

@if(session('success'))
    <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl shadow-sm">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl shadow-sm">
        <ul class="list-disc pl-5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800">SMS Verification Pricing</h3>
        </div>
        
        <div class="p-6">
            <form action="/admin/settings" method="POST">
            @csrf
            
            <div class="mb-6">
                <label for="sms_provider" class="block text-sm font-medium text-gray-700 mb-2">Default SMS Provider</label>
                <select name="sms_provider" id="sms_provider" class="block w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-0 focus:border-[#0d9488] transition text-lg">
                    <option value="herosms" {{ old('sms_provider', $smsProvider) === 'herosms' ? 'selected' : '' }}>Hero SMS (Global - All Countries)</option>
                    <option value="daisysms" {{ old('sms_provider', $smsProvider) === 'daisysms' ? 'selected' : '' }}>Daisy SMS (USA Only)</option>
                    <option value="5sim" {{ old('sms_provider', $smsProvider) === '5sim' ? 'selected' : '' }}>5sim</option>
                </select>
            </div>

            <!-- Provider Activation & API Keys -->
            <div class="mb-6 p-4 rounded-xl border border-gray-200 bg-gray-50 space-y-4">
                <div class="font-bold text-gray-800 text-sm border-b border-gray-200 pb-2">Active SMS Providers & Credentials</div>
                
                <!-- Hero SMS -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="inline-flex items-center gap-2 text-sm font-bold text-gray-800">
                            <input type="hidden" name="sms_provider_herosms_enabled" value="0">
                            <input type="checkbox" name="sms_provider_herosms_enabled" value="1" {{ old('sms_provider_herosms_enabled', ($herosmsEnabled ?? true) ? '1' : '0') === '1' ? 'checked' : '' }} class="rounded border-gray-300 text-[#0d9488] focus:ring-[#0d9488]">
                            <span>Hero SMS (Global)</span>
                        </label>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded bg-teal-100 text-teal-800">hero-sms.com</span>
                    </div>
                    <input type="password" name="herosms_api_key" value="{{ old('herosms_api_key', $herosmsApiKey ?? '') }}" class="w-full text-xs font-mono rounded-lg border-gray-300 focus:border-[#0d9488] focus:ring-0" placeholder="Hero SMS API Key">
                </div>

                <!-- Daisy SMS -->
                <div class="space-y-2 pt-2 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <label class="inline-flex items-center gap-2 text-sm font-bold text-gray-800">
                            <input type="hidden" name="sms_provider_daisysms_enabled" value="0">
                            <input type="checkbox" name="sms_provider_daisysms_enabled" value="1" {{ old('sms_provider_daisysms_enabled', ($daisysmsEnabled ?? true) ? '1' : '0') === '1' ? 'checked' : '' }} class="rounded border-gray-300 text-[#0d9488] focus:ring-[#0d9488]">
                            <span>Daisy SMS (USA Only)</span>
                        </label>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded bg-amber-100 text-amber-800">daisysms.io</span>
                    </div>
                    <input type="password" name="daisysms_api_key" value="{{ old('daisysms_api_key', $daisysmsApiKey ?? '') }}" class="w-full text-xs font-mono rounded-lg border-gray-300 focus:border-[#0d9488] focus:ring-0" placeholder="Daisy SMS API Key">
                </div>
            </div>
            
            <div class="mb-6">
                <label for="sms_markup_percent" class="block text-sm font-medium text-gray-700 mb-2">Global Price Markup (%)</label>
                <div class="relative">
                    <input type="number" name="sms_markup_percent" id="sms_markup_percent" 
                           class="block w-full pl-4 pr-12 py-3 border-2 border-gray-200 rounded-xl focus:ring-0 focus:border-[#0d9488] transition text-lg" 
                           value="{{ old('sms_markup_percent', $smsMarkup) }}" step="0.1" min="0" required>
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                        <span class="text-gray-500 font-bold">%</span>
                    </div>
                </div>
                <p class="mt-2 text-sm text-gray-500">
                    This percentage will be added to the base price returned by the API. <br>
                    <span class="italic text-gray-400">Example: If API charges $1.00 and markup is 20%, the user will pay $1.20.</span>
                </p>
                @error('sms_markup_percent')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6 rounded-2xl border border-gray-100 bg-gray-50 p-5">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <div class="text-sm font-bold text-gray-900">Premium SMS</div>
                        <div class="text-xs font-bold text-gray-500">Adds an extra percentage on top of the normal SMS price.</div>
                    </div>
                    <label class="inline-flex items-center gap-2 text-sm font-bold text-gray-700">
                        <input type="hidden" name="sms_premium_enabled" value="0">
                        <input type="checkbox" name="sms_premium_enabled" value="1" {{ old('sms_premium_enabled', ($smsPremiumEnabled ?? true) ? '1' : '0') === '1' ? 'checked' : '' }} class="rounded border-gray-300 text-[#0d9488] focus:ring-[#0d9488]">
                        <span>Enabled</span>
                    </label>
                </div>
                <div class="mt-4">
                    <label for="sms_premium_markup_percent" class="block text-sm font-medium text-gray-700 mb-2">Premium Markup (%)</label>
                    <div class="relative">
                        <input type="number" name="sms_premium_markup_percent" id="sms_premium_markup_percent"
                               class="block w-full pl-4 pr-12 py-3 border-2 border-gray-200 rounded-xl focus:ring-0 focus:border-[#0d9488] transition text-lg"
                               value="{{ old('sms_premium_markup_percent', $smsPremiumMarkupPercent ?? 40) }}" step="0.1" min="0">
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                            <span class="text-gray-500 font-bold">%</span>
                        </div>
                    </div>
                    @error('sms_premium_markup_percent')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Markup Overrides (Optional)</label>
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <div class="text-xs font-bold text-gray-500 mb-1">Per Country (slug=percent)</div>
                        <textarea name="sms_markup_by_country_text" rows="4" class="w-full rounded-lg border-gray-300 focus:border-[#0d9488] focus:ring focus:ring-teal-200 focus:ring-opacity-50 text-sm font-mono" placeholder="usa=20&#10;nigeria=10">{{ old('sms_markup_by_country_text', implode("\n", $smsCountryLines ?? [])) }}</textarea>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-gray-500 mb-1">Per Service/Product (product=percent)</div>
                        <textarea name="sms_markup_by_service_text" rows="4" class="w-full rounded-lg border-gray-300 focus:border-[#0d9488] focus:ring focus:ring-teal-200 focus:ring-opacity-50 text-sm font-mono" placeholder="instagram=25&#10;facebook=15">{{ old('sms_markup_by_service_text', implode("\n", $smsServiceLines ?? [])) }}</textarea>
                    </div>
                </div>
                <p class="mt-2 text-xs text-gray-500">Country markup applies to the country, and service markup adds on top. Leave empty to remove an override.</p>
            </div>

            <div class="pt-4 border-t border-gray-100">
                <button type="submit" class="bg-gray-900 text-white px-8 py-3 rounded-xl font-bold shadow-md hover:bg-gray-800 transition">
                    Save Pricing Configuration
                </button>
            </div>
        </form>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800">Currency</h3>
        </div>
        
        <div class="p-6">
            <form action="/admin/settings" method="POST">
                @csrf

                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Default Currency</label>
                    <select name="default_currency" class="w-full rounded-lg border-gray-300 focus:border-[#0d9488] focus:ring focus:ring-teal-200 focus:ring-opacity-50">
                        <option value="USD" {{ old('default_currency', $defaultCurrency ?? 'USD') === 'USD' ? 'selected' : '' }}>USD</option>
                        <option value="NGN" {{ old('default_currency', $defaultCurrency ?? 'USD') === 'NGN' ? 'selected' : '' }}>NGN</option>
                    </select>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">NGN per 1 USD</label>
                    <input type="number" step="0.01" min="1" name="ngn_per_usd" value="{{ old('ngn_per_usd', $ngnPerUsd ?? 1500) }}" class="w-full rounded-lg border-gray-300 focus:border-[#0d9488] focus:ring focus:ring-teal-200 focus:ring-opacity-50" required>
                    <p class="mt-2 text-xs text-gray-500">Used for display + funding conversion when users switch to NGN.</p>
                </div>

                <div class="pt-4 border-t border-gray-100">
                    <button type="submit" class="w-full bg-[#0d9488] hover:bg-[#0f766e] text-white px-8 py-3 rounded-xl font-bold shadow-md transition">
                        Save Currency Settings
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800">Branding</h3>
        </div>
        <div class="p-6">
            <form action="/admin/settings" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Site Name</label>
                    <input type="text" name="site_name" value="{{ old('site_name', $siteName ?? 'May22SMS') }}" class="w-full rounded-lg border-gray-300 focus:border-[#0d9488] focus:ring focus:ring-teal-200 focus:ring-opacity-50" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Logo URL (Optional)</label>
                    <input type="text" name="site_logo_url" value="{{ old('site_logo_url', $siteLogoUrl ?? '') }}" class="w-full rounded-lg border-gray-300 focus:border-[#0d9488] focus:ring focus:ring-teal-200 focus:ring-opacity-50" placeholder="https://...">
                    <div class="mt-2 text-xs text-gray-500">If empty, the app uses the text logo.</div>
                </div>
                <div class="pt-4 border-t border-gray-100">
                    <div class="text-sm font-bold text-gray-900 mb-2">Homepage Hero</div>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Hero Title</label>
                            <input type="text" name="home_hero_title" value="{{ old('home_hero_title', $homeHeroTitle ?? '') }}" class="w-full rounded-lg border-gray-300 focus:border-[#0d9488] focus:ring focus:ring-teal-200 focus:ring-opacity-50">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Hero Subtitle</label>
                            <input type="text" name="home_hero_subtitle" value="{{ old('home_hero_subtitle', $homeHeroSubtitle ?? '') }}" class="w-full rounded-lg border-gray-300 focus:border-[#0d9488] focus:ring focus:ring-teal-200 focus:ring-opacity-50">
                        </div>
                    </div>
                </div>
                <div class="pt-4 border-t border-gray-100">
                    <button type="submit" class="w-full bg-gray-900 text-white px-8 py-3 rounded-xl font-bold shadow-md hover:bg-gray-800 transition">Save Branding</button>
                </div>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800">Announcement Banner</h3>
        </div>
        <div class="p-6">
            <form action="/admin/settings" method="POST" class="space-y-4">
                @csrf
                <div class="flex items-center justify-between p-4 rounded-xl border border-gray-100 bg-gray-50">
                    <div>
                        <p class="font-bold text-gray-800">Enable banner</p>
                        <p class="text-xs text-gray-500">Shows a banner on user pages.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <input type="hidden" name="announcement_enabled" value="0">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer" name="announcement_enabled" value="1" {{ old('announcement_enabled', ($announcementEnabled ?? false) ? '1' : '0') === '1' ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-[#0d9488] transition"></div>
                            <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition peer-checked:translate-x-5"></div>
                        </label>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Banner Text</label>
                    <input type="text" name="announcement_text" value="{{ old('announcement_text', $announcementText ?? '') }}" class="w-full rounded-lg border-gray-300 focus:border-[#0d9488] focus:ring focus:ring-teal-200 focus:ring-opacity-50" placeholder="e.g. New promo codes available!">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Link (Optional)</label>
                    <input type="text" name="announcement_link" value="{{ old('announcement_link', $announcementLink ?? '') }}" class="w-full rounded-lg border-gray-300 focus:border-[#0d9488] focus:ring focus:ring-teal-200 focus:ring-opacity-50" placeholder="/promo-codes or https://...">
                </div>
                <div class="pt-4 border-t border-gray-100">
                    <button type="submit" class="w-full bg-[#0d9488] hover:bg-[#0f766e] text-white px-8 py-3 rounded-xl font-bold shadow-md transition">Save Announcement</button>
                </div>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800">Popup Modal</h3>
        </div>
        <div class="p-6">
            <form action="/admin/settings" method="POST" class="space-y-4">
                @csrf
                <div class="flex items-center justify-between p-4 rounded-xl border border-gray-100 bg-gray-50">
                    <div>
                        <p class="font-bold text-gray-800">Enable popup</p>
                        <p class="text-xs text-gray-500">Shows a popup when users open the site.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <input type="hidden" name="site_popup_enabled" value="0">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer" name="site_popup_enabled" value="1" {{ old('site_popup_enabled', ($popupEnabled ?? false) ? '1' : '0') === '1' ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-[#0d9488] transition"></div>
                            <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition peer-checked:translate-x-5"></div>
                        </label>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Popup Title</label>
                    <input type="text" name="site_popup_title" value="{{ old('site_popup_title', $popupTitle ?? '') }}" class="w-full rounded-lg border-gray-300 focus:border-[#0d9488] focus:ring focus:ring-teal-200 focus:ring-opacity-50" placeholder="Important update">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Popup Message</label>
                    <textarea name="site_popup_message" rows="5" class="w-full rounded-lg border-gray-300 focus:border-[#0d9488] focus:ring focus:ring-teal-200 focus:ring-opacity-50" placeholder="Write the message you want users to see...">{{ old('site_popup_message', $popupMessage ?? '') }}</textarea>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Button Text (Optional)</label>
                        <input type="text" name="site_popup_button_text" value="{{ old('site_popup_button_text', $popupButtonText ?? '') }}" class="w-full rounded-lg border-gray-300 focus:border-[#0d9488] focus:ring focus:ring-teal-200 focus:ring-opacity-50" placeholder="Learn more">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Button Link (Optional)</label>
                        <input type="text" name="site_popup_button_link" value="{{ old('site_popup_button_link', $popupButtonLink ?? '') }}" class="w-full rounded-lg border-gray-300 focus:border-[#0d9488] focus:ring focus:ring-teal-200 focus:ring-opacity-50" placeholder="/funding or https://...">
                    </div>
                </div>
                <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-900">
                    Users can close the popup, and it stays hidden in their browser until you change the popup content again.
                </div>
                <div class="pt-4 border-t border-gray-100">
                    <button type="submit" class="w-full bg-[#0d9488] hover:bg-[#0f766e] text-white px-8 py-3 rounded-xl font-bold shadow-md transition">Save Popup</button>
                </div>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800">Payment Methods</h3>
        </div>
        <div class="p-6">
            <form action="/admin/settings" method="POST">
                @csrf

                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 rounded-xl border border-gray-100 bg-gray-50">
                        <div>
                            <p class="font-bold text-gray-800">PaymentPoint</p>
                            <p class="text-xs text-gray-500">Card/bank funding (NGN). Disable to hide it from users.</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-bold px-2 py-1 rounded-full {{ ($paymentpointEnabled ?? true) ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-200 text-gray-700' }}">
                                {{ ($paymentpointEnabled ?? true) ? 'ENABLED' : 'DISABLED' }}
                            </span>
                            <input type="hidden" name="payment_method_paymentpoint_enabled" value="0">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer" name="payment_method_paymentpoint_enabled" value="1" {{ ($paymentpointEnabled ?? true) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-[#0d9488] transition"></div>
                                <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition peer-checked:translate-x-5"></div>
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center justify-between p-4 rounded-xl border border-gray-100 bg-gray-50">
                        <div>
                            <p class="font-bold text-gray-800">Strollwallet</p>
                            <p class="text-xs text-gray-500">Alternate card/bank funding (NGN).</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-bold px-2 py-1 rounded-full {{ ($strollwalletEnabled ?? true) ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-200 text-gray-700' }}">
                                {{ ($strollwalletEnabled ?? true) ? 'ENABLED' : 'DISABLED' }}
                            </span>
                            <input type="hidden" name="payment_method_strollwallet_enabled" value="0">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer" name="payment_method_strollwallet_enabled" value="1" {{ ($strollwalletEnabled ?? true) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-[#0d9488] transition"></div>
                                <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition peer-checked:translate-x-5"></div>
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center justify-between p-4 rounded-xl border border-gray-100 bg-gray-50">
                        <div>
                            <p class="font-bold text-gray-800">Crypto (NOWPayments)</p>
                            <p class="text-xs text-gray-500">USDT/BTC/ETH funding via NOWPayments invoice.</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-bold px-2 py-1 rounded-full {{ ($cryptoEnabled ?? true) ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-200 text-gray-700' }}">
                                {{ ($cryptoEnabled ?? true) ? 'ENABLED' : 'DISABLED' }}
                            </span>
                            <input type="hidden" name="payment_method_crypto_enabled" value="0">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer" name="payment_method_crypto_enabled" value="1" {{ ($cryptoEnabled ?? true) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-[#0d9488] transition"></div>
                                <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition peer-checked:translate-x-5"></div>
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center justify-between p-4 rounded-xl border border-gray-100 bg-gray-50">
                        <div>
                            <p class="font-bold text-gray-800">PocketFi</p>
                            <p class="text-xs text-gray-500">Card/bank funding via PocketFi.</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-bold px-2 py-1 rounded-full {{ ($pocketfiEnabled ?? true) ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-200 text-gray-700' }}">
                                {{ ($pocketfiEnabled ?? true) ? 'ENABLED' : 'DISABLED' }}
                            </span>
                            <input type="hidden" name="payment_method_pocketfi_enabled" value="0">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer" name="payment_method_pocketfi_enabled" value="1" {{ ($pocketfiEnabled ?? true) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-[#0d9488] transition"></div>
                                <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition peer-checked:translate-x-5"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-100 mt-6">
                    <button type="submit" class="w-full bg-gray-900 hover:bg-gray-800 text-white px-8 py-3 rounded-xl font-bold shadow-md transition">
                        Save Payment Settings
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800">Security Controls</h3>
        </div>
        <div class="p-6">
            <form action="/admin/settings" method="POST" class="space-y-4">
                @csrf

                <div class="flex items-center justify-between p-4 rounded-xl border border-gray-100 bg-gray-50">
                    <div>
                        <p class="font-bold text-gray-800">Require 2FA for Staff</p>
                        <p class="text-xs text-gray-500">Admins/support/editor must enable 2FA to access admin.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <input type="hidden" name="require_staff_2fa" value="0">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer" name="require_staff_2fa" value="1" {{ old('require_staff_2fa', ($requireStaff2fa ?? false) ? '1' : '0') === '1' ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-[#0d9488] transition"></div>
                            <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition peer-checked:translate-x-5"></div>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Blocked IPs</label>
                    <textarea name="blocked_ips_text" rows="6" class="w-full rounded-lg border-gray-300 focus:border-[#0d9488] focus:ring focus:ring-teal-200 focus:ring-opacity-50 text-sm font-mono" placeholder="1.2.3.4&#10;5.6.7.8">{{ old('blocked_ips_text', $blockedIpsText ?? '') }}</textarea>
                    <div class="mt-2 text-xs text-gray-500">One IP per line. These IPs will be blocked system-wide.</div>
                </div>

                <div class="pt-4 border-t border-gray-100">
                    <button type="submit" class="w-full bg-gray-900 hover:bg-gray-800 text-white px-8 py-3 rounded-xl font-bold shadow-md transition">Save Security</button>
                </div>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800">SEO Settings</h3>
        </div>
        <div class="p-6">
            <form action="/admin/settings" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Page Title</label>
                    <input type="text" name="seo_title" value="{{ old('seo_title', $seoTitle ?? '') }}" maxlength="60" class="w-full rounded-lg border-gray-300 focus:border-[#0d9488] focus:ring focus:ring-teal-200 focus:ring-opacity-50" placeholder="Buy SMS Numbers | May22SMS">
                    <div class="mt-1 text-xs text-gray-500">Recommended: 50-60 characters.</div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Meta Description</label>
                    <textarea name="seo_description" rows="3" maxlength="160" class="w-full rounded-lg border-gray-300 focus:border-[#0d9488] focus:ring focus:ring-teal-200 focus:ring-opacity-50" placeholder="Buy SMS numbers, accounts, and boosting services without stress. Fund your wallet and place orders in seconds.">{{ old('seo_description', $seoDescription ?? '') }}</textarea>
                    <div class="mt-1 text-xs text-gray-500">Recommended: 150-160 characters.</div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Meta Keywords</label>
                    <input type="text" name="seo_keywords" value="{{ old('seo_keywords', $seoKeywords ?? '') }}" maxlength="500" class="w-full rounded-lg border-gray-300 focus:border-[#0d9488] focus:ring focus:ring-teal-200 focus:ring-opacity-50" placeholder="sms verification, buy sms numbers, boost followers, accounts for sale">
                    <div class="mt-1 text-xs text-gray-500">Comma-separated list of keywords.</div>
                </div>
                <div class="pt-2 border-t border-gray-100">
                    <div class="text-sm font-bold text-gray-900 mb-3">Open Graph (Social Sharing)</div>
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">OG Image URL</label>
                            <input type="url" name="seo_og_image" value="{{ old('seo_og_image', $seoOgImage ?? '') }}" maxlength="500" class="w-full rounded-lg border-gray-300 focus:border-[#0d9488] focus:ring focus:ring-teal-200 focus:ring-opacity-50" placeholder="https://.../og-image.png">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">OG Title</label>
                            <input type="text" name="seo_og_title" value="{{ old('seo_og_title', $seoOgTitle ?? '') }}" maxlength="60" class="w-full rounded-lg border-gray-300 focus:border-[#0d9488] focus:ring focus:ring-teal-200 focus:ring-opacity-50" placeholder="Buy SMS Numbers | May22SMS">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">OG Description</label>
                            <textarea name="seo_og_description" rows="2" maxlength="160" class="w-full rounded-lg border-gray-300 focus:border-[#0d9488] focus:ring focus:ring-teal-200 focus:ring-opacity-50" placeholder="Buy SMS numbers, accounts, and boosting services without stress.">{{ old('seo_og_description', $seoOgDescription ?? '') }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Twitter Card Type</label>
                            <select name="seo_twitter_card" class="w-full rounded-lg border-gray-300 focus:border-[#0d9488] focus:ring focus:ring-teal-200 focus:ring-opacity-50">
                                <option value="summary" {{ old('seo_twitter_card', $seoTwitterCard ?? 'summary_large_image') === 'summary' ? 'selected' : '' }}>Summary (Small Image)</option>
                                <option value="summary_large_image" {{ old('seo_twitter_card', $seoTwitterCard ?? 'summary_large_image') === 'summary_large_image' ? 'selected' : '' }}>Summary (Large Image)</option>
                                <option value="app" {{ old('seo_twitter_card', $seoTwitterCard ?? 'summary_large_image') === 'app' ? 'selected' : '' }}>App</option>
                                <option value="player" {{ old('seo_twitter_card', $seoTwitterCard ?? 'summary_large_image') === 'player' ? 'selected' : '' }}>Player (Video)</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="pt-4 border-t border-gray-100">
                    <button type="submit" class="w-full bg-gray-900 hover:bg-gray-800 text-white px-8 py-3 rounded-xl font-bold shadow-md transition">Save SEO Settings</button>
                </div>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800">Maintenance & Health</h3>
        </div>
        <div class="p-6 space-y-4">
            <div class="p-4 rounded-xl border border-gray-100 bg-gray-50">
                <div class="text-sm font-bold text-gray-900 mb-2">Health</div>
                <div class="text-xs text-gray-600 space-y-1">
                    <div>APP_ENV: <span class="font-bold text-gray-900">{{ $health['app_env'] ?? '' }}</span></div>
                    <div>APP_DEBUG: <span class="font-bold text-gray-900">{{ ($health['app_debug'] ?? false) ? 'true' : 'false' }}</span></div>
                    <div>QUEUE: <span class="font-bold text-gray-900">{{ $health['queue_connection'] ?? '' }}</span></div>
                    <div>CACHE: <span class="font-bold text-gray-900">{{ $health['cache_driver'] ?? '' }}</span></div>
                    <div>STORAGE WRITABLE: <span class="font-bold text-gray-900">{{ ($health['storage_writable'] ?? false) ? 'yes' : 'no' }}</span></div>
                </div>
            </div>

            <div class="p-4 rounded-xl border border-gray-100 bg-gray-50 flex items-center justify-between">
                <div>
                    <div class="text-sm font-bold text-gray-900">Maintenance Mode</div>
                    <div class="text-xs text-gray-600">Current: <span class="font-bold text-gray-900">{{ ($maintenanceEnabled ?? false) ? 'ON' : 'OFF' }}</span></div>
                </div>
                <form action="/admin/settings/maintenance" method="POST" class="flex items-center gap-2">
                    @csrf
                    @if(($maintenanceEnabled ?? false) === false)
                        <input type="hidden" name="action" value="down">
                        <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-red-700 transition" onclick="return confirm('Enable maintenance mode?');">Enable</button>
                    @else
                        <input type="hidden" name="action" value="up">
                        <button type="submit" class="bg-[#0d9488] text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-[#0f766e] transition">Disable</button>
                    @endif
                </form>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <form action="/admin/settings/maintenance" method="POST">
                    @csrf
                    <input type="hidden" name="action" value="optimize_clear">
                    <button type="submit" class="w-full bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-gray-800 transition">Clear Caches</button>
                </form>
                <form action="/admin/settings/maintenance" method="POST">
                    @csrf
                    <input type="hidden" name="action" value="config_cache">
                    <button type="submit" class="w-full bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-gray-800 transition">Cache Config</button>
                </form>
                <form action="/admin/settings/maintenance" method="POST">
                    @csrf
                    <input type="hidden" name="action" value="route_cache">
                    <button type="submit" class="w-full bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-gray-800 transition">Cache Routes</button>
                </form>
                <form action="/admin/settings/maintenance" method="POST">
                    @csrf
                    <input type="hidden" name="action" value="view_cache">
                    <button type="submit" class="w-full bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-gray-800 transition">Cache Views</button>
                </form>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-800">{{ $smsProvider === 'daisysms' ? 'DaisySMS' : '5sim' }} API Sync</h3>
            <span class="bg-[#0d9488] text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm">{{ $totalCountries }} Countries</span>
        </div>
        
        <div class="p-6">
            <p class="text-gray-600 mb-6">
                Click the button below to synchronize the latest list of available countries from the {{ $smsProvider === 'daisysms' ? 'DaisySMS' : '5sim' }} API. 
                This ensures your users see the most up-to-date regions for purchasing numbers.
            </p>

            <form action="/admin/settings/sync-countries" method="POST">
                @csrf
                <button type="submit" class="w-full bg-[#0d9488] hover:bg-[#0f766e] text-white px-8 py-3 rounded-xl font-bold shadow-md transition flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Sync Countries Now
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
