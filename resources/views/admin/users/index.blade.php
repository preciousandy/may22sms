@extends('layouts.admin')

@section('admin_content')
@push('header_title')
    Manage User Accounts
@endpush

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

<!-- Overview Metrics Bar -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <div class="text-xs font-extrabold text-gray-500 uppercase tracking-wider">Total Registered</div>
            <div class="text-2xl font-black text-gray-900 mt-1">{{ number_format((int) ($counts['total'] ?? 0)) }}</div>
            <div class="text-xs font-bold text-gray-500 mt-0.5">User accounts</div>
        </div>
        <div class="p-3 bg-teal-50 text-[#0d9488] rounded-xl">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <div class="text-xs font-extrabold text-gray-500 uppercase tracking-wider">Active Accounts</div>
            <div class="text-2xl font-black text-emerald-600 mt-1">{{ number_format((int) ($counts['active'] ?? 0)) }}</div>
            <div class="text-xs font-bold text-emerald-700 mt-0.5">Verified & active</div>
        </div>
        <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <div class="text-xs font-extrabold text-gray-500 uppercase tracking-wider">Pending Accounts</div>
            <div class="text-2xl font-black text-amber-600 mt-1">{{ number_format((int) ($counts['pending'] ?? 0)) }}</div>
            <div class="text-xs font-bold text-amber-700 mt-0.5">Unverified</div>
        </div>
        <div class="p-3 bg-amber-50 text-amber-600 rounded-xl">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <div class="text-xs font-extrabold text-gray-500 uppercase tracking-wider">Blocked Accounts</div>
            <div class="text-2xl font-black text-rose-600 mt-1">{{ number_format((int) ($counts['blocked'] ?? 0)) }}</div>
            <div class="text-xs font-bold text-rose-700 mt-0.5">Suspended access</div>
        </div>
        <div class="p-3 bg-rose-50 text-rose-600 rounded-xl">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
        </div>
    </div>
</div>

<div class="mb-6 flex flex-wrap gap-2 items-center justify-between">
    <div class="flex gap-2">
        <a href="/admin/users/export/csv?{{ http_build_query(request()->query()) }}" class="inline-flex items-center gap-1.5 rounded-xl bg-gray-900 px-4 py-2.5 text-xs font-extrabold text-white hover:bg-gray-800 transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Export CSV
        </a>
        <a href="/admin/users/export/print?{{ http_build_query(request()->query()) }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-xl bg-gray-100 px-4 py-2.5 text-xs font-extrabold text-gray-800 hover:bg-gray-200 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Export PDF (Print)
        </a>
    </div>
</div>

