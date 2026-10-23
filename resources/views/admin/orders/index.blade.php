@extends('layouts.admin')

@section('admin_content')
@push('header_title')
    Manage Orders & Refunds
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

<!-- Top Overview Metrics Bar -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <div class="text-xs font-extrabold text-gray-500 uppercase tracking-wider">Total Logs Orders</div>
            <div class="text-2xl font-black text-gray-900 mt-1">{{ number_format((int) ($counts['logs'] ?? 0)) }}</div>
            <div class="text-xs font-bold text-amber-600 mt-0.5">{{ number_format((int) ($pendingCounts['logs'] ?? 0)) }} Pending</div>
        </div>
        <div class="p-3 bg-teal-50 text-[#0d9488] rounded-xl">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
        </div>
    </div>
    
    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <div class="text-xs font-extrabold text-gray-500 uppercase tracking-wider">Total SMS Orders</div>
            <div class="text-2xl font-black text-gray-900 mt-1">{{ number_format((int) ($counts['sms'] ?? 0)) }}</div>
            <div class="text-xs font-bold text-amber-600 mt-0.5">{{ number_format((int) ($pendingCounts['sms'] ?? 0)) }} Waiting</div>
        </div>
        <div class="p-3 bg-amber-50 text-amber-600 rounded-xl">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <div class="text-xs font-extrabold text-gray-500 uppercase tracking-wider">Total Boost Orders</div>
            <div class="text-2xl font-black text-gray-900 mt-1">{{ number_format((int) ($counts['boost'] ?? 0)) }}</div>
            <div class="text-xs font-bold text-amber-600 mt-0.5">{{ number_format((int) ($pendingCounts['boost'] ?? 0)) }} Processing</div>
        </div>
        <div class="p-3 bg-indigo-50 text-indigo-600 rounded-xl">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <div class="text-xs font-extrabold text-gray-500 uppercase tracking-wider">Funding Requests</div>
            <div class="text-2xl font-black text-gray-900 mt-1">{{ number_format((int) ($counts['funding'] ?? 0)) }}</div>
            <div class="text-xs font-bold text-amber-600 mt-0.5">{{ number_format((int) ($pendingCounts['funding'] ?? 0)) }} Pending</div>
        </div>
        <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/80 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h3 class="text-xl font-black text-gray-900">Orders & Refunds Management</h3>
            <p class="text-xs font-bold text-gray-500 mt-0.5">Filter user orders, view service details, and issue instant refunds for pending items.</p>
        </div>
        <form method="GET" action="/admin/orders" class="flex flex-col sm:flex-row gap-2 sm:items-center">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Search user, order ID, email..." class="w-full sm:w-64 px-4 py-2 text-sm rounded-xl border border-gray-200 focus:border-[#0d9488] focus:ring-0 transition">
            <select name="status" class="w-full sm:w-48 px-3 py-2 text-sm rounded-xl border border-gray-200 focus:border-[#0d9488] focus:ring-0 transition">
                @foreach(($statusOptions ?? []) as $opt)
                    <option value="{{ $opt }}" {{ (string) ($status ?? 'all') === (string) $opt ? 'selected' : '' }}>
                        {{ strtoupper((string) $opt) }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="bg-gray-900 text-white px-5 py-2 rounded-xl text-sm font-bold hover:bg-gray-800 transition">Filter</button>
        </form>
    </div>

    <div class="px-6 py-4 border-b border-gray-100 bg-white">
        <div class="flex flex-wrap gap-2">
            <a href="/admin/orders?tab=logs" class="px-4 py-2.5 rounded-xl font-black text-xs uppercase tracking-wider transition border {{ $tab === 'logs' ? 'bg-gray-900 text-white border-gray-900 shadow-sm' : 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100' }}">
                Logs ({{ number_format((int) ($counts['logs'] ?? 0)) }})
                @if(($pendingCounts['logs'] ?? 0) > 0)
                    <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] bg-amber-500 text-white font-extrabold">{{ $pendingCounts['logs'] }}</span>
                @endif
            </a>
            <a href="/admin/orders?tab=sms" class="px-4 py-2.5 rounded-xl font-black text-xs uppercase tracking-wider transition border {{ $tab === 'sms' ? 'bg-gray-900 text-white border-gray-900 shadow-sm' : 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100' }}">
                SMS Numbers ({{ number_format((int) ($counts['sms'] ?? 0)) }})
                @if(($pendingCounts['sms'] ?? 0) > 0)
                    <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] bg-amber-500 text-white font-extrabold">{{ $pendingCounts['sms'] }}</span>
                @endif
            </a>
            <a href="/admin/orders?tab=boost" class="px-4 py-2.5 rounded-xl font-black text-xs uppercase tracking-wider transition border {{ $tab === 'boost' ? 'bg-gray-900 text-white border-gray-900 shadow-sm' : 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100' }}">
                Boosting ({{ number_format((int) ($counts['boost'] ?? 0)) }})
                @if(($pendingCounts['boost'] ?? 0) > 0)
                    <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] bg-amber-500 text-white font-extrabold">{{ $pendingCounts['boost'] }}</span>
                @endif
            </a>
            <a href="/admin/orders?tab=funding" class="px-4 py-2.5 rounded-xl font-black text-xs uppercase tracking-wider transition border {{ $tab === 'funding' ? 'bg-gray-900 text-white border-gray-900 shadow-sm' : 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100' }}">
                Funding ({{ number_format((int) ($counts['funding'] ?? 0)) }})
                @if(($pendingCounts['funding'] ?? 0) > 0)
                    <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] bg-amber-500 text-white font-extrabold">{{ $pendingCounts['funding'] }}</span>
                @endif
            </a>
        </div>
    </div>

    @if(($orders ?? null) && $orders->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Date & Time</th>
                        <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">User Details</th>
                        <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Order Items</th>
                        <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3.5 text-right text-xs font-extrabold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @foreach($orders as $o)
                        @php
                            $st = strtoupper((string) ($o->status ?? ''));
                            $isPending = in_array($st, ['PENDING', 'WAITING', 'INITIATED', 'WAITING_PAYMENT', 'PROCESSING'], true);
                            $isTerminal = in_array($st, ['CANCELED', 'CANCELLED', 'REFUNDED', 'REJECTED'], true);
                            $isSuccess = in_array($st, ['RECEIVED', 'COMPLETED', 'FINISHED', 'APPROVED'], true);
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-gray-600">
                                {{ $o->created_at ? \Illuminate\Support\Carbon::parse($o->created_at)->format('M d, Y H:i') : '—' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <a href="/admin/users/{{ $o->user_id }}" class="font-extrabold text-gray-900 hover:text-[#0d9488] transition">
                                    {{ $o->user->name ?? 'User #'.$o->user_id }}
                                </a>
                                <div class="text-xs text-gray-500 font-medium">{{ $o->user->email ?? '' }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                @if($tab === 'logs')
                                    <div class="font-black text-gray-900">Log Order #{{ $o->id }}</div>
                                    <div class="text-xs text-gray-500 font-semibold">{{ number_format((int) ($o->total_items ?? 0)) }} item(s)</div>
                                @elseif($tab === 'sms')
                                    <div class="font-black text-gray-900">SMS Order #{{ $o->id }}</div>
                                    <div class="text-xs text-gray-500 capitalize font-semibold">{{ $o->product ?? '—' }} · <span class="font-mono text-gray-800">{{ $o->phone_number ?? '—' }}</span></div>
                                @elseif($tab === 'boost')
                                    <div class="font-black text-gray-900">Boost Order #{{ $o->id }}</div>
                                    <div class="text-xs text-gray-500 font-semibold">{{ $o->service->name ?? '—' }} · Qty {{ number_format((int) ($o->quantity ?? 0)) }}</div>
                                @elseif($tab === 'funding')
                                    <div class="font-black text-gray-900">Funding #{{ $o->id }}</div>
                                    <div class="text-xs text-gray-500 font-semibold">{{ $o->reference ?? '—' }} · {{ strtoupper((string) ($o->payment_method ?? '')) }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-black text-gray-900">
                                @if($tab === 'logs')
                                    ${{ number_format((float) ($o->total_amount ?? 0), 2) }}
                                    @if(!empty($o->promo_discount_usd))
                                        <div class="text-xs font-extrabold text-teal-600">Promo: -${{ number_format((float) $o->promo_discount_usd, 2) }}</div>
                                    @endif
                                @elseif($tab === 'sms')
                                    ${{ number_format((float) ($o->price ?? 0), 2) }}
                                    @if(!empty($o->promo_discount_usd))
                                        <div class="text-xs font-extrabold text-teal-600">Promo: -${{ number_format((float) $o->promo_discount_usd, 2) }}</div>
                                    @endif
                                @elseif($tab === 'boost')
                                    ${{ number_format((float) ($o->charge ?? 0), 2) }}
                                    @if(!empty($o->promo_discount_usd))
                                        <div class="text-xs font-extrabold text-teal-600">Promo: -${{ number_format((float) $o->promo_discount_usd, 2) }}</div>
                                    @endif
                                @elseif($tab === 'funding')
                                    ${{ number_format((float) ($o->amount_usd ?? 0), 2) }}
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="inline-flex items-center gap-1.5 text-xs font-black px-3 py-1 rounded-full {{ $isSuccess ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($isTerminal ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-amber-50 text-amber-700 border border-amber-200 animate-pulse') }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $isSuccess ? 'bg-emerald-500' : ($isTerminal ? 'bg-rose-500' : 'bg-amber-500') }}"></span>
                                    {{ $st }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                @php
                                    $orderAmt = (float) ($tab === 'logs' ? ($o->total_amount ?? 0) : ($tab === 'sms' ? ($o->price ?? 0) : ($tab === 'boost' ? ($o->charge ?? 0) : ($o->amount_usd ?? 0))));
                                @endphp
                                <div class="flex items-center justify-end gap-1.5" x-data="{ confirmingCancel: false, confirmingRefund: false, confirmingApprove: false, confirmingReverse: false }">
                                    @if($tab === 'funding')
                                        @if($isPending)
                                            <!-- Approve Deposit -->
                                            <button 
                                                type="button" 
                                                @click="confirmingApprove = true" 
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-black shadow-sm transition"
                                                title="Approve and credit wallet">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                Approve
                                            </button>

                                            <!-- Cancel Unpaid Deposit (No Refund) -->
                                            <button 
                                                type="button" 
                                                @click="confirmingCancel = true" 
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-black transition"
                                                title="Cancel unpaid deposit (no refund)">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                Cancel
                                            </button>
                                        @elseif($st === 'APPROVED')
                                            <!-- Reverse Approved Deposit -->
                                            <button 
                                                type="button" 
                                                @click="confirmingReverse = true" 
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 text-xs font-black transition"
                                                title="Reverse approved funding and debit wallet">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                                                Reverse
                                            </button>
                                        @endif
                                    @else
                                        @if($isPending)
                                            <!-- Cancel & Refund -->
                                            <button 
                                                type="button" 
                                                @click="confirmingRefund = true" 
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-black shadow-sm transition"
                                                title="Refund order charge to wallet">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v14a2 2 0 002 2h12a2 2 0 002-2z"></path></svg>
                                                Refund
                                            </button>

                                            <!-- Cancel Without Refund -->
                                            <button 
                                                type="button" 
                                                @click="confirmingCancel = true" 
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-200 text-xs font-black transition"
                                                title="Cancel order without wallet refund">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                                Cancel
                                            </button>
                                        @elseif($isSuccess)
                                            <!-- Refund Completed Order -->
                                            <button 
                                                type="button" 
                                                @click="confirmingRefund = true" 
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-black transition"
                                                title="Refund completed order">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v14a2 2 0 002 2h12a2 2 0 002-2z"></path></svg>
                                                Refund
                                            </button>
                                        @endif
                                    @endif

                                    <a href="/admin/users/{{ $o->user_id }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-black transition">
                                        Profile
                                    </a>

                                    <!-- MODAL: Cancel (No Refund) -->
                                    <div x-show="confirmingCancel" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" style="display: none;" @click.self="confirmingCancel = false">
                                        <div class="bg-white rounded-3xl p-6 max-w-md w-full text-left shadow-2xl border border-gray-100">
                                            <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                                                <h4 class="text-lg font-black text-gray-900">
                                                    @if($tab === 'funding')
                                                        Cancel Unpaid Deposit #{{ $o->id }}
                                                    @else
                                                        Cancel Order #{{ $o->id }} (No Refund)
                                                    @endif
                                                </h4>
                                                <button type="button" @click="confirmingCancel = false" class="text-gray-400 hover:text-gray-600">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                </button>
                                            </div>
                                            
                                            <div class="py-4 space-y-3">
                                                @if($tab === 'funding')
                                                    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-xs font-bold text-amber-900">
                                                        <div class="flex items-center gap-2 font-black text-amber-800 uppercase tracking-wide text-[10px] mb-1">
                                                            <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                            Zero Wallet Impact
                                                        </div>
                                                        This deposit was initiated by the user but was not paid. Canceling marks it as canceled/rejected. <strong>No funds will be refunded or credited to the user's wallet.</strong>
                                                    </div>
                                                @else
                                                    <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4 text-xs font-bold text-rose-900">
                                                        <div class="flex items-center gap-2 font-black text-rose-800 uppercase tracking-wide text-[10px] mb-1">
                                                            <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                                            Cancellation Without Refund
                                                        </div>
                                                        Are you sure you want to cancel this order <strong>WITHOUT refunding</strong> the ${{ number_format($orderAmt, 2) }} payment to the user's wallet? Use this only for violations or abuse.
                                                    </div>
                                                @endif

                                                <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100 text-xs font-bold space-y-1">
                                                    <div class="text-gray-500 uppercase text-[10px] tracking-wider">Item Details</div>
                                                    <div class="text-gray-900">User: {{ $o->user->name ?? 'User' }} ({{ $o->user->email ?? '' }})</div>
                                                    <div class="text-gray-900">Record: {{ strtoupper($tab) }} #{{ $o->id }}</div>
                                                    <div class="text-gray-700 font-extrabold mt-1">Amount: ${{ number_format($orderAmt, 2) }}</div>
                                                </div>
                                            </div>

                                            <form method="POST" action="/admin/orders/cancel">
                                                @csrf
                                                <input type="hidden" name="order_type" value="{{ $tab }}">
                                                <input type="hidden" name="order_id" value="{{ $o->id }}">
                                                
                                                <div class="mb-4">
                                                    <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Reason (Optional)</label>
                                                    <input type="text" name="reason" placeholder="{{ $tab === 'funding' ? 'e.g. Abandoned deposit / Not paid' : 'e.g. Terms violation / Abuse' }}" class="w-full text-sm rounded-xl border border-gray-200 px-3 py-2 focus:border-[#0d9488] focus:ring-0">
                                                </div>

                                                <div class="flex items-center justify-end gap-2 pt-2">
                                                    <button type="button" @click="confirmingCancel = false" class="px-4 py-2 text-xs font-extrabold rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200">
                                                        Dismiss
                                                    </button>
                                                    <button type="submit" class="px-5 py-2 text-xs font-extrabold rounded-xl bg-rose-600 text-white hover:bg-rose-700 shadow-md">
                                                        Confirm Cancel
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    <!-- MODAL: Refund to Wallet -->
                                    <div x-show="confirmingRefund" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" style="display: none;" @click.self="confirmingRefund = false">
                                        <div class="bg-white rounded-3xl p-6 max-w-md w-full text-left shadow-2xl border border-gray-100">
                                            <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                                                <h4 class="text-lg font-black text-gray-900">Refund Order #{{ $o->id }}</h4>
                                                <button type="button" @click="confirmingRefund = false" class="text-gray-400 hover:text-gray-600">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                </button>
                                            </div>
                                            
                                            <div class="py-4">
                                                <p class="text-sm font-semibold text-gray-600 mb-3">
                                                    {{ $isPending ? 'Are you sure you want to cancel this pending order and credit the refund back to the user\'s wallet?' : 'Are you sure you want to refund this order and credit the amount back to the user\'s wallet?' }}
                                                </p>
                                                <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100 text-xs font-bold space-y-1">
                                                    <div class="text-gray-500 uppercase text-[10px] tracking-wider">Refund Summary</div>
                                                    <div class="text-gray-900">User: {{ $o->user->name ?? 'User' }} ({{ $o->user->email ?? '' }})</div>
                                                    <div class="text-gray-900">Type: {{ strtoupper($tab) }} Order #{{ $o->id }}</div>
                                                    <div class="text-[#0d9488] text-sm font-extrabold mt-1">Wallet Credit: +${{ number_format($orderAmt, 2) }}</div>
                                                </div>
                                            </div>

                                            <form method="POST" action="/admin/orders/refund">
                                                @csrf
                                                <input type="hidden" name="order_type" value="{{ $tab }}">
                                                <input type="hidden" name="order_id" value="{{ $o->id }}">
                                                
                                                <div class="mb-4">
                                                    <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Refund Reason (Optional)</label>
                                                    <input type="text" name="reason" placeholder="e.g. Service delayed / User request" class="w-full text-sm rounded-xl border border-gray-200 px-3 py-2 focus:border-[#0d9488] focus:ring-0">
                                                </div>

                                                <div class="flex items-center justify-end gap-2 pt-2">
                                                    <button type="button" @click="confirmingRefund = false" class="px-4 py-2 text-xs font-extrabold rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200">
                                                        Dismiss
                                                    </button>
                                                    <button type="submit" class="px-5 py-2 text-xs font-extrabold rounded-xl bg-rose-600 text-white hover:bg-rose-700 shadow-md">
                                                        Confirm Refund
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    @if($tab === 'funding')
                                        <!-- MODAL: Approve Funding -->
                                        <div x-show="confirmingApprove" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" style="display: none;" @click.self="confirmingApprove = false">
                                            <div class="bg-white rounded-3xl p-6 max-w-md w-full text-left shadow-2xl border border-gray-100">
                                                <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                                                    <h4 class="text-lg font-black text-gray-900">Approve Deposit #{{ $o->id }}</h4>
                                                    <button type="button" @click="confirmingApprove = false" class="text-gray-400 hover:text-gray-600">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                    </button>
                                                </div>
                                                
                                                <div class="py-4">
                                                    <p class="text-sm font-semibold text-gray-600 mb-3">
                                                        Are you sure you want to mark this deposit as received and credit the user's wallet?
                                                    </p>
                                                    <div class="bg-teal-50 rounded-2xl p-4 border border-teal-100 text-xs font-bold space-y-1">
                                                        <div class="text-teal-800 uppercase text-[10px] tracking-wider font-extrabold">Credit Details</div>
                                                        <div class="text-gray-900">User: {{ $o->user->name ?? 'User' }} ({{ $o->user->email ?? '' }})</div>
                                                        <div class="text-gray-900">Reference: {{ $o->reference }}</div>
                                                        <div class="text-teal-700 text-base font-black mt-1">Wallet Credit: +${{ number_format($orderAmt, 2) }}</div>
                                                    </div>
                                                </div>

                                                <form method="POST" action="/admin/orders/approve-funding">
                                                    @csrf
                                                    <input type="hidden" name="order_id" value="{{ $o->id }}">

                                                    <div class="flex items-center justify-end gap-2 pt-2">
                                                        <button type="button" @click="confirmingApprove = false" class="px-4 py-2 text-xs font-extrabold rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200">
                                                            Dismiss
                                                        </button>
                                                        <button type="submit" class="px-5 py-2 text-xs font-extrabold rounded-xl bg-teal-600 text-white hover:bg-teal-700 shadow-md">
                                                            Confirm Approval
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        <!-- MODAL: Reverse Funding -->
                                        <div x-show="confirmingReverse" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" style="display: none;" @click.self="confirmingReverse = false">
                                            <div class="bg-white rounded-3xl p-6 max-w-md w-full text-left shadow-2xl border border-gray-100">
                                                <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                                                    <h4 class="text-lg font-black text-gray-900">Reverse Funding #{{ $o->id }}</h4>
                                                    <button type="button" @click="confirmingReverse = false" class="text-gray-400 hover:text-gray-600">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                    </button>
                                                </div>
                                                
                                                <div class="py-4">
                                                    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-xs font-bold text-amber-900 mb-3">
                                                        <div class="font-black text-amber-800 uppercase tracking-wide text-[10px] mb-1">Warning: Balance Debit</div>
                                                        This funding was already approved. Reversing will deduct <strong>${{ number_format($orderAmt, 2) }}</strong> from the user's wallet.
                                                    </div>
                                                    <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100 text-xs font-bold space-y-1">
                                                        <div class="text-gray-900">User: {{ $o->user->name ?? 'User' }} ({{ $o->user->email ?? '' }})</div>
                                                        <div class="text-gray-900">Reference: {{ $o->reference }}</div>
                                                        <div class="text-amber-700 text-sm font-extrabold mt-1">Debit Amount: -${{ number_format($orderAmt, 2) }}</div>
                                                    </div>
                                                </div>

                                                <form method="POST" action="/admin/orders/reverse-funding">
                                                    @csrf
                                                    <input type="hidden" name="order_id" value="{{ $o->id }}">
                                                    
                                                    <div class="mb-4">
                                                        <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Reversal Reason</label>
                                                        <input type="text" name="reason" placeholder="e.g. Chargeback / Fraudulent deposit" class="w-full text-sm rounded-xl border border-gray-200 px-3 py-2 focus:border-amber-500 focus:ring-0" required>
                                                    </div>

                                                    <div class="flex items-center justify-end gap-2 pt-2">
                                                        <button type="button" @click="confirmingReverse = false" class="px-4 py-2 text-xs font-extrabold rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200">
                                                            Dismiss
                                                        </button>
                                                        <button type="submit" class="px-5 py-2 text-xs font-extrabold rounded-xl bg-amber-600 text-white hover:amber-700 shadow-md">
                                                            Confirm Reversal
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-6 py-5 border-t border-gray-100 bg-gray-50">
            {{ $orders->onEachSide(1)->links() }}
        </div>
    @else
        <div class="p-12 text-center text-gray-500">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
            <p class="font-bold text-gray-600">No orders found matching your search criteria.</p>
        </div>
    @endif
</div>
@endsection
