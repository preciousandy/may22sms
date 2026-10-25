@extends('layouts.app')

@section('content')
@php
    $adminUser = auth()->user();
    $staffPerms = (array) ($adminUser->staff_permissions ?? []);
    $can = function (string $perm) use ($adminUser, $staffPerms): bool {
        if (! $adminUser) {
            return false;
        }
        if ((bool) $adminUser->is_admin) {
            return true;
        }

        return in_array($perm, $staffPerms, true);
    };

    $isActive = function(string $path): bool {
        return request()->is($path) || request()->is($path . '/*');
    };

    // Pending notification counts
    $navCounts = [
        'orders' => \App\Models\LogOrder::where('status', 'pending')->count() 
                  + \App\Models\SmsOrder::whereIn('status', ['PENDING', 'WAITING', 'pending'])->count() 
                  + \App\Models\BoostOrder::whereIn('status', ['pending', 'processing'])->count(),
        'reports' => \App\Models\OrderReport::where('status', 'pending')->count(),
        'users' => \App\Models\User::where('account_status', 'pending')->count(),
        'boosting' => \App\Models\BoostOrder::whereIn('status', ['pending', 'processing'])->count(),
        'funding' => \App\Models\FundingTransaction::whereIn('status', ['pending', 'initiated', 'waiting_payment'])->count(),
        'tickets' => \App\Models\SupportTicket::where('status', 'open')->count(),
        'reviews' => \App\Models\Review::where('is_approved', false)->count(),
    ];
@endphp
<div x-data="{ 
    sidebarOpen: false,
    attendedCounts: JSON.parse(localStorage.getItem('admin_attended_menu') || '{}'),
    markAttended(key, count) {
        if (!key) return;
        this.attendedCounts[key] = Number(count);
        try {
            localStorage.setItem('admin_attended_menu', JSON.stringify(this.attendedCounts));
        } catch (e) {}
    },
    isAttended(key, count) {
        if (!count || Number(count) <= 0) return true;
        return (this.attendedCounts[key] !== undefined && Number(this.attendedCounts[key]) >= Number(count));
    },
    toggleAttended(key, count) {
        if (this.isAttended(key, count)) {
            delete this.attendedCounts[key];
        } else {
            this.attendedCounts[key] = Number(count);
        }
        try {
            localStorage.setItem('admin_attended_menu', JSON.stringify(this.attendedCounts));
        } catch (e) {}
    }
}" 
x-init="
    @if($isActive('admin/orders')) markAttended('orders', {{ $navCounts['orders'] }}); @endif
    @if($isActive('admin/reports')) markAttended('reports', {{ $navCounts['reports'] }}); @endif
    @if($isActive('admin/users')) markAttended('users', {{ $navCounts['users'] }}); @endif
    @if($isActive('admin/boosting')) markAttended('boosting', {{ $navCounts['boosting'] }}); @endif
    @if($isActive('admin/funding')) markAttended('funding', {{ $navCounts['funding'] }}); @endif
    @if($isActive('admin/tickets')) markAttended('tickets', {{ $navCounts['tickets'] }}); @endif
    @if($isActive('admin/reviews')) markAttended('reviews', {{ $navCounts['reviews'] }}); @endif
