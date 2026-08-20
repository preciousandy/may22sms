@extends('layouts.admin')

@section('admin_content')
@push('header_title')
    Order Reports & Disputes
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

<!-- Overview Metrics Bar -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <div class="text-xs font-extrabold text-gray-500 uppercase tracking-wider">Pending Reports</div>
            <div class="text-2xl font-black text-rose-600 mt-1">{{ number_format($pendingCount) }}</div>
            <div class="text-xs font-bold text-rose-700 mt-0.5">{{ $pendingCount > 0 ? 'Requires attention' : 'No pending reports' }}</div>
        </div>
        <div class="p-3 bg-rose-50 text-rose-600 rounded-xl">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <div class="text-xs font-extrabold text-gray-500 uppercase tracking-wider">Resolved Reports</div>
            <div class="text-2xl font-black text-emerald-600 mt-1">{{ number_format($resolvedCount) }}</div>
            <div class="text-xs font-bold text-emerald-700 mt-0.5">Refunded or settled</div>
        </div>
        <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <div class="text-xs font-extrabold text-gray-500 uppercase tracking-wider">Dismissed Reports</div>
            <div class="text-2xl font-black text-gray-900 mt-1">{{ number_format($dismissedCount) }}</div>
            <div class="text-xs font-bold text-gray-500 mt-0.5">Reviewed & dismissed</div>
        </div>
        <div class="p-3 bg-gray-100 text-gray-700 rounded-xl">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
        </div>
    </div>
</div>

