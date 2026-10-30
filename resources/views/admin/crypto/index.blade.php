@extends('layouts.admin')

@section('title', 'Crypto Integration & Off-Ramp')
@push('header_title', 'Crypto Off-Ramp Management')

@section('admin_content')
<div class="space-y-6">
    <!-- Header with Breadcrumbs & Environment Status -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-gray-900 border border-gray-800 rounded-2xl p-6 shadow-xl">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-teal-400 mb-1">
                <span>Services</span>
                <span>/</span>
                <span>Quidax Integration</span>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight flex items-center gap-3">
                <span>Crypto Off-Ramp Management</span>
                @if($isTestMode)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-amber-500/20 text-amber-300 border border-amber-500/40">
                        <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
                        TEST MODE (Sandbox)
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-emerald-500/20 text-emerald-400 border border-emerald-500/40">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        LIVE PRODUCTION
                    </span>
                @endif
            </h1>
            <p class="text-sm text-gray-400 mt-1">
                Monitor stablecoin to NGN conversions, Quidax custodial settlements, deposit addresses, and automated payouts.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- Service Availability Toggle -->
            <form action="{{ route('admin.crypto.toggle') }}" method="POST" onsubmit="return confirm('{{ $isAvailable ? 'Are you sure you want to disable Crypto to Naira conversions? Users will not be able to initiate new conversions until re-enabled.' : 'Enable Crypto to Naira conversions for eligible users?' }}');">
                @csrf
                @if($isAvailable)
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 hover:bg-emerald-500/30 transition shadow-sm">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Service: Active
                    </button>
                @else
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider bg-rose-500/20 text-rose-300 border border-rose-500/30 hover:bg-rose-500/30 transition shadow-sm">
                        <svg class="w-4 h-4 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Service: Disabled
                    </button>
                @endif
            </form>

            <a href="{{ route('admin.services.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider bg-gray-800 text-gray-300 hover:text-white hover:bg-gray-700 border border-gray-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path></svg>
                Service Controls
            </a>
        </div>
    </div>

    <!-- Alert / Messages -->
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-sm font-semibold flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-sm font-semibold flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Key Performance Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Volume Card -->
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5 shadow-lg relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Total USDT Settled</span>
                <span class="p-2 rounded-xl bg-emerald-500/10 text-emerald-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-black text-white">${{ number_format($totalUsdtVolume, 2) }} <span class="text-xs text-gray-400 font-normal">USDT</span></p>
                <p class="text-xs text-gray-400 mt-1">Settled: ₦{{ number_format($totalNgnSettled, 2) }}</p>
            </div>
        </div>

        <!-- Completed Conversions -->
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5 shadow-lg relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Completed Orders</span>
                <span class="p-2 rounded-xl bg-teal-500/10 text-teal-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-black text-white">{{ number_format($completedTransactionsCount) }}</p>
                <p class="text-xs text-gray-400 mt-1">Out of {{ $totalTransactionsCount }} total attempts</p>
            </div>
        </div>

        <!-- Pending Deposits -->
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5 shadow-lg relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Awaiting Deposit</span>
                <span class="p-2 rounded-xl bg-amber-500/10 text-amber-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-black text-white">{{ number_format($pendingTransactionsCount) }}</p>
                <p class="text-xs text-amber-400/80 mt-1">Address issued; deposit pending</p>
            </div>
        </div>

        <!-- Processing & Attention -->
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5 shadow-lg relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Processing / Attention</span>
                <span class="p-2 rounded-xl bg-purple-500/10 text-purple-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                </span>
            </div>
            <div class="mt-4">
                <p class="text-2xl font-black text-white">{{ number_format($processingTransactionsCount) }} <span class="text-xs text-rose-400 font-normal">({{ $attentionTransactionsCount }} flagged)</span></p>
                <p class="text-xs text-gray-400 mt-1">Confirmed on-chain, settling NGN</p>
            </div>
        </div>
    </div>

    <!-- Filter and Search Controls -->
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4 shadow-xl">
        <form method="GET" action="{{ route('admin.crypto.index') }}" class="flex flex-col md:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </span>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search ref, provider ID, deposit address, bank account, user email..." class="w-full bg-gray-950 border border-gray-800 rounded-xl pl-9 pr-4 py-2.5 text-xs text-white placeholder-gray-500 focus:outline-none focus:border-teal-500 transition">
            </div>

            <div class="flex items-center gap-2 w-full md:w-auto">
                <select name="status" class="bg-gray-950 border border-gray-800 rounded-xl px-3 py-2.5 text-xs text-gray-300 focus:outline-none focus:border-teal-500">
                    <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All Statuses</option>
                    <option value="address_assigned" {{ request('status') === 'address_assigned' ? 'selected' : '' }}>Awaiting Deposit</option>
                    <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                    <option value="needs_attention" {{ request('status') === 'needs_attention' ? 'selected' : '' }}>Needs Attention</option>
                </select>

                <select name="currency" class="bg-gray-950 border border-gray-800 rounded-xl px-3 py-2.5 text-xs text-gray-300 focus:outline-none focus:border-teal-500">
                    <option value="all" {{ request('currency') === 'all' ? 'selected' : '' }}>All Assets</option>
                    <option value="usdt" {{ request('currency') === 'usdt' ? 'selected' : '' }}>USDT</option>
                    <option value="usdc" {{ request('currency') === 'usdc' ? 'selected' : '' }}>USDC</option>
                    <option value="btc" {{ request('currency') === 'btc' ? 'selected' : '' }}>BTC</option>
                </select>

                <button type="submit" class="px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-500 text-white text-xs font-bold uppercase tracking-wider transition">
                    Filter
                </button>

                @if(request()->hasAny(['q', 'status', 'currency']))
                    <a href="{{ route('admin.crypto.index') }}" class="p-2.5 rounded-xl bg-gray-800 hover:bg-gray-700 text-gray-300 transition" title="Clear Filters">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Crypto Transactions Table -->
    <div class="bg-gray-900 border border-gray-800 rounded-2xl shadow-xl overflow-hidden">
        <div class="p-5 border-b border-gray-800 flex items-center justify-between">
            <h2 class="text-base font-bold text-white flex items-center gap-2">
                <svg class="w-4 h-4 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                Transactions Audit Log
            </h2>
            <span class="text-xs text-gray-400">Total: {{ $transactions->total() }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-300">
                <thead class="bg-gray-950/70 uppercase tracking-wider text-[11px] text-gray-400 border-b border-gray-800">
                    <tr>
                        <th class="px-5 py-3.5 font-bold">Ref / Provider</th>
                        <th class="px-5 py-3.5 font-bold">User</th>
                        <th class="px-5 py-3.5 font-bold">Crypto Sent</th>
                        <th class="px-5 py-3.5 font-bold">Naira Payout</th>
                        <th class="px-5 py-3.5 font-bold">Destination</th>
                        <th class="px-5 py-3.5 font-bold">Status</th>
                        <th class="px-5 py-3.5 font-bold">Date</th>
                        <th class="px-5 py-3.5 font-bold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800/60 font-mono">
                    @forelse($transactions as $tx)
                        <tr class="hover:bg-gray-800/40 transition">
                            <td class="px-5 py-4">
                                <span class="font-bold text-white block">{{ $tx->merchant_reference }}</span>
                                @if($tx->provider_reference)
                                    <span class="text-[10px] text-gray-400">PID: {{ Str::limit($tx->provider_reference, 16) }}</span>
                                @endif
                                <span class="inline-block mt-0.5 px-1.5 py-0.5 rounded text-[9px] uppercase font-bold {{ $tx->environment === 'production' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-amber-500/10 text-amber-300' }}">
                                    {{ $tx->environment }}
                                </span>
                            </td>

                            <td class="px-5 py-4 font-sans">
                                @if($tx->user)
                                    <a href="/admin/users/{{ $tx->user->id }}" class="font-bold text-white hover:text-teal-400 transition block">
                                        {{ $tx->user->name }}
                                    </a>
                                    <span class="text-gray-400 text-[11px]">{{ $tx->user->email }}</span>
                                @else
                                    <span class="text-gray-500 italic">User #{{ $tx->user_id }}</span>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                <span class="text-white font-bold">{{ number_format($tx->from_amount, 4) }} {{ strtoupper($tx->from_currency) }}</span>
                                <span class="block text-[10px] text-teal-400 uppercase font-sans font-semibold">Net: {{ strtoupper($tx->network) }}</span>
                            </td>

                            <td class="px-5 py-4">
                                <span class="text-emerald-400 font-bold">₦{{ number_format($tx->to_amount, 2) }}</span>
                                <span class="block text-[10px] text-gray-400 font-sans">Rate: ₦{{ number_format($tx->quoted_rate, 2) }}</span>
                            </td>

                            <td class="px-5 py-4 font-sans">
                                @if($tx->payout_destination === 'wallet')
                                    <span class="inline-flex items-center gap-1 text-teal-300 font-semibold text-xs">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                        Wallet Credit
                                    </span>
                                @else
                                    <span class="text-white font-semibold text-xs block">{{ $tx->bank_name ?? 'Bank Account' }}</span>
                                    <span class="text-[11px] text-gray-400">{{ $tx->account_number }} ({{ $tx->account_name }})</span>
                                @endif
                            </td>

                            <td class="px-5 py-4 font-sans">
                                @if($tx->status === 'completed')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                        Completed
                                    </span>
                                @elseif($tx->status === 'processing')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-500/20 text-blue-300 border border-blue-500/30">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-400 animate-pulse"></span>
                                        Processing
                                    </span>
                                @elseif($tx->status === 'address_assigned')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                        Awaiting Crypto
                                    </span>
                                @elseif($tx->status === 'failed')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                                        Failed
                                    </span>
                                @elseif($tx->status === 'needs_attention')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-purple-500/20 text-purple-300 border border-purple-500/30">
                                        <span class="w-1.5 h-1.5 rounded-full bg-purple-400 animate-ping"></span>
                                        Flagged
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-gray-700 text-gray-300">
                                        {{ ucfirst($tx->status) }}
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-4 text-gray-400 font-sans text-[11px]">
                                {{ $tx->created_at->format('M d, Y') }}<br>
                                <span class="text-gray-500">{{ $tx->created_at->format('H:i') }}</span>
                            </td>

                            <td class="px-5 py-4 text-right font-sans">
                                <div class="flex items-center justify-end gap-2">
                                    <form action="{{ route('admin.crypto.reconcile', $tx->id) }}" method="POST" onsubmit="return confirm('Query Quidax for updated settlement status on {{ $tx->merchant_reference }}?');">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-wider bg-teal-500/20 text-teal-300 border border-teal-500/30 hover:bg-teal-500/30 transition shadow-sm" title="Reconcile with Quidax">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                            Reconcile
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-gray-500 font-sans">
                                No crypto transactions recorded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="p-4 border-t border-gray-800 bg-gray-950/50">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

    <!-- Webhook Audit Log Section -->
    <div class="bg-gray-900 border border-gray-800 rounded-2xl shadow-xl p-5">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    Quidax Webhook Delivery Stream
                </h3>
                <p class="text-xs text-gray-400 mt-0.5">Verified HMAC-SHA256 signature events & idempotency audit trail.</p>
            </div>
            <span class="text-xs text-gray-500 font-mono">Endpoint: /api/webhooks/quidax</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-300">
                <thead class="bg-gray-950/70 uppercase tracking-wider text-[10px] text-gray-400 border-b border-gray-800">
                    <tr>
                        <th class="px-4 py-2.5 font-bold">Event Type</th>
                        <th class="px-4 py-2.5 font-bold">Event ID</th>
                        <th class="px-4 py-2.5 font-bold">Merchant Ref</th>
                        <th class="px-4 py-2.5 font-bold">Status</th>
                        <th class="px-4 py-2.5 font-bold">Received At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800/60 font-mono text-[11px]">
                    @forelse($recentWebhooks as $webhook)
                        <tr class="hover:bg-gray-800/30 transition">
                            <td class="px-4 py-2.5 font-bold text-teal-300">{{ $webhook->event }}</td>
                            <td class="px-4 py-2.5 text-gray-400">{{ Str::limit($webhook->event_id, 18) }}</td>
                            <td class="px-4 py-2.5 text-white">{{ $webhook->merchant_reference ?? 'N/A' }}</td>
                            <td class="px-4 py-2.5">
                                @if($webhook->is_processed)
                                    <span class="inline-flex items-center gap-1 text-emerald-400 font-sans font-semibold text-[11px]">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        Processed
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-rose-400 font-sans font-semibold text-[11px]" title="{{ $webhook->error_message }}">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        Failed
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-gray-400 font-sans text-[11px]">{{ $webhook->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-gray-500 font-sans text-xs">
                                No Quidax webhook events recorded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
