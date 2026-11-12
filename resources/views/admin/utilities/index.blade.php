@extends('layouts.admin')

@section('admin_content')
@push('header_title', 'Utilities & VTU Profit Management')

<div class="space-y-6">

    <!-- Top Header & Quick Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2">
                <span>⚡ Utilities Profit &amp; VTU Ledger</span>
                @if($simulate)
                    <span class="text-[10px] font-black uppercase px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 border border-amber-200">
                        Sandbox / Simulation Mode
                    </span>
                @else
                    <span class="text-[10px] font-black uppercase px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">
                        Live Production
                    </span>
                @endif
            </h1>
            <p class="text-xs text-gray-500 mt-1">
                Manage service markups, convenience fees, provider wholesale costs, and real-time gross profit margins.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" 
                    onclick="document.getElementById('pricing-panel').classList.toggle('hidden'); document.getElementById('pricing-panel').scrollIntoView({behavior: 'smooth'})"
                    class="px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-extrabold text-xs uppercase tracking-wider transition shadow-sm flex items-center gap-1.5 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Pricing &amp; Markup Rules</span>
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center justify-between shadow-xs">
            <span>✓ {{ session('success') }}</span>
            <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 cursor-pointer">✕</button>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold flex items-center justify-between shadow-xs">
            <span>⚠️ {{ session('error') }}</span>
            <button onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-900 cursor-pointer">✕</button>
        </div>
    @endif

    <!-- Time Range Filter Pills for Profit Metrics -->
    <div class="flex items-center justify-between gap-3 overflow-x-auto pb-1">
        <div class="flex items-center gap-1.5 bg-white p-1 rounded-2xl border border-gray-200 shadow-xs">
            @php
                $periods = [
                    'all' => 'All Time',
                    'today' => 'Today',
                    '7_days' => 'Past 7 Days',
                    '30_days' => 'Past 30 Days',
                    'this_month' => 'This Month',
                ];
            @endphp
            @foreach($periods as $pkey => $plabel)
                <a href="{{ request()->fullUrlWithQuery(['period' => $pkey, 'date_from' => null, 'date_to' => null]) }}" 
                   class="px-3.5 py-1.5 rounded-xl text-xs font-extrabold transition {{ ($period ?? 'all') === $pkey ? 'bg-teal-600 text-white shadow-xs' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                    {{ $plabel }}
                </a>
            @endforeach
        </div>

        <!-- Live BigiSub Wallet Pill -->
        <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-2xl border border-gray-200 text-xs font-bold shadow-xs whitespace-nowrap">
            <span class="text-gray-400 uppercase text-[10px]">BigiSub Balance:</span>
            <span class="font-mono font-black text-teal-700">
                @if(isset($providerBalance['balance']))
                    ₦{{ number_format((float)$providerBalance['balance'], 2) }}
                @else
                    <span class="text-gray-400">Unavailable</span>
                @endif
            </span>
        </div>
    </div>

    <!-- Profit Overview & Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Metric 1: Realized Gross Profit -->
        <div class="bg-gradient-to-br from-teal-900 via-teal-800 to-slate-900 text-white p-5 rounded-3xl shadow-sm border border-teal-700/40 relative overflow-hidden">
            <div class="relative z-10">
                <span class="text-[10px] font-black uppercase tracking-widest text-teal-300 block">Realized Gross Profit</span>
                <div class="text-2xl sm:text-3xl font-black text-white mt-1 tracking-tight">
                    ₦{{ number_format((float)$totalRealizedProfitNgn, 2) }}
                </div>
                <div class="text-xs font-bold text-teal-200 mt-1 flex items-center gap-1.5">
                    <span class="px-2 py-0.5 rounded-md bg-teal-500/20 text-teal-300 font-mono text-[11px] font-black">
                        ${{ number_format((float)$totalRealizedProfitUsd, 4) }} USD
                    </span>
                    <span>from {{ number_format($totalSuccessful) }} orders</span>
                </div>
            </div>
            <div class="absolute -right-4 -bottom-4 opacity-10 pointer-events-none">
                <svg class="w-24 h-24 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"/></svg>
            </div>
        </div>

        <!-- Metric 2: Customer Charges vs Provider Costs -->
        <div class="bg-white p-5 rounded-3xl border border-gray-200 shadow-sm space-y-2">
            <span class="text-[10px] font-black uppercase tracking-wider text-gray-400 block">Customer Debits vs Costs</span>
            <div>
                <span class="text-[10px] text-gray-500 uppercase font-bold">Total Customer Debited:</span>
                <div class="text-lg font-black text-gray-900">₦{{ number_format((float)$totalCustomerChargedNgn, 2) }}</div>
            </div>
            <div class="pt-1.5 border-t border-gray-100 flex justify-between text-xs">
                <span class="text-gray-500 font-semibold">Provider Cost:</span>
                <span class="font-mono font-bold text-slate-800">₦{{ number_format((float)$totalProviderCostNgn, 2) }}</span>
            </div>
        </div>

        <!-- Metric 3: Pipeline / Pending Profit -->
        <div class="bg-white p-5 rounded-3xl border border-gray-200 shadow-sm space-y-2">
            <span class="text-[10px] font-black uppercase tracking-wider text-gray-400 block">Pending Orders Profit</span>
            <div class="text-xl font-black text-amber-600 mt-1">
                ₦{{ number_format((float)$estimatedPendingProfitNgn, 2) }}
            </div>
            <div class="text-[11px] font-semibold text-gray-500">
                {{ $totalPending }} in-flight / uncertain orders
            </div>
            <div class="pt-1 border-t border-gray-100 text-[10px] text-gray-400">
                Reversed automatically if provider confirms failure.
            </div>
        </div>

        <!-- Metric 4: Failed & Refunded Losses Avoided -->
        <div class="bg-white p-5 rounded-3xl border border-gray-200 shadow-sm space-y-2">
            <span class="text-[10px] font-black uppercase tracking-wider text-gray-400 block">Failed / Refunded Ledger</span>
            <div class="flex items-center gap-3">
                <div>
                    <span class="text-[10px] text-rose-500 font-bold uppercase block">Failed:</span>
                    <span class="text-lg font-black text-rose-600">{{ $totalFailed }}</span>
                </div>
                <div class="h-8 w-px bg-gray-200"></div>
                <div>
                    <span class="text-[10px] text-blue-500 font-bold uppercase block">Refunded:</span>
                    <span class="text-lg font-black text-blue-600">{{ $totalRefunded }}</span>
                </div>
            </div>
            <div class="text-[10px] text-gray-400 pt-1 border-t border-gray-100">
                Customer debits credited back to wallets in full.
            </div>
        </div>
    </div>

    <!-- Per-Service Profit Breakdown Ribbon -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-gray-200 shadow-sm">
        <h4 class="text-xs font-black uppercase tracking-wider text-gray-500 mb-3 flex items-center justify-between">
            <span>Service-by-Service Realized Gross Profit</span>
            <span class="text-[10px] text-teal-600 font-bold">Auto-calculated server-side</span>
        </h4>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <!-- Data Bundles -->
            <div class="p-3.5 rounded-2xl bg-teal-50/70 border border-teal-100">
                <span class="text-[10px] font-black uppercase text-teal-700 block">🌐 Data Bundles</span>
                <div class="text-lg font-black text-teal-900 mt-1">₦{{ number_format((float)$dataProfitNgn, 2) }}</div>
                <span class="text-[10px] text-teal-600 font-semibold block mt-0.5">
                    Config: {{ $pricingSettings['utility_data_pricing_mode'] === 'fixed' ? '₦' . number_format((float)$pricingSettings['utility_data_markup_value']) . '/plan' : $pricingSettings['utility_data_markup_value'] . '%' }} markup
                </span>
            </div>

            <!-- Airtime -->
            <div class="p-3.5 rounded-2xl bg-blue-50/70 border border-blue-100">
                <span class="text-[10px] font-black uppercase text-blue-700 block">📶 Airtime VTU</span>
                <div class="text-lg font-black text-blue-900 mt-1">₦{{ number_format((float)$airtimeProfitNgn, 2) }}</div>
                <span class="text-[10px] text-blue-600 font-semibold block mt-0.5">
                    Discount: {{ $pricingSettings['utility_airtime_discount_percent'] }}% | Provider disc: {{ $pricingSettings['utility_airtime_provider_discount_percent'] }}%
                </span>
            </div>

            <!-- Electricity -->
            <div class="p-3.5 rounded-2xl bg-amber-50/70 border border-amber-100">
                <span class="text-[10px] font-black uppercase text-amber-700 block">⚡ Electricity</span>
                <div class="text-lg font-black text-amber-900 mt-1">₦{{ number_format((float)$electricityProfitNgn, 2) }}</div>
                <span class="text-[10px] text-amber-600 font-semibold block mt-0.5">
                    Convenience Fee: {{ $pricingSettings['utility_electricity_fee_mode'] === 'fixed' ? '₦' . number_format((float)$pricingSettings['utility_electricity_fee_value']) : $pricingSettings['utility_electricity_fee_value'] . '%' }}
                </span>
            </div>

            <!-- Betting -->
            <div class="p-3.5 rounded-2xl bg-purple-50/70 border border-purple-100">
                <span class="text-[10px] font-black uppercase text-purple-700 block">⚽ Betting Top-Up</span>
                <div class="text-lg font-black text-purple-900 mt-1">₦{{ number_format((float)$bettingProfitNgn, 2) }}</div>
                <span class="text-[10px] text-purple-600 font-semibold block mt-0.5">
                    Processing Fee: {{ $pricingSettings['utility_betting_fee_mode'] === 'percent' ? $pricingSettings['utility_betting_fee_value'] . '%' : '₦' . $pricingSettings['utility_betting_fee_value'] }} {{ (int)$pricingSettings['utility_betting_enabled'] === 1 ? '(Active)' : '(Disabled)' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Pricing & Markup Rules Panel (Collapsible) -->
    <div id="pricing-panel" class="hidden bg-white rounded-3xl border border-gray-200 p-6 sm:p-8 shadow-md space-y-6">
        <div class="flex items-center justify-between border-b border-gray-100 pb-4">
            <div>
                <h3 class="text-base font-black text-gray-900 uppercase tracking-wider flex items-center gap-2">
                    <span>⚙️ Utility Pricing, Markup &amp; Gateway Configuration</span>
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">
                    Configure markups, convenience fees, and limits for each service independently. All changes are logged for auditing.
                </p>
            </div>
            <button type="button" 
                    onclick="document.getElementById('pricing-panel').classList.add('hidden')" 
                    class="text-gray-400 hover:text-gray-600 text-sm font-black p-2 rounded-xl hover:bg-gray-100 cursor-pointer">
                ✕ Close
            </button>
        </div>

        <form action="{{ route('admin.utilities.settings') }}" method="POST" class="space-y-6">
            @csrf

            <!-- 1. Data Bundles Pricing Section -->
            <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">🌐</span>
                        <h4 class="text-xs font-black uppercase tracking-wider text-slate-800">1. Data Bundles Markup Configuration</h4>
                    </div>
                    <label class="flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
                        <input type="checkbox" name="utility_data_enabled" value="1" {{ (int)($pricingSettings['utility_data_enabled'] ?? 1) === 1 ? 'checked' : '' }} class="rounded text-teal-600 focus:ring-teal-500 w-4 h-4">
                        <span>Service Active</span>
                    </label>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-[11px] font-black uppercase text-gray-700 mb-1">Pricing Method</label>
                        <select name="utility_data_pricing_mode" class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-gray-200 bg-white">
                            <option value="fixed" {{ ($pricingSettings['utility_data_pricing_mode'] ?? 'fixed') === 'fixed' ? 'selected' : '' }}>Fixed Fee (₦ per plan)</option>
                            <option value="percent" {{ ($pricingSettings['utility_data_pricing_mode'] ?? 'fixed') === 'percent' ? 'selected' : '' }}>Percentage Markup (% of wholesale)</option>
                        </select>
                        <span class="text-[10px] text-gray-400 mt-0.5 block">Recommended: Fixed ₦40</span>
                    </div>

                    <div>
                        <label class="block text-[11px] font-black uppercase text-gray-700 mb-1">Markup Amount / Percent</label>
                        <input type="number" step="0.5" min="0" max="10000" name="utility_data_markup_value" value="{{ old('utility_data_markup_value', $pricingSettings['utility_data_markup_value'] ?? 40.0) }}" required class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-gray-200">
                        <span class="text-[10px] text-gray-400 mt-0.5 block">E.g. 40 = ₦40 profit or 10 = 10%</span>
                    </div>

                    <div>
                        <label class="block text-[11px] font-black uppercase text-gray-700 mb-1">Min Price Limit (₦)</label>
                        <input type="number" step="10" min="1" name="utility_data_min_amount" value="{{ old('utility_data_min_amount', $pricingSettings['utility_data_min_amount'] ?? 50.0) }}" required class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-gray-200">
                    </div>

                    <div>
                        <label class="block text-[11px] font-black uppercase text-gray-700 mb-1">Max Price Limit (₦)</label>
                        <input type="number" step="1000" min="100" name="utility_data_max_amount" value="{{ old('utility_data_max_amount', $pricingSettings['utility_data_max_amount'] ?? 100000.0) }}" required class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-gray-200">
                    </div>
                </div>
            </div>

            <!-- 2. Airtime VTU Pricing Section -->
            <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">📶</span>
                        <h4 class="text-xs font-black uppercase tracking-wider text-slate-800">2. Airtime VTU Pricing &amp; Discounts</h4>
                    </div>
                    <label class="flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
                        <input type="checkbox" name="utility_airtime_enabled" value="1" {{ (int)($pricingSettings['utility_airtime_enabled'] ?? 1) === 1 ? 'checked' : '' }} class="rounded text-teal-600 focus:ring-teal-500 w-4 h-4">
                        <span>Service Active</span>
                    </label>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-[11px] font-black uppercase text-gray-700 mb-1">Customer Discount (%)</label>
                        <input type="number" step="0.1" min="0" max="20" name="utility_airtime_discount_percent" value="{{ old('utility_airtime_discount_percent', $pricingSettings['utility_airtime_discount_percent'] ?? 0.0) }}" required class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-gray-200">
                        <span class="text-[10px] text-gray-400 mt-0.5 block">Recommended: 0.0% for max profit</span>
                    </div>

                    <div>
                        <label class="block text-[11px] font-black uppercase text-gray-700 mb-1">Provider Telco Discount (%)</label>
                        <input type="number" step="0.1" min="0" max="20" name="utility_airtime_provider_discount_percent" value="{{ old('utility_airtime_provider_discount_percent', $pricingSettings['utility_airtime_provider_discount_percent'] ?? 2.0) }}" required class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-gray-200">
                        <span class="text-[10px] text-gray-400 mt-0.5 block">BigiSub wholesale discount (~2%)</span>
                    </div>

                    <div>
                        <label class="block text-[11px] font-black uppercase text-gray-700 mb-1">Extra Service Fee Mode</label>
                        <select name="utility_airtime_fee_mode" class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-gray-200 bg-white">
                            <option value="none" {{ ($pricingSettings['utility_airtime_fee_mode'] ?? 'none') === 'none' ? 'selected' : '' }}>None (₦0 fee)</option>
                            <option value="fixed" {{ ($pricingSettings['utility_airtime_fee_mode'] ?? 'none') === 'fixed' ? 'selected' : '' }}>Fixed Fee (₦)</option>
                            <option value="percent" {{ ($pricingSettings['utility_airtime_fee_mode'] ?? 'none') === 'percent' ? 'selected' : '' }}>Percentage Fee (%)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-black uppercase text-gray-700 mb-1">Extra Service Fee Value</label>
                        <input type="number" step="0.5" min="0" name="utility_airtime_fee_value" value="{{ old('utility_airtime_fee_value', $pricingSettings['utility_airtime_fee_value'] ?? 0.0) }}" required class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-gray-200">
                    </div>

                    <div>
                        <label class="block text-[11px] font-black uppercase text-gray-700 mb-1">Min Airtime Limit (₦)</label>
                        <input type="number" step="10" min="1" name="utility_airtime_min_amount" value="{{ old('utility_airtime_min_amount', $pricingSettings['utility_airtime_min_amount'] ?? 50.0) }}" required class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-gray-200">
                    </div>

                    <div>
                        <label class="block text-[11px] font-black uppercase text-gray-700 mb-1">Max Airtime Limit (₦)</label>
                        <input type="number" step="1000" min="100" name="utility_airtime_max_amount" value="{{ old('utility_airtime_max_amount', $pricingSettings['utility_airtime_max_amount'] ?? 50000.0) }}" required class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-gray-200">
                    </div>
                </div>
            </div>

            <!-- 3. Electricity Bill Payment Convenience Fee -->
            <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">⚡</span>
                        <h4 class="text-xs font-black uppercase tracking-wider text-slate-800">3. Electricity Bill Payment Convenience Fee</h4>
                    </div>
                    <label class="flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
                        <input type="checkbox" name="utility_electricity_enabled" value="1" {{ (int)($pricingSettings['utility_electricity_enabled'] ?? 1) === 1 ? 'checked' : '' }} class="rounded text-teal-600 focus:ring-teal-500 w-4 h-4">
                        <span>Service Active</span>
                    </label>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-[11px] font-black uppercase text-gray-700 mb-1">Convenience Fee Mode</label>
                        <select name="utility_electricity_fee_mode" class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-gray-200 bg-white">
                            <option value="fixed" {{ ($pricingSettings['utility_electricity_fee_mode'] ?? 'fixed') === 'fixed' ? 'selected' : '' }}>Fixed Fee (₦ per bill)</option>
                            <option value="percent" {{ ($pricingSettings['utility_electricity_fee_mode'] ?? 'fixed') === 'percent' ? 'selected' : '' }}>Percentage Fee (%)</option>
                            <option value="none" {{ ($pricingSettings['utility_electricity_fee_mode'] ?? 'fixed') === 'none' ? 'selected' : '' }}>Zero Fee</option>
                        </select>
                        <span class="text-[10px] text-gray-400 mt-0.5 block">Recommended: Fixed ₦100</span>
                    </div>

                    <div>
                        <label class="block text-[11px] font-black uppercase text-gray-700 mb-1">Convenience Fee Amount</label>
                        <input type="number" step="1" min="0" name="utility_electricity_fee_value" value="{{ old('utility_electricity_fee_value', $pricingSettings['utility_electricity_fee_value'] ?? 100.0) }}" required class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-gray-200">
                        <span class="text-[10px] text-gray-400 mt-0.5 block">Retained 100% as platform profit</span>
                    </div>

                    <div>
                        <label class="block text-[11px] font-black uppercase text-gray-700 mb-1">Min Bill Amount (₦)</label>
                        <input type="number" step="100" min="100" name="utility_electricity_min_amount" value="{{ old('utility_electricity_min_amount', $pricingSettings['utility_electricity_min_amount'] ?? 500.0) }}" required class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-gray-200">
                    </div>

                    <div>
                        <label class="block text-[11px] font-black uppercase text-gray-700 mb-1">Max Bill Amount (₦)</label>
                        <input type="number" step="10000" min="1000" name="utility_electricity_max_amount" value="{{ old('utility_electricity_max_amount', $pricingSettings['utility_electricity_max_amount'] ?? 500000.0) }}" required class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-gray-200">
                    </div>
                </div>
            </div>

            <!-- 4. Betting Wallet Funding Processing Fee -->
            <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">⚽</span>
                        <h4 class="text-xs font-black uppercase tracking-wider text-slate-800">4. Betting Wallet Top-Up Processing Fee</h4>
                    </div>
                    <label class="flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
                        <input type="checkbox" name="utility_betting_enabled" value="1" {{ (int)($pricingSettings['utility_betting_enabled'] ?? 0) === 1 ? 'checked' : '' }} class="rounded text-teal-600 focus:ring-teal-500 w-4 h-4">
                        <span>Service Active</span>
                    </label>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-[11px] font-black uppercase text-gray-700 mb-1">Processing Fee Mode</label>
                        <select name="utility_betting_fee_mode" class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-gray-200 bg-white">
                            <option value="percent" {{ ($pricingSettings['utility_betting_fee_mode'] ?? 'percent') === 'percent' ? 'selected' : '' }}>Percentage Fee (%)</option>
                            <option value="fixed" {{ ($pricingSettings['utility_betting_fee_mode'] ?? 'percent') === 'fixed' ? 'selected' : '' }}>Fixed Fee (₦)</option>
                            <option value="none" {{ ($pricingSettings['utility_betting_fee_mode'] ?? 'percent') === 'none' ? 'selected' : '' }}>Zero Fee</option>
                        </select>
                        <span class="text-[10px] text-gray-400 mt-0.5 block">Recommended: 1.5%</span>
                    </div>

                    <div>
                        <label class="block text-[11px] font-black uppercase text-gray-700 mb-1">Processing Fee Value</label>
                        <input type="number" step="0.1" min="0" name="utility_betting_fee_value" value="{{ old('utility_betting_fee_value', $pricingSettings['utility_betting_fee_value'] ?? 1.5) }}" required class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-gray-200">
                    </div>

                    <div>
                        <label class="block text-[11px] font-black uppercase text-gray-700 mb-1">Min Deposit (₦)</label>
                        <input type="number" step="50" min="50" name="utility_betting_min_amount" value="{{ old('utility_betting_min_amount', $pricingSettings['utility_betting_min_amount'] ?? 100.0) }}" required class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-gray-200">
                    </div>

                    <div>
                        <label class="block text-[11px] font-black uppercase text-gray-700 mb-1">Max Deposit (₦)</label>
                        <input type="number" step="10000" min="1000" name="utility_betting_max_amount" value="{{ old('utility_betting_max_amount', $pricingSettings['utility_betting_max_amount'] ?? 500000.0) }}" required class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-gray-200">
                    </div>
                </div>
            </div>

            <!-- 5. BigiSub API Gateway Settings -->
            <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-4">
                <div class="flex items-center gap-2">
                    <span class="text-lg">🔌</span>
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-800">5. BigiSub Gateway Credentials</h4>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-[11px] font-black uppercase text-gray-700 mb-1">API Base URL</label>
                        <input type="url" name="bigisub_base_url" value="{{ old('bigisub_base_url', $baseUrl) }}" required class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-gray-200">
                    </div>

                    <div>
                        <label class="block text-[11px] font-black uppercase text-gray-700 mb-1">Transaction PIN</label>
                        <input type="text" name="bigisub_pin" value="{{ old('bigisub_pin', $pin) }}" maxlength="10" class="w-full text-xs font-mono font-semibold px-3 py-2 rounded-xl border border-gray-200">
                    </div>

                    <div>
                        <label class="block text-[11px] font-black uppercase text-gray-700 mb-1">Simulation Mode</label>
                        <select name="bigisub_simulate" class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-gray-200 bg-white">
                            <option value="0" {{ !$simulate ? 'selected' : '' }}>Live Production</option>
                            <option value="1" {{ $simulate ? 'selected' : '' }}>Simulation / Sandbox</option>
                        </select>
                    </div>

                    <div class="sm:col-span-4">
                        <label class="block text-[11px] font-black uppercase text-gray-700 mb-1">API Token</label>
                        <input type="password" name="bigisub_api_token" value="{{ old('bigisub_api_token', $apiToken) }}" placeholder="Enter live BigiSub API token" class="w-full text-xs font-mono font-semibold px-3 py-2 rounded-xl border border-gray-200">
                    </div>
                </div>
            </div>

            <!-- Financial Safety Guard -->
            <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <div>
                        <div class="text-xs font-black text-amber-900">Negative Profit Guard</div>
                        <div class="text-[11px] text-amber-800">Prevent customer purchases from executing if the selling price is lower than the wholesale provider cost.</div>
                    </div>
                </div>
                <label class="flex items-center gap-2 text-xs font-bold text-amber-900 cursor-pointer shrink-0">
                    <input type="checkbox" name="utility_allow_negative_profit" value="1" {{ (int)($pricingSettings['utility_allow_negative_profit'] ?? 0) === 1 ? 'checked' : '' }} class="rounded text-amber-600 focus:ring-amber-500 w-4 h-4">
                    <span>Allow Negative Override</span>
                </label>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('pricing-panel').classList.add('hidden')" class="px-5 py-3 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-black text-xs uppercase tracking-wider transition cursor-pointer">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-3 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-black text-xs uppercase tracking-wider transition shadow-sm cursor-pointer">
                    Save Pricing Rules &amp; Markups
                </button>
            </div>
        </form>
    </div>

    <!-- Pricing Audit Logs Accordion / Snapshot -->
    @if($auditLogs->count() > 0)
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-gray-200 shadow-xs">
            <details class="group">
                <summary class="flex items-center justify-between cursor-pointer list-none text-xs font-black uppercase tracking-wider text-gray-700">
                    <div class="flex items-center gap-2">
                        <span>🛡️ Recent Pricing Audit Logs ({{ $auditLogs->count() }})</span>
                        <span class="text-[10px] text-gray-400 font-normal">Click to view administrator pricing change trail</span>
                    </div>
                    <span class="text-gray-400 group-open:rotate-180 transition-transform">▼</span>
                </summary>
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 border-b border-gray-100 text-gray-400 uppercase text-[10px] font-black">
                            <tr>
                                <th class="py-2 px-3">Date</th>
                                <th class="py-2 px-3">Admin</th>
                                <th class="py-2 px-3">Setting Key</th>
                                <th class="py-2 px-3">Previous Value</th>
                                <th class="py-2 px-3">New Value</th>
                                <th class="py-2 px-3">IP</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 text-[11px]">
                            @foreach($auditLogs as $log)
                                <tr class="hover:bg-gray-50">
                                    <td class="py-2 px-3 text-gray-500 whitespace-nowrap">{{ \Carbon\Carbon::parse($log->created_at)->format('M d, H:i:s') }}</td>
                                    <td class="py-2 px-3 font-bold text-gray-900">{{ $log->admin_name ?: 'System Admin' }}</td>
                                    <td class="py-2 px-3 font-mono text-gray-700">{{ $log->setting_key }}</td>
                                    <td class="py-2 px-3 font-mono text-rose-600">{{ $log->previous_value ?: 'null' }}</td>
                                    <td class="py-2 px-3 font-mono text-emerald-600 font-bold">{{ $log->new_value }}</td>
                                    <td class="py-2 px-3 text-gray-400">{{ $log->ip_address }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </details>
        </div>
    @endif

    <!-- Filters & Search Toolbar -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-gray-200 shadow-sm space-y-3">
        <form method="GET" action="{{ route('admin.utilities.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <input type="hidden" name="period" value="{{ $period ?? 'all' }}">

            <!-- Search -->
            <div class="sm:col-span-5 relative">
                <input type="text" 
                       name="q" 
                       value="{{ $q ?? '' }}" 
                       placeholder="Search reference, recipient, meter, customer name, user email..." 
                       class="w-full pl-3 pr-4 py-2.5 text-xs font-semibold rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 text-gray-800 placeholder-gray-400">
            </div>

            <!-- Service Type Filter -->
            <div class="sm:col-span-3">
                <select name="type" class="w-full py-2.5 px-3 text-xs font-semibold rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 text-gray-800 bg-white">
                    <option value="">All Services</option>
                    <option value="airtime" {{ ($type ?? '') === 'airtime' ? 'selected' : '' }}>📶 Airtime</option>
                    <option value="data" {{ ($type ?? '') === 'data' ? 'selected' : '' }}>🌐 Data Bundles</option>
                    <option value="electricity" {{ ($type ?? '') === 'electricity' ? 'selected' : '' }}>⚡ Electricity</option>
                    <option value="betting" {{ ($type ?? '') === 'betting' ? 'selected' : '' }}>⚽ Betting Top-Up</option>
                </select>
            </div>

            <!-- Status Filter -->
            <div class="sm:col-span-2">
                <select name="status" class="w-full py-2.5 px-3 text-xs font-semibold rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 text-gray-800 bg-white">
                    <option value="">All Statuses</option>
                    <option value="completed" {{ ($status ?? '') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="pending" {{ ($status ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="uncertain" {{ ($status ?? '') === 'uncertain' ? 'selected' : '' }}>Uncertain</option>
                    <option value="failed" {{ ($status ?? '') === 'failed' ? 'selected' : '' }}>Failed</option>
                    <option value="refunded" {{ ($status ?? '') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                </select>
            </div>

            <!-- Submit -->
            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit" class="flex-1 py-2.5 px-4 bg-gray-900 hover:bg-gray-800 text-white rounded-xl text-xs font-bold transition cursor-pointer">
                    Filter
                </button>
                @if(($q ?? '') || ($type ?? '') || ($status ?? '') || ($period ?? 'all') !== 'all')
                    <a href="{{ route('admin.utilities.index') }}" class="py-2.5 px-3 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl text-xs font-bold transition" title="Clear Filters">
                        ✕
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white rounded-3xl border border-gray-200 shadow-sm overflow-hidden">
        @if($transactions->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50 border-b border-gray-200 text-gray-500 uppercase tracking-wider font-extrabold text-[10px]">
                        <tr>
                            <th class="py-3.5 px-4">User</th>
                            <th class="py-3.5 px-4">Service &amp; Operator</th>
                            <th class="py-3.5 px-4">Target / Recipient</th>
                            <th class="py-3.5 px-4">Customer Charged</th>
                            <th class="py-3.5 px-4">Provider Cost</th>
                            <th class="py-3.5 px-4">Gross Profit</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-4">References</th>
                            <th class="py-3.5 px-4">Date</th>
                            <th class="py-3.5 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($transactions as $t)
                            <tr class="hover:bg-gray-50/70 transition">
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-gray-900">{{ $t->user->name ?? 'User #' . $t->user_id }}</div>
                                    <div class="text-[10px] text-gray-400 font-mono">{{ $t->user->email ?? 'N/A' }}</div>
                                </td>

                                <td class="py-3.5 px-4">
                                    <div class="font-extrabold text-gray-900 uppercase">
                                        {{ $t->service_type }} · {{ $t->network_or_disco }}
                                    </div>
                                    @if($t->product_name)
                                        <div class="text-[10px] text-gray-500 font-medium truncate max-w-xs">
                                            {{ $t->product_name }}
                                        </div>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4">
                                    <div class="font-mono font-bold text-gray-900">{{ $t->customer_target }}</div>
                                    @if($t->customer_name)
                                        <div class="text-[10px] text-gray-500">{{ $t->customer_name }}</div>
                                    @endif
                                    @if($t->token)
                                        <div class="text-[10px] text-emerald-600 font-mono font-bold">
                                            TOKEN: {{ $t->token }}
                                        </div>
                                    @endif
                                </td>

                                <!-- Customer Charged -->
                                <td class="py-3.5 px-4">
                                    <div class="font-extrabold text-gray-900">₦{{ number_format((float)($t->amount_ngn + $t->fee_ngn - $t->discount_ngn), 2) }}</div>
                                    <div class="text-[10px] text-gray-400 font-semibold">${{ number_format((float)$t->total_charged_usd, 4) }} USD</div>
                                </td>

                                <!-- Provider Cost -->
                                <td class="py-3.5 px-4">
                                    <div class="font-mono text-gray-700 font-semibold">₦{{ number_format((float)$t->provider_cost_ngn, 2) }}</div>
                                </td>

                                <!-- Gross Profit -->
                                <td class="py-3.5 px-4">
                                    @if($t->status === 'completed')
                                        @if($t->profit_ngn >= 0)
                                            <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 text-[11px] font-black font-mono">
                                                +₦{{ number_format((float)$t->profit_ngn, 2) }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-md bg-rose-100 text-rose-800 text-[11px] font-black font-mono">
                                                -₦{{ number_format(abs((float)$t->profit_ngn), 2) }}
                                            </span>
                                        @endif
                                    @elseif(in_array($t->status, ['pending', 'uncertain']))
                                        <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 text-[10px] font-bold font-mono">
                                            ~₦{{ number_format((float)$t->profit_ngn, 2) }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 text-[10px] font-bold">₦0.00</span>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4">
                                    @php $b = $t->status_badge; @endphp
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold border {{ $b['bg'] }}">
                                        <span>{{ $b['icon'] }}</span>
                                        {{ $b['label'] }}
                                    </span>
                                </td>

                                <td class="py-3.5 px-4">
                                    <div class="font-mono text-[10px] text-gray-800 font-bold select-all">{{ $t->reference }}</div>
                                    @if($t->provider_reference)
                                        <div class="font-mono text-[9px] text-gray-400 select-all">Prov: {{ $t->provider_reference }}</div>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 text-gray-500 whitespace-nowrap">
                                    <div>{{ $t->created_at->format('M d, Y') }}</div>
                                    <div class="text-[10px] text-gray-400">{{ $t->created_at->format('h:i A') }}</div>
                                </td>

                                <td class="py-3.5 px-4 text-right">
                                    <div class="inline-flex items-center gap-1">
                                        <a href="{{ route('admin.utilities.show', $t->id) }}" 
                                           class="px-2.5 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-[11px] transition">
                                            Inspect
                                        </a>

                                        @if(in_array($t->status, ['pending', 'uncertain']))
                                            <form action="{{ route('admin.utilities.reconcile', $t->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" 
                                                        onclick="return confirm('Reconcile this transaction with BigiSub API?')" 
                                                        class="px-2 py-1 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 font-bold text-[10px] border border-amber-200 transition cursor-pointer"
                                                        title="Check status on BigiSub">
                                                    🔄
                                                </button>
                                            </form>
                                        @endif

                                        @if($t->canRefund())
                                            <form action="{{ route('admin.utilities.refund', $t->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" 
                                                        onclick="return confirm('Refund ₦{{ number_format((float)$t->amount_ngn, 2) }} back to user wallet?')" 
                                                        class="px-2 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-[10px] border border-rose-200 transition cursor-pointer"
                                                        title="Manual Refund">
                                                    ↩
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-100">
                {{ $transactions->links() }}
            </div>
        @else
            <div class="text-center py-16 px-4">
                <div class="w-14 h-14 mx-auto rounded-full bg-gray-100 flex items-center justify-center text-2xl mb-3">
                    📜
                </div>
                <h3 class="text-sm font-extrabold text-gray-800">No utility transactions found</h3>
                <p class="text-gray-500 text-xs mt-1">There are no records matching the selected search or filter criteria.</p>
            </div>
        @endif
    </div>

</div>
@endsection
