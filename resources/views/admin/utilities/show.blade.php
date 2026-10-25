@extends('layouts.admin')

@section('admin_content')
@push('header_title', 'Transaction ' . $tx->reference)

<div class="space-y-6 max-w-5xl mx-auto">

    <!-- Top Breadcrumbs & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-gray-400 font-bold uppercase tracking-wider mb-1">
                <a href="{{ route('admin.utilities.index') }}" class="hover:text-teal-600 transition">Utilities Ledger</a>
                <span>/</span>
                <span class="text-gray-700">Transaction Details</span>
            </div>
            <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2">
                <span>{{ $tx->reference }}</span>
            </h1>
        </div>

        <div class="flex items-center gap-2">
            @if(in_array($tx->status, ['pending', 'uncertain']))
                <form action="{{ route('admin.utilities.reconcile', $tx->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" 
                            onclick="return confirm('Query BigiSub API to reconcile this transaction?')"
                            class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs uppercase tracking-wider transition shadow-sm flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Reconcile with API</span>
                    </button>
                </form>
            @endif

            @if($tx->canRefund())
                <form action="{{ route('admin.utilities.refund', $tx->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" 
                            onclick="return confirm('Immediately refund ${{ number_format((float)$tx->total_charged_usd, 4) }} USD back to user {{ $tx->user->email }}?')"
                            class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs uppercase tracking-wider transition shadow-sm flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                        <span>Manual Refund</span>
                    </button>
                </form>
            @endif

            <a href="{{ route('admin.utilities.index') }}" class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs uppercase tracking-wider transition">
                Back to List
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center justify-between">
            <span>✓ {{ session('success') }}</span>
            <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900">✕</button>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold flex items-center justify-between">
            <span>⚠️ {{ session('error') }}</span>
            <button onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-900">✕</button>
        </div>
    @endif

    <!-- Transaction Summary Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Core Info Card -->
        <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm space-y-4">
            <h3 class="text-xs font-black uppercase tracking-wider text-gray-400 pb-2 border-b border-gray-100 flex items-center justify-between">
                <span>Transaction &amp; Product Info</span>
                @php $b = $tx->status_badge; @endphp
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold border {{ $b['bg'] }}">
                    <span>{{ $b['icon'] }}</span>
                    {{ $b['label'] }}
                </span>
            </h3>

            <div class="grid grid-cols-2 gap-4 text-xs">
                <div>
                    <span class="text-gray-400 text-[10px] uppercase font-bold block">User</span>
                    <span class="font-extrabold text-gray-900">{{ $tx->user->name ?? 'N/A' }}</span>
                    <span class="text-gray-500 text-[10px] block font-mono">{{ $tx->user->email ?? 'N/A' }}</span>
                </div>

                <div>
                    <span class="text-gray-400 text-[10px] uppercase font-bold block">Service Type</span>
                    <span class="font-extrabold text-gray-900 uppercase">{{ $tx->service_type }}</span>
                    <span class="text-gray-500 text-[10px] block">{{ $tx->service_label }}</span>
                </div>

                <div>
                    <span class="text-gray-400 text-[10px] uppercase font-bold block">Operator / Provider</span>
                    <span class="font-extrabold text-gray-900 uppercase">{{ $tx->network_or_disco }}</span>
                    <span class="text-gray-500 text-[10px] block">Gateway: {{ $tx->provider }}</span>
                </div>

                <div>
                    <span class="text-gray-400 text-[10px] uppercase font-bold block">Recipient / Target</span>
                    <span class="font-extrabold text-gray-900 font-mono">{{ $tx->customer_target }}</span>
                    @if($tx->customer_name)
                        <span class="text-gray-500 text-[10px] block">{{ $tx->customer_name }}</span>
                    @endif
                </div>

                @if($tx->product_name)
                    <div class="col-span-2">
                        <span class="text-gray-400 text-[10px] uppercase font-bold block">Product Plan / Description</span>
                        <span class="font-bold text-gray-900">{{ $tx->product_name }}</span>
                        @if($tx->product_code)
                            <span class="text-gray-400 text-[10px] font-mono block">Code: {{ $tx->product_code }}</span>
                        @endif
                    </div>
                @endif

                @if($tx->token)
                    <div class="col-span-2 p-3 bg-amber-50 rounded-xl border border-amber-200">
                        <span class="text-amber-800 text-[10px] uppercase font-black block">Prepaid Electricity Token</span>
                        <span class="font-mono text-lg font-black text-gray-900 select-all">{{ $tx->token }}</span>
                        @if($tx->units)
                            <span class="text-amber-700 text-xs block font-bold mt-0.5">Units: {{ $tx->units }}</span>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Financial & Ledger Card -->
        <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm space-y-4">
            <h3 class="text-xs font-black uppercase tracking-wider text-gray-400 pb-2 border-b border-gray-100">
                Financial Ledger &amp; Wallet Audit
            </h3>

            <div class="grid grid-cols-2 gap-4 text-xs">
                <div>
                    <span class="text-gray-400 text-[10px] uppercase font-bold block">Face Value (NGN)</span>
                    <span class="font-extrabold text-gray-900 text-sm">₦{{ number_format((float)$tx->amount_ngn, 2) }}</span>
                </div>

                <div>
                    <span class="text-gray-400 text-[10px] uppercase font-bold block">Exchange Rate</span>
                    <span class="font-bold text-gray-900">₦{{ number_format((float)$tx->exchange_rate, 2) }}/$</span>
                </div>

                <div>
                    <span class="text-gray-400 text-[10px] uppercase font-bold block">Charged to User Wallet</span>
                    <span class="font-extrabold text-emerald-600 text-sm">${{ number_format((float)$tx->total_charged_usd, 8) }} USD</span>
                    @if($tx->discount_ngn > 0)
                        <span class="text-teal-600 text-[10px] font-bold block">₦{{ number_format((float)$tx->discount_ngn, 2) }} discount applied</span>
                    @endif
                </div>

                <div>
                    <span class="text-gray-400 text-[10px] uppercase font-bold block">Provider Reference</span>
                    <span class="font-mono text-gray-700 font-bold select-all">{{ $tx->provider_reference ?: 'None assigned' }}</span>
                </div>

                <div>
                    <span class="text-gray-400 text-[10px] uppercase font-bold block">Created Date</span>
                    <span class="font-bold text-gray-800">{{ $tx->created_at->format('M d, Y h:i:s A') }}</span>
                </div>

                <div>
                    <span class="text-gray-400 text-[10px] uppercase font-bold block">Refund Status</span>
                    @if($tx->refunded_at)
                        <span class="text-blue-700 font-bold block">Refunded at {{ $tx->refunded_at->format('M d, Y H:i') }}</span>
                    @else
                        <span class="text-gray-500 font-semibold block">Not refunded</span>
                    @endif
                </div>

                <div class="col-span-2 pt-2 border-t border-gray-100">
                    <span class="text-gray-400 text-[10px] uppercase font-bold block">Client IP &amp; User Agent</span>
                    <span class="font-mono text-gray-600 text-[11px] block">{{ $tx->ip_address ?? 'N/A' }}</span>
                    <span class="text-gray-400 text-[10px] block truncate max-w-full">{{ $tx->user_agent ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

    </div>

    <!-- Diagnostic Payloads & Raw Data -->
    <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm space-y-4">
        <h3 class="text-xs font-black uppercase tracking-wider text-gray-400 pb-2 border-b border-gray-100">
            Raw API Diagnostic Payloads
        </h3>

        @if($tx->error_message)
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
                <span class="font-black uppercase tracking-wider text-[10px] block">Error Message</span>
                <span class="mt-1 font-mono block">{{ $tx->error_message }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <span class="text-[10px] font-black uppercase tracking-wider text-gray-500 mb-1 block">Sanitized Outgoing Request Payload</span>
                <pre class="bg-gray-900 text-emerald-400 p-4 rounded-xl text-[11px] font-mono overflow-x-auto max-h-72">{{ json_encode($tx->request_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            </div>

            <div>
                <span class="text-[10px] font-black uppercase tracking-wider text-gray-500 mb-1 block">Sanitized Provider Response Payload</span>
                <pre class="bg-gray-900 text-sky-400 p-4 rounded-xl text-[11px] font-mono overflow-x-auto max-h-72">{{ json_encode($tx->response_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            </div>
        </div>
    </div>

</div>
@endsection
