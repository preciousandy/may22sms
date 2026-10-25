@extends('layouts.dashboard')

@php
    $tx = $tx ?? $transaction;
@endphp

@section('page_title', 'Transaction Receipt — ' . $tx->reference)

@section('dashboard_content')
<div class="max-w-2xl mx-auto space-y-6">

    <!-- Top Action Nav -->
    <div class="flex items-center justify-between no-print">
        <a href="{{ route('user.utilities.history') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-slate-800 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
            Back to Transactions
        </a>

        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print Receipt
            </button>
            <a href="{{ route('user.utilities.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold transition shadow-sm">
                New Transaction
            </a>
        </div>
    </div>

    <!-- Receipt Card (Printable Area) -->
    <div id="receipt-card" class="bg-white rounded-3xl border border-slate-200 shadow-xl overflow-hidden relative">
        
        <!-- Header Brand strip -->
        <div class="bg-gradient-to-r from-slate-900 via-teal-950 to-slate-900 px-6 py-8 text-center text-white relative">
            <div class="w-12 h-12 mx-auto rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center mb-3 border border-white/20 text-teal-300">
                @if($tx->service_type === 'airtime')
                    <svg class="w-6 h-6 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                @elseif($tx->service_type === 'data')
                    <svg class="w-6 h-6 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>
                @elseif($tx->service_type === 'electricity')
                    <svg class="w-6 h-6 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                @else
                    <svg class="w-6 h-6 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"/><circle cx="12" cy="12" r="5" stroke-width="2"/><circle cx="12" cy="12" r="1.5" fill="currentColor"/></svg>
                @endif
            </div>

            <div class="text-xs uppercase tracking-widest font-black text-teal-300">
                May22SMS Utilities Receipt
            </div>

            <div class="text-3xl font-black mt-2 tracking-tight">
                ₦{{ number_format((float)$tx->amount_ngn, 2) }}
            </div>
            <div class="text-xs text-slate-300 font-semibold mt-0.5">
                ≈ ${{ number_format((float)$tx->total_charged_usd, 4) }} USD
            </div>

            <!-- Status Pill -->
            <div class="mt-4">
                @php $badge = $tx->status_badge; @endphp
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full text-xs font-extrabold bg-white/15 text-white border border-white/20 backdrop-blur-sm">
                    @if($tx->status === 'completed')
                        <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    @elseif($tx->status === 'pending')
                        <svg class="w-3.5 h-3.5 animate-spin text-amber-300" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    @elseif($tx->status === 'failed')
                        <svg class="w-3.5 h-3.5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                    @elseif($tx->status === 'refunded')
                        <svg class="w-3.5 h-3.5 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 10h10a5 5 0 0 1 5 5v2m0 0l-3-3m3 3l3-3M3 10l3-3M3 10l3 3"/></svg>
                    @else
                        <svg class="w-3.5 h-3.5 text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    @endif
                    <span>{{ $badge['label'] }}</span>
                </span>
            </div>
        </div>

        <!-- Token Box if Electricity -->
        @if($tx->token)
            <div class="m-6 p-5 rounded-2xl bg-amber-50 border-2 border-amber-300/80 text-center relative">
                <div class="text-[11px] font-black uppercase tracking-wider text-amber-800 flex items-center justify-center gap-1.5">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span>Prepaid Electricity Recharge Token</span>
                </div>
                <div id="meter-token-display" class="font-mono text-2xl sm:text-3xl font-black text-slate-900 tracking-wider my-2 select-all">
                    {{ $tx->token }}
                </div>
                <p class="text-[11px] text-amber-700 font-medium">
                    Enter this token directly into your prepaid meter keypad and press the blue or enter button.
                </p>
                <div class="mt-3 no-print">
                    <button type="button" 
                            onclick="navigator.clipboard.writeText('{{ $tx->token }}'); alert('Token copied to clipboard!')"
                            class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-700 text-white font-extrabold text-xs uppercase tracking-wider shadow-sm transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        Copy Token
                    </button>
                </div>
            </div>
        @endif

        <!-- Details Grid -->
        <div class="p-6 sm:p-8 space-y-4 text-xs">
            <div class="grid grid-cols-2 gap-4 pb-4 border-b border-slate-100">
                <div>
                    <div class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">Service Type</div>
                    <div class="font-extrabold text-slate-900 uppercase mt-0.5">{{ $tx->service_label }}</div>
                </div>
                <div>
                    <div class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">Operator / Network</div>
                    <div class="font-extrabold text-slate-900 uppercase mt-0.5">{{ $tx->network_or_disco }}</div>
                </div>
            </div>

            @if($tx->service_type === 'data' && $tx->product_name)
                <div class="pb-4 border-b border-slate-100">
                    <div class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">Data Plan Package</div>
                    <div class="font-extrabold text-slate-900 text-sm mt-0.5">{{ $tx->product_name }}</div>
                </div>
            @endif

            <div class="grid grid-cols-2 gap-4 pb-4 border-b border-slate-100">
                <div>
                    <div class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">
                        @if($tx->service_type === 'electricity') Meter Number
                        @elseif($tx->service_type === 'betting') Customer / User ID
                        @else Phone Number
                        @endif
                    </div>
                    <div class="font-mono font-bold text-slate-900 mt-0.5">{{ $tx->customer_target }}</div>
                </div>
                @if($tx->customer_name)
                    <div>
                        <div class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">Customer / Account Name</div>
                        <div class="font-extrabold text-slate-900 mt-0.5">{{ $tx->customer_name }}</div>
                    </div>
                @endif
            </div>

            @if($tx->units)
                <div class="pb-4 border-b border-slate-100">
                    <div class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">Units (kWh)</div>
                    <div class="font-extrabold text-slate-900 mt-0.5">{{ $tx->units }}</div>
                </div>
            @endif

            <div class="grid grid-cols-2 gap-4 pb-4 border-b border-slate-100">
                <div>
                    <div class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">Transaction Reference</div>
                    <div class="font-mono text-slate-700 font-semibold mt-0.5 select-all">{{ $tx->reference }}</div>
                </div>
                @if($tx->provider_reference)
                    <div>
                        <div class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">Provider Reference</div>
                        <div class="font-mono text-slate-700 font-semibold mt-0.5 select-all">{{ $tx->provider_reference }}</div>
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-2 gap-4 pb-4 border-b border-slate-100">
                <div>
                    <div class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">Date &amp; Time</div>
                    <div class="font-bold text-slate-800 mt-0.5">{{ $tx->created_at->format('M d, Y — h:i:s A') }}</div>
                </div>
                <div>
                    <div class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">Payment Method</div>
                    <div class="font-bold text-slate-800 mt-0.5">May22SMS Main Wallet</div>
                </div>
            </div>

            @if($tx->error_message)
                <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800">
                    <div class="font-bold uppercase tracking-wider text-[10px]">Diagnostic Note</div>
                    <div class="mt-0.5 text-xs">{{ $tx->error_message }}</div>
                </div>
            @endif

            <div class="text-center pt-4 text-slate-400 text-[11px]">
                Thank you for using May22SMS. Need support? Contact support@may22sim.com with reference <span class="font-mono font-bold">{{ $tx->reference }}</span>.
            </div>
        </div>

    </div>

</div>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    #receipt-card, #receipt-card * {
        visibility: visible;
    }
    #receipt-card {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        border: none;
        box-shadow: none;
    }
    .no-print {
        display: none !important;
    }
}
</style>
@endsection
