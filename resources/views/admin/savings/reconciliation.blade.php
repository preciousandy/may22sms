@extends('layouts.admin')

@section('admin_content')
@push('header_title', 'Reserves & Liabilities Reconciliation')

<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.savings.index') }}" class="text-xs font-bold text-gray-500 hover:text-gray-900">&larr; Savings Dashboard</a>
            </div>
            <h1 class="text-2xl font-black text-gray-900 mt-1">Safeguarded Reserves &amp; Liabilities Reconciliation</h1>
            <p class="text-xs text-gray-500 mt-1">Audit customer savings liabilities against wallet balances to ensure 100% full-reserve solvency.</p>
        </div>
    </div>

    <!-- Reconciliation Balance Sheet Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Net Customer Liability -->
        <div class="bg-gradient-to-br from-gray-900 to-slate-800 p-5 rounded-2xl text-white shadow-lg space-y-1">
            <span class="text-[10px] font-black uppercase tracking-wider text-teal-400 block">Total Safeguarded Liability</span>
            <div class="text-2xl font-black text-[#facc15] font-mono mt-1">₦{{ number_format($totalLiability, 2) }}</div>
            <div class="text-[11px] text-gray-300">Principal + Accrued Profit</div>
        </div>

        <!-- Active Principal -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm space-y-1">
            <span class="text-[10px] font-black uppercase tracking-wider text-gray-400 block">Active Principal Reserve</span>
            <div class="text-xl font-black text-gray-900 font-mono mt-1">₦{{ number_format($totalActivePrincipal, 2) }}</div>
            <div class="text-[11px] text-gray-400">Total customer deposits locked</div>
        </div>

        <!-- Accrued Interest -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm space-y-1">
            <span class="text-[10px] font-black uppercase tracking-wider text-gray-400 block">Accrued Interest Liability</span>
            <div class="text-xl font-black text-emerald-600 font-mono mt-1">+₦{{ number_format($totalAccruedInterest, 2) }}</div>
            <div class="text-[11px] text-gray-400">Daily return liability</div>
        </div>

        <!-- Forfeited Interest Reserve -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm space-y-1">
            <span class="text-[10px] font-black uppercase tracking-wider text-gray-400 block">Forfeited Profit Reserve</span>
            <div class="text-xl font-black text-teal-600 font-mono mt-1">₦{{ number_format($totalForfeitedInterest, 2) }}</div>
            <div class="text-[11px] text-gray-400">Retained via early breaks</div>
        </div>
    </div>

    <!-- Financial Movements Summary Table -->
    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm space-y-3">
        <h3 class="text-sm font-black text-gray-900 uppercase tracking-wider">Cumulative Inflows vs Outflows</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
            <div class="p-4 rounded-xl bg-gray-50 border border-gray-100">
                <span class="text-gray-400 text-[10px] uppercase font-bold block">Total Inflow Deposits</span>
                <div class="text-lg font-black text-emerald-600 font-mono mt-1">₦{{ number_format($totalDeposited, 2) }}</div>
                <div class="text-[10px] text-gray-400">Debited from main wallets</div>
            </div>
            <div class="p-4 rounded-xl bg-gray-50 border border-gray-100">
                <span class="text-gray-400 text-[10px] uppercase font-bold block">Total Outflows &amp; Settlements</span>
                <div class="text-lg font-black text-rose-600 font-mono mt-1">₦{{ number_format($totalWithdrawn, 2) }}</div>
                <div class="text-[10px] text-gray-400">Returned to main wallets</div>
            </div>
            <div class="p-4 rounded-xl bg-gray-50 border border-gray-100">
                <span class="text-gray-400 text-[10px] uppercase font-bold block">Total Profit Paid</span>
                <div class="text-lg font-black text-teal-600 font-mono mt-1">₦{{ number_format($totalPaidInterest, 2) }}</div>
                <div class="text-[10px] text-gray-400">Net interest payouts</div>
            </div>
        </div>
    </div>

    <!-- Immutable Audit Trail -->
    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm space-y-4">
        <h3 class="text-sm font-black text-gray-900 uppercase tracking-wider">Immutable System Audit Logs</h3>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-gray-400 font-bold border-b border-gray-100 text-[10px] uppercase">
                        <th class="pb-2">Timestamp</th>
                        <th class="pb-2">Event</th>
                        <th class="pb-2">Plan</th>
                        <th class="pb-2">Actor / User</th>
                        <th class="pb-2">IP Address</th>
                        <th class="pb-2 text-right">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($auditLogs as $log)
                        <tr>
                            <td class="py-2.5 text-gray-500 font-medium">
                                {{ \Carbon\Carbon::parse($log->created_at)->format('M d, Y h:i:s A') }}
                            </td>
                            <td class="py-2.5 font-bold font-mono text-gray-900">
                                {{ $log->event }}
                            </td>
                            <td class="py-2.5">
                                @if($log->plan)
                                    <a href="{{ route('admin.savings.show', $log->plan->id) }}" class="font-bold text-[#0d9488] hover:underline">
                                        {{ $log->plan->name }} ({{ $log->plan->reference }})
                                    </a>
                                @else
                                    <span class="text-gray-400">General</span>
                                @endif
                            </td>
                            <td class="py-2.5 text-gray-600">
                                {{ $log->actor->name ?? ($log->user->name ?? 'System/Automated') }}
                            </td>
                            <td class="py-2.5 font-mono text-gray-400">
                                {{ $log->ip_address }}
                            </td>
                            <td class="py-2.5 text-right font-mono text-[10px] text-gray-500">
                                @if($log->new_values)
                                    <span title="{{ json_encode($log->new_values) }}" class="bg-gray-100 px-2 py-1 rounded cursor-help">View JSON</span>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-gray-400">No audit logs recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-2">
            {{ $auditLogs->links() }}
        </div>
    </div>

</div>

@endsection
