@extends('layouts.admin')

@section('admin_content')
@if(session('success'))
    <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl shadow-sm">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl shadow-sm">
        <ul class="list-disc pl-5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800">Add SportyBet Code</h3>
        </div>
        <div class="p-6">
            <form action="/admin/sporty-bet-codes" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Bet Code</label>
                    <input name="code" value="{{ old('code') }}" class="w-full rounded-xl border-2 border-gray-200 px-4 py-3 focus:ring-0 focus:border-[#0d9488] font-bold text-gray-900" placeholder="e.g. BC123XYZ" required>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Amount (NGN) - 0 for Free</label>
                    <input type="number" step="0.01" min="0" name="amount" value="{{ old('amount') }}" class="w-full rounded-xl border-2 border-gray-200 px-4 py-3 focus:ring-0 focus:border-[#0d9488] font-bold text-gray-900" placeholder="e.g. 1000 or 0 for free" required>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Description (optional)</label>
                    <textarea name="description" rows="3" class="w-full rounded-xl border-2 border-gray-200 px-4 py-3 focus:ring-0 focus:border-[#0d9488] font-bold text-gray-900" placeholder="e.g. 50+ odds accumulator">{{ old('description') }}</textarea>
                </div>
                <button type="submit" class="w-full bg-gray-900 text-white font-bold py-4 rounded-xl hover:bg-gray-800 transition shadow-lg shadow-gray-200">
                    Add Code
                </button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-800">Manage SportyBet Codes</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="px-6 py-4 text-xs font-extrabold text-gray-500 uppercase tracking-wider">Code</th>
                            <th class="px-6 py-4 text-xs font-extrabold text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-4 text-xs font-extrabold text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-4 text-xs font-extrabold text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-4 text-xs font-extrabold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-xs font-extrabold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($codes as $code)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <span class="font-bold text-gray-900">{{ $code->code }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold {{ $code->amount == 0 ? 'bg-blue-100 text-blue-700' : 'bg-yellow-100 text-yellow-700' }}">
                                        {{ $code->amount == 0 ? 'Free' : 'Paid' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-bold text-gray-900">₦{{ number_format($code->amount, 2) }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $code->description ?: '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    <form action="/admin/sporty-bet-codes/{{ $code->id }}/toggle" method="POST">
                                        @csrf
                                        <button type="submit" class="px-3 py-1 rounded-full text-xs font-bold {{ $code->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                            {{ $code->is_active ? 'Active' : 'Inactive' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <form action="/admin/sporty-bet-codes/{{ $code->id }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this code?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 font-bold text-sm">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500 font-bold">
                                    No SportyBet codes found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($codes->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $codes->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