" class="flex h-screen bg-gray-100 font-sans">
    
    <!-- Mobile sidebar backdrop -->
    <div x-show="sidebarOpen" class="fixed inset-0 z-20 bg-black/60 backdrop-blur-sm lg:hidden" @click="sidebarOpen = false" style="display: none;"></div>

    <!-- Sidebar -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-30 w-64 bg-gray-900 border-r border-gray-800 shadow-2xl flex flex-col justify-between transform transition-transform duration-300 lg:translate-x-0 lg:static lg:inset-auto">
        <div class="flex-1 flex flex-col min-h-0">
            <div class="p-6 border-b border-gray-800 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-[#0d9488] to-[#2dd4bf] flex items-center justify-center font-black text-white text-sm shadow-md">
                        M
                    </div>
                    <div>
                        <h1 class="text-base font-black text-white leading-none">{{ $siteName ?? 'May22SMS' }}</h1>
                        <span class="text-[10px] font-extrabold text-[#facc15] uppercase tracking-widest">Admin Control</span>
                    </div>
                </div>
                <button @click="sidebarOpen = false" class="text-gray-400 hover:text-white focus:outline-none lg:hidden">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <nav class="flex-1 px-4 py-4 space-y-1.5 overflow-y-auto">
                <a href="/admin/dashboard" class="flex items-center px-4 py-3 rounded-xl font-bold text-xs uppercase tracking-wider transition {{ $isActive('admin/dashboard') ? 'bg-[#0d9488] text-white shadow-lg shadow-teal-900/30' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    Dashboard
                </a>

                @if($can('orders'))
                    <a href="/admin/orders" @click="markAttended('orders', {{ $navCounts['orders'] }})" class="flex items-center justify-between px-4 py-3 rounded-xl font-bold text-xs uppercase tracking-wider transition {{ $isActive('admin/orders') ? 'bg-[#0d9488] text-white shadow-lg shadow-teal-900/30' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 11h14l1 12H4L5 11z"></path></svg>
                            Orders & Refunds
                        </span>
                        @if(($navCounts['orders'] ?? 0) > 0)
                            <span 
                                @click.stop.prevent="toggleAttended('orders', {{ $navCounts['orders'] }})"
                                :title="isAttended('orders', {{ $navCounts['orders'] }}) ? 'Marked as attended ({{ $navCounts['orders'] }}). Click to mark as unread.' : '{{ $navCounts['orders'] }} pending items need attention! Click to mark attended.'"
                                class="inline-flex items-center cursor-pointer select-none transition-transform hover:scale-105">
                                <span x-show="!isAttended('orders', {{ $navCounts['orders'] }})" 
                                      class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-600 text-white shadow-sm shadow-rose-900/50 animate-pulse">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white animate-ping"></span>
                                    <span>{{ $navCounts['orders'] }}</span>
                                </span>
                                <span x-show="isAttended('orders', {{ $navCounts['orders'] }})" 
                                      style="display: none;"
                                      class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 shadow-sm">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    <span>{{ $navCounts['orders'] }}</span>
                                    <span class="text-[9px] font-bold uppercase tracking-wider opacity-85">Attended</span>
                                </span>
                            </span>
                        @endif
                    </a>

                    <a href="/admin/reports" @click="markAttended('reports', {{ $navCounts['reports'] }})" class="flex items-center justify-between px-4 py-3 rounded-xl font-bold text-xs uppercase tracking-wider transition {{ $isActive('admin/reports') ? 'bg-[#0d9488] text-white shadow-lg shadow-teal-900/30' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-3 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            Order Reports
                        </span>
                        @if(($navCounts['reports'] ?? 0) > 0)
                            <span 
                                @click.stop.prevent="toggleAttended('reports', {{ $navCounts['reports'] }})"
                                :title="isAttended('reports', {{ $navCounts['reports'] }}) ? 'Marked as attended ({{ $navCounts['reports'] }}). Click to mark as unread.' : '{{ $navCounts['reports'] }} pending reports need attention! Click to mark attended.'"
                                class="inline-flex items-center cursor-pointer select-none transition-transform hover:scale-105">
                                <span x-show="!isAttended('reports', {{ $navCounts['reports'] }})" 
                                      class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-600 text-white shadow-sm shadow-rose-900/50 animate-pulse">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white animate-ping"></span>
                                    <span>{{ $navCounts['reports'] }}</span>
                                </span>
                                <span x-show="isAttended('reports', {{ $navCounts['reports'] }})" 
                                      style="display: none;"
                                      class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 shadow-sm">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    <span>{{ $navCounts['reports'] }}</span>
                                    <span class="text-[9px] font-bold uppercase tracking-wider opacity-85">Attended</span>
                                </span>
                            </span>
                        @endif
                    </a>
                @endif

                @if($can('users'))
                    <a href="/admin/users" @click="markAttended('users', {{ $navCounts['users'] }})" class="flex items-center justify-between px-4 py-3 rounded-xl font-bold text-xs uppercase tracking-wider transition {{ $isActive('admin/users') ? 'bg-[#0d9488] text-white shadow-lg shadow-teal-900/30' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            Manage Users
                        </span>
                        @if(($navCounts['users'] ?? 0) > 0)
                            <span 
                                @click.stop.prevent="toggleAttended('users', {{ $navCounts['users'] }})"
                                :title="isAttended('users', {{ $navCounts['users'] }}) ? 'Marked as attended ({{ $navCounts['users'] }}). Click to mark as unread.' : '{{ $navCounts['users'] }} pending users need verification! Click to mark attended.'"
                                class="inline-flex items-center cursor-pointer select-none transition-transform hover:scale-105">
                                <span x-show="!isAttended('users', {{ $navCounts['users'] }})" 
                                      class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-600 text-white shadow-sm shadow-rose-900/50 animate-pulse">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white animate-ping"></span>
                                    <span>{{ $navCounts['users'] }}</span>
                                </span>
                                <span x-show="isAttended('users', {{ $navCounts['users'] }})" 
                                      style="display: none;"
                                      class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 shadow-sm">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    <span>{{ $navCounts['users'] }}</span>
                                    <span class="text-[9px] font-bold uppercase tracking-wider opacity-85">Attended</span>
                                </span>
                            </span>
                        @endif
                    </a>
                @endif

                @if($can('transactions'))
                    <a href="/admin/transactions" class="flex items-center px-4 py-3 rounded-xl font-bold text-xs uppercase tracking-wider transition {{ $isActive('admin/transactions') ? 'bg-[#0d9488] text-white shadow-lg shadow-teal-900/30' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"></path></svg>
                        Transactions
                    </a>
                @endif

                @if($can('logs'))
                    <a href="/admin/logs" class="flex items-center px-4 py-3 rounded-xl font-bold text-xs uppercase tracking-wider transition {{ request()->is('admin/logs') ? 'bg-[#0d9488] text-white shadow-lg shadow-teal-900/30' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Logs Store
                    </a>
                    <a href="/admin/log-videos" class="flex items-center px-4 py-2.5 pl-7 rounded-xl font-bold text-xs uppercase tracking-wider transition {{ request()->is('admin/log-videos*') ? 'bg-[#0d9488] text-white shadow-lg shadow-teal-900/30' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        <svg class="w-3.5 h-3.5 mr-2.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                        Category Videos & Links
                    </a>
                @endif

                @if($can('boosting'))
                    <a href="/admin/boosting" @click="markAttended('boosting', {{ $navCounts['boosting'] }})" class="flex items-center justify-between px-4 py-3 rounded-xl font-bold text-xs uppercase tracking-wider transition {{ $isActive('admin/boosting') ? 'bg-[#0d9488] text-white shadow-lg shadow-teal-900/30' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            Boosting
                        </span>
                        @if(($navCounts['boosting'] ?? 0) > 0)
                            <span 
                                @click.stop.prevent="toggleAttended('boosting', {{ $navCounts['boosting'] }})"
                                :title="isAttended('boosting', {{ $navCounts['boosting'] }}) ? 'Marked as attended ({{ $navCounts['boosting'] }}). Click to mark as unread.' : '{{ $navCounts['boosting'] }} pending boosts need processing! Click to mark attended.'"
                                class="inline-flex items-center cursor-pointer select-none transition-transform hover:scale-105">
                                <span x-show="!isAttended('boosting', {{ $navCounts['boosting'] }})" 
                                      class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-600 text-white shadow-sm shadow-rose-900/50 animate-pulse">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white animate-ping"></span>
                                    <span>{{ $navCounts['boosting'] }}</span>
                                </span>
                                <span x-show="isAttended('boosting', {{ $navCounts['boosting'] }})" 
                                      style="display: none;"
                                      class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 shadow-sm">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    <span>{{ $navCounts['boosting'] }}</span>
                                    <span class="text-[9px] font-bold uppercase tracking-wider opacity-85">Attended</span>
                                </span>
                            </span>
                        @endif
                    </a>
                @endif

                @if($can('funding'))
                    <a href="/admin/funding" @click="markAttended('funding', {{ $navCounts['funding'] }})" class="flex items-center justify-between px-4 py-3 rounded-xl font-bold text-xs uppercase tracking-wider transition {{ $isActive('admin/funding') ? 'bg-[#0d9488] text-white shadow-lg shadow-teal-900/30' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Funding
                        </span>
                        @if(($navCounts['funding'] ?? 0) > 0)
                            <span 
                                @click.stop.prevent="toggleAttended('funding', {{ $navCounts['funding'] }})"
                                :title="isAttended('funding', {{ $navCounts['funding'] }}) ? 'Marked as attended ({{ $navCounts['funding'] }}). Click to mark as unread.' : '{{ $navCounts['funding'] }} pending deposits need approval! Click to mark attended.'"
                                class="inline-flex items-center cursor-pointer select-none transition-transform hover:scale-105">
                                <span x-show="!isAttended('funding', {{ $navCounts['funding'] }})" 
                                      class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-600 text-white shadow-sm shadow-rose-900/50 animate-pulse">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white animate-ping"></span>
                                    <span>{{ $navCounts['funding'] }}</span>
                                </span>
                                <span x-show="isAttended('funding', {{ $navCounts['funding'] }})" 
                                      style="display: none;"
                                      class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 shadow-sm">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    <span>{{ $navCounts['funding'] }}</span>
                                    <span class="text-[9px] font-bold uppercase tracking-wider opacity-85">Attended</span>
                                </span>
                            </span>
                        @endif
                    </a>

                    <a href="/admin/charges" class="flex items-center px-4 py-3 rounded-xl font-bold text-xs uppercase tracking-wider transition {{ $isActive('admin/charges') ? 'bg-[#0d9488] text-white shadow-lg shadow-teal-900/30' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        <svg class="w-4 h-4 mr-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"></path></svg>
                        Funding Charges
                    </a>

                    <!-- Save Small Small Admin Section -->
                    <div class="space-y-1">
                        <a href="/admin/savings" class="flex items-center justify-between px-4 py-3 rounded-xl font-bold text-xs uppercase tracking-wider transition {{ ($isActive('admin/savings') && !request()->is('admin/savings/categories*') && !request()->is('admin/savings/reconciliation*')) ? 'bg-[#0d9488] text-white shadow-lg shadow-teal-900/30' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-3 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Save Small Small
                            </span>
                            <span class="px-1.5 py-0.5 rounded-md text-[9px] font-black uppercase bg-[#facc15] text-gray-950">16.9%</span>
                        </a>

                        @if(request()->is('admin/savings*'))
                            <div class="pl-9 pr-2 py-1.5 space-y-1 bg-gray-950/60 rounded-xl border border-gray-800/80 my-1">
                                <a href="/admin/savings" class="block py-1 text-[11px] font-bold transition {{ (request()->is('admin/savings') && !request()->is('admin/savings/categories*') && !request()->is('admin/savings/reconciliation*')) ? 'text-teal-400 font-black' : 'text-gray-400 hover:text-white' }}">
                                    • Customer Plans
                                </a>
                                <a href="/admin/savings/categories" class="block py-1 text-[11px] font-bold transition {{ request()->is('admin/savings/categories*') ? 'text-teal-400 font-black' : 'text-gray-400 hover:text-white' }}">
                                    • Categories &amp; Interest
                                </a>
                                <a href="/admin/savings/reconciliation" class="block py-1 text-[11px] font-bold transition {{ request()->is('admin/savings/reconciliation*') ? 'text-teal-400 font-black' : 'text-gray-400 hover:text-white' }}">
                                    • Reserves Reconciliation
                                </a>
                            </div>
                        @endif
                    </div>
                @endif

                @if($can('tickets'))
                    <a href="/admin/tickets" @click="markAttended('tickets', {{ $navCounts['tickets'] }})" class="flex items-center justify-between px-4 py-3 rounded-xl font-bold text-xs uppercase tracking-wider transition {{ $isActive('admin/tickets') ? 'bg-[#0d9488] text-white shadow-lg shadow-teal-900/30' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Support Tickets
                        </span>
                        @if(($navCounts['tickets'] ?? 0) > 0)
                            <span 
                                @click.stop.prevent="toggleAttended('tickets', {{ $navCounts['tickets'] }})"
                                :title="isAttended('tickets', {{ $navCounts['tickets'] }}) ? 'Marked as attended ({{ $navCounts['tickets'] }}). Click to mark as unread.' : '{{ $navCounts['tickets'] }} open support tickets! Click to mark attended.'"
                                class="inline-flex items-center cursor-pointer select-none transition-transform hover:scale-105">
                                <span x-show="!isAttended('tickets', {{ $navCounts['tickets'] }})" 
                                      class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-600 text-white shadow-sm shadow-rose-900/50 animate-pulse">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white animate-ping"></span>
                                    <span>{{ $navCounts['tickets'] }}</span>
                                </span>
                                <span x-show="isAttended('tickets', {{ $navCounts['tickets'] }})" 
                                      style="display: none;"
                                      class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 shadow-sm">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    <span>{{ $navCounts['tickets'] }}</span>
                                    <span class="text-[9px] font-bold uppercase tracking-wider opacity-85">Attended</span>
                                </span>
                            </span>
                        @endif
                    </a>
                @endif

                <a href="/admin/reviews" @click="markAttended('reviews', {{ $navCounts['reviews'] }})" class="flex items-center justify-between px-4 py-3 rounded-xl font-bold text-xs uppercase tracking-wider transition {{ $isActive('admin/reviews') ? 'bg-[#0d9488] text-white shadow-lg shadow-teal-900/30' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                    <span class="flex items-center">
                        <svg class="w-4 h-4 mr-3 text-amber-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                        User Reviews
                    </span>
                    @if(($navCounts['reviews'] ?? 0) > 0)
                        <span 
                            @click.stop.prevent="toggleAttended('reviews', {{ $navCounts['reviews'] }})"
                            :title="isAttended('reviews', {{ $navCounts['reviews'] }}) ? 'Marked as attended ({{ $navCounts['reviews'] }}). Click to mark as unread.' : '{{ $navCounts['reviews'] }} unapproved reviews! Click to mark attended.'"
                            class="inline-flex items-center cursor-pointer select-none transition-transform hover:scale-105">
                            <span x-show="!isAttended('reviews', {{ $navCounts['reviews'] }})" 
                                  class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-600 text-white shadow-sm shadow-rose-900/50 animate-pulse">
                                <span class="w-1.5 h-1.5 rounded-full bg-white animate-ping"></span>
                                <span>{{ $navCounts['reviews'] }}</span>
                            </span>
                            <span x-show="isAttended('reviews', {{ $navCounts['reviews'] }})" 
                                  style="display: none;"
                                  class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 shadow-sm">
                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                <span>{{ $navCounts['reviews'] }}</span>
                                <span class="text-[9px] font-bold uppercase tracking-wider opacity-85">Attended</span>
                            </span>
                        </span>
                    @endif
                </a>

                @if($can('settings'))
                    <a href="/admin/settings" class="flex items-center px-4 py-3 rounded-xl font-bold text-xs uppercase tracking-wider transition {{ $isActive('admin/settings') ? 'bg-[#0d9488] text-white shadow-lg shadow-teal-900/30' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path></svg>
                        API Settings
                    </a>

                    <a href="/admin/services" class="flex items-center px-4 py-3 rounded-xl font-bold text-xs uppercase tracking-wider transition {{ $isActive('admin/services') ? 'bg-[#0d9488] text-white shadow-lg shadow-teal-900/30' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        <svg class="w-4 h-4 mr-3 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                        Service Controls
                    </a>
                @endif

                <a href="/admin/mail" class="flex items-center px-4 py-3 rounded-xl font-bold text-xs uppercase tracking-wider transition {{ $isActive('admin/mail') ? 'bg-[#0d9488] text-white shadow-lg shadow-teal-900/30' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    Mail Sender
                </a>

                @if($can('promos'))
                    <a href="/admin/promo-codes" class="flex items-center px-4 py-3 rounded-xl font-bold text-xs uppercase tracking-wider transition {{ $isActive('admin/promo-codes') ? 'bg-[#0d9488] text-white shadow-lg shadow-teal-900/30' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                        Promo Codes
                    </a>
                @endif

                <a href="/admin/football-matches" class="flex items-center px-4 py-3 rounded-xl font-bold text-xs uppercase tracking-wider transition {{ $isActive('admin/football-matches') ? 'bg-[#0d9488] text-white shadow-lg shadow-teal-900/30' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                    <svg class="w-4 h-4 mr-3 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3a9 9 0 016.36 15.36M12 3a9 9 0 00-6.36 15.36M12 3v18M3 12h18"/></svg>
                    Live Football
                </a>

                <a href="/admin/utilities" class="flex items-center px-4 py-3 rounded-xl font-bold text-xs uppercase tracking-wider transition {{ $isActive('admin/utilities') ? 'bg-[#0d9488] text-white shadow-lg shadow-teal-900/30' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                    <svg class="w-4 h-4 mr-3 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Utilities &amp; BigiSub
                </a>
            </nav>
        </div>

        <div class="p-4 border-t border-gray-800">
            <a href="/dashboard" class="flex items-center justify-center gap-2 w-full py-2.5 px-4 rounded-xl bg-gray-800 hover:bg-gray-700 text-gray-300 text-xs font-bold transition">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to User Portal
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden relative">
        <!-- Header -->
        <header class="flex justify-between items-center py-4 px-6 bg-white border-b border-gray-200 sticky top-0 z-10 shadow-sm">
            <div class="flex items-center">
                <button @click="sidebarOpen = true" class="text-gray-500 hover:text-gray-800 focus:outline-none lg:hidden mr-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                <h2 class="text-lg sm:text-xl font-black text-gray-900 truncate">@stack('header_title', 'Admin Portal')</h2>
            </div>

            <div class="flex items-center space-x-4">
                <a href="/admin/orders" @click="markAttended('orders', {{ $navCounts['orders'] }})" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-teal-50 text-[#0d9488] hover:bg-teal-100 font-extrabold text-xs transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 11h14l1 12H4L5 11z"></path></svg>
                    Orders & Refunds
                    @if(($navCounts['orders'] ?? 0) > 0)
                        <span x-show="!isAttended('orders', {{ $navCounts['orders'] }})" class="ml-1 px-1.5 py-0.5 rounded-full text-[9px] font-black bg-rose-600 text-white animate-pulse">
                            {{ $navCounts['orders'] }}
                        </span>
                        <span x-show="isAttended('orders', {{ $navCounts['orders'] }})" style="display: none;" class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded-full text-[9px] font-black bg-emerald-600 text-white">
                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </span>
                    @endif
                </a>
                <div class="flex items-center gap-2 pl-3 border-l border-gray-200">
                    <div class="w-8 h-8 rounded-full bg-gray-900 text-white font-black text-xs flex items-center justify-center">
                        {{ strtoupper(substr($adminUser->name ?? 'A', 0, 1)) }}
                    </div>
                    <a href="/logout" class="text-gray-600 hover:text-rose-600 font-extrabold text-xs transition">
                        Logout
                    </a>
                </div>
            </div>
        </header>

        <!-- Main section -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-4 sm:p-8">
            <div class="max-w-7xl mx-auto">
                @yield('admin_content')
            </div>
        </main>
    </div>
</div>
@endsection