<!-- Header Controls Bar -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-xl font-black text-gray-900">User Order Reports</h2>
        <p class="text-xs font-bold text-gray-500 mt-0.5">Review reported issues for SMS verifications, Social Boosting, and Logs Store orders.</p>
    </div>

    <div class="flex flex-wrap items-center gap-1.5">
        @php
            $stCur = (string) ($status ?? 'pending');
            $tpCur = (string) ($type ?? 'all');
        @endphp
        <a href="/admin/reports?status=pending&type={{ $tpCur }}" class="px-3.5 py-2 rounded-xl text-xs font-extrabold transition {{ $stCur === 'pending' ? 'bg-rose-600 text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">
            Pending ({{ $pendingCount }})
        </a>
        <a href="/admin/reports?status=resolved&type={{ $tpCur }}" class="px-3.5 py-2 rounded-xl text-xs font-extrabold transition {{ $stCur === 'resolved' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">Resolved</a>
        <a href="/admin/reports?status=dismissed&type={{ $tpCur }}" class="px-3.5 py-2 rounded-xl text-xs font-extrabold transition {{ $stCur === 'dismissed' ? 'bg-gray-900 text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">Dismissed</a>
        <a href="/admin/reports?status=all&type={{ $tpCur }}" class="px-3.5 py-2 rounded-xl text-xs font-extrabold transition {{ $stCur === 'all' ? 'bg-gray-900 text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">All Statuses</a>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex items-center gap-2">
            <a href="/admin/reports?status={{ $stCur }}&type=all" class="px-3 py-1 rounded-lg text-xs font-extrabold {{ $tpCur === 'all' ? 'bg-gray-900 text-white' : 'bg-gray-200 text-gray-700' }}">All Types</a>
            <a href="/admin/reports?status={{ $stCur }}&type=sms" class="px-3 py-1 rounded-lg text-xs font-extrabold {{ $tpCur === 'sms' ? 'bg-gray-900 text-white' : 'bg-gray-200 text-gray-700' }}">SMS</a>
            <a href="/admin/reports?status={{ $stCur }}&type=boost" class="px-3 py-1 rounded-lg text-xs font-extrabold {{ $tpCur === 'boost' ? 'bg-gray-900 text-white' : 'bg-gray-200 text-gray-700' }}">Boosting</a>
            <a href="/admin/reports?status={{ $stCur }}&type=logs" class="px-3 py-1 rounded-lg text-xs font-extrabold {{ $tpCur === 'logs' ? 'bg-gray-900 text-white' : 'bg-gray-200 text-gray-700' }}">Logs</a>
        </div>

        <form method="GET" action="/admin/reports" class="flex items-center gap-2">
            <input type="hidden" name="status" value="{{ $stCur }}">
            <input type="hidden" name="type" value="{{ $tpCur }}">
            <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Search user/reason/order ID..." class="w-full sm:w-64 text-xs font-semibold rounded-xl border border-gray-200 px-3 py-1.5 focus:border-[#0d9488] focus:ring-0">
            <button type="submit" class="bg-gray-900 text-white px-3.5 py-1.5 rounded-xl text-xs font-extrabold hover:bg-gray-800 transition">Search</button>
        </form>
    </div>

    @if($reports->count() === 0)
        <div class="p-12 text-center font-bold text-gray-500">No reported orders found matching your filter.</div>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Report Info</th>
                        <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">User Account</th>
                        <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Transaction Details</th>
                        <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Report Reason</th>
                        <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3.5 text-right text-xs font-extrabold text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @foreach($reports as $r)
                        @php
                            $order = $r->order;
                            $u = $r->user;
                            $rSt = strtolower((string) $r->status);
                            $badge = $rSt === 'pending' ? 'bg-rose-50 text-rose-700 border-rose-200' : ($rSt === 'resolved' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-gray-100 text-gray-600');
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-6 py-4 text-xs whitespace-nowrap">
                                <div class="font-black text-gray-900">Report #{{ $r->id }}</div>
                                <div class="font-semibold text-gray-500 mt-0.5">{{ $r->created_at ? $r->created_at->format('M j, Y H:i') : '—' }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                                @if($u)
                                    <a href="/admin/users/{{ $u->id }}" class="font-extrabold text-gray-900 hover:text-[#0d9488] transition">{{ $u->name }}</a>
                                    <div class="text-xs font-semibold text-gray-500">{{ $u->email }}</div>
                                    <div class="text-[11px] font-black text-emerald-600 mt-0.5">Wallet: ${{ number_format((float) ($u->walletBalance ?? 0), 2) }}</div>
                                @else
                                    <span class="text-xs font-bold text-gray-400">User #{{ $r->user_id }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <div class="flex items-center gap-1.5 mb-1">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-gray-900 text-white">{{ $r->order_type }}</span>
                                    <span class="text-xs font-black text-gray-900">Order #{{ $r->order_id }}</span>
                                </div>
                                @if($order)
                                    @if($r->order_type === 'sms')
                                        <div class="text-xs font-extrabold text-gray-800">{{ $order->product }} ({{ $order->country }})</div>
                                        <div class="text-xs font-mono text-gray-500">+{{ $order->phone_number }} · Price: ${{ number_format((float) $order->price, 2) }}</div>
                                    @elseif($r->order_type === 'boost')
                                        <div class="text-xs font-extrabold text-gray-800">{{ $order->service->name ?? 'Boost Service' }}</div>
                                        <div class="text-xs text-gray-500">Qty: {{ number_format((int)$order->quantity) }} · Charge: ${{ number_format((float) $order->charge, 2) }}</div>
                                    @else
                                        <div class="text-xs font-extrabold text-gray-800">Logs Order #{{ $order->id }}</div>
                                        <div class="text-xs text-gray-500">{{ number_format((int) $order->total_items) }} items · Amount: ${{ number_format((float) $order->total_amount, 2) }}</div>
                                    @endif
                                    <div class="text-[11px] font-bold text-gray-400 mt-0.5">Status: <span class="uppercase font-black text-gray-800">{{ $order->status }}</span></div>
                                @else
                                    <div class="text-xs text-gray-400 italic">Order details unavailable</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-800 max-w-xs">
                                <div class="font-bold text-gray-900 bg-gray-50 p-2.5 rounded-xl border border-gray-100">
                                    {{ $r->reason ?: 'No reason provided.' }}
                                </div>
                                @if(!empty($r->admin_notes))
                                    <div class="text-[11px] font-extrabold text-teal-700 mt-1">Admin note: {{ $r->admin_notes }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-xs whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full border text-[10px] font-black {{ $badge }}">{{ strtoupper($rSt) }}</span>
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                @if($rSt === 'pending')
                                    <div class="flex items-center justify-end gap-1.5">
                                        <form action="/admin/reports/{{ $r->id }}/action" method="POST" onsubmit="return confirm('Refund order and credit user wallet?');">
                                            @csrf
                                            <input type="hidden" name="action" value="refund">
                                            <button type="submit" class="bg-[#0d9488] hover:bg-[#0f766e] text-white px-3 py-1.5 rounded-xl text-xs font-extrabold transition shadow-sm" title="Refund & Resolve">
                                                Refund & Resolve
                                            </button>
                                        </form>

                                        <form action="/admin/reports/{{ $r->id }}/action" method="POST">
                                            @csrf
                                            <input type="hidden" name="action" value="resolve">
                                            <button type="submit" class="bg-gray-900 hover:bg-gray-800 text-white px-3 py-1.5 rounded-xl text-xs font-extrabold transition">
                                                Resolve
                                            </button>
                                        </form>

                                        <form action="/admin/reports/{{ $r->id }}/action" method="POST">
                                            @csrf
                                            <input type="hidden" name="action" value="dismiss">
                                            <button type="submit" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-2.5 py-1.5 rounded-xl text-xs font-extrabold transition">
                                                Dismiss
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
            {{ $reports->links() }}
        </div>
    @endif
</div>
@endsection
