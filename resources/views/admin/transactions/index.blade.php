@extends('layouts.admin')

@section('admin_content')
@push('header_title')
    System Audit Transactions
@endpush

@if(session('success'))
    <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-4 rounded-2xl shadow-sm flex items-center gap-3">
        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span class="font-bold text-sm">{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div class="mb-6 bg-rose-50 border border-rose-200 text-rose-800 px-5 py-4 rounded-2xl shadow-sm flex items-center gap-3">
        <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span class="font-bold text-sm">{{ session('error') }}</span>
    </div>
@endif

<!-- Top Overview Bar -->
<div class="mb-6 flex flex-wrap gap-2 items-center justify-between">
    <div>
        <h2 class="text-xl font-black text-gray-900">System Transactions Audit Log</h2>
        <p class="text-xs font-bold text-gray-500 mt-0.5">Comprehensive audit trail of wallet changes, order debits, refunds, and admin actions.</p>
    </div>
    <form action="/admin/transactions/backfill" method="POST" onsubmit="return confirm('Backfill existing historical data into transactions log?');">
        @csrf
        <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl bg-gray-900 px-4 py-2.5 text-xs font-extrabold text-white hover:bg-gray-800 transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
            Backfill Audit Logs
        </button>
    </form>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/80">
        <form method="GET" action="/admin/transactions" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
            <div>
                <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Search Reference / Email</label>
                <input name="q" value="{{ $q ?? '' }}" placeholder="Ref, ID, User email..." class="w-full text-xs font-semibold rounded-xl border border-gray-200 px-3 py-2 focus:border-[#0d9488] focus:ring-0">
            </div>
            <div>
                <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Transaction Type</label>
                <select name="type" class="w-full text-xs font-bold rounded-xl border border-gray-200 px-3 py-2 focus:border-[#0d9488] focus:ring-0">
                    <option value="all" {{ ($type ?? 'all') === 'all' ? 'selected' : '' }}>All Types</option>
                    @foreach(($typeOptions ?? []) as $t)
                        <option value="{{ $t }}" {{ ($type ?? 'all') === $t ? 'selected' : '' }}>{{ strtoupper($t) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Action Name</label>
                <select name="action" class="w-full text-xs font-bold rounded-xl border border-gray-200 px-3 py-2 focus:border-[#0d9488] focus:ring-0">
                    <option value="all" {{ ($action ?? 'all') === 'all' ? 'selected' : '' }}>All Actions</option>
                    @foreach(($actionOptions ?? []) as $a)
                        <option value="{{ $a }}" {{ ($action ?? 'all') === $a ? 'selected' : '' }}>{{ $a }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Status</label>
                <select name="status" class="w-full text-xs font-bold rounded-xl border border-gray-200 px-3 py-2 focus:border-[#0d9488] focus:ring-0">
                    <option value="all" {{ ($status ?? 'all') === 'all' ? 'selected' : '' }}>All Statuses</option>
                    @foreach(($statusOptions ?? []) as $s)
                        <option value="{{ $s }}" {{ ($status ?? 'all') === $s ? 'selected' : '' }}>{{ strtoupper($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="lg:col-span-4 flex items-center gap-2 pt-2">
                <button type="submit" class="bg-gray-900 text-white px-5 py-2 rounded-xl text-xs font-extrabold hover:bg-gray-800 transition">Filter Results</button>
                <a href="/admin/transactions" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-xl text-xs font-extrabold hover:bg-gray-200 transition">Reset Filters</a>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">TX ID</th>
                    <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Type & Action</th>
                    <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">User & Actor</th>
                    <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Amount</th>
                    <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Date & Time</th>
                    <th class="px-6 py-3.5 text-right text-xs font-extrabold text-gray-500 uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @foreach($transactions as $tx)
                    @php
                        $dt = $tx->created_at ? \Illuminate\Support\Carbon::parse($tx->created_at) : null;
                        $st = strtoupper((string) ($tx->status ?? ''));
                        $statusBadge = in_array($st, ['APPROVED', 'COMPLETED'], true) ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : ($st === 'REVERTED' ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-amber-50 text-amber-700 border-amber-200');
                    @endphp
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="px-6 py-4 text-xs font-black text-gray-900">#{{ $tx->id }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-gray-100 text-gray-800 border border-gray-200">{{ $tx->type }}</span>
                            <div class="font-extrabold text-gray-900 text-xs mt-1">{{ $tx->action }}</div>
                            @if(!empty($tx->reference))
                                <div class="text-[11px] font-mono text-gray-500">{{ $tx->reference }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <a href="/admin/users/{{ $tx->user_id }}" class="font-extrabold text-gray-900 hover:text-[#0d9488] transition">
                                {{ $tx->user->name ?? ('User #'.$tx->user_id) }}
                            </a>
                            <div class="text-xs font-semibold text-gray-500">{{ $tx->user->email ?? '' }}</div>
                            @if($tx->actor)
                                <div class="text-[11px] font-bold text-gray-400 mt-0.5">By: {{ $tx->actor->name }} {{ $tx->actor->is_admin ? '(Admin)' : '' }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm font-black text-gray-900 whitespace-nowrap">
                            @if($tx->amount_usd !== null)
                                <span class="{{ (float)$tx->amount_usd >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ (float) $tx->amount_usd >= 0 ? '+' : '' }}${{ number_format((float) $tx->amount_usd, 2) }}
                                </span>
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full border text-[10px] font-black {{ $statusBadge }}">{{ $st ?: '—' }}</span>
                            @if($tx->reverted_at)
                                <div class="text-[10px] font-extrabold text-rose-500 mt-0.5">Reverted</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-xs font-bold text-gray-600 whitespace-nowrap">{{ $dt ? $dt->format('M j, Y H:i') : '—' }}</td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <a href="/admin/transactions/{{ $tx->id }}" class="inline-flex items-center gap-1 bg-[#0d9488] hover:bg-[#0f766e] text-white px-3 py-1.5 rounded-xl text-xs font-extrabold transition shadow-sm">
                                Details
                            </a>
                        </td>
                    </tr>
                @endforeach

                @if($transactions->isEmpty())
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-gray-500 font-bold">No transactions found matching your criteria.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <div class="px-6 py-5 border-t border-gray-100 bg-gray-50">
        {{ $transactions->onEachSide(1)->links() }}
    </div>
</div>
@endsection
