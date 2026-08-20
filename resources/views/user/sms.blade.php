@extends('layouts.dashboard')

@section('page_title', 'SMS Verifications')

@section('dashboard_content')
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
    <div class="px-6 py-5 border-b border-gray-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-gradient-to-r from-gray-50 to-teal-50/20">
        <div>
            <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                <span>Buy Virtual Number</span>
                <span class="bg-teal-100 text-[#0d9488] text-xs font-black px-2.5 py-0.5 rounded-full uppercase tracking-wider">Live API</span>
            </h3>
            <p class="text-xs text-gray-500 mt-1">Receive SMS verification OTPs instantly for WhatsApp, Telegram, Google & more.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="bg-[#0d9488] text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-sm flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-300 animate-pulse"></span>
                Powered by May22SMS
            </span>
        </div>
    </div>
    
    <div class="p-6 md:p-8">
        <!-- Step 1: Provider Selection -->
        <div class="mb-8">
            <label class="block text-sm font-extrabold text-gray-800 mb-3 flex items-center justify-between">
                <span>1. Select SMS Server</span>
                <span class="text-xs text-gray-500 font-normal">Choose server location</span>
            </label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Hero SMS Option -->
                @if(($herosmsEnabled ?? true))
                <label class="relative cursor-pointer group">
                    <input type="radio" name="sms_provider_choice" value="herosms" class="peer sr-only" id="providerHero" checked>
                    <div class="rounded-2xl border-2 border-gray-200 p-5 peer-checked:border-[#0d9488] peer-checked:bg-teal-50/50 hover:border-teal-300 transition flex flex-col justify-between h-full shadow-sm">
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-teal-100 text-[#0d9488] flex items-center justify-center font-bold text-lg">⚡</div>
                                <div>
                                    <h4 class="font-extrabold text-gray-900 text-base">May SMS (Global)</h4>
                                    <p class="text-xs text-gray-500">Global Service (50+ Countries)</p>
                                </div>
                            </div>
                            <span class="bg-teal-100 text-teal-800 text-[10px] font-black px-2 py-0.5 rounded-full uppercase">Global</span>
                        </div>
                        <p class="text-xs text-gray-600">Best for international numbers across Europe, Asia, Africa & Americas.</p>
                    </div>
                </label>
                @endif

                <!-- Daisy SMS Option -->
                @if(($daisysmsEnabled ?? true))
                <label class="relative cursor-pointer group">
                    <input type="radio" name="sms_provider_choice" value="daisysms" class="peer sr-only" id="providerDaisy" {{ !($herosmsEnabled ?? true) ? 'checked' : '' }}>
                    <div class="rounded-2xl border-2 border-gray-200 p-5 peer-checked:border-[#0d9488] peer-checked:bg-teal-50/50 hover:border-teal-300 transition flex flex-col justify-between h-full shadow-sm">
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center font-bold text-lg">🇺🇸</div>
                                <div>
                                    <h4 class="font-extrabold text-gray-900 text-base">May SMS (USA)</h4>
                                    <p class="text-xs text-gray-500">United States Only</p>
                                </div>
                            </div>
                            <span class="bg-amber-100 text-amber-800 text-[10px] font-black px-2 py-0.5 rounded-full uppercase">USA Only</span>
                        </div>
                        <p class="text-xs text-gray-600">Dedicated high-deliverability US virtual phone numbers.</p>
                    </div>
                </label>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Step 2: Country Selection -->
            <div>
                <label class="block text-sm font-extrabold text-gray-800 mb-2 flex items-center justify-between">
                    <span>2. Select Country</span>
                    <span id="countryNotice" class="text-xs font-semibold text-teal-700"></span>
                </label>
                <select id="countrySelect" class="block w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-0 focus:border-[#0d9488] transition text-sm font-medium">
                    @if(isset($countries) && $countries->count() > 0)
                        @foreach($countries as $country)
                            <option value="{{ strtolower($country->slug) }}">{{ $country->name }}</option>
                        @endforeach
                    @else
                        <option value="usa">United States</option>
                    @endif
                </select>
            </div>
            
            <!-- Step 3: Service Selection -->
            <div>
                <label class="block text-sm font-extrabold text-gray-800 mb-2">3. Select Service</label>
                <div class="relative">
                    <input type="text" id="serviceSearch" class="block w-full px-4 py-2.5 mb-2 border-2 border-gray-200 rounded-xl focus:ring-0 focus:border-[#0d9488] transition text-xs font-medium" placeholder="🔍 Search service (e.g. whatsapp, telegram)..." disabled>
                    <select id="serviceSelect" class="block w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-0 focus:border-[#0d9488] transition text-sm font-medium" disabled size="5">
                        <option value="">Loading services...</option>
                    </select>
                </div>
            </div>

            <!-- Step 4: Operator Selection -->
            <div>
                <label class="block text-sm font-extrabold text-gray-800 mb-2">4. Select Operator</label>
                <select id="operatorSelect" class="block w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-0 focus:border-[#0d9488] transition text-sm font-medium" disabled>
                    <option value="">Select service first</option>
                </select>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6 pt-6 border-t border-gray-100">
            <div>
                <label class="block text-sm font-extrabold text-gray-800 mb-2">5. Promo Code (Optional)</label>
                <input type="text" id="promoCodeInput" class="block w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-0 focus:border-[#0d9488] transition text-sm font-mono" placeholder="e.g. WELCOME10">
                <div class="mt-1.5 text-xs text-gray-500">Enter a discount promo code if you have one.</div>
            </div>

            @if(($premiumEnabled ?? false))
                <div class="flex items-center">
                    <label class="inline-flex items-center gap-3 p-4 rounded-xl border border-teal-100 bg-teal-50/40 w-full cursor-pointer hover:bg-teal-50 transition">
                        <input type="checkbox" id="premiumToggle" class="w-5 h-5 rounded border-gray-300 text-[#0d9488] focus:ring-[#0d9488]">
                        <div>
                            <span class="block text-sm font-extrabold text-gray-800">Premium High-Speed SMS</span>
                            <span class="block text-xs text-gray-500">Priority routing for faster OTP delivery (+{{ (float)($premiumMarkupPercent ?? 40) }}% price)</span>
                        </div>
                    </label>
                </div>
            @endif
        </div>

        <!-- Price Display and Purchase Button -->
        <div class="mt-8 pt-6 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center bg-gray-50 p-6 rounded-2xl">
            <div class="mb-4 md:mb-0">
                <span class="text-gray-500 text-xs font-bold uppercase tracking-wider block">Estimated Price</span>
                <div class="text-3xl font-black text-[#0d9488]" id="priceDisplay">{{ $currencySymbol ?? '$' }}0.00</div>
            </div>
            
            <button id="buyBtn" disabled class="w-full md:w-auto bg-[#facc15] text-black px-10 py-3.5 rounded-xl font-black shadow-md hover:bg-yellow-400 hover:shadow-lg transition transform hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed text-base flex items-center justify-center gap-2">
                <span>Get Phone Number</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </button>
        </div>

        <div id="alertMessage" class="mt-4 hidden p-4 rounded-xl text-sm font-bold"></div>
    </div>
</div>

<!-- Active Orders Section -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-gray-50">
        <div>
            <h3 class="text-lg font-bold text-gray-800">Your Virtual Numbers</h3>
            <p class="text-xs text-gray-500">Real-time status updates and SMS codes</p>
        </div>
        <button onclick="location.reload()" class="text-xs text-[#0d9488] font-bold bg-teal-50 hover:bg-teal-100 px-3 py-2 rounded-xl transition flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
            Refresh
        </button>
    </div>
    
    <div class="p-0">
        @if($orders->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service / Provider</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone Number</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Charge</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SMS Code</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($orders as $order)
                        @php
                            $createdTs = optional($order->created_at)->getTimestamp() ?? time();
                            $isPending = in_array(strtoupper($order->status), ['PENDING', 'WAITING'], true);
                            $isReceived = in_array(strtoupper($order->status), ['RECEIVED', 'COMPLETED', 'FINISHED'], true);
                            $isCanceled = in_array(strtoupper($order->status), ['CANCELED', 'CANCELLED', 'TIMEOUT', 'REFUNDED'], true);
                            $providerLabel = strtolower((string)($order->provider ?? '')) === 'daisysms' ? 'May SMS (USA)' : (strtolower((string)($order->provider ?? '')) === 'herosms' ? 'May SMS (Global)' : 'May SMS');
                        @endphp
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-extrabold text-gray-900 capitalize">{{ $order->product }}</div>
                                <div class="text-xs text-gray-500 capitalize flex items-center gap-1.5 mt-0.5">
                                    <span>{{ $order->country }} ({{ $order->operator }})</span>
                                    <span class="px-2 py-0.2 rounded-full text-[10px] font-bold bg-gray-100 text-gray-600 border border-gray-200">{{ $providerLabel }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <span class="text-base font-mono font-black text-gray-800 select-all">{{ $order->phone_number }}</span>
                                    <button type="button" onclick="navigator.clipboard.writeText('{{ $order->phone_number }}'); showAlert('Phone number copied!')" class="inline-flex items-center gap-1 text-xs px-2.5 py-1 rounded-lg bg-teal-50 text-[#0d9488] hover:bg-teal-100 transition font-bold" title="Copy Phone Number">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                        Copy
                                    </button>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-[#0d9488]">{{ $currencySymbol ?? '$' }}{{ ($formatAmount ?? fn($x) => number_format($x, 2))($order->price) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($isPending)
                                    <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-yellow-100 text-yellow-800 animate-pulse">Waiting for SMS</span>
                                @elseif($isReceived)
                                    <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-green-100 text-green-800">Received</span>
                                @elseif($isCanceled)
                                    <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-red-100 text-red-800">Canceled / Refunded</span>
                                @else
                                    <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-gray-100 text-gray-800">{{ $order->status }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($order->sms_code)
                                    <span class="font-black text-lg tracking-wider text-[#0d9488] select-all">{{ $order->sms_code }}</span>
                                @else
                                    <span class="text-gray-400 italic">--</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div x-data="smsChecker({{ $order->id }}, @js($order->status), {{ $createdTs }})" x-init="initChecker()">
                                    <template x-if="status === 'pending' || status === 'PENDING'">
                                        <div class="flex flex-col items-end gap-2">
                                            <div class="flex items-center justify-end text-yellow-600 font-bold text-xs">
                                                <svg class="animate-spin h-3.5 w-3.5 mr-1.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                <span x-text="pollingMsg"></span>
                                            </div>
                                            <div class="flex items-center justify-end gap-2">
                                                <div x-show="showCountdown" x-transition.opacity class="text-[11px] text-gray-500 font-medium rounded-full bg-gray-100 px-3 py-1">
                                                    Cancel available: <span class="font-mono font-bold text-gray-800" x-text="countdownText"></span>
                                                </div>
                                                <button
                                                    type="button"
                                                    x-show="canCancel && !canceling"
                                                    x-transition
                                                    @click="handleCancel()"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-bold rounded-lg bg-red-600 hover:bg-red-700 text-white shadow-sm transition">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    Cancel & Refund
                                                </button>
                                                <span x-show="canceling" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-bold rounded-lg bg-gray-300 text-gray-600">
                                                    <svg class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                                    Canceling...
                                                </span>
                                            </div>
                                            <div x-show="cancelError" class="text-xs text-red-600" x-text="cancelError"></div>
                                        </div>
                                    </template>
                                    <template x-if="status === 'RECEIVED' || status === 'received' || status === 'completed' || status === 'COMPLETED'">
                                        <div class="flex items-center justify-end">
                                            <span class="inline-flex items-center gap-1 text-green-600 font-bold">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                                <span class="text-lg tracking-wider select-all font-mono font-black" x-text="smsCode"></span>
                                                <button type="button" @click="copyCode()" class="ml-2 text-xs px-2.5 py-1 rounded-lg bg-green-100 text-green-700 hover:bg-green-200 transition font-bold">Copy</button>
                                            </span>
                                        </div>
                                    </template>
                                    <template x-if="status === 'canceled' || status === 'CANCELED' || status === 'CANCELLED' || status === 'TIMEOUT'">
                                        <span class="text-red-500 font-bold text-xs">Canceled (Refunded)</span>
                                    </template>

                                    <div class="mt-2 flex items-center justify-end" x-data="{ reported: @js((bool)($order->is_reported ?? false)), submitting: false }">
                                        <template x-if="reported">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-800 border border-amber-200">
                                                🚩 Reported
                                            </span>
                                        </template>
                                        <template x-if="!reported && !['received', 'completed', 'finished'].includes((status || '').toLowerCase())">
                                            <button 
                                                type="button" 
                                                @click="
                                                    if (confirm('Report issue with this SMS order to admin?')) {
                                                        submitting = true;
                                                        fetch('/orders/report', {
                                                            method: 'POST',
                                                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                                            body: JSON.stringify({ order_type: 'sms', order_id: {{ $order->id }}, reason: 'SMS Verification issue reported from SMS page.' })
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
                                                class="inline-flex items-center gap-1 text-[11px] font-bold text-rose-700 hover:text-rose-900 bg-rose-50 hover:bg-rose-100 border border-rose-200 px-2.5 py-1 rounded-lg transition"
                                                :disabled="submitting">
                                                <span x-show="!submitting">🚩 Report Issue</span>
                                                <span x-show="submitting">Submitting...</span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-6 border-t border-gray-100">
                {{ $orders->links() }}
            </div>
        @else
            <div class="p-12 text-center text-gray-500">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 mb-4 text-gray-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                </div>
                <p class="font-medium">No active virtual numbers. Select a provider and service above to purchase a number.</p>
            </div>
        @endif
    </div>
</div>

<script>
    let currentServicesData = null;
    let selectedBasePrice = 0;
    let selectedPrice = 0;
    const currencySymbol = @js($currencySymbol ?? '$');
    const currencyFx = {{ (float) ($currencyFx ?? 1) }};
    const premiumEnabled = @js((bool) ($premiumEnabled ?? false));
    const premiumMarkupPercent = {{ (float) ($premiumMarkupPercent ?? 40) }};

    const countrySelect = document.getElementById('countrySelect');
    const countryNotice = document.getElementById('countryNotice');
    const serviceSearch = document.getElementById('serviceSearch');
    const serviceSelect = document.getElementById('serviceSelect');
    const operatorSelect = document.getElementById('operatorSelect');
    const priceDisplay = document.getElementById('priceDisplay');
    const buyBtn = document.getElementById('buyBtn');
    const alertMessage = document.getElementById('alertMessage');
    const promoCodeInput = document.getElementById('promoCodeInput');
    const premiumToggle = document.getElementById('premiumToggle');

    const favoriteServices = ['whatsapp', 'telegram', 'tiktok', 'facebook', 'instagram', 'google', 'twitter', 'discord'];

    function formatServiceName(service) {
        const names = {
            'whatsapp': 'WhatsApp',
            'telegram': 'Telegram',
            'tiktok': 'TikTok',
            'facebook': 'Facebook',
            'instagram': 'Instagram',
            'google': 'Google / Gmail',
            'twitter': 'Twitter / X',
            'discord': 'Discord',
            'openai': 'OpenAI / ChatGPT',
            'paypal': 'PayPal',
            'bigolive': 'Bigo Live',
            'doordash': 'DoorDash',
            'protonmail': 'ProtonMail',
            'kakaotalk': 'KakaoTalk',
            'other': 'Any Other',
        };
        const key = (service || '').toLowerCase();
        if (names[key]) {
            return names[key];
        }
        return service.charAt(0).toUpperCase() + service.slice(1);
    }

    function getSelectedProvider() {
        const selectedRadio = document.querySelector('input[name="sms_provider_choice"]:checked');
        return selectedRadio ? selectedRadio.value : 'herosms';
    }

    function round2(x) {
        return Math.round((x + Number.EPSILON) * 100) / 100;
    }

    function isPremiumSelected() {
        return premiumEnabled && premiumToggle && premiumToggle.checked;
    }

    function premiumMultiplier() {
        return isPremiumSelected() ? (1 + (premiumMarkupPercent / 100)) : 1;
    }

    function showAlert(message, isError = false) {
        alertMessage.textContent = message;
        alertMessage.className = `mt-4 p-4 rounded-xl text-sm font-bold ${isError ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-green-50 text-green-700 border border-green-200'}`;
        alertMessage.classList.remove('hidden');
        
        setTimeout(() => {
            alertMessage.classList.add('hidden');
        }, 5000);
    }

    function updateCountryFieldState() {
        const provider = getSelectedProvider();
        if (provider === 'daisysms') {
            countrySelect.value = 'usa';
            countrySelect.disabled = true;
            if (countryNotice) countryNotice.textContent = '🇺🇸 Locked to USA for May SMS (USA)';
        } else {
            countrySelect.disabled = false;
            if (countryNotice) countryNotice.textContent = '';
        }
    }

    async function loadServices() {
        updateCountryFieldState();

        serviceSearch.disabled = true;
        serviceSelect.innerHTML = '<option value="">Loading services...</option>';
        serviceSelect.disabled = true;
        operatorSelect.innerHTML = '<option value="">Select service first</option>';
        operatorSelect.disabled = true;
        buyBtn.disabled = true;
        priceDisplay.textContent = `${currencySymbol}0.00`;

        const provider = getSelectedProvider();
        const country = countrySelect.value || 'usa';
        
        try {
            const response = await fetch(`/api/sms/services?provider=${provider}&country=${country}`);
            const data = await response.json();
            currentServicesData = data;

            serviceSelect.innerHTML = '';
            
            const services = Object.keys(data).sort();
            
            if(services.length === 0) {
                serviceSelect.innerHTML = '<option value="">No services available (Configure API key in Admin Settings)</option>';
                return;
            }

            // Populate Favorites Group
            const favGroup = document.createElement('optgroup');
            favGroup.label = "⭐ Favorites";
            favoriteServices.forEach(fav => {
                const match = services.find(s => s.toLowerCase() === fav.toLowerCase());
                if(match) {
                    const opt = document.createElement('option');
                    opt.value = match;
                    opt.textContent = formatServiceName(match);
                    opt.className = "font-bold text-[#0d9488]";
                    favGroup.appendChild(opt);
                }
            });
            if(favGroup.children.length > 0) {
                serviceSelect.appendChild(favGroup);
            }

            // Populate All Services Group
            const allGroup = document.createElement('optgroup');
            allGroup.label = "All Services";
            services.forEach(service => {
                if(!favoriteServices.some(fav => fav.toLowerCase() === service.toLowerCase())) {
                    const opt = document.createElement('option');
                    opt.value = service;
                    opt.textContent = formatServiceName(service);
                    allGroup.appendChild(opt);
                }
            });
            if(allGroup.children.length > 0) {
                serviceSelect.appendChild(allGroup);
            }

            serviceSelect.disabled = false;
            serviceSearch.disabled = false;
            serviceSearch.value = '';
        } catch (error) {
            console.error('Error loading services', error);
            showAlert('Failed to load services for provider. Please try again.', true);
        }
    }

    function renderOperators(service, preserveOperator = null) {
        operatorSelect.innerHTML = '<option value="">Select an Operator</option>';
        selectedBasePrice = 0;
        selectedPrice = 0;
        priceDisplay.textContent = `${currencySymbol}0.00`;
        buyBtn.disabled = true;

        if (!service || !currentServicesData || !currentServicesData[service]) {
            operatorSelect.disabled = true;
            return;
        }

        const operators = Object.keys(currentServicesData[service]);
        const mult = premiumMultiplier();
        const currentCountry = (countrySelect.value || '').toLowerCase();
        const currentService = (service || '').toLowerCase();

        operators.forEach(op => {
            const details = currentServicesData[service][op];
            if (details.count > 0) {
                const base = parseFloat(details.cost);
                let effective = round2(base * mult);

                // Enforce price rules: Singapore WhatsApp = 20,000 NGN, any service < 1000 NGN = 1500 NGN
                const ngnRate = currencyFx > 1 ? currencyFx : 1500;
                if ((currentCountry === 'singapore' || currentCountry === 'sg') && (currentService === 'whatsapp' || currentService === 'wa')) {
                    effective = round2(20000 / ngnRate);
                } else if ((effective * ngnRate) < 1000) {
                    effective = round2(1500 / ngnRate);
                }

                const opt = document.createElement('option');
                opt.value = op;
                opt.dataset.basePrice = String(base);
                opt.dataset.price = String(effective);
                opt.textContent = `${op} - ${currencySymbol}${(effective * currencyFx).toFixed(2)} (${details.count} available)${isPremiumSelected() ? ' (Premium)' : ''}`;
                operatorSelect.appendChild(opt);
            }
        });

        operatorSelect.disabled = false;
        if (preserveOperator && Array.from(operatorSelect.options).some(opt => opt.value === preserveOperator)) {
            operatorSelect.value = preserveOperator;
            operatorSelect.dispatchEvent(new Event('change'));
        } else if (operatorSelect.options.length > 1) {
            operatorSelect.selectedIndex = 1;
            operatorSelect.dispatchEvent(new Event('change'));
        }
    }

    // Provider choice listener
    document.querySelectorAll('input[name="sms_provider_choice"]').forEach(radio => {
        radio.addEventListener('change', () => {
            loadServices();
        });
    });

    // Service search filter
    serviceSearch.addEventListener('input', (e) => {
        const searchTerm = e.target.value.toLowerCase();
        const options = serviceSelect.querySelectorAll('option');
        
        options.forEach(option => {
            if(option.value === "") return;
            const text = option.textContent.toLowerCase();
            
            if(text.includes(searchTerm)) {
                option.style.display = '';
            } else {
                option.style.display = 'none';
            }
        });
    });

    countrySelect.addEventListener('change', loadServices);

    serviceSelect.addEventListener('change', (e) => {
        const service = e.target.value;
        renderOperators(service);
    });

    operatorSelect.addEventListener('change', (e) => {
        const selectedOption = e.target.options[e.target.selectedIndex];
        if (selectedOption.value) {
            selectedBasePrice = parseFloat(selectedOption.dataset.basePrice || selectedOption.dataset.price || '0');
            selectedPrice = parseFloat(selectedOption.dataset.price || '0');
            if (!Number.isFinite(selectedPrice) || selectedPrice <= 0) {
                selectedPrice = round2(selectedBasePrice * premiumMultiplier());
            }
            priceDisplay.textContent = `${currencySymbol}${(selectedPrice * currencyFx).toFixed(2)}`;
            buyBtn.disabled = false;
        } else {
            selectedBasePrice = 0;
            selectedPrice = 0;
            priceDisplay.textContent = `${currencySymbol}0.00`;
            buyBtn.disabled = true;
        }
    });

    if (premiumToggle) {
        premiumToggle.addEventListener('change', () => {
            const service = serviceSelect.value;
            if (service) {
                renderOperators(service, operatorSelect.value);
            }
            if (selectedBasePrice > 0) {
                selectedPrice = round2(selectedBasePrice * premiumMultiplier());
                priceDisplay.textContent = `${currencySymbol}${(selectedPrice * currencyFx).toFixed(2)}`;
                buyBtn.disabled = false;
            }
        });
    }

    buyBtn.addEventListener('click', async () => {
        buyBtn.disabled = true;
        buyBtn.innerHTML = '<span class="inline-flex items-center gap-2"><svg class="animate-spin h-4 w-4 text-black" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Processing...</span>';

        try {
            const response = await fetch('/api/sms/buy', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    provider: getSelectedProvider(),
                    country: countrySelect.value,
                    product: serviceSelect.value,
                    operator: operatorSelect.value,
                    price: selectedPrice,
                    promo_code: promoCodeInput ? promoCodeInput.value : '',
                    is_premium: isPremiumSelected()
                })
            });

            const result = await response.json();

            if (result.success) {
                showAlert(result.message);
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert(result.message, true);
                buyBtn.disabled = false;
                buyBtn.textContent = 'Get Phone Number';
            }
        } catch (error) {
            showAlert('An error occurred during purchase.', true);
            buyBtn.disabled = false;
            buyBtn.textContent = 'Get Phone Number';
        }
    });

    // Initial load
    loadServices();
    document.addEventListener('alpine:init', () => {
        Alpine.data('smsChecker', (orderId, initialStatus, createdTs) => ({
            orderId: orderId,
            status: (initialStatus || 'PENDING').toString().toUpperCase(),
            smsCode: null,
            pollingMsg: 'Waiting for SMS...',
            minWaitSeconds: 120,
            cancelWindowSeconds: 1200,
            createdTs: createdTs || Math.floor(Date.now() / 1000),
            canCancel: false,
            showCountdown: false,
            countdownText: '',
            canceling: false,
            cancelError: null,
            
            initChecker() {
                this.refreshCancelState();

                if (this.status === 'PENDING' || this.status === 'WAITING') {
                    this.pollingMsg = 'Waiting for SMS...';
                    this.intervalId = setInterval(() => {
                        this.pollStatus();
                    }, 5000);
                    this.pollStatus();

                    this.countdownId = setInterval(() => {
                        this.refreshCancelState();
                    }, 1000);
                }
            },

            refreshCancelState() {
                const nowSec = Math.floor(Date.now() / 1000);
                const elapsed = Math.max(0, nowSec - (this.createdTs | 0));
                const maxRemaining = this.cancelWindowSeconds - elapsed;

                if (maxRemaining <= 0 || (this.status !== 'PENDING' && this.status !== 'WAITING')) {
                    this.canCancel = false;
                    this.showCountdown = false;
                    this.countdownText = '';
                    if (this.countdownId && maxRemaining <= 0) {
                        clearInterval(this.countdownId);
                        this.countdownId = null;
                    }
                    return;
                }

                if (elapsed < this.minWaitSeconds) {
                    this.canCancel = false;
                    this.showCountdown = true;
                    const waitLeft = this.minWaitSeconds - elapsed;
                    const mm = Math.floor(waitLeft / 60).toString().padStart(2, '0');
                    const ss = (waitLeft % 60).toString().padStart(2, '0');
                    this.countdownText = `Cancel in ${mm}:${ss}`;
                } else {
                    this.canCancel = true;
                    this.showCountdown = true;
                    const mm = Math.floor(maxRemaining / 60).toString().padStart(2, '0');
                    const ss = (maxRemaining % 60).toString().padStart(2, '0');
                    this.countdownText = `Expires in ${mm}:${ss}`;
                }
            },

            async handleCancel() {
                if (!this.canCancel || this.canceling) return;
                this.canceling = true;
                this.cancelError = null;

                try {
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
                    const response = await fetch(`/api/sms/cancel/${this.orderId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                        },
                    });
                    const data = await response.json();
                    if (data && data.success) {
                        this.status = 'CANCELED';
                        this.canCancel = false;
                        this.showCountdown = false;
                        clearInterval(this.intervalId);
                        if (this.countdownId) clearInterval(this.countdownId);
                        showAlert('Order canceled and refunded successfully.', true);
                        setTimeout(() => location.reload(), 1200);
                    } else {
                        this.cancelError = (data && data.message) ? data.message : 'Could not cancel this order.';
                        if (this.cancelError && /expired/i.test(this.cancelError)) {
                            this.canCancel = false;
                            this.showCountdown = false;
                        }
                    }
                } catch (error) {
                    console.error('Cancel error:', error);
                    this.cancelError = 'Network error while canceling. Please refresh and try again.';
                } finally {
                    this.canceling = false;
                }
            },
            
            async pollStatus() {
                if (this.status !== 'PENDING' && this.status !== 'WAITING') {
                    clearInterval(this.intervalId);
                    return;
                }

                try {
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
                    const response = await fetch(`/api/sms/check/${this.orderId}`, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                        },
                    });
                    
                    const data = await response.json();
                    const s = (data?.status || '').toString().toUpperCase();
                    
                    if (s === 'RECEIVED' || s === 'COMPLETED' || s === 'FINISHED') {
                        this.status = 'RECEIVED';
                        this.smsCode = data?.sms_code || this.smsCode;
                        this.canCancel = false;
                        this.showCountdown = false;
                        clearInterval(this.intervalId);
                        if (this.countdownId) clearInterval(this.countdownId);
                        showAlert(`SMS Received! Code: ${this.smsCode}`);
                        setTimeout(() => location.reload(), 1500);
                    } else if (s === 'CANCELED' || s === 'CANCELLED' || s === 'TIMEOUT') {
                        this.status = 'CANCELED';
                        this.canCancel = false;
                        this.showCountdown = false;
                        clearInterval(this.intervalId);
                        if (this.countdownId) clearInterval(this.countdownId);
                        showAlert('Order was canceled and refunded.', true);
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        this.pollingMsg = 'Waiting for SMS...';
                    }
                } catch (error) {
                    console.error('Error polling SMS status:', error);
                    this.pollingMsg = 'Connection issue… retrying';
                }
            },

            copyCode() {
                if (!this.smsCode) return;
                const v = String(this.smsCode);
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(v).catch(() => {});
                }
                showAlert(`Copied code: ${v}`);
            }
        }));
    });
</script>
@endsection
