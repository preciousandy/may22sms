@extends('layouts.dashboard')

@section('page_title', 'Savings Returns Calculator — Save Small Small')

@section('dashboard_content')

<div class="space-y-6 max-w-4xl mx-auto" x-data="{
    amount: 50000,
    durationDays: 90,
    annualRate: 16.90,
    currency: 'NGN',
    feePercent: 1.5,

    get dailyInterest() {
        return (Number(this.amount || 0) * (this.annualRate / 100)) / 365;
    },
    get grossInterest() {
        return this.dailyInterest * Number(this.durationDays || 0);
    },
    get maturityFee() {
        return this.grossInterest * (this.feePercent / 100);
    },
    get netInterest() {
        return Math.max(0, this.grossInterest - this.maturityFee);
    },
    get totalPayout() {
        return Number(this.amount || 0) + this.netInterest;
    },
    format(v) {
        return Number(v || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
}">

    <div class="flex items-center justify-between">
        <a href="{{ route('savings.index') }}" class="inline-flex items-center gap-1.5 text-xs font-black text-slate-600 hover:text-slate-900 transition">
            <span>&larr;</span>
            <span>Back to Savings Dashboard</span>
        </a>

        <a href="{{ route('savings.create') }}" class="px-4 py-2 rounded-xl bg-[#0d9488] hover:bg-teal-700 text-white font-black text-xs transition shadow-xs">
            + Start Saving Now
        </a>
    </div>

    <!-- Calculator Card -->
    <div class="bg-white rounded-3xl p-6 sm:p-10 shadow-xs border border-slate-200/80 space-y-8">
        <div>
            <div class="flex items-center gap-2 text-emerald-600 text-xs font-black uppercase tracking-wider">
                <span>🧮 Interactive Financial Tool</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">Save Small Small Returns Calculator</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">See how much your money will grow with daily interest calculated at 16.9% per annum.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
            <!-- Left Controls -->
            <div class="space-y-6">
                <!-- Currency Toggle -->
                <div class="space-y-2">
                    <label class="text-xs font-black text-slate-800">Currency</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" @click="currency = 'NGN'; if(amount < 1000) amount = 50000;" 
                                :class="currency === 'NGN' ? 'bg-[#0d9488] text-white' : 'bg-slate-100 text-slate-700'"
                                class="py-2.5 rounded-xl font-black text-xs transition cursor-pointer">
                            ₦ Naira (NGN)
                        </button>
                        <button type="button" @click="currency = 'USD'; if(amount > 1000) amount = 100;" 
                                :class="currency === 'USD' ? 'bg-[#0d9488] text-white' : 'bg-slate-100 text-slate-700'"
                                class="py-2.5 rounded-xl font-black text-xs transition cursor-pointer">
                            $ US Dollar (USD)
                        </button>
                    </div>
                </div>

                <!-- Principal Amount -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-black text-slate-800">Principal Amount to Save</label>
                        <span class="text-sm font-black text-slate-900" x-text="(currency === 'USD' ? '$' : '₦') + format(amount)"></span>
                    </div>
                    <input type="number" 
                           x-model="amount" 
                           class="w-full px-4 py-3 rounded-2xl border border-slate-200 text-sm font-black text-slate-900 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    <div class="flex flex-wrap gap-2 pt-1">
                        <template x-for="p in (currency === 'NGN' ? [10000, 50000, 100000, 500000, 1000000] : [50, 100, 250, 500, 1000])" :key="p">
                            <button type="button" @click="amount = p" class="px-3 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                                <span x-text="currency === 'USD' ? '$' : '₦'"></span><span x-text="format(p)"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Savings Type Toggle: 16.9% P.A. vs 0% Pure Target Savings -->
                <div class="space-y-2">
                    <label class="text-xs font-black text-slate-800">Plan Interest Model</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" @click="annualRate = 16.90" 
                                :class="annualRate > 0 ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                class="py-2.5 px-3 rounded-xl font-black text-xs transition cursor-pointer text-center">
                            ⚡ 16.9% P.A. Interest
                        </button>
                        <button type="button" @click="annualRate = 0.00" 
                                :class="annualRate == 0 ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                class="py-2.5 px-3 rounded-xl font-black text-xs transition cursor-pointer text-center">
                            ⚪ Pure Goal (0% Interest)
                        </button>
                    </div>
                </div>

                <!-- Duration in Days -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-black text-slate-800">Duration (Days)</label>
                        <span class="text-sm font-black text-[#0d9488]" x-text="durationDays + ' Days'"></span>
                    </div>
                    <input type="range" 
                           x-model="durationDays" 
                           min="30" 
                           max="365" 
                           step="1"
                           class="w-full accent-[#0d9488] cursor-pointer">
                    <div class="flex flex-wrap gap-2 pt-1">
                        <template x-for="d in [30, 60, 90, 180, 365]" :key="d">
                            <button type="button" @click="durationDays = d" 
                                    :class="durationDays == d ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700'"
                                    class="px-3 py-1 rounded-lg text-xs font-bold transition">
                                <span x-text="d + ' Days'"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Right Results Card -->
            <div class="p-6 rounded-3xl bg-slate-900 text-white space-y-6 shadow-xl border border-slate-800">
                <div class="space-y-1">
                    <span class="text-[10px] font-black uppercase tracking-wider text-teal-400">Total Estimated Payout</span>
                    <div class="text-3xl sm:text-4xl font-black text-[#facc15]">
                        <span x-text="currency === 'USD' ? '$' : '₦'"></span><span x-text="format(totalPayout)"></span>
                    </div>
                    <div class="text-xs text-slate-400">
                        Principal + Net Earned Interest
                    </div>
                </div>

                <div class="p-4 rounded-2xl bg-white/5 border border-white/10 space-y-3 text-xs">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Principal Deposit:</span>
                        <span class="font-bold text-white"><span x-text="currency === 'USD' ? '$' : '₦'"></span><span x-text="format(amount)"></span></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Annual Return Rate:</span>
                        <span class="font-bold text-emerald-400" x-text="annualRate + '% P.A.'"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Daily Return:</span>
                        <span class="font-mono text-emerald-400">+<span x-text="currency === 'USD' ? '$' : '₦'"></span><span x-text="format(dailyInterest)"></span>/day</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Gross Accrued Interest:</span>
                        <span class="font-bold text-teal-300">+<span x-text="currency === 'USD' ? '$' : '₦'"></span><span x-text="format(grossInterest)"></span></span>
                    </div>
                    <div class="flex justify-between text-rose-400 pt-2 border-t border-white/10">
                        <span>Maturity Fee (1.5% of profit):</span>
                        <span>-<span x-text="currency === 'USD' ? '$' : '₦'"></span><span x-text="format(maturityFee)"></span></span>
                    </div>
                    <div class="flex justify-between font-bold text-emerald-300">
                        <span>Net Profit:</span>
                        <span>+<span x-text="currency === 'USD' ? '$' : '₦'"></span><span x-text="format(netInterest)"></span></span>
                    </div>
                </div>

                <a href="{{ route('savings.create') }}" class="block w-full py-3.5 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 font-black text-xs uppercase tracking-wider text-center shadow-lg transition active:scale-95">
                    Start This Plan Now &rarr;
                </a>
            </div>
        </div>
    </div>

</div>

@endsection
