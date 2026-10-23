@extends('layouts.dashboard')

@section('page_title', 'Wallet Ledger')

@section('dashboard_content')
<!-- Header Stats & Quick Actions -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center justify-between">
        <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Available Balance</p>
            <p class="text-3xl font-black text-gray-900 mt-1">
                {{ $currencySymbol ?? '$' }}{{ ($formatAmount ?? fn ($x) => number_format($x, 2))(auth()->user()->walletBalance ?? 0) }}
            </p>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-teal-50 text-[#0d9488] flex items-center justify-center text-xl shadow-inner">
            💳
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex items-center justify-between">
        <div>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Recorded Entries</p>
            <p class="text-3xl font-black text-gray-900 mt-1">{{ $transactions->total() }}</p>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shadow-inner">
            📊
        </div>
    </div>

    <div class="bg-gradient-to-r from-gray-900 to-gray-800 rounded-2xl shadow-sm p-6 text-white flex items-center justify-between">
        <div>
            <p class="text-xs font-bold text-gray-300 uppercase tracking-wider">Need more credits?</p>
            <p class="text-sm text-gray-300 mt-0.5">Top up your balance instantly.</p>
        </div>
        <a href="/funding" class="bg-[#facc15] hover:bg-yellow-400 text-black px-4 py-2.5 rounded-xl font-extrabold text-xs shadow-md transition shrink-0">
            + Add Funds
        </a>
    </div>
</div>

<!-- Transactions Table -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/70 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h3 class="text-lg font-bold text-gray-800">Transaction History</h3>
            <p class="text-xs text-gray-500">Every wallet credit, debit, gift, and order charge.</p>
        </div>
        <div class="text-xs font-semibold text-gray-500">
            Page {{ $transactions->currentPage() }} of {{ max(1, $transactions->lastPage()) }}
        </div>
    </div>

    @if($transactions->count() === 0)
        <div class="p-16 text-center">
            <div class="w-16 h-16 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center mx-auto mb-4 text-2xl">
                📂
            </div>
            <h4 class="text-base font-bold text-gray-700">No transactions recorded yet</h4>
            <p class="text-xs text-gray-400 mt-1 max-w-sm mx-auto">When you fund your account, claim daily gifts, or purchase services, your transaction audit log will appear here.</p>
            <a href="/funding" class="mt-4 inline-flex items-center px-4 py-2 rounded-xl bg-[#0d9488] text-white font-bold text-xs shadow-sm hover:bg-[#0f766e] transition">
                Fund Wallet
            </a>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Date & Time</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Reference</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Balance After</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @foreach($transactions as $tx)
                        @php
                            $isCredit = (float) $tx->amount_usd > 0;
                            $typeLabel = str_replace('_', ' ', (string) $tx->type);
                            $badgeClass = match((string) $tx->type) {
                                'funding' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                'daily_gift' => 'bg-amber-100 text-amber-800 border-amber-200',
                                'sms_order' => 'bg-blue-100 text-blue-800 border-blue-200',
                                'log_order' => 'bg-purple-100 text-purple-800 border-purple-200',
                                'boost_order' => 'bg-teal-100 text-teal-800 border-teal-200',
                                'refund' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                                default => 'bg-gray-100 text-gray-700 border-gray-200',
                            };
                        @endphp
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500 font-medium">
                                {{ $tx->created_at ? $tx->created_at->format('M d, Y H:i') : '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-extrabold capitalize border {{ $badgeClass }}">
                                    @if($tx->type === 'daily_gift') 🎁 @endif
                                    {{ $typeLabel }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-black {{ $isCredit ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $isCredit ? '+' : '' }}{{ $currencySymbol ?? '$' }}{{ ($formatAmount ?? fn ($x) => number_format($x, 2))($tx->amount_usd) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs font-mono font-semibold text-gray-600">
                                {{ $tx->reference ?? '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-800">
                                {{ $currencySymbol ?? '$' }}{{ ($formatAmount ?? fn ($x) => number_format($x, 2))($tx->balance_after) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
            {{ $transactions->links() }}
        </div>
    @endif
</div>
@endsection

