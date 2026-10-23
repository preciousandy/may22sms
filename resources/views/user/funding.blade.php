@extends('layouts.dashboard')

@section('page_title', 'Add Funds')

@section('dashboard_content')
@php
    $initialMethod = old('payment_method', $defaultFundingMethod ?? 'monietude');
    $initialPayCurrency = old('pay_currency', 'usdttrc20');
    $enabledFundingMethods = is_array($enabledFundingMethods ?? null) ? $enabledFundingMethods : ['monietude', 'paymentpoint', 'pocketfi', 'strollwallet', 'crypto'];
    if (! in_array($initialMethod, $enabledFundingMethods, true)) {
        $initialMethod = $enabledFundingMethods[0] ?? 'monietude';
    }
    $symbol = ($currency ?? 'USD') === 'NGN' ? '₦' : '$';
    $displayBalance = ($currency ?? 'USD') === 'NGN'
        ? (auth()->user()->walletBalance ?? 0) * ($ngnPerUsd ?? 1500)
        : (auth()->user()->walletBalance ?? 0);
    $charges = [
        'paymentpoint' => \App\Models\FundingTransaction::getChargePercent('paymentpoint'),
        'pocketfi' => \App\Models\FundingTransaction::getChargePercent('pocketfi'),
        'strollwallet' => \App\Models\FundingTransaction::getChargePercent('strollwallet'),
        'monietude' => \App\Models\FundingTransaction::getChargePercent('monietude'),
        'crypto' => \App\Models\FundingTransaction::getChargePercent('crypto'),
    ];
@endphp

