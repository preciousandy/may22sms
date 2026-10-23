@extends('layouts.dashboard')

@section('page_title', 'Save Small Small — Target Savings')

@section('dashboard_content')

@php
    $symbol = ($currency ?? 'USD') === 'NGN' ? '₦' : '$';
    $isNgn = ($currency ?? 'USD') === 'NGN';
@endphp

<div class="space-y-6 max-w-7xl mx-auto">

    <!-- Top Hero Banner: Save Small Small Portfolio -->
    <div class="relative overflow-hidden rounded-3xl p-6 sm:p-8 text-white shadow-xl border border-slate-800"
         style="background: linear-gradient(135deg, #090d16 0%, #0f172a 60%, #0d9488 100%) !important; background-color: #0f172a !important; color: #ffffff !important;">
        <!-- Ambient background glow spheres -->
        <div class="absolute -right-16 -top-16 w-72 h-72 rounded-full blur-3xl pointer-events-none" style="background-color: rgba(13, 148, 136, 0.3);"></div>
        <div class="absolute -left-16 -bottom-16 w-72 h-72 rounded-full blur-3xl pointer-events-none" style="background-color: rgba(250, 204, 21, 0.2);"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="space-y-3 max-w-2xl">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[#facc15] text-[11px] font-black uppercase tracking-wider"
                          style="background-color: rgba(255, 255, 255, 0.12); border: 1px solid rgba(255, 255, 255, 0.25);">
                        💰 Save Small Small
                    </span>
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-emerald-300 text-[11px] font-black uppercase tracking-wider"
                          style="background-color: rgba(255, 255, 255, 0.12); border: 1px solid rgba(255, 255, 255, 0.25);">
                        Up to 16.9% P.A.
                    </span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-teal-200 text-[10px] font-bold"
                          style="background-color: rgba(255, 255, 255, 0.1);">
                        Daily Interest Accrual
                    </span>
                </div>

                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black leading-tight tracking-tight" style="color: #ffffff !important;">
                    Grow Your Money With Goal Savings
                </h1>
                
                <p class="text-xs sm:text-sm font-medium leading-relaxed" style="color: #cbd5e1 !important;">
                    Lock away money for rent, education, gadgets, wedding, or holidays from your main wallet. Earn competitive returns calculated daily and paid upon maturity.
                </p>

                <!-- Value Props -->
                <div class="flex flex-wrap items-center gap-3 pt-2 text-xs font-bold text-slate-300">
                    <span class="flex items-center gap-1.5 bg-white/10 px-3 py-1.5 rounded-xl border border-white/15">
                        <span class="text-emerald-400">✓</span> Zero Deposit Fees
                    </span>
                    <span class="flex items-center gap-1.5 bg-white/10 px-3 py-1.5 rounded-xl border border-white/15">
                        <span class="text-amber-400">🛡</span> Dedicated Safeguarded Vault
                    </span>
                    <span class="flex items-center gap-1.5 bg-white/10 px-3 py-1.5 rounded-xl border border-white/15">
                        <span class="text-sky-400">⚡</span> Break Anytime Option
                    </span>
                </div>
            </div>

            <!-- Elevated CTA & Stats Card -->
            <div class="flex flex-col sm:flex-row lg:flex-col items-center justify-center gap-3 p-5 rounded-3xl shrink-0 text-center shadow-xl"
                 style="background-color: rgba(15, 23, 42, 0.85); border: 1px solid rgba(51, 65, 85, 0.8); min-width: 240px;">
                <div class="w-full">
                    <div class="text-[10px] font-black uppercase tracking-wider text-slate-400">Active Savings Portfolio</div>
                    <div class="text-2xl sm:text-3xl font-black text-emerald-400 mt-1">
                        {{ $symbol }}{{ number_format($isNgn ? $totalSavingsPrincipal : ($totalSavingsPrincipal / $ngnPerUsd), 2) }}
                    </div>
                    <div class="text-[11px] font-semibold text-slate-300 mt-0.5">
                        + {{ $symbol }}{{ number_format($isNgn ? $totalAccruedInterest : ($totalAccruedInterest / $ngnPerUsd), 2) }} Interest Accrued
                    </div>
                </div>

                <a href="{{ route('savings.create') }}" class="w-full px-5 py-3 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 font-black text-xs uppercase tracking-wider shadow-lg transition active:scale-95 text-center">
                    + Create New Plan
                </a>
            </div>
        </div>
    </div>

    <!-- Portfolio KPI Summary Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
        <!-- Active Principal -->
        <div class="bg-white rounded-3xl p-4 sm:p-5 shadow-xs border border-slate-200/80">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-bold uppercase tracking-wider">Total Saved</span>
                <span class="text-lg">🔒</span>
            </div>
            <div class="text-xl sm:text-2xl font-black text-slate-900">
                {{ $symbol }}{{ number_format($isNgn ? $totalSavingsPrincipal : ($totalSavingsPrincipal / $ngnPerUsd), 2) }}
            </div>
            <div class="text-[11px] font-semibold text-slate-400 mt-1">Across {{ $activePlansCount }} active plans</div>
        </div>

        <!-- Accrued Interest -->
        <div class="bg-white rounded-3xl p-4 sm:p-5 shadow-xs border border-slate-200/80">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-bold uppercase tracking-wider">Accrued Interest</span>
                <span class="text-lg">📈</span>
            </div>
            <div class="text-xl sm:text-2xl font-black text-emerald-600">
                +{{ $symbol }}{{ number_format($isNgn ? $totalAccruedInterest : ($totalAccruedInterest / $ngnPerUsd), 2) }}
            </div>
            <div class="text-[11px] font-semibold text-emerald-600 mt-1">Calculated daily @ 16.9%</div>
        </div>

        <!-- Paid Out Interest -->
        <div class="bg-white rounded-3xl p-4 sm:p-5 shadow-xs border border-slate-200/80">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-bold uppercase tracking-wider">Interest Paid Out</span>
                <span class="text-lg">🎉</span>
            </div>
            <div class="text-xl sm:text-2xl font-black text-teal-600">
                {{ $symbol }}{{ number_format($isNgn ? $totalPaidInterest : ($totalPaidInterest / $ngnPerUsd), 2) }}
            </div>
            <div class="text-[11px] font-semibold text-slate-400 mt-1">On matured plans</div>
        </div>

        <!-- Main Wallet Available -->
        <div class="bg-white rounded-3xl p-4 sm:p-5 shadow-xs border border-slate-200/80">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-bold uppercase tracking-wider">Main Wallet</span>
                <span class="text-lg">💳</span>
            </div>
            <div class="text-xl sm:text-2xl font-black text-slate-900">
                {{ $symbol }}{{ number_format($isNgn ? (auth()->user()->walletBalance * $ngnPerUsd) : auth()->user()->walletBalance, 2) }}
            </div>
            <a href="/funding" class="inline-flex items-center gap-1 text-[11px] font-extrabold text-[#0d9488] hover:underline mt-1">
                <span>+ Fund Wallet</span>
                <span>&rarr;</span>
            </a>
        </div>
    </div>

    <!-- Popular Savings Categories Quick-Start Carousel -->
    <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-xs border border-slate-200/80 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-base sm:text-lg font-black text-slate-900">Choose a Financial Goal</h3>
                <p class="text-xs text-slate-500">Pick a category to begin saving toward your target with automatic returns.</p>
            </div>
            <a href="{{ route('savings.calculator') }}" class="text-xs font-bold text-[#0d9488] hover:underline flex items-center gap-1">
                <span>🧮 Returns Calculator</span>
                <span>&rarr;</span>
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3">
            @foreach($categories as $category)
                <a href="{{ route('savings.create', ['category' => $category->slug]) }}" 
                   class="flex flex-col items-center text-center p-3.5 rounded-2xl bg-slate-50 hover:bg-teal-50 border border-slate-100 hover:border-teal-200 transition-all active:scale-95 group">
                    <span class="text-3xl mb-2 group-hover:scale-110 transition-transform">{{ $category->icon }}</span>
                    <span class="text-xs font-black text-slate-800 leading-tight">{{ $category->name }}</span>
                    @if($category->effective_rate > 0)
                        <span class="text-[10px] font-bold text-emerald-600 mt-1">{{ $category->effective_rate }}% P.A.</span>
                    @else
                        <span class="text-[10px] font-bold text-slate-500 mt-1">Pure Savings</span>
                    @endif
                </a>
            @endforeach
        </div>
    </div>

    <!-- Plans Portfolio Feed & Status Filter -->
    <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-xs border border-slate-200/80 space-y-5">
        <!-- Filter Tabs -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0 scrollbar-none">
                <a href="{{ route('savings.index') }}" 
                   class="px-4 py-2 rounded-xl text-xs font-black transition {{ $status === 'all' ? 'bg-[#0d9488] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    All Plans ({{ $plans->total() }})
                </a>
                <a href="{{ route('savings.index', ['status' => 'active']) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-black transition {{ $status === 'active' ? 'bg-[#0d9488] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Active ({{ $activePlansCount }})
                </a>
                <a href="{{ route('savings.index', ['status' => 'matured']) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-black transition {{ $status === 'matured' ? 'bg-[#0d9488] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Matured
                </a>
                <a href="{{ route('savings.index', ['status' => 'broken']) }}" 
                   class="px-4 py-2 rounded-xl text-xs font-black transition {{ $status === 'broken' ? 'bg-[#0d9488] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Early Broken
                </a>
            </div>

            <a href="{{ route('savings.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#0d9488] hover:bg-teal-700 text-white font-black text-xs transition shadow-xs">
                <span>+ New Plan</span>
            </a>
        </div>

        <!-- Plans Cards Grid -->
        @if($plans->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($plans as $plan)
                    @php
                        $pSymbol = $plan->currency === 'USD' ? '$' : '₦';
                        $statusBadgeClass = match($plan->status) {
                            'active' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                            'matured' => 'bg-teal-100 text-teal-800 border-teal-200',
                            'broken' => 'bg-amber-100 text-amber-800 border-amber-200',
                            default => 'bg-slate-100 text-slate-700 border-slate-200',
                        };
                    @endphp

                    <div class="rounded-3xl p-5 border border-slate-200/80 bg-slate-50/50 hover:bg-white hover:border-teal-300 hover:shadow-md transition-all flex flex-col justify-between space-y-4">
                        <!-- Top Header -->
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <span class="w-12 h-12 rounded-2xl bg-white shadow-xs border border-slate-200 flex items-center justify-center text-2xl shrink-0">
                                    {{ $plan->category->icon ?? '🎯' }}
                                </span>
                                <div>
                                    <h4 class="text-sm font-black text-slate-900 leading-snug">{{ $plan->name }}</h4>
                                    <span class="text-[10px] font-bold text-slate-400 font-mono">{{ $plan->reference }}</span>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border {{ $statusBadgeClass }}">
                                {{ ucfirst($plan->status) }}
                            </span>
                        </div>

                        <!-- Balances -->
                        <div class="p-3.5 rounded-2xl bg-white border border-slate-200/60 space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-slate-500">Locked Principal:</span>
                                <span class="font-black text-slate-900">{{ $pSymbol }}{{ number_format($plan->principal_amount, 2) }}</span>
                            </div>
                            @if($plan->annual_interest_rate > 0)
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-bold text-slate-500">Accrued Interest:</span>
                                    <span class="font-black text-emerald-600">+{{ $pSymbol }}{{ number_format($plan->accrued_interest, 2) }}</span>
                                </div>
                            @else
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-bold text-slate-500">Goal Type:</span>
                                    <span class="font-bold text-slate-700">Pure Target Goal (0%)</span>
                                </div>
                            @endif
                            @if($plan->target_amount)
                                <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-100">
                                    <span class="font-bold text-slate-500">Target Goal:</span>
                                    <span class="font-bold text-slate-700">{{ $pSymbol }}{{ number_format($plan->target_amount, 2) }}</span>
                                </div>
                            @endif
                        </div>

                        <!-- Progress Bar & Timeline -->
                        <div class="space-y-1.5 text-xs">
                            <div class="flex items-center justify-between text-[11px] font-bold text-slate-500">
                                <span>Progress ({{ $plan->progress_percent }}%)</span>
                                <span>{{ $plan->days_remaining }} days left</span>
                            </div>
                            <div class="w-full bg-slate-200 h-2 rounded-full overflow-hidden">
                                <div class="bg-gradient-to-r from-emerald-500 to-[#0d9488] h-full rounded-full transition-all duration-500" 
                                     style="width: {{ $plan->progress_percent }}%;"></div>
                            </div>
                            <div class="flex items-center justify-between text-[10px] text-slate-400 font-medium">
                                <span>Started: {{ \Carbon\Carbon::parse($plan->start_date)->format('M d, Y') }}</span>
                                <span>Matures: {{ \Carbon\Carbon::parse($plan->maturity_date)->format('M d, Y') }}</span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="pt-2 border-t border-slate-200/60 flex items-center justify-between gap-2">
                            <span class="text-[11px] font-black text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg">
                                {{ $plan->annual_interest_rate }}% P.A.
                            </span>

                            <a href="{{ route('savings.show', $plan->id) }}" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-black text-xs transition active:scale-95">
                                View Plan &rarr;
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="pt-4">
                {{ $plans->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="py-12 px-4 text-center space-y-4">
                <div class="w-20 h-20 rounded-3xl bg-teal-50 text-[#0d9488] mx-auto flex items-center justify-center text-4xl shadow-inner">
                    💰
                </div>
                <div class="space-y-1 max-w-sm mx-auto">
                    <h4 class="text-base font-black text-slate-900">No Savings Plans Found</h4>
                    <p class="text-xs text-slate-500">
                        @if($status !== 'all')
                            You do not have any {{ $status }} savings plans right now.
                        @else
                            You haven't started saving yet! Lock funds towards your goals and earn daily interest at up to 16.9% per annum.
                        @endif
                    </p>
                </div>
                <a href="{{ route('savings.create') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-[#0d9488] hover:bg-teal-700 text-white font-black text-xs uppercase tracking-wider shadow-lg shadow-teal-600/20 transition active:scale-95">
                    <span>+ Start Saving Now</span>
                </a>
            </div>
        @endif
    </div>

</div>

@endsection
