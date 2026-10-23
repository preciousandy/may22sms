@extends('layouts.admin')

@section('admin_content')
@push('header_title', 'Save Small Small — Customer Plans')

<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2">
                <span>Save Small Small</span>
                <span class="text-xs font-black uppercase px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800">16.9% P.A.</span>
            </h1>
            <p class="text-xs text-gray-500 mt-1">Monitor all customer goal savings plans, liabilities, accrued interest, and financial contracts.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.savings.categories') }}" class="px-4 py-2.5 rounded-xl bg-gray-900 hover:bg-gray-800 text-white font-bold text-xs uppercase tracking-wider transition shadow-sm flex items-center gap-1.5">
                <span>⚙️ Categories &amp; Terms</span>
            </a>
            <a href="{{ route('admin.savings.reconciliation') }}" class="px-4 py-2.5 rounded-xl bg-[#0d9488] hover:bg-teal-700 text-white font-bold text-xs uppercase tracking-wider transition shadow-sm flex items-center gap-1.5">
                <span>📊 Reserves Reconciliation</span>
            </a>
        </div>
    </div>

    <!-- Liability KPI Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- Total Active Principal Liability -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
            <span class="text-[10px] font-black uppercase tracking-wider text-gray-400 block">Customer Principal</span>
            <div class="text-xl font-black text-gray-900 mt-1">₦{{ number_format($totalCustomerPrincipal, 2) }}</div>
            <div class="text-[11px] font-semibold text-emerald-600 mt-0.5">{{ $activePlansCount }} active plans</div>
        </div>

        <!-- Accrued Interest Liability -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
            <span class="text-[10px] font-black uppercase tracking-wider text-gray-400 block">Interest Liability</span>
            <div class="text-xl font-black text-emerald-600 mt-1">+₦{{ number_format($totalAccruedInterestLiability, 2) }}</div>
            <div class="text-[11px] font-semibold text-gray-500 mt-0.5">Accrued daily @ 16.9%</div>
        </div>

        <!-- Total Paid Out -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
            <span class="text-[10px] font-black uppercase tracking-wider text-gray-400 block">Total Profit Paid</span>
            <div class="text-xl font-black text-teal-600 mt-1">₦{{ number_format($totalPaidInterest, 2) }}</div>
            <div class="text-[11px] font-semibold text-gray-500 mt-0.5">{{ $maturedPlansCount }} matured plans</div>
        </div>

        <!-- Matured Plans Count -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
            <span class="text-[10px] font-black uppercase tracking-wider text-gray-400 block">Completed / Matured</span>
            <div class="text-xl font-black text-gray-900 mt-1">{{ $maturedPlansCount }}</div>
            <div class="text-[11px] font-semibold text-gray-500 mt-0.5">Full payout executed</div>
        </div>

        <!-- Broken Plans -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
            <span class="text-[10px] font-black uppercase tracking-wider text-gray-400 block">Early Broken</span>
            <div class="text-xl font-black text-amber-600 mt-1">{{ $brokenPlansCount }}</div>
            <div class="text-[11px] font-semibold text-gray-500 mt-0.5">Interest forfeited</div>
        </div>
    </div>

    <!-- Filter Bar & Search -->
    <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm space-y-4">
        <form method="GET" action="{{ route('admin.savings.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1 relative">
                <input type="text" 
                       name="search" 
                       value="{{ $search ?? '' }}" 
                       placeholder="Search by customer name, email, plan title, or reference..." 
                       class="w-full pl-4 pr-10 py-2.5 rounded-xl border border-gray-200 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-[#0d9488]">
            </div>

            <select name="status" class="py-2.5 px-4 rounded-xl border border-gray-200 text-xs font-bold text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#0d9488]">
                <option value="">All Statuses</option>
                <option value="active" {{ ($status ?? '') === 'active' ? 'selected' : '' }}>Active Only</option>
                <option value="matured" {{ ($status ?? '') === 'matured' ? 'selected' : '' }}>Matured Only</option>
                <option value="broken" {{ ($status ?? '') === 'broken' ? 'selected' : '' }}>Broken Early</option>
            </select>

            <button type="submit" class="px-5 py-2.5 rounded-xl bg-gray-900 hover:bg-gray-800 text-white font-bold text-xs uppercase tracking-wider transition">
                Filter
            </button>

            @if(!empty($search) || !empty($status))
                <a href="{{ route('admin.savings.index') }}" class="px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs uppercase tracking-wider transition text-center">
                    Clear
                </a>
            @endif
        </form>

        <!-- Plans Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-gray-400 font-black border-b border-gray-100 text-[10px] uppercase tracking-wider">
                        <th class="pb-3">Reference / Plan</th>
                        <th class="pb-3">Customer</th>
                        <th class="pb-3">Category</th>
                        <th class="pb-3">Principal</th>
                        <th class="pb-3">Accrued Interest</th>
                        <th class="pb-3">Timeline</th>
                        <th class="pb-3">Status</th>
                        <th class="pb-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($plans as $plan)
                        @php
                            $currSymbol = $plan->currency === 'USD' ? '$' : '₦';
                            $statusBadge = match($plan->status) {
                                'active' => 'bg-emerald-100 text-emerald-800',
                                'matured' => 'bg-teal-100 text-teal-800',
                                'broken' => 'bg-amber-100 text-amber-800',
                                default => 'bg-gray-100 text-gray-700',
                            };
                        @endphp
                        <tr class="hover:bg-gray-50/60 transition">
                            <td class="py-3 font-medium">
                                <div class="font-black text-gray-900">{{ $plan->name }}</div>
                                <div class="font-mono text-[10px] text-gray-400">{{ $plan->reference }}</div>
                            </td>
                            <td class="py-3">
                                <div class="font-bold text-gray-800">{{ $plan->user->name ?? 'User #' . $plan->user_id }}</div>
                                <div class="text-[10px] text-gray-400">{{ $plan->user->email ?? '' }}</div>
                            </td>
                            <td class="py-3">
                                <span class="inline-flex items-center gap-1 font-bold text-gray-700">
                                    <span>{{ $plan->category->icon ?? '🎯' }}</span>
                                    <span>{{ $plan->category->name ?? 'Custom' }}</span>
                                </span>
                            </td>
                            <td class="py-3 font-black text-gray-900 font-mono">
                                {{ $currSymbol }}{{ number_format($plan->principal_amount, 2) }}
                            </td>
                            <td class="py-3 font-bold text-emerald-600 font-mono">
                                +{{ $currSymbol }}{{ number_format($plan->accrued_interest, 4) }}
                            </td>
                            <td class="py-3 text-[11px] text-gray-500">
                                <div>{{ $plan->days_remaining }} days left</div>
                                <div class="text-[10px] text-gray-400">Matures {{ \Carbon\Carbon::parse($plan->maturity_date)->format('M d, Y') }}</div>
                            </td>
                            <td class="py-3">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase {{ $statusBadge }}">
                                    {{ $plan->status }}
                                </span>
                            </td>
                            <td class="py-3 text-right">
                                <a href="{{ route('admin.savings.show', $plan->id) }}" class="px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-[#0d9488] hover:text-white font-bold text-xs text-gray-700 transition">
                                    Inspect &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-gray-400 text-xs">
                                No savings plans match your criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-2">
            {{ $plans->links() }}
        </div>
    </div>

</div>

@endsection
