@extends('layouts.admin')

@section('admin_content')
@push('header_title', 'Utilities & BigiSub Management')

<div class="space-y-6">

    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2">
                <span>⚡ Utilities &amp; BigiSub VTU</span>
                @if($simulate)
                    <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 border border-amber-200">
                        Sandbox / Simulation Mode
                    </span>
                @else
                    <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">
                        Live Production
                    </span>
                @endif
            </h1>
            <p class="text-xs text-gray-500 mt-1">
                Monitor VTU airtime, data bundles, electricity meter tokens, and betting wallet funding.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" 
                    onclick="document.getElementById('settings-panel').classList.toggle('hidden')"
                    class="px-4 py-2 rounded-xl bg-gray-900 hover:bg-gray-800 text-white font-bold text-xs uppercase tracking-wider transition shadow-sm flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>BigiSub Settings</span>
            </button>
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

    <!-- Settings Card (Collapsible) -->
    <div id="settings-panel" class="hidden bg-white rounded-2xl border border-gray-200 p-5 sm:p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
            <div>
                <h3 class="text-sm font-black text-gray-900 uppercase tracking-wider">BigiSub API &amp; VTU Configuration</h3>
                <p class="text-xs text-gray-500">Configure provider endpoints, API credentials, transaction PIN, and customer discounts.</p>
            </div>
            <button type="button" onclick="document.getElementById('settings-panel').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 text-sm font-bold">
                ✕ Close
            </button>
        </div>

        <form action="{{ route('admin.utilities.settings') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @csrf

            <!-- API Base URL -->
            <div>
                <label class="block text-[11px] font-black uppercase text-gray-700 mb-1">Provider API Base URL</label>
                <input type="url" 
                       name="bigisub_base_url" 
                       value="{{ old('bigisub_base_url', $baseUrl) }}" 
                       required 
                       placeholder="https://api.bigisub.ng" 
                       class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                <span class="text-[10px] text-gray-400 mt-1 block">Default: https://api.bigisub.ng or https://bigisub.ng/api/v2</span>
            </div>

            <!-- API Token -->
            <div>
                <label class="block text-[11px] font-black uppercase text-gray-700 mb-1">Authorization Token / API Key</label>
                <div class="relative">
                    <input type="password" 
                           id="api-token-input"
                           name="bigisub_api_token" 
                           value="{{ old('bigisub_api_token', $apiToken) }}" 
                           placeholder="Enter live BigiSub API token" 
                           class="w-full text-xs font-mono font-semibold px-3 py-2 pr-10 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                    <button type="button" 
                            onclick="const el = document.getElementById('api-token-input'); el.type = el.type === 'password' ? 'text' : 'password';"
                            class="absolute inset-y-0 right-0 px-3 text-xs text-gray-400 hover:text-gray-700">
                        👁
                    </button>
                </div>
                <span class="text-[10px] text-gray-400 mt-1 block">Passed in Authorization: Token {key} header</span>
            </div>

            <!-- Transaction PIN -->
            <div>
                <label class="block text-[11px] font-black uppercase text-gray-700 mb-1">BigiSub Transaction PIN</label>
                <input type="text" 
                       name="bigisub_pin" 
                       value="{{ old('bigisub_pin', $pin) }}" 
                       maxlength="10" 
                       placeholder="1234" 
                       class="w-full text-xs font-mono font-semibold px-3 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                <span class="text-[10px] text-gray-400 mt-1 block">4-digit wallet transfer/purchase PIN on BigiSub</span>
            </div>

            <!-- Airtime Discount % -->
            <div>
                <label class="block text-[11px] font-black uppercase text-gray-700 mb-1">Airtime Discount to Users (%)</label>
                <input type="number" 
                       step="0.1" 
                       min="0" 
                       max="20" 
                       name="utility_airtime_discount_percent" 
                       value="{{ old('utility_airtime_discount_percent', $airtimeDiscount) }}" 
                       required 
                       class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500">
                <span class="text-[10px] text-gray-400 mt-1 block">E.g. 2.0 = Users get 2% discount on recharge</span>
            </div>

            <!-- Simulation Toggle -->
            <div>
                <label class="block text-[11px] font-black uppercase text-gray-700 mb-1">Mode &amp; Safety</label>
                <select name="bigisub_simulate" class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 bg-white">
                    <option value="0" {{ !$simulate ? 'selected' : '' }}>Live Production (Real Money &amp; API)</option>
                    <option value="1" {{ $simulate ? 'selected' : '' }}>Simulation Mode (Safe Mock Testing)</option>
                </select>
                <span class="text-[10px] text-gray-400 mt-1 block">Use simulation if credentials or sandbox not yet funded</span>
            </div>

            <div class="sm:col-span-2 lg:col-span-1 flex items-end">
                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs uppercase tracking-wider transition shadow-sm">
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- Total Completed Volume -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
            <span class="text-[10px] font-black uppercase tracking-wider text-gray-400 block">Total Completed</span>
            <div class="text-xl font-black text-gray-900 mt-1">₦{{ number_format((float)$totalVolumeNgn, 2) }}</div>
            <div class="text-[11px] font-semibold text-emerald-600 mt-0.5">{{ $totalSuccessful }} successful txns</div>
        </div>

        <!-- Pending / Uncertain -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
            <span class="text-[10px] font-black uppercase tracking-wider text-gray-400 block">Pending / In-Flight</span>
            <div class="text-xl font-black text-amber-600 mt-1">{{ $totalPending }}</div>
            <div class="text-[11px] font-semibold text-gray-500 mt-0.5">Awaiting reconciliation</div>
        </div>

        <!-- Failed & Refunded -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
            <span class="text-[10px] font-black uppercase tracking-wider text-gray-400 block">Failed / Refunded</span>
            <div class="text-xl font-black text-rose-600 mt-1">{{ $totalFailed }}</div>
            <div class="text-[11px] font-semibold text-gray-500 mt-0.5">Definite failures</div>
        </div>

        <!-- Live Provider Balance -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
            <span class="text-[10px] font-black uppercase tracking-wider text-gray-400 block">BigiSub Live Balance</span>
            <div class="text-xl font-black text-teal-600 mt-1">
                @if(!is_null($providerBalance))
                    ₦{{ number_format((float)$providerBalance, 2) }}
                @else
                    <span class="text-gray-400 text-sm">Unavailable</span>
                @endif
            </div>
            <div class="text-[11px] font-semibold text-gray-500 mt-0.5">Provider reserve balance</div>
        </div>

        <!-- Mode -->
        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
            <span class="text-[10px] font-black uppercase tracking-wider text-gray-400 block">Integration Status</span>
            <div class="text-base font-black text-gray-900 mt-1">
                @if($simulate)
                    <span class="text-amber-600">Simulated / Safe</span>
                @else
                    <span class="text-emerald-600">Live API</span>
                @endif
            </div>
            <div class="text-[11px] font-semibold text-gray-500 mt-0.5">BigiSub Gateway</div>
        </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-gray-200 shadow-sm space-y-3">
        <form method="GET" action="{{ route('admin.utilities.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <!-- Search -->
            <div class="sm:col-span-5 relative">
                <input type="text" 
                       name="q" 
                       value="{{ $q ?? '' }}" 
                       placeholder="Search reference, recipient, meter, customer name, user email..." 
                       class="w-full pl-3 pr-4 py-2.5 text-xs font-semibold rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 text-gray-800 placeholder-gray-400">
            </div>

            <!-- Service Type Filter -->
            <div class="sm:col-span-3">
                <select name="type" class="w-full py-2.5 px-3 text-xs font-semibold rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 text-gray-800 bg-white">
                    <option value="">All Services</option>
                    <option value="airtime" {{ ($type ?? '') === 'airtime' ? 'selected' : '' }}>📶 Airtime</option>
                    <option value="data" {{ ($type ?? '') === 'data' ? 'selected' : '' }}>🌐 Data Bundles</option>
                    <option value="electricity" {{ ($type ?? '') === 'electricity' ? 'selected' : '' }}>⚡ Electricity</option>
                    <option value="betting" {{ ($type ?? '') === 'betting' ? 'selected' : '' }}>⚽ Betting Top-Up</option>
                </select>
            </div>

            <!-- Status Filter -->
            <div class="sm:col-span-2">
                <select name="status" class="w-full py-2.5 px-3 text-xs font-semibold rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 text-gray-800 bg-white">
                    <option value="">All Statuses</option>
                    <option value="completed" {{ ($status ?? '') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="pending" {{ ($status ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="uncertain" {{ ($status ?? '') === 'uncertain' ? 'selected' : '' }}>Uncertain</option>
                    <option value="failed" {{ ($status ?? '') === 'failed' ? 'selected' : '' }}>Failed</option>
                    <option value="refunded" {{ ($status ?? '') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                </select>
            </div>

            <!-- Submit -->
            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit" class="flex-1 py-2.5 px-4 bg-gray-900 hover:bg-gray-800 text-white rounded-xl text-xs font-bold transition">
                    Filter
                </button>
                @if(($q ?? '') || ($type ?? '') || ($status ?? ''))
                    <a href="{{ route('admin.utilities.index') }}" class="py-2.5 px-3 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl text-xs font-bold transition" title="Clear Filters">
                        ✕
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        @if($transactions->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50 border-b border-gray-200 text-gray-500 uppercase tracking-wider font-extrabold text-[10px]">
                        <tr>
                            <th class="py-3.5 px-4">User</th>
                            <th class="py-3.5 px-4">Service &amp; Operator</th>
                            <th class="py-3.5 px-4">Target / Recipient</th>
                            <th class="py-3.5 px-4">Amount</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-4">References</th>
                            <th class="py-3.5 px-4">Date</th>
                            <th class="py-3.5 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($transactions as $t)
                            <tr class="hover:bg-gray-50/70 transition">
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-gray-900">{{ $t->user->name ?? 'User #' . $t->user_id }}</div>
                                    <div class="text-[10px] text-gray-400 font-mono">{{ $t->user->email ?? 'N/A' }}</div>
                                </td>

                                <td class="py-3.5 px-4">
                                    <div class="font-extrabold text-gray-900 uppercase">
                                        {{ $t->service_type }} · {{ $t->network_or_disco }}
                                    </div>
                                    @if($t->product_name)
                                        <div class="text-[10px] text-gray-500 font-medium truncate max-w-xs">
                                            {{ $t->product_name }}
                                        </div>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4">
                                    <div class="font-mono font-bold text-gray-900">{{ $t->customer_target }}</div>
                                    @if($t->customer_name)
                                        <div class="text-[10px] text-gray-500">{{ $t->customer_name }}</div>
                                    @endif
                                    @if($t->token)
                                        <div class="text-[10px] text-emerald-600 font-mono font-bold">
                                            TOKEN: {{ $t->token }}
                                        </div>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4">
                                    <div class="font-extrabold text-gray-900">₦{{ number_format((float)$t->amount_ngn, 2) }}</div>
                                    <div class="text-[10px] text-gray-400 font-semibold">${{ number_format((float)$t->total_charged_usd, 4) }} USD</div>
                                </td>

                                <td class="py-3.5 px-4">
                                    @php $b = $t->status_badge; @endphp
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold border {{ $b['bg'] }}">
                                        <span>{{ $b['icon'] }}</span>
                                        {{ $b['label'] }}
                                    </span>
                                </td>

                                <td class="py-3.5 px-4">
                                    <div class="font-mono text-[10px] text-gray-800 font-bold select-all">{{ $t->reference }}</div>
                                    @if($t->provider_reference)
                                        <div class="font-mono text-[9px] text-gray-400 select-all">Prov: {{ $t->provider_reference }}</div>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 text-gray-500 whitespace-nowrap">
                                    <div>{{ $t->created_at->format('M d, Y') }}</div>
                                    <div class="text-[10px] text-gray-400">{{ $t->created_at->format('h:i A') }}</div>
                                </td>

                                <td class="py-3.5 px-4 text-right">
                                    <div class="inline-flex items-center gap-1">
                                        <a href="{{ route('admin.utilities.show', $t->id) }}" 
                                           class="px-2.5 py-1 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-[11px] transition">
                                            Inspect
                                        </a>

                                        @if(in_array($t->status, ['pending', 'uncertain']))
                                            <form action="{{ route('admin.utilities.reconcile', $t->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" 
                                                        onclick="return confirm('Reconcile this transaction with BigiSub API?')" 
                                                        class="px-2 py-1 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 font-bold text-[10px] border border-amber-200 transition"
                                                        title="Check status on BigiSub">
                                                    🔄
                                                </button>
                                            </form>
                                        @endif

                                        @if($t->canRefund())
                                            <form action="{{ route('admin.utilities.refund', $t->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" 
                                                        onclick="return confirm('Refund ₦{{ number_format((float)$t->amount_ngn, 2) }} back to user wallet?')" 
                                                        class="px-2 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-[10px] border border-rose-200 transition"
                                                        title="Manual Refund">
                                                    ↩
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-100">
                {{ $transactions->links() }}
            </div>
        @else
            <div class="text-center py-16 px-4">
                <div class="w-14 h-14 mx-auto rounded-full bg-gray-100 flex items-center justify-center text-2xl mb-3">
                    📜
                </div>
                <h3 class="text-sm font-extrabold text-gray-800">No utility transactions found</h3>
                <p class="text-gray-500 text-xs mt-1">There are no records matching the selected search or filter criteria.</p>
            </div>
        @endif
    </div>

</div>
@endsection
