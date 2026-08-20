@extends('layouts.dashboard')

@section('page_title', 'Social Boosting')

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

<div class="mb-8 bg-gradient-to-r from-gray-800 to-gray-900 rounded-2xl p-6 sm:p-8 text-white shadow-lg relative overflow-hidden">
    <div class="relative z-10">
        <h2 class="text-2xl sm:text-3xl font-bold mb-2 text-[#facc15]">Boost Your Social Media</h2>
        <p class="text-gray-300 max-w-2xl text-sm sm:text-base">Choose a service, enter your link, set quantity, and track delivery progress in real-time.</p>
        <div class="mt-3 inline-flex items-center bg-white/10 border border-white/10 px-3 py-1.5 rounded-xl text-xs font-bold">
            Powered by May22SMS     
        </div>
    </div>
    <div class="absolute right-0 top-0 h-full w-1/3 bg-gradient-to-l from-[#facc15] opacity-10"></div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10" x-data="boostingPageData()" x-init="init()">
    @if($services->isEmpty())
        <div class="lg:col-span-3 bg-white rounded-2xl shadow-sm border border-gray-100 p-10 text-center">
            <div class="text-5xl mb-4">🚀</div>
            <div class="text-xl font-bold text-gray-900 mb-2">No boosting services available yet</div>
            <div class="text-gray-500">Ask admin to sync services from the provider in Admin → Manage Boosting.</div>
        </div>
    @else
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden lg:col-span-1">
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800">Pick a Service</h3>
        </div>
        <div class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                <select x-model="category" class="w-full rounded-lg border-gray-300 focus:border-[#0d9488] focus:ring focus:ring-teal-200 focus:ring-opacity-50">
                    <option value="">All Categories</option>
                    <template x-for="cat in categories" :key="cat">
                        <option :value="cat" x-text="cat"></option>
                    </template>
                </select>
            </div>

            <div class="flex items-center justify-between">
                <div class="text-sm font-medium text-gray-700">Favorites only</div>
                <button type="button" @click="toggleFavoritesOnly()" class="px-3 py-2 rounded-lg font-bold text-sm" :class="favoritesOnly ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-800 hover:bg-gray-200'">
                    <span x-text="favoritesOnly ? 'ON' : 'OFF'"></span>
                </button>
            </div>
            <div class="text-xs text-gray-500" x-show="favoritesOnly && (!favorites || favorites.length === 0)" style="display:none;">
                You don’t have any favorites yet. Turn Favorites off to see all services, then add favorites.
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <input type="text" x-model="search" class="w-full rounded-lg border-gray-300 focus:border-[#0d9488] focus:ring focus:ring-teal-200 focus:ring-opacity-50" placeholder="e.g. Instagram followers">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Service</label>
                <select x-model="serviceId" class="w-full rounded-lg border-gray-300 focus:border-[#0d9488] focus:ring focus:ring-teal-200 focus:ring-opacity-50">
                    <option value="">Select service</option>
                    <template x-for="srv in filteredServices" :key="srv.id">
                        <option :value="srv.id" x-text="`${srv.name} — ${symbol}${format(Number(srv.rate) * fx)}/1000`"></option>
                    </template>
                </select>
                <div class="mt-3 flex items-center justify-between" x-show="serviceId" style="display:none;">
                    <button type="button" @click="toggleFav()" class="px-3 py-2 rounded-lg font-bold text-sm" :class="isFav(serviceId) ? 'bg-[#facc15] text-black' : 'bg-gray-100 text-gray-800 hover:bg-gray-200'">
                        <span x-text="isFav(serviceId) ? '★ Favorited' : '☆ Add to favorites'"></span>
                    </button>
                    <a href="/orders?type=boost" class="text-sm font-bold text-[#0d9488] hover:underline">My Orders</a>
                </div>
                <div class="mt-2 text-xs text-gray-500" x-show="selectedService">
                    <span class="font-bold">Min:</span> <span x-text="selectedService ? selectedService.min : ''"></span>
                    <span class="mx-2">•</span>
                    <span class="font-bold">Max:</span> <span x-text="selectedService ? selectedService.max : ''"></span>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden lg:col-span-2">
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-800">Place Order</h3>
            <span class="text-xs font-bold bg-teal-50 text-[#0d9488] px-3 py-1.5 rounded-full">Markup: {{ number_format($markup ?? 0, 1) }}%</span>
        </div>
        <div class="p-6">
            <form action="/social-boosting/order" method="POST" class="space-y-4" @submit="if (submitting) { $event.preventDefault(); return; } submitting = true">
                @csrf
                <input type="hidden" name="boost_service_id" :value="serviceId">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Link</label>
                    <input type="url" name="link" x-model="link" class="w-full rounded-lg border-gray-300 focus:border-[#0d9488] focus:ring focus:ring-teal-200 focus:ring-opacity-50" placeholder="https://instagram.com/username" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                    <input type="number" name="quantity" x-model="qty" min="1" :min="selectedService ? selectedService.min : 1" :max="selectedService && selectedService.max ? selectedService.max : null" class="w-full rounded-lg border-gray-300 focus:border-[#0d9488] focus:ring focus:ring-teal-200 focus:ring-opacity-50" placeholder="1000" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Promo Code (Optional)</label>
                    <input type="text" name="promo_code" value="{{ old('promo_code') }}" class="w-full rounded-lg border-gray-300 focus:border-[#0d9488] focus:ring focus:ring-teal-200 focus:ring-opacity-50" placeholder="e.g. WELCOME10">
                    <div class="mt-2 text-xs text-gray-500">If valid, a discount will be applied at checkout.</div>
                </div>

                <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-700 font-bold">Total Charge</span>
                        <span class="text-xl font-extrabold text-[#0d9488]"><span x-text="symbol"></span><span x-text="total.toFixed(2)"></span></span>
                    </div>
                    <div class="mt-2 text-xs text-gray-500" x-show="selectedService">
                        Rate: <span x-text="symbol"></span><span x-text="selectedService ? format(Number(selectedService.rate) * fx) : ''"></span> per 1000
                    </div>
                </div>

                <button type="submit" class="w-full bg-[#facc15] hover:bg-yellow-400 text-black font-extrabold px-4 py-3 rounded-xl shadow-sm transition disabled:opacity-60 disabled:cursor-not-allowed" :disabled="submitting || !serviceId || !link || !qty || qty < 1">
                    <span x-show="!submitting">Place Order</span>
                    <span x-show="submitting" style="display: none;">Placing Order...</span>
                </button>
            </form>
        </div>
    </div>
    @endif
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-6 py-5 border-b border-gray-100 bg-gray-50">
        <h3 class="text-lg font-bold text-gray-800">My Boost Orders</h3>
    </div>

    @if($orders->count() === 0)
        <div class="p-10 text-center text-gray-500">No boost orders yet.</div>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Charge</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($orders as $order)
                        <tr x-data="{
                            status: @js($order->status),
                            startCount: {{ $order->start_count ?? 'null' }},
                            remains: {{ $order->remains ?? 'null' }},
                            progress: null,
                            reported: @js((bool)($order->is_reported ?? false)),
                            submittingReport: false,
                            init() {
                                const isFinal = () => {
                                    const s = (this.status || '').toLowerCase();
                                    return s === 'completed' || s === 'canceled' || s === 'cancelled' || s === 'partial';
                                };
                                const calcProgress = () => {
                                    if (this.remains === null) return null;
                                    const qty = {{ (int) $order->quantity }};
                                    if (!qty || qty <= 0) return null;
                                    const done = qty - Math.max(0, Number(this.remains));
                                    const pct = Math.round((done / qty) * 100);
                                    return Math.min(100, Math.max(0, pct));
                                };
                                this.progress = calcProgress();
                                const poll = async () => {
                                    try {
                                        const res = await fetch(`/api/boost/check/{{ $order->id }}`);
                                        if (!res.ok) return;
                                        const data = await res.json();
                                        if (data && data.status) {
                                            this.status = data.status;
                                            if (data.start_count !== undefined) this.startCount = data.start_count;
                                            if (data.remains !== undefined) this.remains = data.remains;
                                            this.progress = calcProgress();
                                        }
                                    } catch (e) {}
                                };

                                const interval = setInterval(async () => {
                                    if (isFinal()) {
                                        clearInterval(interval);
                                        return;
                                    }
                                    await poll();
                                }, 10000);
                            }
                        }">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $order->created_at->format('M d, Y H:i') }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 font-medium">{{ $order->service->name ?? 'Unknown' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $order->quantity }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-[#0d9488]">{{ $currencySymbol ?? '$' }}{{ ($formatAmount ?? fn($x) => number_format($x, 2))($order->charge) }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <div class="w-48">
                                    <div class="flex items-center justify-between text-xs mb-1">
                                        <span x-text="progress === null ? '—' : (progress + '%')"></span>
                                        <span x-show="remains !== null" class="text-gray-500">Remains: <span x-text="remains"></span></span>
                                    </div>
                                    <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                        <div class="h-2 bg-[#0d9488]" :style="`width: ${progress === null ? 0 : progress}%`"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="font-bold text-gray-800" x-text="status"></span>
                                <div class="text-xs text-gray-500 mt-1" x-show="startCount !== null">Start: <span x-text="startCount"></span></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-xs">
                                <template x-if="reported">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-800 border border-amber-200">
                                        🚩 Reported
                                    </span>
                                </template>
                                <template x-if="!reported && !['completed', 'received', 'success'].includes((status || '').toLowerCase())">
                                    <button 
                                        type="button" 
                                        @click="
                                            if (confirm('Report issue with this boosting order to admin?')) {
                                                submittingReport = true;
                                                fetch('/orders/report', {
                                                    method: 'POST',
                                                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                                    body: JSON.stringify({ order_type: 'boost', order_id: {{ $order->id }}, reason: 'Boosting order issue reported.' })
                                                })
                                                .then(r => r.json())
                                                .then(data => {
                                                    submittingReport = false;
                                                    if (data.success) {
                                                        reported = true;
                                                        alert(data.message);
                                                    } else {
                                                        alert(data.message || 'Could not report order.');
                                                    }
                                                })
                                                .catch(e => { submittingReport = false; alert('Error submitting report.'); });
                                            }
                                        "
                                        class="inline-flex items-center gap-1 text-[11px] font-bold text-rose-700 hover:text-rose-900 bg-rose-50 hover:bg-rose-100 border border-rose-200 px-2.5 py-1 rounded-lg transition"
                                        :disabled="submittingReport">
                                        <span x-show="!submittingReport">🚩 Report</span>
                                        <span x-show="submittingReport">Submitting...</span>
                                    </button>
                                </template>
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
</div>

@endsection

<script>
    window.__boostingServices = @json(($services ?? collect())->values());
    window.__boostingFavorites = @json(($favoriteBoostIds ?? collect())->values());
    window.__boostingInitialServiceId = @json((string) request()->query('boost_service_id', ''));
    window.__boostingInitialLink = @json((string) request()->query('link', ''));
    window.__boostingInitialQty = @json((string) request()->query('quantity', ''));
    window.__boostingCurrencyFx = {{ (float) ($currencyFx ?? 1) }};
    window.__boostingCurrencySymbol = @json($currencySymbol ?? '$');

    function boostingPageData() {
        return {
            services: Array.isArray(window.__boostingServices) ? window.__boostingServices : [],
            favorites: Array.isArray(window.__boostingFavorites) ? window.__boostingFavorites : [],
            favoritesOnly: false,
            category: '',
            search: '',
            serviceId: window.__boostingInitialServiceId || '',
            link: window.__boostingInitialLink || '',
            qty: window.__boostingInitialQty || '',
            submitting: false,
            fx: Number(window.__boostingCurrencyFx || 1),
            symbol: window.__boostingCurrencySymbol || '$',
            init() {
                if (this.favoritesOnly && (!this.favorites || this.favorites.length === 0)) {
                    this.favoritesOnly = false;
                }
            },
            toggleFavoritesOnly() {
                const next = !this.favoritesOnly;
                if (next && (!this.favorites || this.favorites.length === 0)) {
                    this.favoritesOnly = false;
                    return;
                }
                this.favoritesOnly = next;
            },
            format(v) { return Number(v).toFixed(this.fx === 1 ? 4 : 2); },
            get categories() {
                const set = new Set(this.services.map(s => s.category || 'Other'));
                return Array.from(set).sort();
            },
            get filteredServices() {
                const q = (this.search || '').toLowerCase();
                return this.services
                    .filter(s => !this.favoritesOnly || this.favorites.includes(Number(s.id)))
                    .filter(s => !this.category || s.category === this.category)
                    .filter(s => !q || (s.name || '').toLowerCase().includes(q));
            },
            get selectedService() {
                const id = parseInt(this.serviceId || '0', 10);
                return this.services.find(s => s.id === id) || null;
            },
            get totalUsd() {
                const s = this.selectedService;
                const qty = parseInt(this.qty || '0', 10);
                if (!s || !qty || qty < 1) return 0;
                return (qty / 1000) * parseFloat(s.rate);
            },
            get total() {
                return this.totalUsd * this.fx;
            },
            isFav(id) {
                return this.favorites.includes(Number(id));
            },
            async toggleFav() {
                const id = Number(this.serviceId || 0);
                if (!id) return;
                try {
                    const res = await fetch('/favorites/toggle', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                        body: JSON.stringify({ type: 'boost', key: String(id) })
                    });
                    const data = await res.json();
                    if (data && data.favorited === true) {
                        if (!this.favorites.includes(id)) this.favorites.push(id);
                    } else if (data && data.favorited === false) {
                        this.favorites = this.favorites.filter(x => Number(x) !== id);
                    }
                } catch (e) {}
            }
        };
    }
</script>
