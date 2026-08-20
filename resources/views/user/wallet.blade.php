@extends('layouts.dashboard')

@section('page_title', 'Wallet')

@section('dashboard_content')
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-6 py-5 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
        <div>
            <h3 class="text-lg font-bold text-gray-800">Wallet Transactions</h3>
            <p class="text-sm text-gray-500">Every wallet credit and debit.</p>
        </div>
        <div class="text-sm text-gray-700 font-bold">
            Balance: {{ $currencySymbol ?? '$' }}{{ ($formatAmount ?? fn ($x) => number_format($x, 2))(auth()->user()->walletBalance ?? 0) }}
        </div>
    </div>

    @if($transactions->count() === 0)
        <div class="p-10 text-center text-gray-500">No wallet transactions yet.</div>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($transactions as $tx)
                        @php($isCredit = (float) $tx->amount_usd > 0)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $tx->created_at->format('M d, Y H:i') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-800">{{ $tx->type }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold {{ $isCredit ? 'text-green-600' : 'text-red-600' }}">
                                {{ $isCredit ? '+' : '' }}{{ $currencySymbol ?? '$' }}{{ ($formatAmount ?? fn ($x) => number_format($x, 2))($tx->amount_usd) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-700">{{ $tx->reference }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                                {{ $currencySymbol ?? '$' }}{{ ($formatAmount ?? fn ($x) => number_format($x, 2))($tx->balance_after) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-6 py-5 border-t border-gray-100 bg-gray-50">
            {{ $transactions->links() }}
        </div>
    @endif
</div>
@endsection

