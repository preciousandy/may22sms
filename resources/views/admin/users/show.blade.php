@extends('layouts.admin')

@section('admin_content')
@push('header_title')
    User Profile #{{ $user->id }} — {{ $user->name }}
@endpush

@php
    $tab = (string) ($tab ?? 'overview');
    $stats = is_array($stats ?? null) ? $stats : [];
    $st = (string) ($user->account_status ?? 'pending');
    $statusBadge = $st === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : ($st === 'blocked' ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-amber-50 text-amber-700 border-amber-200');
@endphp

<!-- Alerts -->
@if(session('success'))
    <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-4 rounded-2xl shadow-sm flex items-center gap-3">
        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span class="font-bold text-sm">{{ session('success') }}</span>
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

<!-- Header Banner -->
<div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm mb-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
    <div class="flex items-start gap-4">
        <div class="w-14 h-14 rounded-2xl bg-gray-900 text-white font-black text-xl flex items-center justify-center shrink-0 shadow-md">
            {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
        </div>
        <div>
            <div class="flex items-center gap-2 flex-wrap">
                <h1 class="text-2xl font-black text-gray-900 tracking-tight">{{ $user->name }}</h1>
                <span class="inline-flex items-center px-3 py-0.5 rounded-full border text-xs font-black {{ $statusBadge }}">{{ strtoupper($st) }}</span>
                @if($user->is_admin)
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-gray-900 text-white">ADMIN</span>
                @endif
                @if($user->is_support)
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-blue-50 text-blue-700 border border-blue-200">SUPPORT</span>
                @endif
                @if($user->is_editor)
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-purple-50 text-purple-700 border border-purple-200">EDITOR</span>
                @endif
            </div>
            <div class="text-xs font-bold text-gray-500 mt-1 flex items-center gap-3 flex-wrap">
                <span>Email: <strong class="text-gray-800">{{ $user->email }}</strong></span>
                <span>User ID: <strong class="text-gray-800">#{{ $user->id }}</strong></span>
                <span>Joined: <strong class="text-gray-800">{{ $user->created_at ? \Illuminate\Support\Carbon::parse($user->created_at)->format('M j, Y') : '—' }}</strong></span>
            </div>
        </div>
    </div>

    <!-- Action Bar -->
    <div class="flex flex-wrap items-center gap-2">
        <!-- 1-Click Login to User Account -->
        <form action="/admin/users/{{ $user->id }}/impersonate" method="POST" onsubmit="return confirm('Log into account as {{ addslashes($user->name) }}?');">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 bg-[#0d9488] hover:bg-[#0f766e] text-white px-5 py-2.5 rounded-xl font-black text-xs uppercase tracking-wider transition shadow-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                Login to Account
            </button>
        </form>

        <form action="/admin/users/{{ $user->id }}/force-logout" method="POST" onsubmit="return confirm('Force logout this user across all sessions?');">
            @csrf
            <button type="submit" class="inline-flex items-center gap-1.5 bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 px-4 py-2.5 rounded-xl font-extrabold text-xs transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                Force Logout
            </button>
        </form>

        <a href="/admin/users" class="bg-gray-100 text-gray-700 hover:bg-gray-200 px-4 py-2.5 rounded-xl font-extrabold text-xs transition">
            ← Back to Users
        </a>
    </div>
</div>

<!-- Key Performance Stat Cards Grid -->
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
    <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
        <div class="text-[10px] font-extrabold text-gray-400 uppercase tracking-wider">Wallet Balance</div>
        <div class="text-xl font-black text-gray-900 mt-1">${{ number_format((float) ($user->walletBalance ?? 0), 2) }}</div>
        <div class="text-[11px] font-bold text-[#0d9488] mt-0.5">≈ ₦{{ number_format((float) (($user->walletBalance ?? 0) * ($ngnPerUsd ?? 1500)), 2) }} NGN</div>
    </div>

    <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
        <div class="text-[10px] font-extrabold text-gray-400 uppercase tracking-wider">Total Funding</div>
        <div class="text-xl font-black text-emerald-600 mt-1">${{ number_format((float) ($stats['funding_total_usd'] ?? 0), 2) }}</div>
        <div class="text-[11px] font-bold text-gray-500 mt-0.5">{{ number_format((int) ($stats['funding_count'] ?? 0)) }} deposits</div>
    </div>

    <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
        <div class="text-[10px] font-extrabold text-gray-400 uppercase tracking-wider">Logs Purchased</div>
        <div class="text-xl font-black text-indigo-600 mt-1">{{ number_format((int) ($stats['logs_count'] ?? 0)) }}</div>
        <div class="text-[11px] font-bold text-gray-500 mt-0.5">Store orders</div>
    </div>

    <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
        <div class="text-[10px] font-extrabold text-gray-400 uppercase tracking-wider">SMS Numbers</div>
        <div class="text-xl font-black text-amber-600 mt-1">{{ number_format((int) ($stats['sms_count'] ?? 0)) }}</div>
        <div class="text-[11px] font-bold text-gray-500 mt-0.5">Verifications</div>
    </div>

    <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
        <div class="text-[10px] font-extrabold text-gray-400 uppercase tracking-wider">Boost Orders</div>
        <div class="text-xl font-black text-teal-600 mt-1">{{ number_format((int) ($stats['boost_count'] ?? 0)) }}</div>
        <div class="text-[11px] font-bold text-gray-500 mt-0.5">Services ordered</div>
    </div>

    <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
        <div class="text-[10px] font-extrabold text-gray-400 uppercase tracking-wider">Tickets</div>
        <div class="text-xl font-black text-purple-600 mt-1">{{ number_format((int) ($stats['tickets_count'] ?? 0)) }}</div>
        <div class="text-[11px] font-bold text-gray-500 mt-0.5">Support threads</div>
    </div>
</div>

<!-- Primary Navigation Tabs Bar -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-2 mb-6 flex flex-wrap gap-1.5 overflow-x-auto">
    @php
        $tabs = [
            'overview' => 'Profile Overview',
            'wallet' => 'Wallet History',
            'funding' => 'Funding Deposits',
            'logs' => 'Logs Orders',
            'sms' => 'SMS Numbers',
            'boost' => 'Boost Orders',
            'tickets' => 'Support Tickets',
            'notifications' => 'Notifications',
            'favorites' => 'Favorites',
        ];
    @endphp
    @foreach($tabs as $key => $label)
        @php $active = $tab === $key; @endphp
        <a href="/admin/users/{{ $user->id }}?tab={{ $key }}" class="px-4 py-2 rounded-xl text-xs font-extrabold transition {{ $active ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-700 hover:bg-gray-100' }}">
            {{ $label }}
        </a>
    @endforeach
</div>

<!-- Tab Content Container -->
@if($tab === 'overview')
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <!-- Left: Profile & Settings Forms -->
        <div class="xl:col-span-2 space-y-6">
            <!-- Profile Information & Status Form Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/80">
                    <h3 class="text-base font-black text-gray-900">User Profile & Account Status</h3>
                </div>
                <div class="p-6">
                    <form action="/admin/users/{{ $user->id }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Full Name</label>
                                <input name="name" value="{{ old('name', $user->name) }}" class="w-full text-xs font-semibold rounded-xl border border-gray-200 px-3.5 py-2.5 focus:border-[#0d9488] focus:ring-0" required>
                            </div>
                            <div>
                                <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Email Address</label>
                                <input name="email" value="{{ old('email', $user->email) }}" class="w-full text-xs font-semibold rounded-xl border border-gray-200 px-3.5 py-2.5 focus:border-[#0d9488] focus:ring-0" required>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Country</label>
                                <input name="country" value="{{ old('country', $user->country) }}" class="w-full text-xs font-semibold rounded-xl border border-gray-200 px-3.5 py-2.5 focus:border-[#0d9488] focus:ring-0">
                            </div>
                            <div>
                                <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Phone Number</label>
                                <input name="phone_number" value="{{ old('phone_number', $user->phone_number) }}" class="w-full text-xs font-semibold rounded-xl border border-gray-200 px-3.5 py-2.5 focus:border-[#0d9488] focus:ring-0">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Account Status</label>
                                <select name="account_status" class="w-full text-xs font-bold rounded-xl border border-gray-200 px-3.5 py-2.5 focus:border-[#0d9488] focus:ring-0" required>
                                    @php $stVal = old('account_status', $user->account_status ?? 'pending'); @endphp
                                    <option value="active" {{ $stVal === 'active' ? 'selected' : '' }}>Active (Full Access)</option>
                                    <option value="pending" {{ $stVal === 'pending' ? 'selected' : '' }}>Pending Verification</option>
                                    <option value="blocked" {{ $stVal === 'blocked' ? 'selected' : '' }}>Blocked / Suspended</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Two-Factor Authentication (2FA)</label>
                                @php $tfa = old('two_factor_enabled', ($user->two_factor_enabled ?? false) ? '1' : '0'); @endphp
                                <select name="two_factor_enabled" class="w-full text-xs font-bold rounded-xl border border-gray-200 px-3.5 py-2.5 focus:border-[#0d9488] focus:ring-0">
                                    <option value="0" {{ $tfa === '0' ? 'selected' : '' }}>Disabled</option>
                                    <option value="1" {{ $tfa === '1' ? 'selected' : '' }}>Enabled</option>
                                </select>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 pt-4">
                            <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-2">Staff & Administrative Roles</label>
                            <div class="flex gap-4">
                                <label class="inline-flex items-center gap-2 text-xs font-bold text-gray-700">
                                    <input type="checkbox" name="is_support" value="1" {{ old('is_support', $user->is_support ? '1' : '') ? 'checked' : '' }} class="rounded border-gray-300 text-[#0d9488] focus:ring-0">
                                    <span>Support Staff Role</span>
                                </label>
                                <label class="inline-flex items-center gap-2 text-xs font-bold text-gray-700">
                                    <input type="checkbox" name="is_editor" value="1" {{ old('is_editor', $user->is_editor ? '1' : '') ? 'checked' : '' }} class="rounded border-gray-300 text-[#0d9488] focus:ring-0">
                                    <span>Editor Role</span>
                                </label>
                            </div>
                        </div>

                        @if(!$user->is_admin)
                            @php
                                $staffPermsOld = old('staff_permissions');
                                $staffPerms = is_array($staffPermsOld) ? $staffPermsOld : (array) ($user->staff_permissions ?? []);
                            @endphp
                            <div class="border-t border-gray-100 pt-4">
                                <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-2">Staff Module Permissions</label>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                    @php
                                        $availablePerms = [
                                            'tickets' => 'Support Tickets',
                                            'logs' => 'Manage Logs',
                                            'boosting' => 'Manage Boosting',
                                            'funding' => 'Funding Verification',
                                            'transactions' => 'System Audit Log',
                                            'orders' => 'Order Management',
                                            'promos' => 'Promo Codes',
                                            'settings' => 'API Settings',
                                            'users' => 'Manage Users',
                                        ];
                                    @endphp
                                    @foreach($availablePerms as $permKey => $permLabel)
                                        <label class="inline-flex items-center gap-2 text-xs font-bold text-gray-700">
                                            <input type="checkbox" name="staff_permissions[]" value="{{ $permKey }}" {{ in_array($permKey, $staffPerms, true) ? 'checked' : '' }} class="rounded border-gray-300 text-[#0d9488] focus:ring-0">
                                            <span>{{ $permLabel }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="pt-3">
                            <button type="submit" class="w-full bg-gray-900 hover:bg-gray-800 text-white px-4 py-3 rounded-xl font-black text-xs uppercase tracking-wider transition shadow-md">
                                Save Profile Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right: Wallet Adjustment & Notification Sender -->
        <div class="space-y-6">
            <!-- Wallet Adjustment Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden" x-data="{
                rate: @js((float) ($ngnPerUsd ?? 1500)),
                currentBalUsd: @js((float) ($user->walletBalance ?? 0)),
                direction: 'credit',
                amountUsd: '',
                amountNgn: '',
                onUsdChange(val) {
                    this.amountUsd = val;
                    if(val && !isNaN(val) && parseFloat(val) > 0) {
                        this.amountNgn = (parseFloat(val) * this.rate).toFixed(2);
                    } else {
                        this.amountNgn = '';
                    }
                },
                onNgnChange(val) {
                    this.amountNgn = val;
                    if(val && !isNaN(val) && parseFloat(val) > 0) {
                        this.amountUsd = (parseFloat(val) / this.rate).toFixed(2);
                    } else {
                        this.amountUsd = '';
                    }
                },
                get newBalUsd() {
                    const amt = parseFloat(this.amountUsd) || 0;
                    if(this.direction === 'credit') {
                        return this.currentBalUsd + amt;
                    } else {
                        return Math.max(0, this.currentBalUsd - amt);
                    }
                },
                get newBalNgn() {
                    return this.newBalUsd * this.rate;
                }
            }">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/80 flex items-center justify-between flex-wrap gap-2">
                    <h3 class="text-base font-black text-gray-900">Adjust Wallet Balance</h3>
                    <span class="text-xs font-bold text-[#0d9488] bg-teal-50 px-2.5 py-1 rounded-lg border border-teal-100">Rate: 1 USD = ₦{{ number_format((float) ($ngnPerUsd ?? 1500), 2) }}</span>
                </div>
                <div class="p-6">
                    <form action="/admin/users/{{ $user->id }}/wallet-adjust" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Direction</label>
                            <select name="direction" x-model="direction" class="w-full text-xs font-bold rounded-xl border border-gray-200 px-3 py-2.5 focus:border-[#0d9488] focus:ring-0" required>
                                <option value="credit">Credit (+ Add Funds)</option>
                                <option value="debit">Debit (- Subtract Funds)</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Amount ($ USD)</label>
                                <input type="number" step="0.01" min="0.01" name="amount_usd" :value="amountUsd" @input="onUsdChange($event.target.value)" class="w-full text-xs font-bold rounded-xl border border-gray-200 px-3 py-2.5 focus:border-[#0d9488] focus:ring-0" placeholder="10.00" required>
                            </div>
                            <div>
                                <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Amount (₦ NGN)</label>
                                <input type="number" step="0.01" min="1" :value="amountNgn" @input="onNgnChange($event.target.value)" class="w-full text-xs font-bold rounded-xl border border-gray-200 px-3 py-2.5 focus:border-[#0d9488] focus:ring-0" placeholder="15000.00">
                            </div>
                        </div>

                        <!-- Live Conversion & New Balance Preview -->
                        <template x-if="amountUsd && parseFloat(amountUsd) > 0">
                            <div class="p-3.5 rounded-xl border bg-teal-50/60 border-teal-100 text-xs space-y-1.5">
                                <div class="flex items-center justify-between font-bold text-gray-700">
                                    <span>Naira Equivalent:</span>
                                    <span class="font-extrabold text-[#0d9488] text-sm">≈ ₦<span x-text="(parseFloat(amountUsd) * rate).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span> NGN</span>
                                </div>
                                <div class="flex items-center justify-between font-bold text-gray-600 pt-1 border-t border-teal-200/50">
                                    <span>New Balance After Adjustment:</span>
                                    <span class="font-black text-gray-900">$<span x-text="newBalUsd.toFixed(2)"></span> (≈ ₦<span x-text="newBalNgn.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>)</span>
                                </div>
                            </div>
                        </template>

                        <div>
                            <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Reason / Reference Note</label>
                            <input name="note" class="w-full text-xs font-semibold rounded-xl border border-gray-200 px-3 py-2.5 focus:border-[#0d9488] focus:ring-0" placeholder="e.g. Manual refund / bonus credit">
                        </div>
                        <button type="submit" class="w-full bg-[#0d9488] hover:bg-[#0f766e] text-white px-4 py-3 rounded-xl font-black text-xs uppercase tracking-wider transition shadow-md flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                            <span>Apply Wallet Adjustment</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Send Notification Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/80 flex items-center justify-between">
                    <h3 class="text-base font-black text-gray-900">Send Direct Notification</h3>
                    <form action="/admin/users/{{ $user->id }}/notifications/mark-all-read" method="POST">
                        @csrf
                        <button type="submit" class="text-xs font-extrabold text-gray-600 hover:text-gray-900 bg-gray-100 px-2.5 py-1 rounded-lg">Mark all read</button>
                    </form>
                </div>
                <div class="p-6">
                    <form action="/admin/users/{{ $user->id }}/notify" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Title</label>
                            <input name="title" class="w-full text-xs font-semibold rounded-xl border border-gray-200 px-3 py-2.5 focus:border-[#0d9488] focus:ring-0" required placeholder="Notification title">
                        </div>
                        <div>
                            <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Message Body</label>
                            <textarea name="message" rows="3" class="w-full text-xs font-semibold rounded-xl border border-gray-200 p-3 focus:border-[#0d9488] focus:ring-0" required placeholder="Type notification text..."></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Action URL (Optional)</label>
                            <input name="action_url" class="w-full text-xs font-semibold rounded-xl border border-gray-200 px-3 py-2.5 focus:border-[#0d9488] focus:ring-0" placeholder="/funding">
                        </div>
                        <button type="submit" class="w-full bg-gray-900 hover:bg-gray-800 text-white px-4 py-3 rounded-xl font-black text-xs uppercase tracking-wider transition shadow-md">
                            Send Notification
                        </button>
                    </form>
                </div>
            </div>

            <!-- Reset Password Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/80">
                    <h3 class="text-base font-black text-gray-900">Reset Password</h3>
                </div>
                <div class="p-6">
                    <form action="/admin/users/{{ $user->id }}/password" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">New Password</label>
                            <input type="password" name="password" class="w-full text-xs font-bold rounded-xl border border-gray-200 px-3 py-2 focus:border-[#0d9488] focus:ring-0" required>
                        </div>
                        <div>
                            <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="w-full text-xs font-bold rounded-xl border border-gray-200 px-3 py-2 focus:border-[#0d9488] focus:ring-0" required>
                        </div>
                        <button type="submit" class="w-full bg-gray-900 hover:bg-gray-800 text-white px-4 py-2.5 rounded-xl font-black text-xs uppercase tracking-wider transition shadow-sm">
                            Update User Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@else
    <!-- Detailed History Tables for specific Tabs -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/80">
            <h3 class="text-base font-black text-gray-900 capitalize">{{ $tabs[$tab] ?? $tab }} Audit History</h3>
        </div>

        <div class="p-6">
            @if($tab === 'wallet')
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Date & Time</th>
                                <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Balance After</th>
                                <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Reference</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach(($walletTransactions ?? []) as $tx)
                                @php $dt = $tx->created_at ? \Illuminate\Support\Carbon::parse($tx->created_at) : null; @endphp
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-6 py-4 text-xs font-bold text-gray-600 whitespace-nowrap">{{ $dt ? $dt->format('M j, Y H:i') : '—' }}</td>
                                    <td class="px-6 py-4 text-xs font-extrabold text-gray-900 uppercase"><span class="px-2.5 py-1 rounded-lg bg-gray-100">{{ $tx->type }}</span></td>
                                    <td class="px-6 py-4 text-sm font-black {{ (float) $tx->amount_usd >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ (float) $tx->amount_usd >= 0 ? '+' : '' }}${{ number_format((float) $tx->amount_usd, 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm font-black text-gray-900">${{ number_format((float) ($tx->balance_after ?? 0), 2) }}</td>
                                    <td class="px-6 py-4 text-xs font-mono text-gray-600">{{ $tx->reference }}</td>
                                </tr>
                            @endforeach
                            @if(empty($walletTransactions) || $walletTransactions->isEmpty())
                                <tr><td colspan="5" class="px-6 py-10 text-center font-bold text-gray-500">No wallet transactions found.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ ($walletTransactions ?? null)?->onEachSide(1)->links() }}</div>

            @elseif($tab === 'funding')
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">USD Amount</th>
                                <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Gateway</th>
                                <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Reference</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach(($funding ?? []) as $tx)
                                @php $dt = $tx->created_at ? \Illuminate\Support\Carbon::parse($tx->created_at) : null; @endphp
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-6 py-4 text-xs font-bold text-gray-600 whitespace-nowrap">{{ $dt ? $dt->format('M j, Y H:i') : '—' }}</td>
                                    <td class="px-6 py-4 text-sm font-black text-[#0d9488]">${{ number_format((float) ($tx->amount_usd ?? 0), 2) }}</td>
                                    <td class="px-6 py-4 text-xs font-bold text-gray-800 uppercase">{{ $tx->payment_method ?? $tx->provider ?? '—' }}</td>
                                    <td class="px-6 py-4 text-xs font-extrabold uppercase"><span class="px-2.5 py-0.5 rounded-full text-[10px] bg-gray-100 text-gray-800">{{ $tx->status }}</span></td>
                                    <td class="px-6 py-4 text-xs font-mono text-gray-600">{{ $tx->reference }}</td>
                                </tr>
                            @endforeach
                            @if(empty($funding) || $funding->isEmpty())
                                <tr><td colspan="5" class="px-6 py-10 text-center font-bold text-gray-500">No deposit history found.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ ($funding ?? null)?->onEachSide(1)->links() }}</div>

            @elseif($tab === 'logs')
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Order ID</th>
                                <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Items Count</th>
                                <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Total Amount</th>
                                <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach(($logs ?? []) as $o)
                                @php $dt = $o->created_at ? \Illuminate\Support\Carbon::parse($o->created_at) : null; @endphp
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-6 py-4 text-xs font-black text-gray-900">#{{ $o->id }}</td>
                                    <td class="px-6 py-4 text-xs font-bold text-gray-600 whitespace-nowrap">{{ $dt ? $dt->format('M j, Y H:i') : '—' }}</td>
                                    <td class="px-6 py-4 text-xs font-bold text-gray-800">{{ (int) ($o->total_items ?? 0) }} item(s)</td>
                                    <td class="px-6 py-4 text-sm font-black text-gray-900">${{ number_format((float) ($o->total_amount ?? 0), 2) }}</td>
                                    <td class="px-6 py-4 text-xs font-extrabold uppercase"><span class="px-2.5 py-0.5 rounded-full text-[10px] bg-emerald-50 text-emerald-700">{{ $o->status }}</span></td>
                                </tr>
                            @endforeach
                            @if(empty($logs) || $logs->isEmpty())
                                <tr><td colspan="5" class="px-6 py-10 text-center font-bold text-gray-500">No log store purchases found.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ ($logs ?? null)?->onEachSide(1)->links() }}</div>

            @elseif($tab === 'sms')
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Order ID</th>
                                <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Phone Number</th>
                                <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Country</th>
                                <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Product</th>
                                <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Price</th>
                                <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach(($sms ?? []) as $o)
                                @php $dt = $o->created_at ? \Illuminate\Support\Carbon::parse($o->created_at) : null; @endphp
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-6 py-4 text-xs font-black text-gray-900">#{{ $o->id }}</td>
                                    <td class="px-6 py-4 text-xs font-bold text-gray-600 whitespace-nowrap">{{ $dt ? $dt->format('M j, Y H:i') : '—' }}</td>
                                    <td class="px-6 py-4 text-xs font-mono font-bold text-gray-900">{{ $o->phone_number }}</td>
                                    <td class="px-6 py-4 text-xs font-bold text-gray-700">{{ $o->country }}</td>
                                    <td class="px-6 py-4 text-xs font-bold text-gray-700">{{ $o->product }}</td>
                                    <td class="px-6 py-4 text-sm font-black text-gray-900">${{ number_format((float) ($o->price ?? 0), 2) }}</td>
                                    <td class="px-6 py-4 text-xs font-extrabold uppercase"><span class="px-2.5 py-0.5 rounded-full text-[10px] bg-gray-100 text-gray-800">{{ $o->status }}</span></td>
                                </tr>
                            @endforeach
                            @if(empty($sms) || $sms->isEmpty())
                                <tr><td colspan="7" class="px-6 py-10 text-center font-bold text-gray-500">No SMS orders found.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ ($sms ?? null)?->onEachSide(1)->links() }}</div>

            @elseif($tab === 'boost')
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Order ID</th>
                                <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Service Name</th>
                                <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Charge ($ USD)</th>
                                <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach(($boost ?? []) as $o)
                                @php $dt = $o->created_at ? \Illuminate\Support\Carbon::parse($o->created_at) : null; @endphp
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-6 py-4 text-xs font-black text-gray-900">#{{ $o->id }}</td>
                                    <td class="px-6 py-4 text-xs font-bold text-gray-600 whitespace-nowrap">{{ $dt ? $dt->format('M j, Y H:i') : '—' }}</td>
                                    <td class="px-6 py-4 text-xs font-extrabold text-gray-900">{{ $o->service_name ?? '—' }}</td>
                                    <td class="px-6 py-4 text-xs font-bold text-gray-700">{{ number_format((int) ($o->quantity ?? 0)) }}</td>
                                    <td class="px-6 py-4 text-sm font-black text-gray-900">${{ number_format((float) ($o->charge ?? 0), 2) }}</td>
                                    <td class="px-6 py-4 text-xs font-extrabold uppercase"><span class="px-2.5 py-0.5 rounded-full text-[10px] bg-teal-50 text-teal-700">{{ $o->status }}</span></td>
                                </tr>
                            @endforeach
                            @if(empty($boost) || $boost->isEmpty())
                                <tr><td colspan="6" class="px-6 py-10 text-center font-bold text-gray-500">No social boosting orders found.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ ($boost ?? null)?->onEachSide(1)->links() }}</div>

            @elseif($tab === 'tickets')
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Ticket ID</th>
                                <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Subject</th>
                                <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Last Updated</th>
                                <th class="px-6 py-3.5 text-right text-xs font-extrabold text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach(($tickets ?? []) as $t)
                                @php $dt = $t->updated_at ? \Illuminate\Support\Carbon::parse($t->updated_at) : null; @endphp
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-6 py-4 text-xs font-black text-gray-900">#{{ $t->id }}</td>
                                    <td class="px-6 py-4 text-xs font-extrabold text-gray-900">{{ $t->subject }}</td>
                                    <td class="px-6 py-4 text-xs font-extrabold uppercase"><span class="px-2.5 py-0.5 rounded-full text-[10px] bg-emerald-50 text-emerald-700">{{ $t->status }}</span></td>
                                    <td class="px-6 py-4 text-xs font-bold text-gray-600 whitespace-nowrap">{{ $dt ? $dt->format('M j, Y H:i') : '—' }}</td>
                                    <td class="px-6 py-4 text-right whitespace-nowrap">
                                        <a href="/admin/tickets/{{ $t->id }}" class="bg-gray-900 text-white px-3 py-1.5 rounded-xl text-xs font-extrabold hover:bg-gray-800 transition">View Ticket</a>
                                    </td>
                                </tr>
                            @endforeach
                            @if(empty($tickets) || $tickets->isEmpty())
                                <tr><td colspan="5" class="px-6 py-10 text-center font-bold text-gray-500">No support tickets created.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ ($tickets ?? null)?->onEachSide(1)->links() }}</div>

            @elseif($tab === 'notifications')
                <div class="space-y-3">
                    @foreach(($notifications ?? []) as $n)
                        @php
                            $data = is_array($n->data ?? null) ? $n->data : [];
                            $dt = $n->created_at ? \Illuminate\Support\Carbon::parse($n->created_at) : null;
                        @endphp
                        <div class="rounded-2xl border border-gray-100 bg-white p-4 hover:bg-gray-50/50 transition">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="font-extrabold text-xs text-gray-900">{{ $data['title'] ?? 'Notification' }}</h4>
                                        @if(!$n->read_at)
                                            <span class="text-[10px] font-black px-2 py-0.5 rounded-full bg-[#0d9488] text-white">NEW</span>
                                        @endif
                                    </div>
                                    <p class="text-xs font-semibold text-gray-600 mt-1">{{ $data['message'] ?? '' }}</p>
                                    <div class="text-[10px] font-bold text-gray-400 mt-2">{{ $dt ? $dt->format('M j, Y H:i') : '—' }}</div>
                                </div>
                                @if(!empty($data['action_url']))
                                    <a href="{{ $data['action_url'] }}" class="bg-[#0d9488] text-white px-3 py-1.5 rounded-xl text-xs font-extrabold transition">Link</a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                    @if(empty($notifications) || $notifications->isEmpty())
                        <div class="py-8 text-center font-bold text-gray-500">No notification log found.</div>
                    @endif
                </div>
                <div class="mt-4">{{ ($notifications ?? null)?->onEachSide(1)->links() }}</div>

            @elseif($tab === 'favorites')
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Key</th>
                                <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Label</th>
                                <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Created</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach(($favorites ?? []) as $f)
                                @php $dt = $f->created_at ? \Illuminate\Support\Carbon::parse($f->created_at) : null; @endphp
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-6 py-4 text-xs font-black text-gray-900 uppercase">{{ $f->type }}</td>
                                    <td class="px-6 py-4 text-xs font-bold text-gray-700">{{ $f->key }}</td>
                                    <td class="px-6 py-4 text-xs font-bold text-gray-700">{{ $f->label }}</td>
                                    <td class="px-6 py-4 text-xs font-bold text-gray-600 whitespace-nowrap">{{ $dt ? $dt->format('M j, Y H:i') : '—' }}</td>
                                </tr>
                            @endforeach
                            @if(empty($favorites) || $favorites->isEmpty())
                                <tr><td colspan="4" class="px-6 py-10 text-center font-bold text-gray-500">No favorited items.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ ($favorites ?? null)?->onEachSide(1)->links() }}</div>
            @endif
        </div>
    </div>
@endif
@endsection