<div x-data="{
    method: @js($initialMethod),
    amount: @js(old('amount', '')),
    currency: @js($currency ?? 'USD'),
    symbol: @js($symbol),
    charges: @js($charges),
    ngnPerUsd: {{ (float) ($ngnPerUsd ?? 1500) }},
    get activeChargePercent() { return Number(this.charges[this.method] || 0); },
    get chargeFeeAmount() { return (Number(this.amount || 0) * (this.activeChargePercent / 100)).toFixed(2); },
    get netCreditedAmount() { return Math.max(0, Number(this.amount || 0) - Number(this.chargeFeeAmount)).toFixed(2); },
    get minUsd() { return this.method === 'crypto' ? 1 : (100 / Number(this.ngnPerUsd || 1)); },
    get minNgn() { return this.method === 'crypto' ? Number(this.ngnPerUsd || 0) : 100; },
    formatUsd(v) { return Number(v || 0).toFixed(2); },
    formatNgn(v) { return Number(v || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
}">
@if(isset($userVirtualAccounts) && count($userVirtualAccounts) > 0)
    <div class="mb-6 bg-white rounded-2xl shadow-lg border-2 border-[#0d9488] overflow-hidden">
        <div class="bg-[#0d9488] px-6 py-4">
            <div class="flex items-center justify-between">
                <h3 class="text-white font-bold text-lg">Your Virtual Account</h3>
                <div class="flex items-center gap-2">
                    <a href="/tutorials?tab=funding" class="bg-white/20 hover:bg-white/30 text-white text-xs font-bold px-3 py-1 rounded-full transition flex items-center gap-1">
                        <span>🎓</span>
                        <span>How It Works</span>
                    </a>
                    <span class="bg-white/20 text-white text-xs font-bold px-3 py-1 rounded-full">Active</span>
                </div>
            </div>
            <p class="text-teal-100 text-sm mt-1">Use this account for all your deposits</p>
        </div>
        <div class="p-6">
            @foreach(['monietude', 'paymentpoint', 'strollwallet'] as $method)
                @if(isset($userVirtualAccounts[$method]))
                    <div x-show="method === '{{ $method }}'" style="{{ $initialMethod !== $method ? 'display:none;' : '' }}">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div class="bg-gray-50 rounded-xl p-4">
                                <div class="text-xs text-gray-500 uppercase tracking-wide mb-1">Bank</div>
                                <div class="font-bold text-gray-900 text-lg">{{ $userVirtualAccounts[$method]['bank_name'] ?? 'PalmPay' }}</div>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-4">
                                <div class="text-xs text-gray-500 uppercase tracking-wide mb-1">Account Name</div>
                                <div class="font-bold text-gray-900 text-lg">{{ $userVirtualAccounts[$method]['account_name'] ?? '' }}</div>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-4">
                                <div class="text-xs text-gray-500 uppercase tracking-wide mb-1">Account Number</div>
                                <div class="flex items-center justify-between">
                                    <div class="font-bold text-gray-900 text-lg font-mono">{{ $userVirtualAccounts[$method]['account_number'] ?? '' }}</div>
                                    <button type="button" @click="navigator.clipboard.writeText('{{ $userVirtualAccounts[$method]['account_number'] ?? '' }}'); alert('Account number copied!')" class="text-xs bg-gray-200 hover:bg-gray-300 text-gray-700 px-2.5 py-1 rounded-md font-medium transition">Copy</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-4">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div>
                        <div class="font-bold text-blue-900">How to fund</div>
                        <div class="text-blue-800 text-sm">Send any amount to this account. Your wallet will be credited automatically once payment is received.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

@if(session('virtual_account'))
    @php $va = session('virtual_account'); @endphp
    <div class="mb-6 bg-white rounded-2xl shadow-lg border-2 border-[#0d9488] overflow-hidden">
        <div class="bg-[#0d9488] px-6 py-4">
            <div class="flex items-center justify-between">
                <h3 class="text-white font-bold text-lg">Deposit Request Created</h3>
                <span class="bg-white/20 text-white text-xs font-bold px-3 py-1 rounded-full">Waiting Payment</span>
            </div>
            <p class="text-teal-100 text-sm mt-1">Reference: <span class="font-mono">{{ $va['reference'] ?? '' }}</span></p>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-gray-50 rounded-xl p-4">
                    <div class="text-xs text-gray-500 uppercase tracking-wide mb-1">Bank</div>
                    <div class="font-bold text-gray-900 text-lg">{{ $va['bank_name'] ?? 'Palmpay' }}</div>
                </div>
                <div class="bg-gray-50 rounded-xl p-4">
                    <div class="text-xs text-gray-500 uppercase tracking-wide mb-1">Account Name</div>
                    <div class="font-bold text-gray-900 text-lg">{{ $va['account_name'] ?? '' }}</div>
                </div>
                <div class="bg-gray-50 rounded-xl p-4">
                    <div class="text-xs text-gray-500 uppercase tracking-wide mb-1">Account Number</div>
                    <div class="font-bold text-gray-900 text-lg">{{ $va['account_number'] ?? '' }}</div>
                </div>
            </div>
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-4">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <div>
                        <div class="font-bold text-yellow-900">Send Payment</div>
                        <div class="text-yellow-800 text-sm">Send any amount to the account above. Your wallet will be credited automatically once payment is received.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

@if(session('success'))
    <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl shadow-sm">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl shadow-sm">
        {{ session('error') }}
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

<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-800">Deposit Funds</h3>
            <div class="text-sm text-gray-500">Current Balance: <span class="font-bold text-[#0d9488]">{{ $symbol }}{{ number_format($displayBalance, 2) }}</span></div>
        </div>
        
        <div class="p-8">
            <form action="/funding" method="POST" data-loader="true" data-loader-title="Initiating Deposit..." data-loader-subtitle="Connecting to secure payment gateway">
                @csrf

                <h4 class="text-md font-medium text-gray-700 mb-4">1. Select Payment Method</h4>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
                    <!-- Monietude PalmPay (Account 1) -->
                    @if(in_array('monietude', $enabledFundingMethods, true))
                    <label class="relative cursor-pointer">
                        <input type="radio" name="payment_method" value="monietude" class="peer sr-only" x-model="method">
                        <div class="rounded-xl border-2 border-gray-200 p-4 peer-checked:border-[#0d9488] peer-checked:bg-teal-50 hover:bg-gray-50 transition flex flex-col items-center justify-center text-center h-32">
                            <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center shadow-sm mb-2 text-emerald-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <span class="font-bold text-gray-800">Account 1</span>
                            <span class="text-[10px] text-teal-600 font-semibold mt-0.5">PalmPay</span>
                        </div>
                        <div class="absolute top-2 right-2 hidden peer-checked:block text-[#0d9488]">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        </div>
                    </label>
                    @endif

                    <!-- PaymentPoint (Account 2) -->
                    @if(in_array('paymentpoint', $enabledFundingMethods, true))
                    <label class="relative cursor-pointer">
                        <input type="radio" name="payment_method" value="paymentpoint" class="peer sr-only" x-model="method">
                        <div class="rounded-xl border-2 border-gray-200 p-4 peer-checked:border-[#0d9488] peer-checked:bg-teal-50 hover:bg-gray-50 transition flex flex-col items-center justify-center text-center h-32">
                            <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center shadow-sm mb-2 text-[#0d9488]">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                            </div>
                            <span class="font-bold text-gray-800">Account 2</span>
                        </div>
                        <div class="absolute top-2 right-2 hidden peer-checked:block text-[#0d9488]">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        </div>
                    </label>
                    @endif

                    <!-- PocketFi (Account 3) -->
                    @if(in_array('pocketfi', $enabledFundingMethods, true))
                    <label class="relative cursor-pointer">
                        <input type="radio" name="payment_method" value="pocketfi" class="peer sr-only" x-model="method">
                        <div class="rounded-xl border-2 border-gray-200 p-4 peer-checked:border-[#0d9488] peer-checked:bg-teal-50 hover:bg-gray-50 transition flex flex-col items-center justify-center text-center h-32">
                            <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center shadow-sm mb-2 text-indigo-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                            </div>
                            <span class="font-bold text-gray-800">Account 3</span>
                        </div>
                        <div class="absolute top-2 right-2 hidden peer-checked:block text-[#0d9488]">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        </div>
                    </label>
                    @endif

                    <!-- Strollwallet (Account 4) -->
                    @if(in_array('strollwallet', $enabledFundingMethods, true))
                    <label class="relative cursor-pointer">
                        <input type="radio" name="payment_method" value="strollwallet" class="peer sr-only" x-model="method">
                        <div class="rounded-xl border-2 border-gray-200 p-4 peer-checked:border-[#0d9488] peer-checked:bg-teal-50 hover:bg-gray-50 transition flex flex-col items-center justify-center text-center h-32">
                            <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center shadow-sm mb-2 text-purple-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            </div>
                            <span class="font-bold text-gray-800">Account 4</span>
                        </div>
                        <div class="absolute top-2 right-2 hidden peer-checked:block text-[#0d9488]">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        </div>
                    </label>
                    @endif

                    <!-- Crypto Funding -->
                    @if(in_array('crypto', $enabledFundingMethods, true))
                    <label class="relative cursor-pointer">
                        <input type="radio" name="payment_method" value="crypto" class="peer sr-only" x-model="method">
                        <div class="rounded-xl border-2 border-gray-200 p-4 peer-checked:border-[#0d9488] peer-checked:bg-teal-50 hover:bg-gray-50 transition flex flex-col items-center justify-center text-center h-32">
                            <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center shadow-sm mb-2 text-yellow-500">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            </div>
                            <span class="font-bold text-gray-800">Crypto</span>
                            <span class="text-[10px] text-yellow-600 font-semibold mt-0.5">USDT/BTC</span>
                        </div>
                        <div class="absolute top-2 right-2 hidden peer-checked:block text-[#0d9488]">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        </div>
                    </label>
                    @endif
                </div>

                <div class="mb-8" x-show="method === 'crypto'" style="display:none;">
                    <h4 class="text-md font-medium text-gray-700 mb-4">2. Select Crypto</h4>
                    <div class="max-w-md">
                        <select name="pay_currency" class="w-full rounded-xl border-2 border-gray-200 px-4 py-3 focus:ring-0 focus:border-[#0d9488] font-bold text-gray-900">
                            <option value="usdttrc20" {{ $initialPayCurrency === 'usdttrc20' ? 'selected' : '' }}>USDT TRC20</option>
                            <option value="usdtbep20" {{ $initialPayCurrency === 'usdtbep20' ? 'selected' : '' }}>USDT BEP20</option>
                            <option value="btc" {{ $initialPayCurrency === 'btc' ? 'selected' : '' }}>Bitcoin (BTC)</option>
                            <option value="eth" {{ $initialPayCurrency === 'eth' ? 'selected' : '' }}>Ethereum (ETH)</option>
                            <option value="trx" {{ $initialPayCurrency === 'trx' ? 'selected' : '' }}>TRON (TRX)</option>
                            <option value="ltc" {{ $initialPayCurrency === 'ltc' ? 'selected' : '' }}>Litecoin (LTC)</option>
                        </select>
                        <p class="text-sm text-gray-500 mt-2">You will be redirected to the payment page after creating the invoice.</p>
                    </div>
                </div>

                <div class="mb-8" x-show="method === 'crypto' || method === 'pocketfi' || method === 'paymentpoint' || method === 'strollwallet' || method === 'monietude'">
                    <h4 class="text-md font-medium text-gray-700 mb-4">2. Enter Amount ({{ $currency ?? 'USD' }})</h4>
                    <div class="relative max-w-md">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <span class="text-gray-500 font-bold text-lg">{{ $symbol }}</span>
                        </div>
                        <input type="number" name="amount" min="1" x-model="amount" value="{{ old('amount') }}" step="{{ ($currency ?? 'USD') === 'NGN' ? '1' : '0.01' }}" class="block w-full pl-10 pr-4 py-4 border-2 border-gray-200 rounded-xl focus:ring-0 focus:border-[#0d9488] text-lg font-bold text-gray-900 transition" placeholder="0.00" x-bind:required="method === 'crypto' || method === 'pocketfi' || method === 'paymentpoint' || method === 'strollwallet' || method === 'monietude'">
                    </div>
                    <p class="text-sm text-gray-500 mt-2">
                        Minimum deposit:
                        <span x-show="currency === 'NGN'" style="display:none;">
                            ₦<span x-text="formatNgn(minNgn)"></span> (≈ $<span x-text="formatUsd(minUsd)"></span>)
                        </span>
                        <span x-show="currency !== 'NGN'" style="display:none;">
                            $<span x-text="formatUsd(minUsd)"></span> (≈ ₦<span x-text="formatNgn(minNgn)"></span>)
                        </span>
                    </p>
                    <p class="text-xs text-gray-400 mt-1" x-show="currency === 'NGN' && amount">
                        Approx USD: $<span x-text="(Number(amount || 0) / Number(ngnPerUsd || 1)).toFixed(2)"></span>
                    </p>
                </div>

                <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm" x-show="method === 'paymentpoint'">Submit request and pay via PaymentPoint.</p>
                        <p class="text-gray-500 text-sm" x-show="method === 'strollwallet'" style="display:none;">Submit request and pay via Strollwallet.</p>
                        <p class="text-gray-500 text-sm" x-show="method === 'crypto'" style="display:none;">Submit request and pay via crypto transfer.</p>
                        <p class="text-gray-500 text-sm" x-show="method === 'pocketfi'" style="display:none;">Submit request and pay via PocketFi payment gateway.</p>
                    </div>
                    <button type="submit" class="bg-[#facc15] text-black px-8 py-3 rounded-xl font-bold shadow-md hover:bg-yellow-400 hover:shadow-lg transition transform hover:-translate-y-0.5">
                        Create Funding Request
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800">Funding History</h3>
        </div>
        @if(isset($funding) && $funding->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($funding as $tx)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $tx->created_at->format('M d, Y H:i') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 capitalize">{{ $tx->payment_method }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ $tx->currency }} {{ number_format($tx->amount, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-700">{{ $tx->reference }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $tx->status }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if(in_array($tx->status, ['pending', 'initiated', 'waiting_payment']))
                                        <div class="flex flex-wrap items-center gap-2">
                                            @if(($tx->payment_method === 'pocketfi' || $tx->provider === 'pocketfi') && $tx->provider_payment_id)
                                                <form action="{{ route('funding.check-status', $tx->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="bg-teal-500 hover:bg-teal-600 text-white px-3 py-1 rounded-lg text-sm font-bold transition">Check Status</button>
                                                </form>
                                            @endif
                                            <form action="{{ route('funding.cancel', $tx->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-lg text-sm font-bold transition">Cancel</button>
                                            </form>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-5 border-t border-gray-100 bg-gray-50">
                {{ $funding->links() }}
            </div>
        @else
            <div class="p-10 text-center text-gray-500">No funding history yet.</div>
        @endif
    </div>

    <div class="bg-blue-50 border border-blue-100 rounded-xl p-6 flex items-start">
        <svg class="w-6 h-6 text-blue-500 mt-0.5 mr-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <div>
            <h4 class="font-medium text-blue-900 mb-1">Secure Transactions</h4>
            <p class="text-sm text-blue-800 opacity-90">PaymentPoint, PocketFi, Strollwallet, PalmPay (Monietude), and Crypto deposits are credited automatically once payment is confirmed. Keep your reference for support.</p>
        </div>
    </div>
</div>
</div>
@endsection
