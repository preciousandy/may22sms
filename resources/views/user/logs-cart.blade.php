@extends('layouts.dashboard')

@section('page_title', 'Logs Cart')

@section('dashboard_content')

@if(session('success'))
    <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl shadow-sm">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl shadow-sm">
        {{ session('error') }}
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

<div class="mb-8 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">My Cart</h2>
        <p class="text-sm text-gray-500">Review items, update quantity, then checkout.</p>
    </div>
    <a href="/logs-store" class="text-sm font-bold text-[#0d9488] hover:underline">Continue Shopping</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800">Items</h3>
        </div>

        @if($cartItems->isEmpty())
            <div class="p-10 text-center text-gray-500">
                Your cart is empty.
            </div>
        @else
            <div class="divide-y divide-gray-100">
                @foreach($cartItems as $cartItem)
                    <div class="p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <div class="font-bold text-gray-900">{{ $cartItem->item->title }}</div>
                            <div class="text-sm text-gray-500 capitalize">{{ $cartItem->item->platform }}</div>
                            <div class="mt-2 text-xs font-medium px-3 py-1.5 rounded-md inline-block {{ $cartItem->item->available_stock > 0 ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">
                                {{ $cartItem->item->available_stock > 0 ? $cartItem->item->available_stock . ' in stock' : 'Out of Stock' }}
                            </div>
                        </div>

                        <div class="flex flex-col sm:items-end gap-2">
                            <div class="text-sm text-gray-600">
                                <span class="font-bold text-gray-900">{{ $currencySymbol ?? '$' }}{{ ($formatAmount ?? fn($x) => number_format($x, 2))($cartItem->item->price) }}</span> each
                            </div>

                            <div class="flex items-center gap-2">
                                <form action="/logs-store/cart/{{ $cartItem->item->id }}" method="POST" class="flex items-center gap-2">
                                    @csrf
                                    <input type="number" name="quantity" min="1" max="{{ $cartItem->item->available_stock }}" value="{{ $cartItem->quantity }}" class="w-24 rounded-lg border-gray-300 py-2 px-3 focus:ring-[#0d9488] focus:border-[#0d9488] disabled:bg-gray-100" required {{ $cartItem->item->available_stock < 1 ? 'disabled' : '' }}>
                                    <button type="submit" class="bg-[#0d9488] hover:bg-[#0f766e] text-white px-4 py-2 rounded-lg font-bold shadow-sm transition disabled:opacity-60 disabled:cursor-not-allowed" {{ $cartItem->item->available_stock < 1 ? 'disabled' : '' }}>
                                        Update
                                    </button>
                                </form>

                                <form action="/logs-store/cart/{{ $cartItem->item->id }}" method="POST" onsubmit="return confirm('Remove this item from cart?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-bold transition">
                                        Remove
                                    </button>
                                </form>
                            </div>

                            <div class="text-sm font-extrabold text-[#0d9488]">
                                Subtotal: {{ $currencySymbol ?? '$' }}{{ ($formatAmount ?? fn($x) => number_format($x, 2))($cartItem->quantity * $cartItem->item->price) }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden h-fit">
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800">Summary</h3>
        </div>
        <div class="p-6">
            <div class="flex items-center justify-between text-sm text-gray-600 mb-3">
                <span>Total</span>
                <span class="text-lg font-extrabold text-gray-900">{{ $currencySymbol ?? '$' }}{{ ($formatAmount ?? fn($x) => number_format($x, 2))($total) }}</span>
            </div>
            <div class="text-xs text-gray-500 mb-5">
                Checkout will deduct from your wallet balance and deliver credentials instantly.
            </div>
            <form action="/logs-store/checkout" method="POST" data-loader="true" data-loader-title="Processing Checkout..." data-loader-subtitle="Deducting balance and preparing instant credentials">
                @csrf
                <div class="mb-4">
                    <div class="text-xs font-extrabold text-gray-500 uppercase mb-1">Promo Code (Optional)</div>
                    <input type="text" name="promo_code" value="{{ old('promo_code') }}" class="w-full rounded-xl border-gray-300 focus:border-[#0d9488] focus:ring focus:ring-teal-200 focus:ring-opacity-50 font-bold" placeholder="e.g. WELCOME10">
                </div>
                <button type="submit" class="w-full bg-[#facc15] hover:bg-yellow-400 text-black font-extrabold py-3 px-4 rounded-xl shadow-sm transition disabled:opacity-60 disabled:cursor-not-allowed" {{ $cartItems->isEmpty() ? 'disabled' : '' }}>
                    Checkout
                </button>
            </form>
        </div>
    </div>
</div>

@endsection