<details class="mb-6 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <summary class="px-6 py-4 border-b border-gray-100 bg-gray-50/80 cursor-pointer select-none font-black text-sm text-gray-900 flex items-center justify-between">
        <span class="inline-flex items-center gap-2">
            <svg class="w-4 h-4 text-[#0d9488]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
            Create New User Account
        </span>
        <span class="text-xs font-bold text-[#0d9488]">+ Add User</span>
    </summary>
    <div class="p-6">
        <form action="/admin/users/create" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @csrf
            <div>
                <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Full Name</label>
                <input name="name" value="{{ old('name') }}" class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm font-semibold focus:border-[#0d9488] focus:ring-0" required>
            </div>
            <div>
                <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Email Address</label>
                <input name="email" type="email" value="{{ old('email') }}" class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm font-semibold focus:border-[#0d9488] focus:ring-0" required>
            </div>
            <div>
                <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Country</label>
                <input name="country" value="{{ old('country') }}" class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm font-semibold focus:border-[#0d9488] focus:ring-0">
            </div>
            <div>
                <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Phone Number</label>
                <input name="phone_number" value="{{ old('phone_number') }}" class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm font-semibold focus:border-[#0d9488] focus:ring-0">
            </div>
            <div>
                <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Account Status</label>
                <select name="account_status" class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm font-bold focus:border-[#0d9488] focus:ring-0" required>
                    @php $st = old('account_status', 'active'); @endphp
                    <option value="active" {{ $st === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="pending" {{ $st === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="blocked" {{ $st === 'blocked' ? 'selected' : '' }}>Blocked</option>
                </select>
            </div>
            <div class="flex gap-4 items-center pt-5">
                <label class="inline-flex items-center gap-2 text-xs font-bold text-gray-700">
                    <input type="checkbox" name="is_support" value="1" {{ old('is_support') ? 'checked' : '' }} class="rounded border-gray-300 text-[#0d9488] focus:ring-0">
                    <span>Support Role</span>
                </label>
                <label class="inline-flex items-center gap-2 text-xs font-bold text-gray-700">
                    <input type="checkbox" name="is_editor" value="1" {{ old('is_editor') ? 'checked' : '' }} class="rounded border-gray-300 text-[#0d9488] focus:ring-0">
                    <span>Editor Role</span>
                </label>
            </div>
            <div>
                <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Password</label>
                <input name="password" type="password" class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm font-semibold focus:border-[#0d9488] focus:ring-0" required>
            </div>
            <div>
                <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Confirm Password</label>
                <input name="password_confirmation" type="password" class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm font-semibold focus:border-[#0d9488] focus:ring-0" required>
            </div>
            <div class="md:col-span-2 pt-2">
                <button type="submit" class="w-full bg-[#0d9488] hover:bg-[#0f766e] text-white px-4 py-3 rounded-xl font-black text-xs uppercase tracking-wider transition shadow-md">Create User Account</button>
            </div>
        </form>
    </div>
</details>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden" x-data="bulkUsersManager()">
    <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/80 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h3 class="text-lg font-black text-gray-900">Registered Users</h3>
        <form method="GET" action="/admin/users" class="flex flex-col sm:flex-row gap-2 sm:items-center">
            <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Search name/email/phone/ID..." class="w-full sm:w-64 px-3 py-2 text-xs font-semibold rounded-xl border border-gray-200 focus:border-[#0d9488] focus:ring-0">
            <select name="status" class="w-full sm:w-40 px-3 py-2 text-xs font-bold rounded-xl border border-gray-200 focus:border-[#0d9488] focus:ring-0">
                <option value="all" {{ ($status ?? 'all') === 'all' ? 'selected' : '' }}>All Statuses</option>
                <option value="active" {{ ($status ?? 'all') === 'active' ? 'selected' : '' }}>Active Only</option>
                <option value="pending" {{ ($status ?? 'all') === 'pending' ? 'selected' : '' }}>Pending Only</option>
                <option value="blocked" {{ ($status ?? 'all') === 'blocked' ? 'selected' : '' }}>Blocked Only</option>
            </select>
            <button type="submit" class="bg-gray-900 text-white px-4 py-2 rounded-xl text-xs font-extrabold hover:bg-gray-800 transition">Search</button>
        </form>
    </div>

    <!-- Bulk Actions Floating Strip -->
    <div x-show="selectedIds.length > 0" x-cloak x-transition class="bg-rose-50 border-b border-rose-200 px-6 py-3.5 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-rose-600 text-white text-xs font-black">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            </span>
            <span class="text-xs font-black text-rose-900">
                <span x-text="selectedIds.length"></span> user(s) selected
            </span>
            <span class="text-xs text-rose-600 hidden sm:inline">• Choose an action below</span>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" @click="selectedIds = []" class="px-3 py-1.5 text-xs font-bold text-gray-700 bg-white border border-gray-200 hover:bg-gray-50 rounded-xl transition cursor-pointer">
                Deselect All
            </button>
            <button type="button" @click="showBulkModal = true" class="px-4 py-1.5 text-xs font-black text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-sm transition flex items-center gap-1.5 cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                <span>Bulk Delete (<span x-text="selectedIds.length"></span>)</span>
            </button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="w-12 px-4 py-3.5 text-center">
                        <input type="checkbox" @change="toggleSelectAll($event)" :checked="isAllSelected" class="h-4 w-4 rounded border-gray-300 text-rose-600 focus:ring-rose-500 cursor-pointer" title="Select all on this page">
                    </th>
                    <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Roles</th>
                    <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Wallet Balance</th>
                    <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Joined</th>
                    <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Last Activity</th>
                    <th class="px-6 py-3.5 text-right text-xs font-extrabold text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @foreach($users as $u)
                    @php
                        $st = (string) ($u->account_status ?? '');
                        $badge = $st === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : ($st === 'blocked' ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-amber-50 text-amber-700 border-amber-200');
                        $isSelf = ((int) auth()->id() === (int) $u->id);
                        $isPrimaryAdmin = ((int) $u->id === 1 && (int) auth()->id() !== 1);
                    @endphp
                    <tr class="hover:bg-gray-50/50 transition" :class="{ 'bg-rose-50/40': selectedIds.includes('{{ $u->id }}') || selectedIds.includes({{ $u->id }}) }">
                        <td class="w-12 px-4 py-4 text-center">
                            @if(!$isSelf && !$isPrimaryAdmin)
                                <input type="checkbox" value="{{ $u->id }}" x-model="selectedIds" class="h-4 w-4 rounded border-gray-300 text-rose-600 focus:ring-rose-500 cursor-pointer">
                            @else
                                <span class="text-gray-300 text-xs" title="Protected account">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-gray-900 text-white font-black text-xs flex items-center justify-center shrink-0">
                                    {{ strtoupper(substr($u->name ?? 'U', 0, 1)) }}
                                </div>
                                <div>
                                    <a href="/admin/users/{{ $u->id }}" class="font-extrabold text-gray-900 hover:text-[#0d9488] transition">{{ $u->name }}</a>
                                    <div class="text-xs font-semibold text-gray-500">{{ $u->email }}</div>
                                    <div class="text-[11px] font-bold text-gray-400">ID: #{{ $u->id }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <div class="flex flex-wrap gap-1">
                                @if($u->is_admin)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black bg-gray-900 text-white">ADMIN</span>
                                @endif
                                @if($u->is_support)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black bg-blue-50 text-blue-700 border border-blue-200">SUPPORT</span>
                                @endif
                                @if($u->is_editor)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black bg-purple-50 text-purple-700 border border-purple-200">EDITOR</span>
                                @endif
                                @if(!$u->is_admin && !$u->is_support && !$u->is_editor)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black bg-gray-100 text-gray-600">USER</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full border text-xs font-black {{ $badge }}">{{ strtoupper($st ?: 'UNKNOWN') }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm font-black text-gray-900 whitespace-nowrap">
                            ${{ number_format((float) ($u->walletBalance ?? 0), 2) }}
                        </td>
                        <td class="px-6 py-4 text-xs font-bold text-gray-600 whitespace-nowrap">
                            {{ $u->created_at ? \Illuminate\Support\Carbon::parse($u->created_at)->format('M j, Y') : '—' }}
                        </td>
                        <td class="px-6 py-4 text-xs font-bold text-gray-600 whitespace-nowrap">
                            <div>{{ $u->last_login_at ? \Illuminate\Support\Carbon::parse($u->last_login_at)->format('M j, Y H:i') : '—' }}</div>
                            <div class="text-[10px] text-gray-400 font-medium">{{ $u->last_login_ip ?? '—' }}</div>
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="/admin/users/{{ $u->id }}" class="inline-flex items-center gap-1 bg-[#0d9488] hover:bg-[#0f766e] text-white px-3 py-1.5 rounded-xl text-xs font-extrabold transition shadow-sm">
                                    Profile
                                </a>
                                @if(!$isSelf && !$isPrimaryAdmin)
                                    <button type="button" @click="confirmSingleDelete({{ $u->id }}, '{{ addslashes($u->name) }}')" class="inline-flex items-center justify-center h-8 w-8 text-rose-500 hover:text-white hover:bg-rose-600 rounded-xl transition border border-rose-200 hover:border-rose-600 cursor-pointer" title="Delete User">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach

                @if($users->isEmpty())
                    <tr>
                        <td colspan="8" class="px-6 py-10 text-center text-gray-500 font-bold">No users found matching your query.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <div class="px-6 py-5 border-t border-gray-100 bg-gray-50">
        {{ $users->onEachSide(1)->links() }}
    </div>

    <!-- Bulk Delete Confirmation Modal -->
    <div x-show="showBulkModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showBulkModal = false"></div>
        <div class="relative bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-gray-100 z-10 overflow-hidden">
            <div class="flex items-center gap-3 text-rose-600 mb-4">
                <div class="h-11 w-11 rounded-2xl bg-rose-50 border border-rose-200 flex items-center justify-center text-xl shrink-0">
                    <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <div>
                    <h3 class="text-lg font-black text-gray-900">Confirm Bulk Delete</h3>
                    <p class="text-xs text-rose-600 font-bold">Irreversible operation</p>
                </div>
            </div>

            <p class="text-sm text-gray-600 leading-relaxed mb-4">
                Are you sure you want to permanently delete the <strong class="text-gray-900" x-text="selectedIds.length"></strong> selected user account(s)?
            </p>

            <div class="bg-rose-50/70 border border-rose-100 rounded-2xl p-3.5 mb-6 text-xs text-rose-800 leading-normal">
                <strong>Warning:</strong> All orders, SMS records, wallet transactions, tickets, and associated logs for these users will be permanently erased.
            </div>

            <form action="/admin/users/bulk-delete" method="POST">
                @csrf
                <template x-for="id in selectedIds" :key="id">
                    <input type="hidden" name="user_ids[]" :value="id">
                </template>

                <div class="flex items-center justify-end gap-3">
                    <button type="button" @click="showBulkModal = false" class="px-5 py-2.5 text-xs font-bold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2.5 text-xs font-black text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-md transition flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        <span>Yes, Delete Selected</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Single Delete Confirmation Modal -->
    <div x-show="showSingleModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showSingleModal = false"></div>
        <div class="relative bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-gray-100 z-10 overflow-hidden">
            <div class="flex items-center gap-3 text-rose-600 mb-4">
                <div class="h-11 w-11 rounded-2xl bg-rose-50 border border-rose-200 flex items-center justify-center text-xl shrink-0">
                    <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </div>
                <div>
                    <h3 class="text-lg font-black text-gray-900">Delete User Account</h3>
                    <p class="text-xs text-rose-600 font-bold" x-text="singleDeleteName"></p>
                </div>
            </div>

            <p class="text-sm text-gray-600 leading-relaxed mb-4">
                Are you sure you want to permanently delete user <strong class="text-gray-900" x-text="singleDeleteName"></strong> (ID #<span x-text="singleDeleteId"></span>)?
            </p>

            <div class="bg-rose-50/70 border border-rose-100 rounded-2xl p-3.5 mb-6 text-xs text-rose-800 leading-normal">
                <strong>Warning:</strong> All orders, SMS records, wallet transactions, tickets, and associated logs for this user will be permanently deleted.
            </div>

            <form :action="'/admin/users/' + singleDeleteId + '/delete'" method="POST">
                @csrf
                <div class="flex items-center justify-end gap-3">
                    <button type="button" @click="showSingleModal = false" class="px-5 py-2.5 text-xs font-bold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2.5 text-xs font-black text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-md transition flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        <span>Yes, Delete User</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function bulkUsersManager() {
        return {
            selectedIds: [],
            pageIds: @json($users->pluck('id')),
            showBulkModal: false,
            showSingleModal: false,
            singleDeleteId: null,
            singleDeleteName: '',

            get isAllSelected() {
                const currentAdminId = {{ (int) auth()->id() }};
                const eligiblePageIds = this.pageIds.filter(id => id !== currentAdminId && ({{ (int) auth()->id() }} === 1 || id !== 1));
                if (eligiblePageIds.length === 0) return false;
                return eligiblePageIds.every(id => this.selectedIds.includes(String(id)) || this.selectedIds.includes(Number(id)));
            },

            toggleSelectAll(event) {
                const currentAdminId = {{ (int) auth()->id() }};
                const eligiblePageIds = this.pageIds.filter(id => id !== currentAdminId && ({{ (int) auth()->id() }} === 1 || id !== 1));

                if (event.target.checked) {
                    const currentSet = new Set(this.selectedIds.map(String));
                    eligiblePageIds.forEach(id => currentSet.add(String(id)));
                    this.selectedIds = Array.from(currentSet);
                } else {
                    const removeSet = new Set(eligiblePageIds.map(String));
                    this.selectedIds = this.selectedIds.filter(id => !removeSet.has(String(id)));
                }
            },

            confirmSingleDelete(id, name) {
                this.singleDeleteId = id;
                this.singleDeleteName = name;
                this.showSingleModal = true;
            }
        };
    }
</script>
@endsection
