@extends('layouts.admin')

@section('admin_content')
<div class="mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Deposit Funding Charges</h2>
            <p class="text-gray-500 text-sm">Configure deposit percentage charges per payment method. Whenever users fund their wallet, the charge percentage is automatically deducted.</p>
        </div>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $fundingChargeEnabled ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-gray-100 text-gray-600 border border-gray-300' }}">
            {{ $fundingChargeEnabled ? '● Charges Active' : '○ Charges Disabled' }}
        </span>
    </div>
</div>

@if(session('success'))
    <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl shadow-sm font-medium flex items-center gap-2">
        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        <span>{{ session('success') }}</span>
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

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8" x-data="{
    enabled: {{ $fundingChargeEnabled ? '1' : '0' }},
    globalPercent: {{ (float) $globalChargePercent }},
    paymentpointPercent: {{ (float) $paymentpointCharge }},
    pocketfiPercent: {{ (float) $pocketfiCharge }},
    strollwalletPercent: {{ (float) $strollwalletCharge }},
    cryptoPercent: {{ (float) $cryptoCharge }},
    testAmount: 1000,
    testMethod: 'pocketfi',
    get activeRate() {
        if (!this.enabled) return 0;
        if (this.testMethod === 'paymentpoint') return this.paymentpointPercent;
        if (this.testMethod === 'pocketfi') return this.pocketfiPercent;
        if (this.testMethod === 'strollwallet') return this.strollwalletPercent;
        if (this.testMethod === 'crypto') return this.cryptoPercent;
        return this.globalPercent;
    },
    get feeAmount() {
        return (Number(this.testAmount || 0) * (this.activeRate / 100)).toFixed(2);
    },
    get netAmount() {
        return Math.max(0, Number(this.testAmount || 0) - Number(this.feeAmount)).toFixed(2);
    }
}">
    <!-- Left 2 Cols: Form -->
    <div class="lg:col-span-2">
        <form action="/admin/charges" method="POST" class="space-y-6">
            @csrf

            <!-- Toggle Activation Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-gray-800">Enable Funding Charges</h3>
                        <p class="text-xs text-gray-500 mt-1">Turn ON to automatically deduct percentage charges from user deposits upon funding.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="funding_charge_enabled" value="0">
                        <input type="checkbox" name="funding_charge_enabled" value="1" x-model="enabled" :checked="enabled == 1" class="sr-only peer">
                        <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-[#0d9488]"></div>
                    </label>
                </div>
            </div>

            <!-- Gateway Percentage Settings -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                    <h3 class="text-base font-bold text-gray-800">Charge Percentage Per Gateway</h3>
                    <span class="text-xs font-semibold text-gray-500">Values in percentage (%)</span>
                </div>

                <div class="p-6 space-y-6">
                    <!-- Global Default -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Global Default Charge (%)</label>
                        <p class="text-xs text-gray-500 mb-2">Used as the fallback fee percentage for any funding method without a specific rate.</p>
                        <div class="relative">
                            <input type="number" step="0.1" min="0" max="100" name="funding_charge_percentage" x-model.number="globalPercent" value="{{ old('funding_charge_percentage', $globalChargePercent) }}" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#0d9488] focus:ring-0 font-bold text-lg text-gray-800">
                            <span class="absolute right-4 top-3.5 text-gray-400 font-bold">%</span>
                        </div>
                    </div>

                    <hr class="border-gray-100">

                    <!-- Account 1: PaymentPoint -->
                    <div class="p-4 rounded-xl border border-teal-100 bg-teal-50/40">
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-bold text-gray-800">Account 1 (PaymentPoint Bank Transfer)</label>
                            <span class="text-xs font-bold text-teal-700 bg-teal-100 px-2 py-0.5 rounded">PalmPay Virtual Account</span>
                        </div>
                        <div class="relative">
                            <input type="number" step="0.1" min="0" max="100" name="payment_method_paymentpoint_charge_percent" x-model.number="paymentpointPercent" value="{{ old('payment_method_paymentpoint_charge_percent', $paymentpointCharge) }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#0d9488] focus:ring-0 font-bold text-gray-800">
                            <span class="absolute right-4 top-2.5 text-gray-400 font-bold">%</span>
                        </div>
                    </div>

                    <!-- Account 2: PocketFi -->
                    <div class="p-4 rounded-xl border border-indigo-100 bg-indigo-50/40">
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-bold text-gray-800">Account 2 (PocketFi Checkout)</label>
                            <span class="text-xs font-bold text-indigo-700 bg-indigo-100 px-2 py-0.5 rounded">SafeHaven MFB Checkout</span>
                        </div>
                        <div class="relative">
                            <input type="number" step="0.1" min="0" max="100" name="payment_method_pocketfi_charge_percent" x-model.number="pocketfiPercent" value="{{ old('payment_method_pocketfi_charge_percent', $pocketfiCharge) }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#0d9488] focus:ring-0 font-bold text-gray-800">
                            <span class="absolute right-4 top-2.5 text-gray-400 font-bold">%</span>
                        </div>
                    </div>

                    <!-- Account 3: Strollwallet -->
                    <div class="p-4 rounded-xl border border-purple-100 bg-purple-50/40">
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-bold text-gray-800">Account 3 (Strollwallet Bank Transfer)</label>
                            <span class="text-xs font-bold text-purple-700 bg-purple-100 px-2 py-0.5 rounded">Strollwallet Virtual Account</span>
                        </div>
                        <div class="relative">
                            <input type="number" step="0.1" min="0" max="100" name="payment_method_strollwallet_charge_percent" x-model.number="strollwalletPercent" value="{{ old('payment_method_strollwallet_charge_percent', $strollwalletCharge) }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#0d9488] focus:ring-0 font-bold text-gray-800">
                            <span class="absolute right-4 top-2.5 text-gray-400 font-bold">%</span>
                        </div>
                    </div>

                    <!-- Crypto -->
                    <div class="p-4 rounded-xl border border-amber-100 bg-amber-50/40">
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-bold text-gray-800">Crypto Payments (NOWPayments)</label>
                            <span class="text-xs font-bold text-amber-700 bg-amber-100 px-2 py-0.5 rounded">USDT / Crypto Invoice</span>
                        </div>
                        <div class="relative">
                            <input type="number" step="0.1" min="0" max="100" name="payment_method_crypto_charge_percent" x-model.number="cryptoPercent" value="{{ old('payment_method_crypto_charge_percent', $cryptoCharge) }}" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#0d9488] focus:ring-0 font-bold text-gray-800">
                            <span class="absolute right-4 top-2.5 text-gray-400 font-bold">%</span>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end">
                    <button type="submit" class="px-6 py-3 rounded-xl bg-[#0d9488] hover:bg-[#0f766e] text-white font-bold text-sm shadow-md transition flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>Save Charge Settings</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Right Col: Live Calculator & Preview -->
    <div class="space-y-6">
        <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-teal-950 text-white rounded-2xl shadow-xl p-6 border border-teal-900/40">
            <h3 class="text-lg font-extrabold mb-1 flex items-center gap-2 text-teal-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                <span>Live Charge Calculator</span>
            </h3>
            <p class="text-xs text-slate-300 mb-6">Preview how user deposits will be calculated under your current charge settings.</p>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Select Gateway</label>
                    <select x-model="testMethod" class="w-full px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 text-white text-xs font-bold focus:ring-0 focus:border-teal-400">
                        <option value="paymentpoint">Account 1 (PaymentPoint)</option>
                        <option value="pocketfi">Account 2 (PocketFi)</option>
                        <option value="strollwallet">Account 3 (Strollwallet)</option>
                        <option value="crypto">Crypto (NOWPayments)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Sample Deposit Amount (₦)</label>
                    <input type="number" x-model.number="testAmount" class="w-full px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 text-white text-sm font-bold focus:ring-0 focus:border-teal-400">
                </div>

                <div class="border-t border-slate-800 pt-4 space-y-2">
                    <div class="flex justify-between text-xs text-slate-300">
                        <span>Charge Rate:</span>
                        <span class="font-bold text-amber-400" x-text="activeRate + '%'"></span>
                    </div>
                    <div class="flex justify-between text-xs text-slate-300">
                        <span>Deducted Charge:</span>
                        <span class="font-bold text-red-400" x-text="'- ₦' + feeAmount"></span>
                    </div>
                    <div class="flex justify-between text-sm pt-2 border-t border-slate-800/80">
                        <span class="font-bold text-white">Net Credited to User:</span>
                        <span class="font-extrabold text-emerald-400 text-base" x-text="'₦' + netAmount"></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-teal-50/70 border border-teal-200/80 rounded-2xl p-5 text-teal-900 text-xs space-y-2">
            <div class="font-bold text-teal-950 flex items-center gap-1.5 text-sm">
                <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>Important Information</span>
            </div>
            <p>1. Charges are automatically calculated and deducted when payments are confirmed.</p>
            <p>2. Set any charge percentage to <strong class="text-teal-950">0%</strong> if you want zero fee deduction for that payment method.</p>
            <p>3. You can update these charge rates at any time, and changes apply immediately to future user deposits.</p>
        </div>
    </div>
</div>
@endsection
