@extends('layouts.admin')

@section('admin_content')
@push('header_title')
    Executive Dashboard
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
@endphp

<div class="space-y-8 pb-12">
    <!-- Header & Quick Navigation Bar -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 bg-white p-6 sm:p-7 rounded-3xl border border-gray-200/80 shadow-sm">
        <div>
            <div class="flex items-center gap-2.5">
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-2xl bg-teal-50 text-[#0d9488] shadow-inner">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                </span>
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-gray-900 tracking-tight flex items-center gap-2">
                        <span>Command Center</span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Live System
                        </span>
                    </h1>
                    <p class="text-xs font-semibold text-gray-500 mt-0.5">
                        Real-time revenue, spending volume, margins, and operational performance · <span class="font-extrabold text-teal-700">{{ now()->format('l, F j, Y') }}</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Quick Executive Actions -->
        <div class="flex flex-wrap items-center gap-2">
            <a href="/admin/orders" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-gradient-to-r from-[#0d9488] to-[#0f766e] hover:from-[#0f766e] hover:to-[#115e59] text-white text-xs font-extrabold shadow-sm transition hover:scale-[1.02]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M16 11V7a4 4 0 00-8 0v4M5 11h14l1 12H4L5 11z"></path></svg>
                <span>Orders</span>
                @if(($pendingOrdersCount ?? 0) > 0)
                    <span class="px-1.5 py-0.5 rounded-full text-[9px] font-black bg-rose-500 text-white animate-pulse">{{ $pendingOrdersCount }}</span>
                @endif
            </a>

            <a href="/admin/funding" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-gray-900 hover:bg-gray-800 text-white text-xs font-extrabold shadow-sm transition">
                <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>Funding</span>
                @if(($pendingFundingCount ?? 0) > 0)
                    <span class="px-1.5 py-0.5 rounded-full text-[9px] font-black bg-amber-500 text-gray-950">{{ $pendingFundingCount }}</span>
                @endif
            </a>

            <a href="/admin/reports" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-gray-50 hover:bg-gray-100 text-gray-700 border border-gray-200 text-xs font-extrabold transition">
                <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <span>Reports</span>
                @if(($pendingReportsCount ?? 0) > 0)
                    <span class="px-1.5 py-0.5 rounded-full text-[9px] font-black bg-rose-600 text-white">{{ $pendingReportsCount }}</span>
                @endif
            </a>

            <a href="/admin/tickets" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-gray-50 hover:bg-gray-100 text-gray-700 border border-gray-200 text-xs font-extrabold transition">
                <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>Tickets</span>
                @if(($openTickets ?? 0) > 0)
                    <span class="px-1.5 py-0.5 rounded-full text-[9px] font-black bg-indigo-100 text-indigo-700">{{ $openTickets }}</span>
                @endif
            </a>

            <a href="/admin/settings" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-gray-50 hover:bg-gray-100 text-gray-700 border border-gray-200 text-xs font-extrabold transition" title="API Settings">
                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path></svg>
            </a>
        </div>
    </div>

    <!-- 4 Main Financial & Platform KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
        
        <!-- KPI 1: Total Profit (Admin Net Margin) -->
        <div class="bg-white rounded-3xl p-6 border border-gray-200/80 shadow-sm hover:shadow-md transition duration-200 relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-emerald-50 text-emerald-700 text-[10px] font-black uppercase tracking-wider border border-emerald-100">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    Net Profit
                </span>
                <span class="inline-flex items-center justify-center w-10 h-10 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-400 text-white shadow-md shadow-emerald-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.3" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </span>
            </div>

            <div class="mt-4">
                <div class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">All-Time Net Profit</div>
                <div class="text-3xl sm:text-4xl font-black text-gray-900 tracking-tight mt-1">
                    ${{ number_format((float) ($totalAllTimeGain ?? 0), 2) }}
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs">
                <div class="flex items-center gap-1 text-emerald-600 font-extrabold">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                    <span>+${{ number_format((float) ($todayTotalGain ?? 0), 2) }} today</span>
                </div>
                <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-black text-[10px]">
                    {{ $allTimeProfitMargin }}% margin
                </span>
            </div>

            <!-- Mini profit breakdown chips -->
            <div class="mt-3 grid grid-cols-3 gap-1.5 pt-2 border-t border-dashed border-gray-100 text-[10px] font-bold text-gray-500">
                <div class="bg-gray-50/80 rounded-xl p-1.5 text-center">
                    <span class="block text-[9px] text-gray-400 font-bold uppercase">SMS</span>
                    <span class="font-extrabold text-gray-900">${{ number_format((float) ($totalSmsGain ?? 0), 0) }}</span>
                </div>
                <div class="bg-gray-50/80 rounded-xl p-1.5 text-center">
                    <span class="block text-[9px] text-gray-400 font-bold uppercase">Logs</span>
                    <span class="font-extrabold text-gray-900">${{ number_format((float) ($totalLogsGain ?? 0), 0) }}</span>
                </div>
                <div class="bg-gray-50/80 rounded-xl p-1.5 text-center">
                    <span class="block text-[9px] text-gray-400 font-bold uppercase">Boost</span>
                    <span class="font-extrabold text-gray-900">${{ number_format((float) ($totalBoostGain ?? 0), 0) }}</span>
                </div>
            </div>
        </div>

        <!-- KPI 2: Total Spent (User Purchasing Volume) -->
        <div class="bg-white rounded-3xl p-6 border border-gray-200/80 shadow-sm hover:shadow-md transition duration-200 relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-indigo-50 text-indigo-700 text-[10px] font-black uppercase tracking-wider border border-indigo-100">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                    User Spending
                </span>
                <span class="inline-flex items-center justify-center w-10 h-10 rounded-2xl bg-gradient-to-tr from-indigo-600 to-blue-500 text-white shadow-md shadow-indigo-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M16 11V7a4 4 0 00-8 0v4M5 11h14l1 12H4L5 11z"></path></svg>
                </span>
            </div>

            <div class="mt-4">
                <div class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">All-Time Total Spent</div>
                <div class="text-3xl sm:text-4xl font-black text-gray-900 tracking-tight mt-1">
                    ${{ number_format((float) ($allTimeSpentUsd ?? 0), 2) }}
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs">
                <div class="flex items-center gap-1 text-indigo-600 font-extrabold">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>+${{ number_format((float) ($todaySpentUsd ?? 0), 2) }} today</span>
                </div>
                <span class="px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 font-black text-[10px]">
                    {{ number_format($allTimeOrdersCount ?? 0) }} orders
                </span>
            </div>

            <!-- Mini spend breakdown chips -->
            <div class="mt-3 grid grid-cols-3 gap-1.5 pt-2 border-t border-dashed border-gray-100 text-[10px] font-bold text-gray-500">
                <div class="bg-gray-50/80 rounded-xl p-1.5 text-center">
                    <span class="block text-[9px] text-gray-400 font-bold uppercase">SMS</span>
                    <span class="font-extrabold text-gray-900">${{ number_format((float) ($allTimeSmsUsd ?? 0), 0) }}</span>
                </div>
                <div class="bg-gray-50/80 rounded-xl p-1.5 text-center">
                    <span class="block text-[9px] text-gray-400 font-bold uppercase">Logs</span>
                    <span class="font-extrabold text-gray-900">${{ number_format((float) ($allTimeLogsUsd ?? 0), 0) }}</span>
                </div>
                <div class="bg-gray-50/80 rounded-xl p-1.5 text-center">
                    <span class="block text-[9px] text-gray-400 font-bold uppercase">Boost</span>
                    <span class="font-extrabold text-gray-900">${{ number_format((float) ($allTimeBoostUsd ?? 0), 0) }}</span>
                </div>
            </div>
        </div>

        <!-- KPI 3: Funding Confirmed (Deposits) -->
        <div class="bg-white rounded-3xl p-6 border border-gray-200/80 shadow-sm hover:shadow-md transition duration-200 relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-amber-50 text-amber-700 text-[10px] font-black uppercase tracking-wider border border-amber-100">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                    Wallet Deposits
                </span>
                <span class="inline-flex items-center justify-center w-10 h-10 rounded-2xl bg-gradient-to-tr from-amber-500 to-yellow-400 text-white shadow-md shadow-amber-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </span>
            </div>

            <div class="mt-4">
                <div class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">All-Time Funding Confirmed</div>
                <div class="text-3xl sm:text-4xl font-black text-gray-900 tracking-tight mt-1">
                    ${{ number_format((float) ($allTimeFundingUsd ?? 0), 2) }}
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs">
                <div class="flex items-center gap-1 text-amber-600 font-extrabold">
                    <span>+${{ number_format((float) ($todayFundingUsd ?? 0), 2) }} today</span>
                </div>
                <span class="text-gray-400 font-semibold text-[11px]">
                    {{ number_format($allTimeFundingCount ?? 0) }} total deposits
                </span>
            </div>

            <div class="mt-3 pt-2 border-t border-dashed border-gray-100 flex items-center justify-between text-[11px]">
                <span class="text-gray-500 font-bold">Pending Approval:</span>
                @if(($pendingFundingCount ?? 0) > 0)
                    <a href="/admin/funding" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-rose-50 text-rose-700 font-black text-[10px] hover:bg-rose-100 transition">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-ping"></span>
                        {{ $pendingFundingCount }} awaiting action →
                    </a>
                @else
                    <span class="text-emerald-600 font-black text-[10px]">All clear</span>
                @endif
            </div>
        </div>

        <!-- KPI 4: Total Users & Platform Health -->
        <div class="bg-white rounded-3xl p-6 border border-gray-200/80 shadow-sm hover:shadow-md transition duration-200 relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-teal-50 text-teal-700 text-[10px] font-black uppercase tracking-wider border border-teal-100">
                    <span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span>
                    Community
                </span>
                <span class="inline-flex items-center justify-center w-10 h-10 rounded-2xl bg-gradient-to-tr from-[#0d9488] to-[#2dd4bf] text-white shadow-md shadow-teal-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </span>
            </div>

            <div class="mt-4">
                <div class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Total Registered Users</div>
                <div class="text-3xl sm:text-4xl font-black text-gray-900 tracking-tight mt-1">
                    {{ number_format((int) ($totalUsers ?? 0)) }}
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs">
                <div class="flex items-center gap-1 text-teal-600 font-extrabold">
                    <span>+{{ number_format((int) ($todayUsers ?? 0)) }} registered today</span>
                </div>
                <span class="px-2 py-0.5 rounded-full bg-teal-50 text-teal-700 font-black text-[10px]">
                    {{ $activeRatio }}% Active
                </span>
            </div>

            <!-- User status mini breakdown -->
            <div class="mt-3 grid grid-cols-3 gap-1.5 pt-2 border-t border-dashed border-gray-100 text-[10px] font-bold">
                <div class="bg-emerald-50/60 border border-emerald-100/60 rounded-xl p-1.5 text-center text-emerald-800">
                    <span class="block text-[9px] uppercase font-bold text-emerald-600">Active</span>
                    <span class="font-black">{{ number_format($verifiedUsers ?? 0) }}</span>
                </div>
                <div class="bg-amber-50/60 border border-amber-100/60 rounded-xl p-1.5 text-center text-amber-800">
                    <span class="block text-[9px] uppercase font-bold text-amber-600">Pending</span>
                    <span class="font-black">{{ number_format($pendingUsers ?? 0) }}</span>
                </div>
                <div class="bg-rose-50/60 border border-rose-100/60 rounded-xl p-1.5 text-center text-rose-800">
                    <span class="block text-[9px] uppercase font-bold text-rose-600">Blocked</span>
                    <span class="font-black">{{ number_format($blockedUsers ?? 0) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Service Performance & Margin Breakdown Cards -->
    <div class="bg-white rounded-3xl p-6 sm:p-7 border border-gray-200/80 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 pb-6 border-b border-gray-100">
            <div>
                <h2 class="text-lg font-black text-gray-900 tracking-tight">Revenue & Profit Breakdown by Service</h2>
                <p class="text-xs font-semibold text-gray-500 mt-0.5">Real-time breakdown of user spend volume, order counts, and net administrative profits.</p>
            </div>
            <div class="flex items-center gap-2 text-xs font-extrabold text-gray-500">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-gray-50 border border-gray-200">
                    <span>Today's Spend: <strong class="text-gray-900">${{ number_format($todaySpentUsd, 2) }}</strong></span>
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700">
                    <span>Today's Profit: <strong>+${{ number_format((float) ($todayTotalGain ?? 0), 2) }}</strong></span>
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
            
            <!-- Service 1: Virtual SMS -->
            <div class="p-5 rounded-2xl bg-gray-50/70 border border-gray-200/70 hover:bg-gray-50 hover:border-gray-300 transition duration-200 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-sm font-black shadow-xs">
                                📱
                            </span>
                            <div>
                                <h3 class="font-black text-gray-900 text-sm">Virtual SMS Service</h3>
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">SMS Activation & Numbers</span>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800">
                            +${{ number_format((float) ($totalSmsGain ?? 0), 2) }} Profit
                        </span>
                    </div>

                    <div class="mt-5 grid grid-cols-2 gap-4">
                        <div>
                            <span class="block text-[10px] font-extrabold uppercase tracking-wider text-gray-400">Total User Spend</span>
                            <span class="text-xl font-black text-gray-900 mt-0.5 block">${{ number_format((float) ($allTimeSmsUsd ?? 0), 2) }}</span>
                            <span class="text-[10px] font-bold text-gray-500 mt-1 block">Today: ${{ number_format((float) ($todaySmsUsd ?? 0), 2) }}</span>
                        </div>
                        <div>
                            <span class="block text-[10px] font-extrabold uppercase tracking-wider text-gray-400">Total Orders</span>
                            <span class="text-xl font-black text-gray-900 mt-0.5 block">{{ number_format($allTimeSmsCount ?? 0) }}</span>
                            <span class="text-[10px] font-bold text-gray-500 mt-1 block">Today: {{ number_format($todaySmsCount ?? 0) }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-5 pt-3 border-t border-gray-200/60 flex items-center justify-between text-xs font-bold text-gray-600">
                    <span>Today Profit: <strong class="text-emerald-600">+${{ number_format((float) ($todaySmsGain ?? 0), 2) }}</strong></span>
                    <a href="/admin/orders?tab=sms" class="text-[#0d9488] hover:underline font-extrabold text-[11px]">View Orders →</a>
                </div>
            </div>

            <!-- Service 2: Store Logs -->
            <div class="p-5 rounded-2xl bg-gray-50/70 border border-gray-200/70 hover:bg-gray-50 hover:border-gray-300 transition duration-200 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-8 rounded-xl bg-teal-100 text-[#0d9488] flex items-center justify-center text-sm font-black shadow-xs">
                                🛒
                            </span>
                            <div>
                                <h3 class="font-black text-gray-900 text-sm">Account Logs Store</h3>
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Inventory & Social Accounts</span>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800">
                            +${{ number_format((float) ($totalLogsGain ?? 0), 2) }} Profit
                        </span>
                    </div>

                    <div class="mt-5 grid grid-cols-2 gap-4">
                        <div>
                            <span class="block text-[10px] font-extrabold uppercase tracking-wider text-gray-400">Total User Spend</span>
                            <span class="text-xl font-black text-gray-900 mt-0.5 block">${{ number_format((float) ($allTimeLogsUsd ?? 0), 2) }}</span>
                            <span class="text-[10px] font-bold text-gray-500 mt-1 block">Today: ${{ number_format((float) ($todayLogsUsd ?? 0), 2) }}</span>
                        </div>
                        <div>
                            <span class="block text-[10px] font-extrabold uppercase tracking-wider text-gray-400">Completed Orders</span>
                            <span class="text-xl font-black text-gray-900 mt-0.5 block">{{ number_format($allTimeLogsCount ?? 0) }}</span>
                            <span class="text-[10px] font-bold text-gray-500 mt-1 block">{{ number_format($todayLogsItems ?? 0) }} items today</span>
                        </div>
                    </div>
                </div>

                <div class="mt-5 pt-3 border-t border-gray-200/60 flex items-center justify-between text-xs font-bold text-gray-600">
                    <span>Today Profit: <strong class="text-emerald-600">+${{ number_format((float) ($todayLogsGain ?? 0), 2) }}</strong></span>
                    <a href="/admin/orders?tab=logs" class="text-[#0d9488] hover:underline font-extrabold text-[11px]">Manage Logs →</a>
                </div>
            </div>

            <!-- Service 3: Social Boosting -->
            <div class="p-5 rounded-2xl bg-gray-50/70 border border-gray-200/70 hover:bg-gray-50 hover:border-gray-300 transition duration-200 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-8 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center text-sm font-black shadow-xs">
                                🚀
                            </span>
                            <div>
                                <h3 class="font-black text-gray-900 text-sm">Social Boosting</h3>
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">SMM Panels & Engagement</span>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800">
                            +${{ number_format((float) ($totalBoostGain ?? 0), 2) }} Profit
                        </span>
                    </div>

                    <div class="mt-5 grid grid-cols-2 gap-4">
                        <div>
                            <span class="block text-[10px] font-extrabold uppercase tracking-wider text-gray-400">Total User Spend</span>
                            <span class="text-xl font-black text-gray-900 mt-0.5 block">${{ number_format((float) ($allTimeBoostUsd ?? 0), 2) }}</span>
                            <span class="text-[10px] font-bold text-gray-500 mt-1 block">Today: ${{ number_format((float) ($todayBoostUsd ?? 0), 2) }}</span>
                        </div>
                        <div>
                            <span class="block text-[10px] font-extrabold uppercase tracking-wider text-gray-400">Boost Orders</span>
                            <span class="text-xl font-black text-gray-900 mt-0.5 block">{{ number_format($allTimeBoostCount ?? 0) }}</span>
                            <span class="text-[10px] font-bold text-gray-500 mt-1 block">Today: {{ number_format($todayBoostCount ?? 0) }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-5 pt-3 border-t border-gray-200/60 flex items-center justify-between text-xs font-bold text-gray-600">
                    <span>Today Profit: <strong class="text-emerald-600">+${{ number_format((float) ($todayBoostGain ?? 0), 2) }}</strong></span>
                    <a href="/admin/boosting" class="text-[#0d9488] hover:underline font-extrabold text-[11px]">Boost Console →</a>
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
                    <h3 class="font-black text-gray-900 text-base tracking-tight">7-Day User Registrations</h3>
                    <p class="text-xs font-semibold text-gray-500 mt-0.5">New customer sign-ups over the past week</p>
                </div>
                <span class="px-3 py-1 rounded-xl bg-teal-50 text-[#0d9488] font-black text-xs border border-teal-100">
                    {{ number_format($weekRegs) }} total users
                </span>
            </div>

            <div class="pt-6">
                <div class="h-32 flex items-end gap-3 sm:gap-4">
                    @foreach($series as $row)
                        @php $h = (int) round(((int) $row['registrations'] / $maxRegs) * 100); @endphp
                        <div class="flex-1 flex flex-col items-center group">
                            <div class="w-full bg-gray-100 rounded-xl h-24 flex items-end overflow-hidden p-0.5">
                                <div class="w-full bg-gradient-to-t from-[#0d9488] to-[#2dd4bf] rounded-lg group-hover:from-[#0f766e] group-hover:to-[#14b8a6] transition-all duration-300" 
                                     style="height: {{ max(6, $h) }}%" 
                                     title="{{ (int) ($row['registrations'] ?? 0) }} registrations"></div>
                            </div>
                            <div class="mt-2 text-[11px] font-bold text-gray-500">{{ \Illuminate\Support\Carbon::parse($row['date'])->format('D') }}</div>
                            <div class="mt-0.5 text-[11px] font-black text-gray-900">{{ number_format((int) ($row['registrations'] ?? 0)) }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between text-xs font-bold text-gray-600">
                <span>Total Registered Today: <strong class="text-gray-900">+{{ number_format((int) ($todayUsers ?? 0)) }} users</strong></span>
                <span class="text-teal-700 font-extrabold">Active sign-up trend</span>
            </div>
        </div>

        <!-- Chart 2: 7-Day Financial Flow -->
        <div class="bg-white rounded-3xl p-6 sm:p-7 border border-gray-200/80 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                <div>
                    <h3 class="font-black text-gray-900 text-base tracking-tight">7-Day Financial Flow</h3>
                    <p class="text-xs font-semibold text-gray-500 mt-0.5">Comparing User Order Spending vs Funding Deposits</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 text-[10px] font-extrabold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-lg">
                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span> Spend
                    </span>
                    <span class="inline-flex items-center gap-1 text-[10px] font-extrabold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-lg">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span> Deposit
                    </span>
                </div>
            </div>

            <div class="pt-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <div class="text-[10px] font-black uppercase tracking-wider text-gray-400 mb-2">User Spending Volume</div>
                        <div class="h-28 flex items-end gap-2.5">
                            @foreach($series as $row)
                                @php $h = (int) round(((float) $row['sales_usd'] / $maxSales) * 100); @endphp
                                <div class="flex-1 flex flex-col items-center group">
                                    <div class="w-full bg-gray-100 rounded-xl h-24 flex items-end overflow-hidden p-0.5">
                                        <div class="w-full bg-gradient-to-t from-indigo-600 to-indigo-400 rounded-lg group-hover:from-indigo-700 group-hover:to-indigo-500 transition-all duration-300" 
                                             style="height: {{ max(6, $h) }}%" 
                                             title="${{ number_format((float) ($row['sales_usd'] ?? 0), 2) }}"></div>
                                    </div>
                                    <div class="mt-1.5 text-[10px] font-bold text-gray-400 text-center">{{ \Illuminate\Support\Carbon::parse($row['date'])->format('D') }}</div>
                                    <div class="mt-0.5 text-[10px] font-black text-gray-900 text-center">${{ number_format((float) ($row['sales_usd'] ?? 0), 0) }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <div class="text-[10px] font-black uppercase tracking-wider text-gray-400 mb-2">Confirmed Deposits</div>
                        <div class="h-28 flex items-end gap-2.5">
                            @foreach($series as $row)
                                @php $h = (int) round(((float) $row['funding_usd'] / $maxFunding) * 100); @endphp
                                <div class="flex-1 flex flex-col items-center group">
                                    <div class="w-full bg-gray-100 rounded-xl h-24 flex items-end overflow-hidden p-0.5">
                                        <div class="w-full bg-gradient-to-t from-amber-500 to-amber-300 rounded-lg group-hover:from-amber-600 group-hover:to-amber-400 transition-all duration-300" 
                                             style="height: {{ max(6, $h) }}%" 
                                             title="${{ number_format((float) ($row['funding_usd'] ?? 0), 2) }}"></div>
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
                <span>7-Day Volume: <strong class="text-indigo-700">${{ number_format($weekSpend, 2) }}</strong></span>
                <span>7-Day Deposits: <strong class="text-amber-700">${{ number_format($weekFunding, 2) }}</strong></span>
            </div>
        </div>

    </div>

    <!-- Management Data Tables Grid -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
        
        <!-- Table 1: Latest Users -->
        <div class="bg-white rounded-3xl border border-gray-200/80 shadow-sm overflow-hidden flex flex-col">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                <div>
                    <h3 class="font-black text-gray-900 text-base tracking-tight">Latest Registered Users</h3>
                    <p class="text-xs font-semibold text-gray-500 mt-0.5">Recently onboarded platform customers</p>
                </div>
                @if(method_exists($latestUsers, 'total'))
                    <span class="text-xs font-extrabold text-gray-600 bg-white px-3 py-1 rounded-xl border border-gray-200 shadow-xs">
                        {{ number_format($latestUsers->total()) }} total
                    </span>
                @endif
            </div>

            <div class="overflow-x-auto flex-1">
                <table class="min-w-full text-xs">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/30 text-gray-500 font-extrabold uppercase tracking-wider text-[10px]">
                            <th class="text-left px-6 py-3.5">User Profile</th>
                            <th class="text-left px-6 py-3.5">Status</th>
                            <th class="text-left px-6 py-3.5">Registered</th>
                            <th class="text-right px-6 py-3.5">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse(($latestUsers ?? []) as $u)
                            <tr class="hover:bg-gray-50/80 transition">
                                <td class="px-6 py-3.5">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-gray-900 to-gray-700 text-white flex items-center justify-center font-black text-xs shadow-xs">
                                            {{ strtoupper(substr($u->name, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <span class="font-extrabold text-gray-900 block truncate">{{ $u->name }}</span>
                                            <span class="text-[11px] font-semibold text-gray-500 block truncate">{{ $u->email }}</span>
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
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full border text-[10px] font-black uppercase tracking-wider {{ $badge }}">
                                        {{ $st ?: 'active' }}
                                    </span>
                                </td>
                                <td class="px-6 py-3.5 font-bold text-gray-600">
                                    {{ $u->created_at ? \Illuminate\Support\Carbon::parse($u->created_at)->format('M j, Y') : '—' }}
                                </td>
                                <td class="px-6 py-3.5 text-right">
                                    <a href="/admin/users/{{ $u->id }}" class="inline-flex items-center px-2.5 py-1 rounded-xl bg-gray-100 hover:bg-teal-50 text-gray-700 hover:text-teal-700 font-extrabold text-[11px] transition">
                                        Manage →
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-gray-400 font-bold">No users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($latestUsers, 'links'))
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                    {{ $latestUsers->onEachSide(1)->links() }}
                </div>
            @endif
        </div>

        <!-- Table 2: Live Activity Feed -->
        <div class="bg-white rounded-3xl border border-gray-200/80 shadow-sm overflow-hidden flex flex-col">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                <div>
                    <h3 class="font-black text-gray-900 text-base tracking-tight">Recent Activity Stream</h3>
                    <p class="text-xs font-semibold text-gray-500 mt-0.5">Live purchases, SMS acquisitions, and deposits</p>
                </div>
                @if(method_exists($latestActivity, 'total'))
                    <span class="text-xs font-extrabold text-gray-600 bg-white px-3 py-1 rounded-xl border border-gray-200 shadow-xs">
                        {{ number_format($latestActivity->total()) }} events
                    </span>
                @endif
            </div>

            <div class="p-6 overflow-y-auto flex-1 max-h-[480px]">
                @if(($latestActivity ?? collect())->count() === 0)
                    <div class="text-center text-gray-400 py-16 font-bold">No recent transaction activity recorded.</div>
                @else
                    <div class="space-y-3.5">
                        @foreach($latestActivity as $a)
                            @php
                                $type = (string) ($a->type ?? '');
                                $status = (string) ($a->status ?? '');
                                $typeLabel = $type === 'funding' ? 'Deposit' : ($type === 'logs' ? 'Logs' : ($type === 'sms' ? 'SMS' : ($type === 'boost' ? 'Boost' : strtoupper($type))));
                                
                                $typeBadge = $type === 'funding' 
                                    ? 'bg-amber-50 text-amber-800 border-amber-200' 
                                    : ($type === 'logs' 
                                        ? 'bg-teal-50 text-teal-800 border-teal-200' 
                                        : ($type === 'sms' 
                                            ? 'bg-blue-50 text-blue-800 border-blue-200' 
                                            : 'bg-indigo-50 text-indigo-800 border-indigo-200'));
                                
                                $statusLower = strtolower($status);
                                $statusBadge = in_array($statusLower, ['approved', 'completed', 'success', 'successful', 'done', 'received', 'finished'], true) 
                                    ? 'bg-emerald-50 text-emerald-700 border-emerald-200' 
                                    : (in_array($statusLower, ['pending', 'processing', 'waiting', 'waiting_payment', 'initiated'], true) 
                                        ? 'bg-amber-50 text-amber-700 border-amber-200' 
                                        : 'bg-rose-50 text-rose-700 border-rose-200');
                                
                                $meta = (string) ($a->meta ?? '');
                                $dt = $a->created_at ? \Illuminate\Support\Carbon::parse($a->created_at) : null;
                            @endphp
                            <div class="p-4 rounded-2xl bg-gray-50/70 border border-gray-200/70 hover:bg-white hover:border-gray-300 hover:shadow-sm transition">
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
                                                <span class="text-[10px] font-bold text-gray-500 truncate max-w-[160px]">{{ $meta }}</span>
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
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                    {{ $latestActivity->onEachSide(1)->links() }}
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
