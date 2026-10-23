@extends('layouts.dashboard')

@section('page_title', 'Create Savings Plan — Save Small Small')

@section('dashboard_content')

@php
    $symbol = ($currency ?? 'USD') === 'NGN' ? '₦' : '$';
    $isNgn = ($currency ?? 'USD') === 'NGN';
@endphp

<div class="space-y-6 max-w-4xl mx-auto" x-data="{
    categories: @js($categories),
    selectedCategory: @js($selectedCategory->id ?? ($categories->first()->id ?? 1)),
    currency: @js($currency ?? 'NGN'),
    initialDeposit: '',
    targetAmount: '',
    durationDays: 90,
    planName: '',
    agreedToTerms: false,
    ngnPerUsd: {{ (float) $ngnPerUsd }},
    walletBalanceUsd: {{ (float) $walletBalance }},

    get currentCategory() {
        return this.categories.find(c => c.id === this.selectedCategory) || this.categories[0];
    },
    get annualRate() {
        return Number(this.currentCategory.effective_rate !== undefined ? this.currentCategory.effective_rate : (this.currentCategory.default_annual_rate || 0));
    },
    get minDeposit() {
        return this.currency === 'USD' ? Number(this.currentCategory.min_deposit_usd || 0.5) : Number(this.currentCategory.min_deposit_ngn || 500);
    },
    get maxDays() {
        return Number(this.currentCategory.max_duration_days || 365);
    },
    get minDays() {
        return Number(this.currentCategory.min_duration_days || 30);
    },
    get availableWalletBalance() {
        return this.currency === 'USD' ? this.walletBalanceUsd : (this.walletBalanceUsd * this.ngnPerUsd);
    },
    get depositUsd() {
        if (!this.initialDeposit) return 0;
        return this.currency === 'USD' ? Number(this.initialDeposit) : (Number(this.initialDeposit) / this.ngnPerUsd);
    },
    get isWalletSufficient() {
        return this.walletBalanceUsd >= this.depositUsd;
    },
    get dailyInterest() {
        const principal = Number(this.initialDeposit || 0);
        return (principal * (this.annualRate / 100)) / 365;
    },
    get projectedGrossInterest() {
        return this.dailyInterest * Number(this.durationDays || 0);
    },
    get maturityFee() {
        // 1.5% of earned interest
        return this.projectedGrossInterest * (Number(this.currentCategory.maturity_fee_percent || 1.5) / 100);
    },
    get projectedNetPayout() {
        const principal = Number(this.initialDeposit || 0);
        return principal + Math.max(0, this.projectedGrossInterest - this.maturityFee);
    },
    formatMoney(val) {
        return Number(val || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },
    init() {
        this.updateDefaultName();
    },
    updateDefaultName() {
        if (!this.planName || this.categories.some(c => c.name === this.planName)) {
            this.planName = this.currentCategory.name;
        }
    }
}">

    <!-- Breadcrumb Header -->
    <div class="flex items-center justify-between">
        <a href="{{ route('savings.index') }}" class="inline-flex items-center gap-1.5 text-xs font-black text-slate-600 hover:text-slate-900 transition">
            <span>&larr;</span>
            <span>Back to Savings Dashboard</span>
        </a>

        <div class="flex items-center gap-2 bg-slate-100 px-3 py-1.5 rounded-full text-xs font-bold text-slate-700">
            <span>Wallet Available:</span>
            <span class="font-black text-emerald-600">
                <span x-text="currency === 'USD' ? '$' : '₦'"></span><span x-text="formatMoney(availableWalletBalance)"></span>
            </span>
        </div>
    </div>

    <!-- Error Messages Display -->
    @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold flex items-center gap-2">
            <span>⚠️</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold space-y-1">
            @foreach($errors->all() as $err)
                <div>• {{ $err }}</div>
            @endforeach
        </div>
    @endif

    <form action="{{ route('savings.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- 1. Select Savings Category -->
        <div class="bg-white rounded-3xl p-6 sm:p-7 shadow-xs border border-slate-200/80 space-y-4">
            <div>
                <span class="text-[10px] font-black uppercase tracking-wider text-[#0d9488]">Step 01</span>
                <h3 class="text-lg font-black text-slate-900 mt-0.5">Select Your Savings Goal</h3>
                <p class="text-xs text-slate-500">Each plan comes with a locked annual return and custom timeline terms.</p>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <template x-for="cat in categories" :key="cat.id">
                    <button type="button" 
                            @click="selectedCategory = cat.id; updateDefaultName();"
                            :class="selectedCategory === cat.id ? 'border-[#0d9488] bg-teal-50/60 ring-2 ring-teal-500/20' : 'border-slate-200 bg-slate-50 hover:bg-slate-100'"
                            class="p-4 rounded-2xl border text-left transition-all active:scale-95 flex flex-col justify-between cursor-pointer space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-3xl" x-text="cat.icon"></span>
                            <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full" 
                                  :class="(cat.effective_rate > 0 || (cat.effective_rate === undefined && cat.default_annual_rate > 0)) ? 'text-emerald-700 bg-emerald-100' : 'text-slate-600 bg-slate-100'"
                                  x-text="(cat.effective_rate > 0 || (cat.effective_rate === undefined && cat.default_annual_rate > 0)) ? ((cat.effective_rate || cat.default_annual_rate) + '% P.A.') : '0% Pure Goal'"></span>
                        </div>
                        <div>
                            <div class="text-xs font-black text-slate-900" x-text="cat.name"></div>
                            <div class="text-[10px] text-slate-400 font-semibold mt-0.5 line-clamp-1" x-text="cat.description"></div>
                        </div>
                    </button>
                </template>
            </div>

            <input type="hidden" name="category_id" :value="selectedCategory">
        </div>

        <!-- 2. Configure Plan Details & Timeline -->
        <div class="bg-white rounded-3xl p-6 sm:p-7 shadow-xs border border-slate-200/80 space-y-6">
            <div>
                <span class="text-[10px] font-black uppercase tracking-wider text-[#0d9488]">Step 02</span>
                <h3 class="text-lg font-black text-slate-900 mt-0.5">Plan Terms &amp; Financial Target</h3>
                <p class="text-xs text-slate-500">Choose your currency, how much you want to deposit today, and your goal timeline.</p>
            </div>

            <!-- Goal Name -->
            <div class="space-y-1.5">
                <label class="text-xs font-black text-slate-800">Plan Title / Goal Name <span class="text-rose-500">*</span></label>
                <input type="text" 
                       name="name" 
                       x-model="planName" 
                       placeholder="e.g. 2026 Apartment Rent, Christmas Shopping, New MacBook"
                       class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-teal-500"
                       required>
            </div>

            <!-- Currency Selection -->
            <div class="space-y-1.5">
                <label class="text-xs font-black text-slate-800">Savings Currency <span class="text-rose-500">*</span></label>
                <div class="grid grid-cols-2 gap-3">
                    <button type="button" 
                            @click="currency = 'NGN'" 
                            :class="currency === 'NGN' ? 'bg-[#0d9488] text-white border-transparent' : 'bg-slate-50 text-slate-700 border-slate-200'"
                            class="py-3 px-4 rounded-2xl border font-black text-xs transition cursor-pointer flex items-center justify-center gap-2">
                        <span>🇳🇬</span>
                        <span>Nigerian Naira (NGN ₦)</span>
                    </button>
                    <button type="button" 
                            @click="currency = 'USD'" 
                            :class="currency === 'USD' ? 'bg-[#0d9488] text-white border-transparent' : 'bg-slate-50 text-slate-700 border-slate-200'"
                            class="py-3 px-4 rounded-2xl border font-black text-xs transition cursor-pointer flex items-center justify-center gap-2">
                        <span>🇺🇸</span>
                        <span>US Dollar (USD $)</span>
                    </button>
                </div>
                <input type="hidden" name="currency" :value="currency">
            </div>

            <!-- Initial Deposit & Target Amount -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Initial Deposit -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-black text-slate-800">Initial Deposit <span class="text-rose-500">*</span></label>
                        <span class="text-[10px] font-bold text-slate-400">
                            Min: <span x-text="currency === 'USD' ? '$' : '₦'"></span><span x-text="formatMoney(minDeposit)"></span>
                        </span>
                    </div>
                    <div class="relative">
                        <span class="absolute left-4 top-3 text-slate-400 font-bold text-sm" x-text="currency === 'USD' ? '$' : '₦'"></span>
                        <input type="number" 
                               step="0.01" 
                               name="initial_deposit" 
                               x-model="initialDeposit" 
                               placeholder="0.00"
                               class="w-full pl-9 pr-4 py-3 rounded-2xl border text-xs font-black text-slate-900 focus:outline-none focus:ring-2 focus:ring-teal-500"
                               :class="!isWalletSufficient && initialDeposit ? 'border-rose-300 bg-rose-50/50' : 'border-slate-200'"
                               required>
                    </div>

                    <!-- Insufficient Balance Warning -->
                    <template x-if="!isWalletSufficient && initialDeposit">
                        <div class="text-[11px] font-bold text-rose-600 flex items-center gap-1 mt-1">
                            <span>⚠️ Insufficient funds in main wallet.</span>
                            <a href="/funding" target="_blank" class="underline font-black text-[#0d9488]">Top up now &rarr;</a>
                        </div>
                    </template>
                </div>

                <!-- Target Goal Amount (Optional) -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-black text-slate-800">Target Goal Amount <span class="text-slate-400 font-medium">(Optional)</span></label>
                        <span class="text-[10px] font-bold text-slate-400">Milestone</span>
                    </div>
                    <div class="relative">
                        <span class="absolute left-4 top-3 text-slate-400 font-bold text-sm" x-text="currency === 'USD' ? '$' : '₦'"></span>
                        <input type="number" 
                               step="0.01" 
                               name="target_amount" 
                               x-model="targetAmount" 
                               placeholder="e.g. 500,000"
                               class="w-full pl-9 pr-4 py-3 rounded-2xl border border-slate-200 text-xs font-black text-slate-900 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    </div>
                </div>
            </div>

            <!-- Duration Selection -->
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <label class="text-xs font-black text-slate-800">Lock Duration (Days) <span class="text-rose-500">*</span></label>
                    <span class="text-[10px] font-bold text-slate-400">
                        Between <span x-text="minDays"></span> and <span x-text="maxDays"></span> days
                    </span>
                </div>

                <!-- Quick Presets -->
                <div class="flex flex-wrap gap-2">
                    <template x-for="days in [30, 60, 90, 180, 365]" :key="days">
                        <button type="button" 
                                @click="durationDays = days"
                                :class="durationDays == days ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                class="px-3.5 py-1.5 rounded-xl text-xs font-black transition cursor-pointer"
                                x-text="days + ' Days'">
                        </button>
                    </template>
                </div>

                <input type="range" 
                       x-model="durationDays" 
                       :min="minDays" 
                       :max="maxDays" 
                       step="1"
                       class="w-full accent-[#0d9488] cursor-pointer">

                <div class="flex items-center justify-between text-[11px] font-bold text-slate-600">
                    <span>Duration: <strong class="text-slate-900" x-text="durationDays + ' days'"></strong></span>
                    <span>Maturity Date: <strong class="text-[#0d9488]" x-text="new Date(Date.now() + durationDays * 86400000).toLocaleDateString(undefined, {month: 'short', day: 'numeric', year: 'numeric'})"></strong></span>
                </div>
                <input type="hidden" name="duration_days" :value="durationDays">
            </div>

            <!-- Live Returns Estimation Card -->
            <div class="p-5 rounded-2xl bg-gradient-to-br from-slate-900 via-slate-950 to-teal-950 text-white space-y-3 shadow-lg">
                <div class="flex items-center justify-between text-xs pb-2 border-b border-white/10">
                    <span class="font-bold text-teal-200">Financial Projection &amp; Returns</span>
                    <span class="font-mono font-black" 
                          :class="annualRate > 0 ? 'text-amber-400' : 'text-slate-300'"
                          x-text="annualRate > 0 ? (annualRate + '% P.A.') : 'Pure Goal (0% Interest)'"></span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                    <div>
                        <div class="text-[10px] text-slate-400">Principal Deposit</div>
                        <div class="text-sm font-black text-white mt-0.5">
                            <span x-text="currency === 'USD' ? '$' : '₦'"></span><span x-text="formatMoney(initialDeposit)"></span>
                        </div>
                    </div>
                    <div>
                        <div class="text-[10px] text-slate-400">Daily Return</div>
                        <div class="text-sm font-black text-emerald-400 mt-0.5">
                            +<span x-text="currency === 'USD' ? '$' : '₦'"></span><span x-text="formatMoney(dailyInterest)"></span>
                        </div>
                    </div>
                    <div>
                        <div class="text-[10px] text-slate-400">Projected Interest</div>
                        <div class="text-sm font-black text-teal-300 mt-0.5">
                            +<span x-text="currency === 'USD' ? '$' : '₦'"></span><span x-text="formatMoney(projectedGrossInterest)"></span>
                        </div>
                    </div>
                    <div>
                        <div class="text-[10px] text-slate-400">Est. Maturity Payout</div>
                        <div class="text-sm font-black text-[#facc15] mt-0.5">
                            <span x-text="currency === 'USD' ? '$' : '₦'"></span><span x-text="formatMoney(projectedNetPayout)"></span>
                        </div>
                    </div>
                </div>

                <div class="text-[10px] text-slate-400 pt-1 border-t border-white/10 flex flex-wrap items-center justify-between gap-1">
                    <span x-show="annualRate > 0">* 1.5% maturity fee applies to earned interest only (<span x-text="currency === 'USD' ? '$' : '₦'"></span><span x-text="formatMoney(maturityFee)"></span>). Principal is 100% safeguarded.</span>
                    <span x-show="annualRate == 0">* Pure Target Savings: You will receive 100% of your saved deposit back (<span x-text="currency === 'USD' ? '$' : '₦'"></span><span x-text="formatMoney(initialDeposit)"></span>) upon maturity with ₦0 fee.</span>
                </div>
            </div>

            <!-- Plan Terms & Legal Disclosure Checkbox -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2 text-xs text-slate-600">
                <label class="flex items-start gap-2.5 cursor-pointer">
                    <input type="checkbox" 
                           x-model="agreedToTerms" 
                           class="w-4 h-4 mt-0.5 rounded border-slate-300 text-[#0d9488] focus:ring-teal-500 cursor-pointer" 
                           required>
                    <span class="leading-snug">
                        I agree to lock <strong class="text-slate-900" x-text="(currency === 'USD' ? '$' : '₦') + formatMoney(initialDeposit)"></strong> for <strong class="text-slate-900" x-text="durationDays + ' days'"></strong>. I understand that daily interest begins on the next eligible day and that breaking the plan early will result in the forfeiture of accrued interest.
                    </span>
                </label>
            </div>

            <!-- Submit Button -->
            <button type="submit" 
                    :disabled="!agreedToTerms || !isWalletSufficient || !initialDeposit || initialDeposit < minDeposit"
                    class="w-full py-4 rounded-2xl bg-[#0d9488] hover:bg-teal-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-black text-xs uppercase tracking-wider shadow-lg shadow-teal-600/20 transition active:scale-95 cursor-pointer">
                Lock Funds &amp; Start Saving Now &rarr;
            </button>
        </div>
    </form>

</div>

@endsection
