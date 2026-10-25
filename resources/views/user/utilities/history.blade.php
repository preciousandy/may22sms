@extends('layouts.dashboard')

@section('page_title', 'Utility & VTU Transaction History')

@section('dashboard_content')
<div class="max-w-6xl mx-auto space-y-6">

    <!-- Top Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 font-bold uppercase tracking-wider mb-1">
                <a href="{{ route('user.utilities.index') }}" class="hover:text-teal-600 transition">Utilities Hub</a>
                <span>/</span>
                <span class="text-slate-800">Transaction History</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Utility Transactions</h1>
            <p class="text-slate-500 text-sm mt-0.5">Track all your airtime, data, electricity, and betting transactions.</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('user.utilities.index') }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs uppercase tracking-wider shadow-sm transition active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                New Purchase
            </a>
        </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-sm space-y-3">
        <form method="GET" action="{{ route('user.utilities.history') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <!-- Search -->
            <div class="sm:col-span-5 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" 
                       name="q" 
                       value="{{ $q ?? $search ?? '' }}" 
                       placeholder="Search reference, recipient phone/meter/ID..." 
                       class="w-full pl-10 pr-4 py-2.5 text-xs font-semibold rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 text-slate-800 placeholder-slate-400">
            </div>

            <!-- Service Type Filter -->
            <div class="sm:col-span-3">
                <select name="type" class="w-full py-2.5 px-3 text-xs font-semibold rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 text-slate-800 bg-white">
                    <option value="">All Services</option>
                    <option value="airtime" {{ ($type ?? '') === 'airtime' ? 'selected' : '' }}>Airtime Recharge</option>
                    <option value="data" {{ ($type ?? '') === 'data' ? 'selected' : '' }}>Data Bundles</option>
                    <option value="electricity" {{ ($type ?? '') === 'electricity' ? 'selected' : '' }}>Electricity Bill</option>
                    <option value="betting" {{ ($type ?? '') === 'betting' ? 'selected' : '' }}>Betting Funding</option>
                </select>
            </div>

            <!-- Status Filter -->
            <div class="sm:col-span-2">
                <select name="status" class="w-full py-2.5 px-3 text-xs font-semibold rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 text-slate-800 bg-white">
                    <option value="">All Statuses</option>
                    <option value="completed" {{ ($status ?? '') === 'completed' ? 'selected' : '' }}>Successful</option>
                    <option value="pending" {{ ($status ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="uncertain" {{ ($status ?? '') === 'uncertain' ? 'selected' : '' }}>Uncertain</option>
                    <option value="failed" {{ ($status ?? '') === 'failed' ? 'selected' : '' }}>Failed</option>
                    <option value="refunded" {{ ($status ?? '') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                </select>
            </div>

            <!-- Action buttons -->
            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit" class="flex-1 py-2.5 px-4 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition">
                    Filter
                </button>
                @if(($q ?? $search ?? '') || $type || $status)
                    <a href="{{ route('user.utilities.history') }}" class="py-2.5 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition flex items-center justify-center" title="Clear Filters">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Transactions List Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        @if($transactions->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50/80 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-extrabold text-[10px]">
                        <tr>
                            <th class="py-3.5 px-4">Service &amp; Ref</th>
                            <th class="py-3.5 px-4">Recipient / Meter</th>
                            <th class="py-3.5 px-4">Amount</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-4">Date &amp; Time</th>
                            <th class="py-3.5 px-4 text-right">Receipt</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($transactions as $txn)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-xl flex items-center justify-center font-bold text-sm shrink-0
                                            @if($txn->service_type === 'airtime') bg-amber-50 text-amber-600 border border-amber-200
                                            @elseif($txn->service_type === 'data') bg-blue-50 text-blue-600 border border-blue-200
                                            @elseif($txn->service_type === 'electricity') bg-emerald-50 text-emerald-600 border border-emerald-200
                                            @else bg-purple-50 text-purple-600 border border-purple-200
                                            @endif">
                                            @if($txn->service_type === 'airtime')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                            @elseif($txn->service_type === 'data')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>
                                            @elseif($txn->service_type === 'electricity')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"/><circle cx="12" cy="12" r="5" stroke-width="2"/><circle cx="12" cy="12" r="1.5" fill="currentColor"/></svg>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="font-extrabold text-slate-900 uppercase">
                                                {{ $txn->network_or_disco ?? $txn->service_type }} 
                                                @if($txn->service_type === 'data')
                                                    <span class="text-slate-500 text-[11px] font-normal lowercase">({{ $txn->product_name ?? 'bundle' }})</span>
                                                @endif
                                            </div>
                                            <div class="font-mono text-[10px] text-slate-400">
                                                {{ $txn->reference }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="py-3.5 px-4">
                                    <div class="font-mono font-bold text-slate-800 text-xs">
                                        {{ $txn->customer_target }}
                                    </div>
                                    @if($txn->token)
                                        <div class="text-[10px] text-emerald-600 font-extrabold mt-0.5 font-mono flex items-center gap-1">
                                            <span>TOKEN:</span> {{ $txn->token }}
                                        </div>
                                    @elseif($txn->customer_name)
                                        <div class="text-[10px] text-slate-500 font-medium">
                                            {{ $txn->customer_name }}
                                        </div>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4">
                                    <div class="font-extrabold text-slate-900">
                                        ₦{{ number_format((float)$txn->amount_ngn, 2) }}
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-semibold">
                                        ${{ number_format((float)$txn->total_charged_usd, 4) }} USD
                                    </div>
                                </td>

                                <td class="py-3.5 px-4">
                                    @if($txn->status === 'completed')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                            Successful
                                        </span>
                                    @elseif($txn->status === 'pending')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-50 text-amber-700 border border-amber-200">
                                            <svg class="w-2.5 h-2.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                            Processing
                                        </span>
                                    @elseif($txn->status === 'failed')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-50 text-rose-700 border border-rose-200">
                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                                            Failed
                                        </span>
                                    @elseif($txn->status === 'refunded')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-blue-50 text-blue-700 border border-blue-200">
                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 10h10a5 5 0 0 1 5 5v2m0 0l-3-3m3 3l3-3M3 10l3-3M3 10l3 3"/></svg>
                                            Refunded
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-yellow-50 text-yellow-800 border border-yellow-300">
                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                            Pending Verification
                                        </span>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 text-slate-500 whitespace-nowrap">
                                    <div class="font-semibold">{{ $txn->created_at->format('M d, Y') }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $txn->created_at->format('h:i A') }}</div>
                                </td>

                                <td class="py-3.5 px-4 text-right">
                                    <a href="{{ route('user.utilities.receipt', $txn->reference) }}" 
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                                        View
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $transactions->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-16 px-4">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mb-3">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <h3 class="text-base font-extrabold text-slate-800">No transactions found</h3>
                <p class="text-slate-500 text-xs mt-1 max-w-sm mx-auto">
                    You haven't made any utility transactions yet, or no records match your filter criteria.
                </p>
                <div class="mt-5">
                    <a href="{{ route('user.utilities.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs uppercase tracking-wider transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        Purchase Airtime, Data or Utilities
                    </a>
                </div>
            </div>
        @endif
    </div>

</div>
@endsection
