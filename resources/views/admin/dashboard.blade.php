@extends('layouts.admin')

@section('admin_content')
@push('header_title')
    Command Center Dashboard
@endpush

@php
    $series = collect($series ?? []);
    $maxRegs = max(1, (int) $series->max('registrations'));
    $maxSales = max(1, (float) $series->max('sales_usd'));
    $maxFunding = max(1, (float) $series->max('funding_usd'));
    $todaySpend = (float) ($todaySpentUsd ?? 0);
    $weekRegs = (int) $series->sum('registrations');
    $weekSpend = (float) $series->sum('sales_usd');
    $weekFunding = (float) $series->sum('funding_usd');
    $totalUsrVal = max(1, (int) ($totalUsers ?? 0));
    $activeRatio = round(((int) ($verifiedUsers ?? 0) / $totalUsrVal) * 100);

    $servicesTotal = $servicesTotal ?? 9;
    $servicesEnabled = $servicesEnabled ?? 9;
    $servicesDisabled = $servicesDisabled ?? 0;
    $allSystemsOnline = ($servicesDisabled === 0);
@endphp

<div class="space-y-8 pb-12">

    <!-- Top Executive Header & Command Bar -->
    <div class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-gray-900 to-slate-950 p-6 sm:p-8 rounded-3xl border border-gray-800 text-white shadow-xl">
        <!-- Ambient lighting glow -->
        <div class="absolute -right-20 -top-20 w-80 h-80 rounded-full bg-teal-500/10 blur-3xl pointer-events-none"></div>
        <div class="absolute -left-20 -bottom-20 w-80 h-80 rounded-full bg-indigo-500/10 blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div>
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-[#0d9488] to-teal-400 text-white flex items-center justify-center shadow-lg shadow-teal-900/40">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">May22SMS Executive Console</h1>
                            @if($allSystemsOnline)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                    All Systems Operational
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
                                    {{ $servicesDisabled }} {{ Str::plural('Service', $servicesDisabled) }} Paused
                                </span>
                            @endif
                        </div>
                        <p class="text-xs font-medium text-slate-300 mt-1 flex items-center gap-2">
                            <span>Welcome back, <strong class="text-white font-extrabold">{{ auth()->user()->name ?? 'Administrator' }}</strong></span>
                            <span>•</span>
                            <span class="text-teal-400 font-bold">{{ now()->format('l, F j, Y · h:i A') }}</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Fast Action Nav Pills -->
            <div class="flex flex-wrap items-center gap-2.5">
                <a href="/admin/services" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-teal-500/15 hover:bg-teal-500/25 border border-teal-500/30 text-teal-300 text-xs font-black transition active:scale-95 shadow-sm">
                    <svg class="w-4 h-4 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                    <span>Services: {{ $servicesEnabled }}/{{ $servicesTotal }} Online</span>
                </a>

                <a href="/admin/orders" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-gray-800/90 hover:bg-gray-700 text-white text-xs font-extrabold border border-gray-700/80 transition active:scale-95 shadow-sm">
                    <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M16 11V7a4 4 0 00-8 0v4M5 11h14l1 12H4L5 11z"></path></svg>
                    <span>Orders</span>
                    @if(($pendingOrdersCount ?? 0) > 0)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-600 text-white animate-pulse">{{ $pendingOrdersCount }}</span>
                    @endif
                </a>

                <a href="/admin/funding" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-amber-500/15 hover:bg-amber-500/25 text-amber-300 text-xs font-extrabold border border-amber-500/30 transition active:scale-95 shadow-sm">
                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>Funding</span>
                    @if(($pendingFundingCount ?? 0) > 0)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-400 text-gray-950">{{ $pendingFundingCount }}</span>
                    @endif
                </a>

                <a href="/admin/reports" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-rose-500/15 hover:bg-rose-500/25 text-rose-300 text-xs font-extrabold border border-rose-500/30 transition active:scale-95 shadow-sm">
                    <svg class="w-4 h-4 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <span>Reports</span>
                    @if(($pendingReportsCount ?? 0) > 0)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-500 text-white">{{ $pendingReportsCount }}</span>
                    @endif
                </a>

                <a href="/admin/tickets" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-indigo-500/15 hover:bg-indigo-500/25 text-indigo-300 text-xs font-extrabold border border-indigo-500/30 transition active:scale-95 shadow-sm">
                    <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>Tickets</span>
                    @if(($openTickets ?? 0) > 0)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-indigo-400 text-gray-950">{{ $openTickets }}</span>
                    @endif
                </a>
            </div>
        </div>
    </div>

    <!-- 4 Primary Financial & Operational Master Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
        
        <!-- Master KPI 1: Net Platform Profit -->
        <div class="bg-white rounded-3xl p-6 border border-gray-200/90 shadow-sm hover:shadow-lg transition-all duration-300 relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-emerald-50 text-emerald-800 text-[10px] font-black uppercase tracking-wider border border-emerald-200/80">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    Net Revenue Margin
                </span>
                <span class="inline-flex items-center justify-center w-11 h-11 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white shadow-md shadow-emerald-600/30 group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.3" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </span>
            </div>

            <div class="mt-4">
                <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">All-Time Net Profit</div>
                <div class="text-3xl sm:text-4xl font-black text-gray-900 tracking-tight mt-1">
                    ${{ number_format((float) ($totalAllTimeGain ?? 0), 2) }}
                </div>
            </div>

            <div class="mt-4 pt-3.5 border-t border-gray-100 flex items-center justify-between text-xs">
                <div class="flex items-center gap-1.5 text-emerald-600 font-black">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                    <span>+${{ number_format((float) ($todayTotalGain ?? 0), 2) }} today</span>
                </div>
                <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-black text-[10px]">
                    {{ $allTimeProfitMargin }}% margin
                </span>
            </div>

            <!-- Mini profit breakdown chips -->
            <div class="mt-3.5 grid grid-cols-3 gap-2 pt-2.5 border-t border-dashed border-gray-100 text-[10px] font-bold">
                <div class="bg-gray-50 rounded-xl p-2 text-center border border-gray-100">
                    <span class="block text-[9px] text-gray-400 uppercase font-extrabold">SMS</span>
                    <span class="font-black text-gray-900 text-xs">${{ number_format((float) ($totalSmsGain ?? 0), 0) }}</span>
                </div>
                <div class="bg-gray-50 rounded-xl p-2 text-center border border-gray-100">
                    <span class="block text-[9px] text-gray-400 uppercase font-extrabold">Logs</span>
                    <span class="font-black text-gray-900 text-xs">${{ number_format((float) ($totalLogsGain ?? 0), 0) }}</span>
                </div>
                <div class="bg-gray-50 rounded-xl p-2 text-center border border-gray-100">
                    <span class="block text-[9px] text-gray-400 uppercase font-extrabold">Boost</span>
                    <span class="font-black text-gray-900 text-xs">${{ number_format((float) ($totalBoostGain ?? 0), 0) }}</span>
                </div>
            </div>
        </div>

        <!-- Master KPI 2: Gross User Spending (GMV Volume) -->
        <div class="bg-white rounded-3xl p-6 border border-gray-200/90 shadow-sm hover:shadow-lg transition-all duration-300 relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-indigo-50 text-indigo-800 text-[10px] font-black uppercase tracking-wider border border-indigo-200/80">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                    Gross User Spend (GMV)
                </span>
                <span class="inline-flex items-center justify-center w-11 h-11 rounded-2xl bg-gradient-to-tr from-indigo-600 to-blue-500 text-white shadow-md shadow-indigo-600/30 group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M16 11V7a4 4 0 00-8 0v4M5 11h14l1 12H4L5 11z"></path></svg>
                </span>
            </div>

            <div class="mt-4">
                <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">All-Time User Spending</div>
                <div class="text-3xl sm:text-4xl font-black text-gray-900 tracking-tight mt-1">
                    ${{ number_format((float) ($allTimeSpentUsd ?? 0), 2) }}
                </div>
            </div>

            <div class="mt-4 pt-3.5 border-t border-gray-100 flex items-center justify-between text-xs">
                <div class="flex items-center gap-1.5 text-indigo-600 font-black">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span>+${{ number_format((float) ($todaySpentUsd ?? 0), 2) }} today</span>
                </div>
                <span class="px-2.5 py-0.5 rounded-full bg-indigo-100 text-indigo-800 font-black text-[10px]">
                    {{ number_format($allTimeOrdersCount ?? 0) }} orders
                </span>
            </div>

            <!-- Mini spend breakdown chips -->
            <div class="mt-3.5 grid grid-cols-3 gap-2 pt-2.5 border-t border-dashed border-gray-100 text-[10px] font-bold">
                <div class="bg-gray-50 rounded-xl p-2 text-center border border-gray-100">
                    <span class="block text-[9px] text-gray-400 uppercase font-extrabold">SMS Spend</span>
                    <span class="font-black text-gray-900 text-xs">${{ number_format((float) ($allTimeSmsUsd ?? 0), 0) }}</span>
                </div>
                <div class="bg-gray-50 rounded-xl p-2 text-center border border-gray-100">
                    <span class="block text-[9px] text-gray-400 uppercase font-extrabold">Logs Spend</span>
                    <span class="font-black text-gray-900 text-xs">${{ number_format((float) ($allTimeLogsUsd ?? 0), 0) }}</span>
                </div>
                <div class="bg-gray-50 rounded-xl p-2 text-center border border-gray-100">
                    <span class="block text-[9px] text-gray-400 uppercase font-extrabold">Boost Spend</span>
                    <span class="font-black text-gray-900 text-xs">${{ number_format((float) ($allTimeBoostUsd ?? 0), 0) }}</span>
                </div>
            </div>
        </div>

        <!-- Master KPI 3: Confirmed Wallet Deposits -->
        <div class="bg-white rounded-3xl p-6 border border-gray-200/90 shadow-sm hover:shadow-lg transition-all duration-300 relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-amber-50 text-amber-800 text-[10px] font-black uppercase tracking-wider border border-amber-200/80">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                    Wallet Funding
                </span>
                <span class="inline-flex items-center justify-center w-11 h-11 rounded-2xl bg-gradient-to-tr from-amber-500 to-yellow-500 text-white shadow-md shadow-amber-500/30 group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </span>
            </div>

            <div class="mt-4">
                <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">All-Time Confirmed Deposits</div>
                <div class="text-3xl sm:text-4xl font-black text-gray-900 tracking-tight mt-1">
                    ${{ number_format((float) ($allTimeFundingUsd ?? 0), 2) }}
                </div>
            </div>

            <div class="mt-4 pt-3.5 border-t border-gray-100 flex items-center justify-between text-xs">
                <div class="flex items-center gap-1.5 text-amber-600 font-black">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>+${{ number_format((float) ($todayFundingUsd ?? 0), 2) }} today</span>
                </div>
                <span class="text-gray-500 font-bold text-[11px]">
                    {{ number_format($allTimeFundingCount ?? 0) }} total deposits
                </span>
            </div>

            <div class="mt-3.5 pt-2.5 border-t border-dashed border-gray-100 flex items-center justify-between text-[11px]">
                <span class="text-gray-500 font-bold">Pending Approval:</span>
                @if(($pendingFundingCount ?? 0) > 0)
                    <a href="/admin/funding" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-rose-50 text-rose-700 font-black text-[10px] border border-rose-200/80 hover:bg-rose-100 transition">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-ping"></span>
                        {{ $pendingFundingCount }} awaiting action →
                    </a>
                @else
                    <span class="inline-flex items-center gap-1 text-emerald-600 font-black text-[10px]">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        All Deposits Cleared
                    </span>
                @endif
            </div>
        </div>

        <!-- Master KPI 4: Customer Base & Account Health -->
        <div class="bg-white rounded-3xl p-6 border border-gray-200/90 shadow-sm hover:shadow-lg transition-all duration-300 relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-teal-50 text-teal-800 text-[10px] font-black uppercase tracking-wider border border-teal-200/80">
                    <span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span>
                    Registered Customers
                </span>
                <span class="inline-flex items-center justify-center w-11 h-11 rounded-2xl bg-gradient-to-tr from-[#0d9488] to-cyan-500 text-white shadow-md shadow-teal-600/30 group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </span>
            </div>

            <div class="mt-4">
                <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Total Registered Users</div>
                <div class="text-3xl sm:text-4xl font-black text-gray-900 tracking-tight mt-1">
                    {{ number_format((int) ($totalUsers ?? 0)) }}
                </div>
            </div>

            <div class="mt-4 pt-3.5 border-t border-gray-100 flex items-center justify-between text-xs">
                <div class="flex items-center gap-1.5 text-teal-700 font-black">
                    <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    <span>+{{ number_format((int) ($todayUsers ?? 0)) }} registered today</span>
                </div>
                <span class="px-2.5 py-0.5 rounded-full bg-teal-100 text-teal-800 font-black text-[10px]">
                    {{ $activeRatio }}% Active
                </span>
            </div>

            <!-- User status mini breakdown -->
            <div class="mt-3.5 grid grid-cols-3 gap-2 pt-2.5 border-t border-dashed border-gray-100 text-[10px] font-bold">
                <div class="bg-emerald-50/70 border border-emerald-100 rounded-xl p-2 text-center text-emerald-900">
                    <span class="block text-[9px] uppercase font-bold text-emerald-600">Active</span>
                    <span class="font-black text-xs">{{ number_format($verifiedUsers ?? 0) }}</span>
                </div>
                <div class="bg-amber-50/70 border border-amber-100 rounded-xl p-2 text-center text-amber-900">
                    <span class="block text-[9px] uppercase font-bold text-amber-600">Pending</span>
                    <span class="font-black text-xs">{{ number_format($pendingUsers ?? 0) }}</span>
                </div>
                <div class="bg-rose-50/70 border border-rose-100 rounded-xl p-2 text-center text-rose-900">
                    <span class="block text-[9px] uppercase font-bold text-rose-600">Blocked</span>
                    <span class="font-black text-xs">{{ number_format($blockedUsers ?? 0) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Service Performance & Operational Health Breakdown -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-200/90 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-gray-100">
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-teal-500"></span>
                    <h2 class="text-lg sm:text-xl font-black text-gray-900 tracking-tight">Business Units &amp; Service Health</h2>
                </div>
                <p class="text-xs font-semibold text-gray-500 mt-0.5">Real-time spend volume, net profits, and availability controls per service vertical.</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-2">
                <a href="/admin/services" class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-gray-900 hover:bg-gray-800 text-white font-bold text-xs uppercase tracking-wider transition shadow-sm">
                    <svg class="w-3.5 h-3.5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                    <span>Manage Availability</span>
                </a>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-black">
                    <span>Total Day Profit: +${{ number_format((float) ($todayTotalGain ?? 0), 2) }}</span>
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mt-6">
            
            <!-- Service 1: Virtual SMS -->
            @php
                $smsAvailable = !isset($serviceStatuses['sms_verify']) || (is_array($serviceStatuses['sms_verify']) ? (bool) $serviceStatuses['sms_verify']['is_enabled'] : (bool) $serviceStatuses['sms_verify']);
            @endphp
            <div class="p-5 rounded-2xl bg-slate-50/70 border border-slate-200/80 hover:bg-white hover:border-teal-500/40 hover:shadow-md transition-all duration-200 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2.5">
                            <span class="w-10 h-10 rounded-xl bg-teal-50 text-[#0d9488] border border-teal-100 flex items-center justify-center shadow-xs">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            </span>
                            <div>
                                <h3 class="font-black text-gray-900 text-sm">Virtual SMS</h3>
                                <span class="text-[10px] font-bold text-gray-400 uppercase">Verification Numbers</span>
                            </div>
                        </div>
                        @if($smsAvailable)
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800">
                                Active
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-rose-100 text-rose-800">
                                Paused
                            </span>
                        @endif
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-3 pt-3 border-t border-slate-200/60">
                        <div>
                            <span class="block text-[10px] font-extrabold uppercase tracking-wider text-gray-400">Total Spend</span>
                            <span class="text-base font-black text-gray-900 block mt-0.5">${{ number_format((float) ($allTimeSmsUsd ?? 0), 2) }}</span>
                            <span class="text-[10px] font-bold text-gray-500 block mt-0.5">Today: ${{ number_format((float) ($todaySmsUsd ?? 0), 2) }}</span>
                        </div>
                        <div>
                            <span class="block text-[10px] font-extrabold uppercase tracking-wider text-gray-400">Orders</span>
                            <span class="text-base font-black text-gray-900 block mt-0.5">{{ number_format($allTimeSmsCount ?? 0) }}</span>
                            <span class="text-[10px] font-bold text-gray-500 block mt-0.5">Today: {{ number_format($todaySmsCount ?? 0) }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-200/60 flex items-center justify-between text-xs font-bold">
                    <span class="text-emerald-700 font-black">+${{ number_format((float) ($totalSmsGain ?? 0), 2) }} Net</span>
                    <a href="/admin/orders" class="text-[#0d9488] hover:underline font-extrabold text-[11px]">View Orders →</a>
                </div>
            </div>

            <!-- Service 2: Account Logs Store -->
            @php
                $logsAvailable = !isset($serviceStatuses['digital_logs']) || (is_array($serviceStatuses['digital_logs']) ? (bool) $serviceStatuses['digital_logs']['is_enabled'] : (bool) $serviceStatuses['digital_logs']);
            @endphp
            <div class="p-5 rounded-2xl bg-slate-50/70 border border-slate-200/80 hover:bg-white hover:border-amber-500/40 hover:shadow-md transition-all duration-200 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2.5">
                            <span class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 border border-amber-100 flex items-center justify-center shadow-xs">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </span>
                            <div>
                                <h3 class="font-black text-gray-900 text-sm">Logs Store</h3>
                                <span class="text-[10px] font-bold text-gray-400 uppercase">Account Inventory</span>
                            </div>
                        </div>
                        @if($logsAvailable)
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800">
                                Active
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-rose-100 text-rose-800">
                                Paused
                            </span>
                        @endif
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-3 pt-3 border-t border-slate-200/60">
                        <div>
                            <span class="block text-[10px] font-extrabold uppercase tracking-wider text-gray-400">Total Spend</span>
                            <span class="text-base font-black text-gray-900 block mt-0.5">${{ number_format((float) ($allTimeLogsUsd ?? 0), 2) }}</span>
                            <span class="text-[10px] font-bold text-gray-500 block mt-0.5">Today: ${{ number_format((float) ($todayLogsUsd ?? 0), 2) }}</span>
                        </div>
                        <div>
                            <span class="block text-[10px] font-extrabold uppercase tracking-wider text-gray-400">Items Sold</span>
                            <span class="text-base font-black text-gray-900 block mt-0.5">{{ number_format($allTimeLogsCount ?? 0) }}</span>
                            <span class="text-[10px] font-bold text-gray-500 block mt-0.5">{{ number_format($todayLogsItems ?? 0) }} today</span>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-200/60 flex items-center justify-between text-xs font-bold">
                    <span class="text-emerald-700 font-black">+${{ number_format((float) ($totalLogsGain ?? 0), 2) }} Net</span>
                    <a href="/admin/logs" class="text-amber-600 hover:underline font-extrabold text-[11px]">Manage Logs →</a>
                </div>
            </div>

            <!-- Service 3: Social Boosting -->
            @php
                $boostAvailable = !isset($serviceStatuses['boosting']) || (is_array($serviceStatuses['boosting']) ? (bool) $serviceStatuses['boosting']['is_enabled'] : (bool) $serviceStatuses['boosting']);
            @endphp
            <div class="p-5 rounded-2xl bg-slate-50/70 border border-slate-200/80 hover:bg-white hover:border-purple-500/40 hover:shadow-md transition-all duration-200 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2.5">
                            <span class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 border border-purple-100 flex items-center justify-center shadow-xs">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </span>
                            <div>
                                <h3 class="font-black text-gray-900 text-sm">Social Boost</h3>
                                <span class="text-[10px] font-bold text-gray-400 uppercase">SMM Engagement</span>
                            </div>
                        </div>
                        @if($boostAvailable)
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800">
                                Active
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-rose-100 text-rose-800">
                                Paused
                            </span>
                        @endif
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-3 pt-3 border-t border-slate-200/60">
                        <div>
                            <span class="block text-[10px] font-extrabold uppercase tracking-wider text-gray-400">Total Spend</span>
                            <span class="text-base font-black text-gray-900 block mt-0.5">${{ number_format((float) ($allTimeBoostUsd ?? 0), 2) }}</span>
                            <span class="text-[10px] font-bold text-gray-500 block mt-0.5">Today: ${{ number_format((float) ($todayBoostUsd ?? 0), 2) }}</span>
                        </div>
                        <div>
                            <span class="block text-[10px] font-extrabold uppercase tracking-wider text-gray-400">Orders</span>
                            <span class="text-base font-black text-gray-900 block mt-0.5">{{ number_format($allTimeBoostCount ?? 0) }}</span>
                            <span class="text-[10px] font-bold text-gray-500 block mt-0.5">Today: {{ number_format($todayBoostCount ?? 0) }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-200/60 flex items-center justify-between text-xs font-bold">
                    <span class="text-emerald-700 font-black">+${{ number_format((float) ($totalBoostGain ?? 0), 2) }} Net</span>
                    <a href="/admin/boosting" class="text-purple-600 hover:underline font-extrabold text-[11px]">Boost Console →</a>
                </div>
            </div>

            <!-- Service 4: Utilities & VTU (Airtime, Data, Power, Betting) -->
            @php
                $vtuAvailable = (!isset($serviceStatuses['airtime']) || (is_array($serviceStatuses['airtime']) ? (bool) $serviceStatuses['airtime']['is_enabled'] : (bool) $serviceStatuses['airtime']))
                             || (!isset($serviceStatuses['data']) || (is_array($serviceStatuses['data']) ? (bool) $serviceStatuses['data']['is_enabled'] : (bool) $serviceStatuses['data']));
            @endphp
            <div class="p-5 rounded-2xl bg-slate-50/70 border border-slate-200/80 hover:bg-white hover:border-sky-500/40 hover:shadow-md transition-all duration-200 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2.5">
                            <span class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 border border-sky-100 flex items-center justify-center shadow-xs">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>
                            </span>
                            <div>
                                <h3 class="font-black text-gray-900 text-sm">Utilities &amp; VTU</h3>
                                <span class="text-[10px] font-bold text-gray-400 uppercase">Airtime, Data &amp; Bills</span>
                            </div>
                        </div>
                        @if($vtuAvailable)
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800">
                                Active
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-rose-100 text-rose-800">
                                Paused
                            </span>
                        @endif
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-3 pt-3 border-t border-slate-200/60">
                        <div>
                            <span class="block text-[10px] font-extrabold uppercase tracking-wider text-gray-400">Total Volume</span>
                            <span class="text-base font-black text-gray-900 block mt-0.5">${{ number_format((float) ($allTimeUtilityUsd ?? 0), 2) }}</span>
                            <span class="text-[10px] font-bold text-gray-500 block mt-0.5">Today: ${{ number_format((float) ($todayUtilityUsd ?? 0), 2) }}</span>
                        </div>
                        <div>
                            <span class="block text-[10px] font-extrabold uppercase tracking-wider text-gray-400">Transactions</span>
                            <span class="text-base font-black text-gray-900 block mt-0.5">{{ number_format($allTimeUtilityCount ?? 0) }}</span>
                            <span class="text-[10px] font-bold text-gray-500 block mt-0.5">Today: {{ number_format($todayUtilityCount ?? 0) }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-200/60 flex items-center justify-between text-xs font-bold">
                    <span class="text-sky-700 font-black">VTU Provider Live</span>
                    <a href="/admin/utilities" class="text-sky-600 hover:underline font-extrabold text-[11px]">BigiSub Console →</a>
                </div>
            </div>

        </div>
    </div>

    <!-- Visual Analytics: 7-Day Performance Charts -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
        
        <!-- Chart 1: 7-Day User Growth -->
        <div class="bg-white rounded-3xl p-6 sm:p-7 border border-gray-200/80 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                <div>
                    <h3 class="font-black text-gray-900 text-base tracking-tight flex items-center gap-2">
                        <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        <span>7-Day Customer Acquisition Trend</span>
                    </h3>
                    <p class="text-xs font-semibold text-gray-500 mt-0.5">New account registrations per calendar day</p>
                </div>
                <span class="px-3 py-1 rounded-xl bg-teal-50 text-[#0d9488] font-black text-xs border border-teal-100">
                    {{ number_format($weekRegs) }} in 7 days
                </span>
            </div>

            <div class="pt-6">
                <div class="h-32 flex items-end gap-3 sm:gap-4">
                    @foreach($series as $row)
                        @php $h = (int) round(((int) $row['registrations'] / $maxRegs) * 100); @endphp
                        <div class="flex-1 flex flex-col items-center group cursor-pointer">
                            <div class="w-full bg-slate-100 rounded-xl h-24 flex items-end overflow-hidden p-0.5 group-hover:bg-teal-50/50 transition">
                                <div class="w-full bg-gradient-to-t from-[#0d9488] to-[#2dd4bf] rounded-lg group-hover:from-[#0f766e] group-hover:to-[#14b8a6] transition-all duration-300 shadow-xs" 
                                     style="height: {{ max(8, $h) }}%" 
                                     title="{{ (int) ($row['registrations'] ?? 0) }} registrations on {{ $row['date'] }}"></div>
                            </div>
                            <div class="mt-2 text-[11px] font-bold text-gray-400 group-hover:text-gray-900 transition">{{ \Illuminate\Support\Carbon::parse($row['date'])->format('D') }}</div>
                            <div class="mt-0.5 text-[11px] font-black text-gray-900">{{ number_format((int) ($row['registrations'] ?? 0)) }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between text-xs font-bold text-gray-600">
                <span>Registrations Today: <strong class="text-gray-900">+{{ number_format((int) ($todayUsers ?? 0)) }} users</strong></span>
                <span class="text-teal-700 font-extrabold flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    <span>Growth Track Active</span>
                </span>
            </div>
        </div>

        <!-- Chart 2: 7-Day Financial Flow -->
        <div class="bg-white rounded-3xl p-6 sm:p-7 border border-gray-200/80 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                <div>
                    <h3 class="font-black text-gray-900 text-base tracking-tight flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <span>7-Day Volume: Spend vs. Deposits</span>
                    </h3>
                    <p class="text-xs font-semibold text-gray-500 mt-0.5">Customer purchasing consumption vs confirmed funding inflows</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 text-[10px] font-extrabold text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-lg border border-indigo-100">
                        <span class="w-2 h-2 rounded-full bg-indigo-600"></span> Spend
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-[10px] font-extrabold text-amber-700 bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-100">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span> Deposits
                    </span>
                </div>
            </div>

            <div class="pt-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <div class="text-[10px] font-black uppercase tracking-wider text-gray-400 mb-2 flex items-center justify-between">
                            <span>User Order Spend</span>
                            <span class="text-indigo-600 font-extrabold">${{ number_format($weekSpend, 0) }} total</span>
                        </div>
                        <div class="h-28 flex items-end gap-2.5">
                            @foreach($series as $row)
                                @php $h = (int) round(((float) $row['sales_usd'] / $maxSales) * 100); @endphp
                                <div class="flex-1 flex flex-col items-center group cursor-pointer">
                                    <div class="w-full bg-slate-100 rounded-xl h-24 flex items-end overflow-hidden p-0.5 group-hover:bg-indigo-50/50 transition">
                                        <div class="w-full bg-gradient-to-t from-indigo-600 to-indigo-400 rounded-lg group-hover:from-indigo-700 group-hover:to-indigo-500 transition-all duration-300 shadow-xs" 
                                             style="height: {{ max(8, $h) }}%" 
                                             title="${{ number_format((float) ($row['sales_usd'] ?? 0), 2) }} on {{ $row['date'] }}"></div>
                                    </div>
                                    <div class="mt-1.5 text-[10px] font-bold text-gray-400 text-center">{{ \Illuminate\Support\Carbon::parse($row['date'])->format('D') }}</div>
                                    <div class="mt-0.5 text-[10px] font-black text-gray-900 text-center">${{ number_format((float) ($row['sales_usd'] ?? 0), 0) }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <div class="text-[10px] font-black uppercase tracking-wider text-gray-400 mb-2 flex items-center justify-between">
                            <span>Inflow Deposits</span>
                            <span class="text-amber-600 font-extrabold">${{ number_format($weekFunding, 0) }} total</span>
                        </div>
                        <div class="h-28 flex items-end gap-2.5">
                            @foreach($series as $row)
                                @php $h = (int) round(((float) $row['funding_usd'] / $maxFunding) * 100); @endphp
                                <div class="flex-1 flex flex-col items-center group cursor-pointer">
                                    <div class="w-full bg-slate-100 rounded-xl h-24 flex items-end overflow-hidden p-0.5 group-hover:bg-amber-50/50 transition">
                                        <div class="w-full bg-gradient-to-t from-amber-500 to-amber-300 rounded-lg group-hover:from-amber-600 group-hover:to-amber-400 transition-all duration-300 shadow-xs" 
                                             style="height: {{ max(8, $h) }}%" 
                                             title="${{ number_format((float) ($row['funding_usd'] ?? 0), 2) }} on {{ $row['date'] }}"></div>
                                    </div>
                                    <div class="mt-1.5 text-[10px] font-bold text-gray-400 text-center">{{ \Illuminate\Support\Carbon::parse($row['date'])->format('D') }}</div>
                                    <div class="mt-0.5 text-[10px] font-black text-gray-900 text-center">${{ number_format((float) ($row['funding_usd'] ?? 0), 0) }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between text-xs font-bold text-gray-600">
                <span>7-Day Outflow Volume: <strong class="text-indigo-700">${{ number_format($weekSpend, 2) }}</strong></span>
                <span>7-Day Deposit Inflow: <strong class="text-amber-700">${{ number_format($weekFunding, 2) }}</strong></span>
            </div>
        </div>

    </div>

    <!-- Operational Data Tables: Recent Users & Real-Time Event Activity -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
        
        <!-- Table 1: Latest Customers -->
        <div class="bg-white rounded-3xl border border-gray-200/90 shadow-sm overflow-hidden flex flex-col">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-slate-50/60">
                <div>
                    <h3 class="font-black text-gray-900 text-base tracking-tight flex items-center gap-2">
                        <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <span>Latest Registered Customers</span>
                    </h3>
                    <p class="text-xs font-semibold text-gray-500 mt-0.5">Recently registered accounts awaiting or verified</p>
                </div>
                @if(method_exists($latestUsers, 'total'))
                    <span class="text-xs font-extrabold text-gray-700 bg-white px-3 py-1 rounded-xl border border-gray-200 shadow-xs">
                        {{ number_format($latestUsers->total()) }} total
                    </span>
                @endif
            </div>

            <div class="overflow-x-auto flex-1">
                <table class="min-w-full text-xs">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/40 text-gray-400 font-black uppercase tracking-wider text-[10px]">
                            <th class="text-left px-6 py-3.5">User Profile</th>
                            <th class="text-left px-6 py-3.5">Status</th>
                            <th class="text-left px-6 py-3.5">Registered</th>
                            <th class="text-right px-6 py-3.5">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse(($latestUsers ?? []) as $u)
                            <tr class="hover:bg-slate-50/80 transition duration-150">
                                <td class="px-6 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-slate-900 to-slate-700 text-white flex items-center justify-center font-black text-xs shadow-xs border border-slate-700">
                                            {{ strtoupper(substr($u->name, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <span class="font-extrabold text-gray-900 block truncate">{{ $u->name }}</span>
                                            <span class="text-[11px] font-semibold text-gray-400 block truncate">{{ $u->email }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-3.5">
                                    @php
                                        $st = (string) ($u->account_status ?? '');
                                        $badge = $st === 'active' 
                                            ? 'bg-emerald-50 text-emerald-700 border-emerald-200' 
                                            : ($st === 'blocked' 
                                                ? 'bg-rose-50 text-rose-700 border-rose-200' 
                                                : 'bg-amber-50 text-amber-700 border-amber-200');
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full border text-[10px] font-black uppercase tracking-wider {{ $badge }}">
                                        {{ $st ?: 'active' }}
                                    </span>
                                </td>
                                <td class="px-6 py-3.5 font-bold text-gray-500">
                                    {{ $u->created_at ? \Illuminate\Support\Carbon::parse($u->created_at)->format('M j, Y') : '—' }}
                                </td>
                                <td class="px-6 py-3.5 text-right">
                                    <a href="/admin/users/{{ $u->id }}" class="inline-flex items-center px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-teal-50 text-slate-700 hover:text-teal-700 font-extrabold text-[11px] transition">
                                        Manage →
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-gray-400 font-bold">No users registered yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($latestUsers, 'links'))
                <div class="px-6 py-4 border-t border-gray-100 bg-slate-50/50">
                    {{ $latestUsers->onEachSide(1)->links() }}
                </div>
            @endif
        </div>

        <!-- Table 2: Real-Time Event & Transaction Feed -->
        <div class="bg-white rounded-3xl border border-gray-200/90 shadow-sm overflow-hidden flex flex-col">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-slate-50/60">
                <div>
                    <h3 class="font-black text-gray-900 text-base tracking-tight flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Live Transaction Stream</span>
                    </h3>
                    <p class="text-xs font-semibold text-gray-500 mt-0.5">Real-time orders, deposits, and service execution</p>
                </div>
                @if(method_exists($latestActivity, 'total'))
                    <span class="text-xs font-extrabold text-gray-700 bg-white px-3 py-1 rounded-xl border border-gray-200 shadow-xs">
                        {{ number_format($latestActivity->total()) }} events
                    </span>
                @endif
            </div>

            <div class="p-6 overflow-y-auto flex-1 max-h-[500px]">
                @if(($latestActivity ?? collect())->count() === 0)
                    <div class="text-center text-gray-400 py-16 font-bold">No recent transaction activity recorded.</div>
                @else
                    <div class="space-y-3.5">
                        @foreach($latestActivity as $a)
                            @php
                                $type = (string) ($a->type ?? '');
                                $status = (string) ($a->status ?? '');
                                $typeLabel = $type === 'funding' 
                                    ? 'Deposit' 
                                    : ($type === 'logs' 
                                        ? 'Logs' 
                                        : ($type === 'sms' 
                                            ? 'SMS' 
                                            : ($type === 'boost' 
                                                ? 'Boost' 
                                                : ($type === 'utility' ? 'Utility VTU' : strtoupper($type)))));
                                
                                $typeBadge = $type === 'funding' 
                                    ? 'bg-amber-50 text-amber-800 border-amber-200' 
                                    : ($type === 'logs' 
                                        ? 'bg-teal-50 text-teal-800 border-teal-200' 
                                        : ($type === 'sms' 
                                            ? 'bg-blue-50 text-blue-800 border-blue-200' 
                                            : ($type === 'utility' ? 'bg-sky-50 text-sky-800 border-sky-200' : 'bg-indigo-50 text-indigo-800 border-indigo-200')));
                                
                                $statusLower = strtolower($status);
                                $statusBadge = in_array($statusLower, ['approved', 'completed', 'success', 'successful', 'done', 'received', 'finished'], true) 
                                    ? 'bg-emerald-50 text-emerald-700 border-emerald-200' 
                                    : (in_array($statusLower, ['pending', 'processing', 'waiting', 'waiting_payment', 'initiated'], true) 
                                        ? 'bg-amber-50 text-amber-700 border-amber-200' 
                                        : 'bg-rose-50 text-rose-700 border-rose-200');
                                
                                $meta = (string) ($a->meta ?? '');
                                $dt = $a->created_at ? \Illuminate\Support\Carbon::parse($a->created_at) : null;
                            @endphp
                            <div class="p-4 rounded-2xl bg-slate-50/70 border border-slate-200/80 hover:bg-white hover:border-gray-300 hover:shadow-md transition duration-200">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-1.5">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full border text-[9px] font-black uppercase tracking-wider {{ $typeBadge }}">
                                                {{ $typeLabel }}
                                            </span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full border text-[9px] font-black uppercase tracking-wider {{ $statusBadge }}">
                                                {{ $status ?: 'COMPLETED' }}
                                            </span>
                                            @if($meta !== '')
                                                <span class="text-[10px] font-bold text-gray-500 truncate max-w-[180px] bg-white px-2 py-0.5 rounded-md border border-gray-200">{{ $meta }}</span>
                                            @endif
                                        </div>
                                        <div class="mt-2 flex flex-wrap items-center gap-1.5 text-xs">
                                            <span class="font-extrabold text-gray-900">{{ $a->user_name ?? 'Customer' }}</span>
                                            <span class="text-gray-400">({{ $a->user_email ?? '—' }})</span>
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <div class="text-sm font-black text-gray-900">
                                            ${{ number_format((float) ($a->amount_usd ?? 0), 2) }}
                                        </div>
                                        <div class="text-[10px] font-bold text-gray-400 mt-0.5">
                                            {{ $dt ? $dt->diffForHumans() : '—' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            @if(method_exists($latestActivity, 'links'))
                <div class="px-6 py-4 border-t border-gray-100 bg-slate-50/50">
                    {{ $latestActivity->onEachSide(1)->links() }}
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
