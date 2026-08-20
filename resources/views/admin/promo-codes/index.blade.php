@extends('layouts.admin')

@section('admin_content')
@push('header_title')
    Manage Promo Codes
@endpush

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

@if($errors->any())
    <div class="mb-6 bg-rose-50 border border-rose-200 text-rose-800 px-5 py-4 rounded-2xl shadow-sm">
        <ul class="list-disc pl-5 font-bold text-sm space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- Top Overview Bar -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <div class="text-xs font-extrabold text-gray-500 uppercase tracking-wider">Total Promo Codes</div>
            <div class="text-2xl font-black text-gray-900 mt-1">{{ number_format($codes->count()) }}</div>
            <div class="text-xs font-bold text-teal-600 mt-0.5">{{ number_format($codes->filter(fn($c) => $c->is_active)->count()) }} Active</div>
        </div>
        <div class="p-3 bg-teal-50 text-[#0d9488] rounded-xl">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 11h.01M7 15h.01M13 7h.01M13 11h.01M13 15h.01M19 7h.01M19 11h.01M19 15h.01M4 21h16a2 2 0 002-2V5a2 2 0 00-2-2H4a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <div class="text-xs font-extrabold text-gray-500 uppercase tracking-wider">Public User Promos</div>
            <div class="text-2xl font-black text-indigo-600 mt-1">{{ number_format($codes->filter(fn($c) => $c->is_public)->count()) }}</div>
            <div class="text-xs font-bold text-gray-500 mt-0.5">Visible on coupon page</div>
        </div>
        <div class="p-3 bg-indigo-50 text-indigo-600 rounded-xl">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <div class="text-xs font-extrabold text-gray-500 uppercase tracking-wider">Total Redemptions</div>
            <div class="text-2xl font-black text-amber-600 mt-1">{{ number_format($codes->sum('used_count')) }}</div>
            <div class="text-xs font-bold text-amber-700 mt-0.5">Times redeemed</div>
        </div>
        <div class="p-3 bg-amber-50 text-amber-600 rounded-xl">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Create Code Card -->
    <div class="lg:col-span-1 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col justify-between">
        <div>
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/80">
                <h3 class="text-base font-black text-gray-900">Create Promo Code</h3>
            </div>
            <div class="p-6">
                <form action="/admin/promo-codes" method="POST" class="space-y-4" x-data="{
                    makeCode() {
                        const letters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
                        let out = '';
                        for (let i = 0; i < 10; i++) {
                            out += letters[Math.floor(Math.random() * letters.length)];
                        }
                        this.$refs.code.value = out;
                    }
                }">
                    @csrf
                    <div>
                        <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Promo Code</label>
                        <div class="flex gap-2">
                            <input x-ref="code" name="code" value="{{ old('code') }}" class="flex-1 text-xs font-black uppercase tracking-wider rounded-xl border border-gray-200 px-3 py-2.5 focus:border-[#0d9488] focus:ring-0" placeholder="DISCOUNT10" required>
                            <button type="button" @click="makeCode()" class="bg-gray-900 text-white px-3.5 py-2.5 rounded-xl font-extrabold text-xs hover:bg-gray-800 transition whitespace-nowrap">Auto</button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Title (Optional)</label>
                        <input name="title" value="{{ old('title') }}" class="w-full text-xs font-semibold rounded-xl border border-gray-200 px-3 py-2 focus:border-[#0d9488] focus:ring-0" placeholder="e.g. Weekend Special Bonus">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Type</label>
                            <select name="type" class="w-full text-xs font-bold rounded-xl border border-gray-200 px-3 py-2 focus:border-[#0d9488] focus:ring-0">
                                <option value="percent" {{ old('type', 'percent') === 'percent' ? 'selected' : '' }}>Percent (%)</option>
                                <option value="fixed" {{ old('type') === 'fixed' ? 'selected' : '' }}>Fixed ($ USD)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Value</label>
                            <input type="number" step="0.01" min="0" name="value" value="{{ old('value', '10') }}" class="w-full text-xs font-bold rounded-xl border border-gray-200 px-3 py-2 focus:border-[#0d9488] focus:ring-0" required>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Max Uses</label>
                            <input type="number" min="1" name="max_uses" value="{{ old('max_uses') }}" class="w-full text-xs font-bold rounded-xl border border-gray-200 px-3 py-2 focus:border-[#0d9488] focus:ring-0" placeholder="Unlimited">
                        </div>
                        <div>
                            <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Expires At</label>
                            <input type="date" name="expires_at" value="{{ old('expires_at') }}" class="w-full text-xs font-semibold rounded-xl border border-gray-200 px-3 py-2 focus:border-[#0d9488] focus:ring-0">
                        </div>
                    </div>
                    <div class="border-t border-gray-100 pt-3">
                        <div class="text-[11px] font-extrabold text-gray-500 uppercase tracking-wider mb-2">Applies To Services</div>
                        <div class="grid grid-cols-2 gap-2">
                            @php
                                $appliesOld = old('applies_to');
                                $applies = is_array($appliesOld) ? $appliesOld : ['funding','logs','sms','boost'];
                            @endphp
                            <label class="inline-flex items-center gap-2 text-xs font-bold text-gray-700">
                                <input type="checkbox" name="applies_to[]" value="funding" class="rounded border-gray-300 text-[#0d9488] focus:ring-0" {{ in_array('funding', $applies, true) ? 'checked' : '' }}>
                                <span>Funding</span>
                            </label>
                            <label class="inline-flex items-center gap-2 text-xs font-bold text-gray-700">
                                <input type="checkbox" name="applies_to[]" value="logs" class="rounded border-gray-300 text-[#0d9488] focus:ring-0" {{ in_array('logs', $applies, true) ? 'checked' : '' }}>
                                <span>Logs Store</span>
                            </label>
                            <label class="inline-flex items-center gap-2 text-xs font-bold text-gray-700">
                                <input type="checkbox" name="applies_to[]" value="sms" class="rounded border-gray-300 text-[#0d9488] focus:ring-0" {{ in_array('sms', $applies, true) ? 'checked' : '' }}>
                                <span>SMS Numbers</span>
                            </label>
                            <label class="inline-flex items-center gap-2 text-xs font-bold text-gray-700">
                                <input type="checkbox" name="applies_to[]" value="boost" class="rounded border-gray-300 text-[#0d9488] focus:ring-0" {{ in_array('boost', $applies, true) ? 'checked' : '' }}>
                                <span>Boosting</span>
                            </label>
                        </div>
                    </div>
                    <label class="inline-flex items-center gap-2 text-xs font-bold text-gray-700 pt-1">
                        <input type="checkbox" name="is_public" value="1" {{ old('is_public') ? 'checked' : '' }} class="rounded border-gray-300 text-[#0d9488] focus:ring-0">
                        <span>Make Public on Coupon Banner</span>
                    </label>
                    <button type="submit" class="w-full bg-[#0d9488] hover:bg-[#0f766e] text-white px-4 py-3 rounded-xl font-black text-xs uppercase tracking-wider transition shadow-md">Create Promo Code</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Active Promo Codes Table -->
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col justify-between">
        <div>
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/80">
                <h3 class="text-base font-black text-gray-900">Active & Past Promo Codes</h3>
            </div>
            @if($codes->count() === 0)
                <div class="p-10 text-center text-gray-500 font-bold">No promo codes generated yet.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Code</th>
                                <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Type & Value</th>
                                <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Redemptions</th>
                                <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3.5 text-right text-xs font-extrabold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach($codes as $c)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-mono font-black text-gray-900">{{ $c->code }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-gray-700">
                                        <span class="text-gray-900 font-extrabold">{{ $c->type === 'fixed' ? ('$'.number_format($c->value, 2)) : (number_format($c->value, 1).'%') }}</span>
                                        <span class="text-gray-400 font-normal">({{ strtoupper($c->type) }})</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-gray-700">
                                        {{ number_format($c->used_count) }}{{ $c->max_uses ? (' / '.number_format($c->max_uses)) : ' (Unlimited)' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs">
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase {{ $c->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-gray-600' }}">{{ $c->is_active ? 'ACTIVE' : 'DISABLED' }}</span>
                                        <span class="ml-1 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase {{ $c->is_public ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-gray-100 text-gray-500' }}">{{ $c->is_public ? 'PUBLIC' : 'PRIVATE' }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-xs">
                                        <div class="flex justify-end gap-1.5">
                                            <form action="/admin/promo-codes/{{ $c->id }}/toggle" method="POST">
                                                @csrf
                                                <button type="submit" class="bg-gray-900 text-white px-2.5 py-1.5 rounded-xl text-xs font-extrabold hover:bg-gray-800 transition">{{ $c->is_active ? 'Disable' : 'Enable' }}</button>
                                            </form>
                                            <form action="/admin/promo-codes/{{ $c->id }}/public" method="POST">
                                                @csrf
                                                <button type="submit" class="bg-gray-100 text-gray-800 px-2.5 py-1.5 rounded-xl text-xs font-extrabold hover:bg-gray-200 transition">{{ $c->is_public ? 'Hide' : 'Show' }}</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                    {{ $codes->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
