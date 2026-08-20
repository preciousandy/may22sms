@extends('layouts.admin')

@section('admin_content')
@push('header_title')
    User Funding & Deposits
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
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-xl font-black text-gray-900">Deposit & Funding Requests</h2>
        <p class="text-xs font-bold text-gray-500 mt-0.5">Approve, reject, cancel, or audit pending and waiting payment user deposits.</p>
    </div>

    <div class="flex flex-wrap items-center gap-1.5">
        @php $stCur = strtolower((string) ($status ?? 'pending')); @endphp
        <a href="/admin/funding?status=pending" class="px-3.5 py-2 rounded-xl text-xs font-extrabold transition {{ $stCur === 'pending' ? 'bg-amber-600 text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">Pending</a>
        <a href="/admin/funding?status=waiting_payment" class="px-3.5 py-2 rounded-xl text-xs font-extrabold transition {{ $stCur === 'waiting_payment' ? 'bg-amber-600 text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">Waiting Payment</a>
        <a href="/admin/funding?status=initiated" class="px-3.5 py-2 rounded-xl text-xs font-extrabold transition {{ $stCur === 'initiated' ? 'bg-gray-900 text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">Initiated</a>
        <a href="/admin/funding?status=approved" class="px-3.5 py-2 rounded-xl text-xs font-extrabold transition {{ $stCur === 'approved' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">Approved</a>
        <a href="/admin/funding?status=rejected" class="px-3.5 py-2 rounded-xl text-xs font-extrabold transition {{ $stCur === 'rejected' ? 'bg-rose-600 text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">Rejected / Canceled</a>
        <a href="/admin/funding?status=all" class="px-3.5 py-2 rounded-xl text-xs font-extrabold transition {{ $stCur === 'all' ? 'bg-gray-900 text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">All</a>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    @if($funding->count() === 0)
        <div class="p-12 text-center text-gray-500 font-bold">No funding requests found for status: <span class="uppercase text-gray-900 font-black">{{ $stCur }}</span></div>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Date & Time</th>
                        <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">User Account</th>
                        <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Gateway</th>
                        <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Original Amount</th>
                        <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">USD Credit</th>
                        <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Reference</th>
                        <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3.5 text-right text-xs font-extrabold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @foreach($funding as $tx)
                        @php
                            $st = strtolower((string) $tx->status);
                            $badge = $st === 'approved' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : (in_array($st, ['rejected', 'failed', 'canceled', 'cancelled'], true) ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-amber-50 text-amber-700 border-amber-200 animate-pulse');
                            $canAct = !in_array($st, ['approved', 'rejected', 'canceled', 'cancelled'], true);
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-6 py-4 text-xs font-bold text-gray-600 whitespace-nowrap">{{ $tx->created_at ? $tx->created_at->format('M d, Y H:i') : '—' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                                <a href="/admin/users/{{ $tx->user_id }}" class="font-extrabold text-gray-900 hover:text-[#0d9488] transition">User #{{ $tx->user_id }}</a>
                                <div class="text-xs text-gray-500 font-medium">{{ $tx->user->email ?? '' }}</div>
                            </td>
                            <td class="px-6 py-4 text-xs font-extrabold text-gray-800 uppercase tracking-wider whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-lg bg-gray-100">{{ $tx->payment_method }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm font-extrabold text-gray-700 whitespace-nowrap">
                                {{ $tx->currency }} {{ number_format($tx->amount, 2) }}
                            </td>
                            <td class="px-6 py-4 text-sm font-black text-[#0d9488] whitespace-nowrap">
                                ${{ number_format($tx->amount_usd, 2) }}
                            </td>
                            <td class="px-6 py-4 text-xs font-mono font-semibold text-gray-600 whitespace-nowrap">{{ $tx->reference }}</td>
                            <td class="px-6 py-4 text-sm whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full border text-[10px] font-black {{ $badge }}">{{ strtoupper($st) }}</span>
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                @if($canAct)
                                    <div class="flex items-center justify-end gap-2">
                                        <form action="/admin/funding/{{ $tx->id }}/approve" method="POST" onsubmit="return confirm('Approve deposit and credit user wallet?');">
                                            @csrf
                                            <button type="submit" class="bg-[#0d9488] hover:bg-[#0f766e] text-white px-3.5 py-1.5 rounded-xl text-xs font-extrabold transition shadow-sm" title="Approve & Credit">
                                                Approve
                                            </button>
                                        </form>
                                        <form action="/admin/funding/{{ $tx->id }}/reject" method="POST" onsubmit="return confirm('Cancel and reject this deposit request?');">
                                            @csrf
                                            <button type="submit" class="bg-rose-50 text-rose-700 hover:bg-rose-100 px-3 py-1.5 rounded-xl text-xs font-extrabold transition border border-rose-200" title="Cancel & Reject">
                                                Cancel & Reject
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-xs font-bold text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-6 py-5 border-t border-gray-100 bg-gray-50">
            {{ $funding->links() }}
        </div>
    @endif
</div>
@endsection
