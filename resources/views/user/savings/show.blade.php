@extends('layouts.dashboard')

@section('page_title', $plan->name . ' — Plan Details')

@section('dashboard_content')

@php
    $pSymbol = $plan->currency === 'USD' ? '$' : '₦';
    $statusBadgeClass = match($plan->status) {
        'active' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
        'matured' => 'bg-teal-100 text-teal-800 border-teal-200',
        'broken' => 'bg-amber-100 text-amber-800 border-amber-200',
        default => 'bg-slate-100 text-slate-700 border-slate-200',
    };
@endphp

<div class="space-y-6 max-w-6xl mx-auto" x-data="{
    activeTab: 'contributions',
    showTopupModal: false,
    showBreakModal: false,
    topupAmount: '',
    confirmBreakCheckbox: false,
    formatMoney(val) {
        return Number(val || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
}">

    <!-- Top Breadcrumb & Flash Alerts -->
    <div class="flex items-center justify-between">
        <a href="{{ route('savings.index') }}" class="inline-flex items-center gap-1.5 text-xs font-black text-slate-600 hover:text-slate-900 transition">
            <span>&larr;</span>
            <span>Back to All Savings</span>
        </a>

        <div class="flex items-center gap-2">
            <span class="text-xs font-bold text-slate-400">Ref:</span>
            <span class="text-xs font-mono font-black text-slate-800 bg-slate-100 px-2.5 py-1 rounded-lg">{{ $plan->reference }}</span>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center gap-2">
            <span>🎉</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold flex items-center gap-2">
            <span>⚠️</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Hero Plan Card -->
    <div class="relative overflow-hidden rounded-3xl p-6 sm:p-8 text-white shadow-xl border border-slate-800"
         style="background: linear-gradient(135deg, #090d16 0%, #0f172a 60%, #0d9488 100%) !important; background-color: #0f172a !important; color: #ffffff !important;">
        <!-- Ambient glow spheres -->
        <div class="absolute -right-16 -top-16 w-64 h-64 rounded-full blur-3xl pointer-events-none" style="background-color: rgba(13, 148, 136, 0.3);"></div>
        <div class="absolute -left-16 -bottom-16 w-64 h-64 rounded-full blur-3xl pointer-events-none" style="background-color: rgba(250, 204, 21, 0.2);"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-3">
                <div class="flex items-center gap-2.5 flex-wrap">
                    <span class="w-12 h-12 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center text-3xl shrink-0">
                        {{ $plan->category->icon ?? '🎯' }}
                    </span>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider {{ $statusBadgeClass }}">
                                {{ ucfirst($plan->status) }}
                            </span>
                            <span class="text-xs text-teal-200 font-bold">
                                {{ $plan->category->name ?? 'Custom Goal' }}
                            </span>
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-black text-white leading-tight mt-0.5" style="color: #ffffff !important;">
                            {{ $plan->name }}
                        </h1>
                    </div>
                </div>

                @if($plan->description)
                    <p class="text-xs sm:text-sm text-slate-300 max-w-xl">
                        {{ $plan->description }}
                    </p>
                @endif

                <!-- Progress Bar -->
                <div class="pt-2 max-w-md space-y-1.5 text-xs">
                    <div class="flex justify-between font-bold text-slate-300 text-[11px]">
                        <span>Goal Progress: {{ $plan->progress_percent }}%</span>
                        <span>{{ $plan->days_remaining }} days remaining</span>
                    </div>
                    <div class="w-full bg-white/15 h-2 rounded-full overflow-hidden">
                        <div class="bg-gradient-to-r from-emerald-400 to-[#facc15] h-full rounded-full transition-all duration-500" 
                             style="width: {{ $plan->progress_percent }}%;"></div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons on Hero -->
            @if($plan->isActive())
                <div class="flex flex-col sm:flex-row md:flex-col gap-2.5 shrink-0">
                    <button type="button" 
                            @click="showTopupModal = true" 
                            class="px-5 py-3 rounded-2xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs uppercase tracking-wider shadow-lg transition active:scale-95 cursor-pointer flex items-center justify-center gap-2">
                        <span>+ Add Money</span>
                    </button>

                    @if($plan->allow_early_withdrawal)
                        <button type="button" 
                                @click="showBreakModal = true" 
                                class="px-5 py-2.5 rounded-2xl bg-white/15 hover:bg-white/25 text-white font-black text-xs uppercase tracking-wider transition active:scale-95 cursor-pointer border border-white/20 flex items-center justify-center gap-1.5">
                            <span>⚡ Break Savings</span>
                        </button>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Payout Statement Card if Matured or Broken -->
    @if($plan->payout_statement)
        @php $statement = $plan->payout_statement; @endphp
        <div class="rounded-3xl p-6 bg-slate-900 text-white shadow-xl border border-slate-800 space-y-4">
            <div class="flex items-center justify-between border-b border-white/10 pb-3">
                <div class="flex items-center gap-2">
                    <span class="text-xl">📜</span>
                    <h3 class="text-sm sm:text-base font-black text-white">Official Payout Statement</h3>
                </div>
                <span class="text-xs text-slate-400">Processed: {{ \Carbon\Carbon::parse($statement['processed_at'] ?? now())->format('M d, Y h:i A') }}</span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                <div class="p-3 rounded-2xl bg-white/5 border border-white/10">
                    <div class="text-[10px] text-slate-400">Original Principal</div>
                    <div class="text-base font-black text-white mt-1">{{ $pSymbol }}{{ number_format($statement['principal'] ?? 0, 2) }}</div>
                </div>
                <div class="p-3 rounded-2xl bg-white/5 border border-white/10">
                    <div class="text-[10px] text-slate-400">Gross Interest Earned</div>
                    <div class="text-base font-black text-teal-400 mt-1">+{{ $pSymbol }}{{ number_format($statement['gross_interest_earned'] ?? ($statement['accrued_interest'] ?? 0), 2) }}</div>
                </div>
                <div class="p-3 rounded-2xl bg-white/5 border border-white/10">
                    <div class="text-[10px] text-slate-400">Fees / Forfeited</div>
                    <div class="text-base font-black text-rose-400 mt-1">-{{ $pSymbol }}{{ number_format($statement['forfeited_interest'] ?? ($statement['maturity_fee'] ?? 0), 2) }}</div>
                </div>
                <div class="p-3 rounded-2xl bg-emerald-500/10 border border-emerald-500/30">
                    <div class="text-[10px] text-emerald-300 font-bold">Net Credited to Wallet</div>
                    <div class="text-base font-black text-emerald-400 mt-1">{{ $pSymbol }}{{ number_format($statement['total_payout'] ?? ($statement['net_payout'] ?? 0), 2) }}</div>
                </div>
            </div>
        </div>
    @endif

    <!-- Key Metrics Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
        <!-- Principal -->
        <div class="bg-white rounded-3xl p-4 sm:p-5 shadow-xs border border-slate-200/80">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Locked Principal</span>
            <div class="text-xl sm:text-2xl font-black text-slate-900 mt-1">
                {{ $pSymbol }}{{ number_format($plan->principal_amount, 2) }}
            </div>
            <div class="text-[10px] text-slate-400 mt-0.5">100% Safeguarded</div>
        </div>

        <!-- Accrued Interest or Pure Savings Mode -->
        <div class="bg-white rounded-3xl p-4 sm:p-5 shadow-xs border border-slate-200/80">
            @if($plan->annual_interest_rate > 0)
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Accrued Interest</span>
                <div class="text-xl sm:text-2xl font-black text-emerald-600 mt-1">
                    +{{ $pSymbol }}{{ number_format($plan->accrued_interest, 2) }}
                </div>
                <div class="text-[10px] text-emerald-600 mt-0.5">Daily return @ {{ $plan->annual_interest_rate }}% p.a.</div>
            @else
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Savings Type</span>
                <div class="text-base sm:text-lg font-black text-slate-800 mt-1">
                    Pure Target Goal
                </div>
                <div class="text-[10px] text-slate-500 mt-0.5">0% Interest • 100% Deposit Back</div>
            @endif
        </div>

        <!-- Target Amount -->
        <div class="bg-white rounded-3xl p-4 sm:p-5 shadow-xs border border-slate-200/80">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Target Goal</span>
            <div class="text-xl sm:text-2xl font-black text-slate-900 mt-1">
                {{ $plan->target_amount ? $pSymbol . number_format($plan->target_amount, 2) : 'Open Target' }}
            </div>
            <div class="text-[10px] text-slate-400 mt-0.5">{{ $plan->duration_days }} Days Total</div>
        </div>

        <!-- Maturity Date -->
        <div class="bg-white rounded-3xl p-4 sm:p-5 shadow-xs border border-slate-200/80">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Maturity Date</span>
            <div class="text-lg sm:text-xl font-black text-teal-700 mt-1">
                {{ \Carbon\Carbon::parse($plan->maturity_date)->format('M d, Y') }}
            </div>
            <div class="text-[10px] text-teal-600 mt-0.5">{{ $plan->days_remaining }} days remaining</div>
        </div>
    </div>

    <!-- Tabs Container: Contributions vs Daily Accrual Ledger -->
    <div class="bg-white rounded-3xl p-5 sm:p-7 shadow-xs border border-slate-200/80 space-y-6">
        <!-- Tab Selector -->
        <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
            <button type="button" 
                    @click="activeTab = 'contributions'" 
                    :class="activeTab === 'contributions' ? 'bg-[#0d9488] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    class="px-4 py-2 rounded-xl text-xs font-black transition cursor-pointer">
                Contributions History ({{ $plan->contributions->count() }})
            </button>
            <button type="button" 
                    @click="activeTab = 'ledger'" 
                    :class="activeTab === 'ledger' ? 'bg-[#0d9488] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    class="px-4 py-2 rounded-xl text-xs font-black transition cursor-pointer">
                Daily Accrued Interest Ledger ({{ $plan->interestAccruals->count() }})
            </button>
            <button type="button" 
                    @click="activeTab = 'rules'" 
                    :class="activeTab === 'rules' ? 'bg-[#0d9488] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    class="px-4 py-2 rounded-xl text-xs font-black transition cursor-pointer">
                Contract Terms &amp; Rules
            </button>
        </div>

        <!-- TAB 1: Contributions History -->
        <div x-show="activeTab === 'contributions'" class="space-y-3">
            @if($plan->contributions->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="text-slate-400 font-bold border-b border-slate-100 text-[10px] uppercase">
                                <th class="pb-3">Date</th>
                                <th class="pb-3">Type</th>
                                <th class="pb-3">Reference</th>
                                <th class="pb-3">Amount</th>
                                <th class="pb-3">Principal After</th>
                                <th class="pb-3 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($plan->contributions as $item)
                                <tr>
                                    <td class="py-3 text-slate-500 font-medium">
                                        {{ $item->created_at->format('M d, Y h:i A') }}
                                    </td>
                                    <td class="py-3">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase {{ $item->type === 'deposit' ? 'bg-emerald-50 text-emerald-700' : ($item->type === 'additional_deposit' ? 'bg-teal-50 text-teal-700' : 'bg-amber-50 text-amber-700') }}">
                                            {{ str_replace('_', ' ', $item->type) }}
                                        </span>
                                    </td>
                                    <td class="py-3 font-mono font-bold text-slate-700">
                                        {{ $item->reference }}
                                    </td>
                                    <td class="py-3 font-black text-slate-900">
                                        {{ $pSymbol }}{{ number_format($item->amount, 2) }}
                                    </td>
                                    <td class="py-3 font-bold text-slate-600">
                                        {{ $pSymbol }}{{ number_format($item->principal_after, 2) }}
                                    </td>
                                    <td class="py-3 text-right">
                                        <span class="text-emerald-600 font-bold">✓ {{ ucfirst($item->status) }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="py-8 text-center text-slate-400 text-xs">
                    No contributions recorded yet.
                </div>
            @endif
        </div>

        <!-- TAB 2: Daily Accrued Interest Ledger -->
        <div x-show="activeTab === 'ledger'" class="space-y-3" style="display: none;">
            @if($plan->interestAccruals->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="text-slate-400 font-bold border-b border-slate-100 text-[10px] uppercase">
                                <th class="pb-3">Accrual Date</th>
                                <th class="pb-3">Eligible Principal</th>
                                <th class="pb-3">Annual Rate</th>
                                <th class="pb-3">Day's Interest Credited</th>
                                <th class="pb-3 text-right">Cumulative Interest</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($plan->interestAccruals as $accrual)
                                <tr>
                                    <td class="py-3 text-slate-700 font-bold">
                                        {{ \Carbon\Carbon::parse($accrual->accrual_date)->format('M d, Y') }}
                                    </td>
                                    <td class="py-3 font-mono text-slate-600">
                                        {{ $pSymbol }}{{ number_format($accrual->eligible_principal, 2) }}
                                    </td>
                                    <td class="py-3 text-slate-600">
                                        {{ $accrual->annual_rate }}% P.A.
                                    </td>
                                    <td class="py-3 font-mono font-black text-emerald-600">
                                        +{{ $pSymbol }}{{ number_format($accrual->day_interest_amount, 4) }}
                                    </td>
                                    <td class="py-3 text-right font-mono font-bold text-slate-900">
                                        {{ $pSymbol }}{{ number_format($accrual->cumulative_interest_after, 4) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="py-8 text-center text-slate-400 text-xs">
                    No daily interest accruals yet. Daily interest begins calculation on the next eligible day ({{ \Carbon\Carbon::parse($plan->interest_commencement_date)->format('M d, Y') }}).
                </div>
            @endif
        </div>

        <!-- TAB 3: Contract Terms & Rules -->
        <div x-show="activeTab === 'rules'" class="space-y-4" style="display: none;">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                    <div class="text-[10px] text-slate-400 uppercase font-black">Interest Calculation Convention</div>
                    <div class="font-bold text-slate-900">Daily = Principal × ({{ $plan->annual_interest_rate }}% ÷ 365)</div>
                    <p class="text-[11px] text-slate-500">Calculated each night at 00:05 AM and recorded in the immutable interest ledger.</p>
                </div>
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                    <div class="text-[10px] text-slate-400 uppercase font-black">Maturity Processing Fee</div>
                    <div class="font-bold text-slate-900">{{ $plan->maturity_fee_percent }}% of earned interest</div>
                    <p class="text-[11px] text-slate-500">Fee is deducted solely from accrued profit. Principal is 100% exempt from maturity fees.</p>
                </div>
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                    <div class="text-[10px] text-slate-400 uppercase font-black">Early Break Policy</div>
                    <div class="font-bold text-slate-900">{{ $plan->early_break_interest_forfeit_percent }}% interest forfeiture</div>
                    <p class="text-[11px] text-slate-500">If broken before {{ \Carbon\Carbon::parse($plan->maturity_date)->format('M d, Y') }}, accrued interest is forfeited and principal returned to main wallet.</p>
                </div>
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                    <div class="text-[10px] text-slate-400 uppercase font-black">Custody &amp; Safeguarding</div>
                    <div class="font-bold text-slate-900">Automated Atomic Ledger</div>
                    <p class="text-[11px] text-slate-500">All deposits and payouts are linked to your immutable May22SMS wallet transaction history.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Up Modal -->
    <div x-show="showTopupModal" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
         style="display: none;">
        <div @click.away="showTopupModal = false" class="bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-black text-slate-900">Add Money to Plan</h3>
                <button type="button" @click="showTopupModal = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
            </div>

            <form action="{{ route('savings.contribute', $plan->id) }}" method="POST" class="space-y-4">
                @csrf
                <div class="space-y-1.5">
                    <label class="text-xs font-black text-slate-800">Amount to Transfer from Wallet</label>
                    <div class="relative">
                        <span class="absolute left-4 top-3 text-slate-400 font-bold text-sm">{{ $pSymbol }}</span>
                        <input type="number" 
                               step="0.01" 
                               name="amount" 
                               x-model="topupAmount" 
                               placeholder="1,000.00" 
                               class="w-full pl-9 pr-4 py-3 rounded-2xl border border-slate-200 text-xs font-black text-slate-900 focus:outline-none focus:ring-2 focus:ring-teal-500" 
                               required>
                    </div>
                    <div class="text-[11px] text-slate-500 flex justify-between">
                        <span>Wallet: {{ $pSymbol }}{{ number_format($plan->currency === 'USD' ? $walletBalance : ($walletBalance * $ngnPerUsd), 2) }}</span>
                        <span class="text-[#0d9488] font-bold">Earns daily interest next day</span>
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <button type="button" @click="showTopupModal = false" class="w-1/2 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-black">
                        Cancel
                    </button>
                    <button type="submit" class="w-1/2 py-3 rounded-xl bg-[#0d9488] hover:bg-teal-700 text-white text-xs font-black shadow-md shadow-teal-600/20">
                        Confirm Top Up
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Early Break Confirmation Modal -->
    <div x-show="showBreakModal" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
         style="display: none;">
        <div @click.away="showBreakModal = false" class="bg-white rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2 text-amber-600">
                    <span class="text-xl">⚠️</span>
                    <h3 class="text-base font-black text-slate-900">Break Savings Early?</h3>
                </div>
                <button type="button" @click="showBreakModal = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
            </div>

            <p class="text-xs text-slate-600 leading-relaxed">
                You are requesting an early withdrawal before the official maturity date (<strong>{{ \Carbon\Carbon::parse($plan->maturity_date)->format('M d, Y') }}</strong>). Please review the penalty and net return breakdown:
            </p>

            <!-- Penalty Disclosure Statement -->
            <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200/80 space-y-2 text-xs">
                <div class="flex justify-between font-bold text-slate-700">
                    <span>Locked Principal:</span>
                    <span>{{ $pSymbol }}{{ number_format($earlyBreakSummary['principal'], 2) }}</span>
                </div>
                <div class="flex justify-between font-bold text-slate-500">
                    <span>Accrued Interest to Date:</span>
                    <span>+{{ $pSymbol }}{{ number_format($earlyBreakSummary['accrued_interest'], 2) }}</span>
                </div>
                <div class="flex justify-between font-bold text-rose-600">
                    <span>Forfeited Interest ({{ $earlyBreakSummary['penalty_rate'] }}%):</span>
                    <span>-{{ $pSymbol }}{{ number_format($earlyBreakSummary['forfeited_interest'], 2) }}</span>
                </div>
                @if($earlyBreakSummary['early_break_fee'] > 0)
                    <div class="flex justify-between font-bold text-rose-600">
                        <span>Early Break Fee:</span>
                        <span>-{{ $pSymbol }}{{ number_format($earlyBreakSummary['early_break_fee'], 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between font-black text-slate-900 pt-2 border-t border-amber-200 text-sm">
                    <span>Net Payout to Main Wallet:</span>
                    <span class="text-emerald-700">{{ $pSymbol }}{{ number_format($earlyBreakSummary['net_payout'], 2) }}</span>
                </div>
            </div>

            <form action="{{ route('savings.break', $plan->id) }}" method="POST" class="space-y-4">
                @csrf
                <label class="flex items-start gap-2.5 text-xs text-slate-700 cursor-pointer">
                    <input type="checkbox" 
                           name="confirm_early_break" 
                           value="1" 
                           x-model="confirmBreakCheckbox" 
                           class="w-4 h-4 mt-0.5 rounded border-slate-300 text-rose-600 focus:ring-rose-500 cursor-pointer" 
                           required>
                    <span class="leading-tight">
                        I understand that I am forfeiting <strong class="text-rose-600">{{ $pSymbol }}{{ number_format($earlyBreakSummary['forfeited_interest'], 2) }}</strong> in interest by breaking early, and I authorize the immediate return of <strong class="text-emerald-700">{{ $pSymbol }}{{ number_format($earlyBreakSummary['net_payout'], 2) }}</strong> to my main wallet.
                    </span>
                </label>

                <div class="flex items-center gap-2 pt-2">
                    <button type="button" @click="showBreakModal = false" class="w-1/2 py-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-black">
                        Keep Saving
                    </button>
                    <button type="submit" 
                            :disabled="!confirmBreakCheckbox"
                            class="w-1/2 py-3 rounded-xl bg-rose-600 hover:bg-rose-700 disabled:opacity-50 text-white text-xs font-black shadow-md shadow-rose-600/20 cursor-pointer">
                        Break &amp; Withdraw
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

@endsection
