@extends('layouts.admin')

@section('admin_content')
@push('header_title', 'Savings Plan Inspection')

@php
    $pSymbol = $plan->currency === 'USD' ? '$' : '₦';
    $statusBadge = match($plan->status) {
        'active' => 'bg-emerald-100 text-emerald-800',
        'matured' => 'bg-teal-100 text-teal-800',
        'broken' => 'bg-amber-100 text-amber-800',
        default => 'bg-gray-100 text-gray-700',
    };
@endphp

<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.savings.index') }}" class="text-xs font-bold text-gray-500 hover:text-gray-900">&larr; Back to Savings Dashboard</a>
            </div>
            <div class="flex items-center gap-3 mt-1">
                <span class="text-3xl">{{ $plan->category->icon ?? '🎯' }}</span>
                <div>
                    <h1 class="text-2xl font-black text-gray-900 leading-tight">{{ $plan->name }}</h1>
                    <span class="font-mono text-xs font-bold text-gray-400">Reference: {{ $plan->reference }}</span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <span class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider {{ $statusBadge }}">
                Status: {{ $plan->status }}
            </span>
        </div>
    </div>

    <!-- Overview Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
        <!-- Customer Info -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm space-y-1">
            <span class="text-[10px] font-black uppercase tracking-wider text-gray-400 block">Customer</span>
            <div class="text-base font-black text-gray-900">{{ $plan->user->name ?? 'User #' . $plan->user_id }}</div>
            <div class="text-[11px] text-gray-500">{{ $plan->user->email ?? '' }}</div>
            <a href="/admin/users/{{ $plan->user_id }}" class="inline-block text-[#0d9488] font-bold text-[11px] hover:underline pt-1">
                View User Profile &rarr;
            </a>
        </div>

        <!-- Principal Balance -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm space-y-1">
            <span class="text-[10px] font-black uppercase tracking-wider text-gray-400 block">Locked Principal</span>
            <div class="text-xl font-black text-gray-900 font-mono">{{ $pSymbol }}{{ number_format($plan->principal_amount, 2) }}</div>
            <div class="text-[11px] text-gray-400">Target: {{ $plan->target_amount ? $pSymbol . number_format($plan->target_amount, 2) : 'None' }}</div>
        </div>

        <!-- Accrued Interest -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm space-y-1">
            <span class="text-[10px] font-black uppercase tracking-wider text-gray-400 block">Accrued Profit</span>
            <div class="text-xl font-black text-emerald-600 font-mono">+{{ $pSymbol }}{{ number_format($plan->accrued_interest, 4) }}</div>
            <div class="text-[11px] text-emerald-600 font-bold">{{ $plan->annual_interest_rate }}% P.A. (Daily)</div>
        </div>

        <!-- Timeline & Dates -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm space-y-1">
            <span class="text-[10px] font-black uppercase tracking-wider text-gray-400 block">Contract Timeline</span>
            <div class="text-sm font-black text-gray-800">{{ $plan->duration_days }} Days Plan</div>
            <div class="text-[11px] text-gray-500">Maturity: {{ \Carbon\Carbon::parse($plan->maturity_date)->format('M d, Y') }}</div>
            <div class="text-[10px] text-teal-600 font-bold">{{ $plan->days_remaining }} days left</div>
        </div>
    </div>

    <!-- Payout Statement if available -->
    @if($plan->payout_statement)
        @php $statement = $plan->payout_statement; @endphp
        <div class="bg-gray-900 text-white p-6 rounded-2xl shadow-xl space-y-3">
            <div class="flex items-center justify-between border-b border-gray-800 pb-2">
                <span class="font-black text-xs uppercase tracking-wider text-teal-400">Settlement Payout Statement</span>
                <span class="text-xs text-gray-400">Processed: {{ \Carbon\Carbon::parse($statement['processed_at'] ?? now())->format('M d, Y h:i A') }}</span>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                <div>
                    <span class="text-gray-400 text-[10px]">Principal Settled:</span>
                    <div class="font-mono font-black text-base">{{ $pSymbol }}{{ number_format($statement['principal'] ?? 0, 2) }}</div>
                </div>
                <div>
                    <span class="text-gray-400 text-[10px]">Net Profit Paid:</span>
                    <div class="font-mono font-black text-emerald-400 text-base">+{{ $pSymbol }}{{ number_format($statement['net_interest'] ?? 0, 2) }}</div>
                </div>
                <div>
                    <span class="text-gray-400 text-[10px]">Fee / Forfeiture:</span>
                    <div class="font-mono font-black text-rose-400 text-base">-{{ $pSymbol }}{{ number_format($statement['forfeited_interest'] ?? ($statement['maturity_fee'] ?? 0), 2) }}</div>
                </div>
                <div>
                    <span class="text-gray-400 text-[10px]">Total Returned to Wallet:</span>
                    <div class="font-mono font-black text-[#facc15] text-base">{{ $pSymbol }}{{ number_format($statement['total_payout'] ?? ($statement['net_payout'] ?? 0), 2) }}</div>
                </div>
            </div>
        </div>
    @endif

    <!-- Contributions & Ledger History -->
    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm space-y-4">
        <h3 class="text-sm font-black text-gray-900 uppercase tracking-wider">Contributions &amp; Financial Ledger</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-gray-400 font-bold border-b border-gray-100 text-[10px] uppercase">
                        <th class="pb-2">Date</th>
                        <th class="pb-2">Type</th>
                        <th class="pb-2">Reference</th>
                        <th class="pb-2">Amount</th>
                        <th class="pb-2">Principal Before</th>
                        <th class="pb-2">Principal After</th>
                        <th class="pb-2 text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($plan->contributions as $trx)
                        <tr>
                            <td class="py-2.5 text-gray-500">{{ $trx->created_at->format('M d, Y h:i A') }}</td>
                            <td class="py-2.5 font-bold uppercase text-[10px]">{{ str_replace('_', ' ', $trx->type) }}</td>
                            <td class="py-2.5 font-mono text-gray-700 font-bold">{{ $trx->reference }}</td>
                            <td class="py-2.5 font-black text-gray-900 font-mono">{{ $pSymbol }}{{ number_format($trx->amount, 2) }}</td>
                            <td class="py-2.5 font-mono text-gray-500">{{ $pSymbol }}{{ number_format($trx->principal_before, 2) }}</td>
                            <td class="py-2.5 font-mono text-gray-900 font-bold">{{ $pSymbol }}{{ number_format($trx->principal_after, 2) }}</td>
                            <td class="py-2.5 text-right font-bold text-emerald-600">✓ {{ ucfirst($trx->status) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-4 text-center text-gray-400">No ledger entries recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Audit Logs -->
    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm space-y-4">
        <h3 class="text-sm font-black text-gray-900 uppercase tracking-wider">Immutable Contract Audit Trail</h3>
        <div class="space-y-2 text-xs">
            @forelse($auditLogs as $log)
                <div class="p-3 rounded-xl bg-gray-50 border border-gray-100 flex items-center justify-between">
                    <div>
                        <span class="font-black text-gray-800 font-mono">{{ $log->event }}</span>
                        <span class="text-gray-400 text-[10px] ml-2">by {{ $log->actor->name ?? 'System/Automated' }} ({{ $log->ip_address }})</span>
                    </div>
                    <span class="text-gray-400 text-[10px]">{{ \Carbon\Carbon::parse($log->created_at)->format('M d, Y h:i:s A') }}</span>
                </div>
            @empty
                <div class="text-gray-400 text-xs">No audit logs recorded for this plan.</div>
            @endforelse
        </div>
    </div>

</div>

@endsection
