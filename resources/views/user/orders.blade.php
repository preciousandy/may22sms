@extends('layouts.dashboard')

@section('page_title', 'Order History')

@section('dashboard_content')
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

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden" x-data="{ reportModalOpen: false, reportType: '', reportId: null, reportReason: '', submittingReport: false }">
    <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h3 class="text-lg font-black text-gray-900">Order History & Purchases</h3>
            <p class="text-xs font-bold text-gray-500 mt-0.5">Track your SMS verifications, Social Boosting, and Logs orders.</p>
        </div>
        <div class="flex flex-wrap items-center gap-1.5">
            <a href="/orders?type=sms" class="px-4 py-2 rounded-xl text-xs font-extrabold transition {{ ($type ?? 'sms') === 'sms' ? 'bg-[#0d9488] text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">SMS Numbers</a>
            <a href="/orders?type=boost" class="px-4 py-2 rounded-xl text-xs font-extrabold transition {{ ($type ?? 'sms') === 'boost' ? 'bg-[#0d9488] text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">Social Boosting</a>
            <a href="/orders?type=logs" class="px-4 py-2 rounded-xl text-xs font-extrabold transition {{ ($type ?? 'sms') === 'logs' ? 'bg-[#0d9488] text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">Logs Store</a>
        </div>
    </div>

    <div class="p-6 border-b border-gray-100 bg-white">
        <form method="GET" action="/orders" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <input type="hidden" name="type" value="{{ $type }}">
            <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Search details / order ID..." class="w-full text-xs font-semibold rounded-xl border border-gray-200 px-3.5 py-2.5 focus:border-[#0d9488] focus:ring-0">
            <input type="text" name="status" value="{{ $status ?? '' }}" placeholder="Status (e.g. pending, canceled)..." class="w-full text-xs font-semibold rounded-xl border border-gray-200 px-3.5 py-2.5 focus:border-[#0d9488] focus:ring-0">
            <button type="submit" class="bg-gray-900 text-white px-5 py-2.5 rounded-xl font-extrabold text-xs hover:bg-gray-800 transition shadow-sm">Filter Orders</button>
        </form>
    </div>

    @if(!$orders || $orders->count() === 0)
        <div class="p-12 text-center text-gray-500 font-bold">No orders found matching your criteria.</div>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Date & Time</th>
                        <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Order Details</th>
                        <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3.5 text-right text-xs font-extrabold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @foreach($orders as $order)
                        @php
                            $isReported = (bool) ($order->is_reported ?? false);
                            $stLower = strtolower((string) $order->status);
                            $badgeClass = in_array($stLower, ['received', 'completed', 'success'], true) ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : (in_array($stLower, ['canceled', 'cancelled', 'timeout', 'failed'], true) ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-amber-50 text-amber-700 border-amber-200');
                            $isCompleted = ($type ?? 'sms') !== 'logs' && in_array($stLower, ['received', 'completed', 'success'], true);
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition" x-data="{ reported: @js($isReported), isCompleted: @js($isCompleted) }">
                            <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-gray-600">{{ $order->created_at->format('M d, Y H:i') }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                @if(($type ?? 'sms') === 'sms')
                                    <div class="font-extrabold text-gray-900">+{{ $order->phone_number }}</div>
                                    <div class="text-xs font-semibold text-gray-500">{{ $order->country }} · {{ $order->product }} · {{ $order->operator }}</div>
                                @elseif(($type ?? 'sms') === 'boost')
                                    <div class="font-extrabold text-gray-900">{{ $order->service->name ?? 'Boost Service' }}</div>
                                    <div class="text-xs font-semibold text-gray-500">Qty: {{ number_format((int)$order->quantity) }} · {{ $order->link }}</div>
                                @else
                                    <div class="font-extrabold text-gray-900">Logs Order #{{ $order->id }}</div>
                                    <div class="text-xs font-semibold text-gray-500">{{ number_format((int)$order->total_items) }} items</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-black text-gray-900">
                                @if(($type ?? 'sms') === 'sms')
                                    {{ $currencySymbol ?? '$' }}{{ ($formatAmount ?? fn ($x) => number_format($x, 2))($order->price) }}
                                @elseif(($type ?? 'sms') === 'boost')
                                    {{ $currencySymbol ?? '$' }}{{ ($formatAmount ?? fn ($x) => number_format($x, 2))($order->charge) }}
                                @else
                                    {{ $currencySymbol ?? '$' }}{{ ($formatAmount ?? fn ($x) => number_format($x, 2))($order->total_amount) }}
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black border uppercase {{ $badgeClass }}">{{ $order->status }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-xs">
                                <div class="flex items-center justify-end gap-2">
                                    <!-- Reported Badge (if already reported, report button goes out) -->
                                    <template x-if="reported">
                                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[11px] font-black bg-amber-100 text-amber-800 border border-amber-200">
                                            🚩 Reported (Under Review)
                                        </span>
                                    </template>

                                    <!-- Report Button (Visible only when NOT reported yet AND NOT completed/received) -->
                                    <template x-if="!reported && !isCompleted">
                                        <button 
                                            type="button" 
                                            @click="reportType = @js($type); reportId = {{ $order->id }}; reportReason = ''; reportModalOpen = true" 
                                            class="inline-flex items-center gap-1 bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 px-3 py-1.5 rounded-xl font-extrabold text-xs transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                            Report Issue
                                        </button>
                                    </template>

                                    @if(($type ?? 'sms') === 'boost')
                                        <a href="/social-boosting?boost_service_id={{ $order->boost_service_id }}&link={{ urlencode($order->link) }}&quantity={{ $order->quantity }}" class="bg-gray-900 text-white px-3 py-1.5 rounded-xl font-extrabold hover:bg-gray-800 transition">Reorder</a>
                                    @elseif(($type ?? 'sms') === 'sms')
                                        <a href="/sms-verify" class="bg-gray-900 text-white px-3 py-1.5 rounded-xl font-extrabold hover:bg-gray-800 transition">View</a>
                                    @else
                                        <a href="/logs-store" class="bg-gray-900 text-white px-3 py-1.5 rounded-xl font-extrabold hover:bg-gray-800 transition">Browse</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-6 py-5 border-t border-gray-100 bg-gray-50">
            {{ $orders->links() }}
        </div>
    @endif

    <!-- Report Order Modal Dialog -->
    <div x-show="reportModalOpen" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4" x-cloak style="display: none;">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-gray-100" @click.away="reportModalOpen = false">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100">
                <div class="flex items-center gap-2 text-rose-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <h3 class="text-base font-black text-gray-900">Report Order Issue</h3>
                </div>
                <button type="button" @click="reportModalOpen = false" class="text-gray-400 hover:text-gray-600 font-bold">✕</button>
            </div>
            
            <form @submit.prevent="
                submittingReport = true;
                fetch('/orders/report', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        order_type: reportType,
                        order_id: reportId,
                        reason: reportReason
                    })
                })
                .then(r => r.json())
                .then(data => {
                    submittingReport = false;
                    if(data.success) {
                        alert(data.message);
                        window.location.reload();
                    } else {
                        alert(data.message || 'Error submitting report');
                    }
                })
                .catch(err => {
                    submittingReport = false;
                    alert('An error occurred. Please try again.');
                });
            ">
                <p class="text-xs font-bold text-gray-600 mb-3">Submitting a report alerts our admin team immediately to inspect your transaction and process a resolution or refund.</p>
                
                <div class="mb-4">
                    <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Reason for Report (Optional)</label>
                    <textarea x-model="reportReason" rows="4" class="w-full text-xs font-semibold rounded-xl border border-gray-200 p-3 focus:border-[#0d9488] focus:ring-0" placeholder="Describe the issue with this order (e.g. SMS code not received, order failed, incomplete delivery)..."></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" @click="reportModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-extrabold text-gray-700 bg-gray-100 hover:bg-gray-200 transition">Cancel</button>
                    <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white px-5 py-2 rounded-xl text-xs font-extrabold transition shadow-md" :disabled="submittingReport">
                        <span x-show="!submittingReport">Submit Order Report</span>
                        <span x-show="submittingReport">Submitting...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
