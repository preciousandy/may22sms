@extends('layouts.admin')

@section('admin_content')
@push('header_title')
    Admin Dashboard
@endpush
@php
    $series = collect($series ?? []);
    $maxRegs = max(1, (int) $series->max('registrations'));
    $maxSales = max(1, (float) $series->max('sales_usd'));
    $maxFunding = max(1, (float) $series->max('funding_usd'));
    $todaySpend = (float) ($todayLogsUsd ?? 0) + (float) ($todaySmsUsd ?? 0) + (float) ($todayBoostUsd ?? 0);
    $weekRegs = (int) $series->sum('registrations');
    $weekSpend = (float) $series->sum('sales_usd');
    $weekFunding = (float) $series->sum('funding_usd');
@endphp

<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Space+Mono:wght@400;700&display=swap');

    .admin-dash-container {
        font-family: 'Outfit', sans-serif;
    }

    .glass-card {
        background: rgba(17, 24, 39, 0.95);
        border: 1px solid rgba(255, 255, 255, 0.08);
        box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-radius: 20px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .glass-card:hover {
        border-color: rgba(13, 148, 136, 0.4);
        box-shadow: 0 12px 40px 0 rgba(13, 148, 136, 0.15);
        transform: translateY(-2px);
    }

    .neon-text-green {
        color: #10b981;
        text-shadow: 0 0 12px rgba(16, 185, 129, 0.3);
    }

    .neon-text-teal {
        color: #0d9488;
        text-shadow: 0 0 12px rgba(13, 148, 136, 0.3);
    }

    .neon-text-amber {
        color: #facc15;
        text-shadow: 0 0 12px rgba(250, 204, 21, 0.3);
    }

    .gradient-btn {
        background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
        box-shadow: 0 4px 15px rgba(13, 148, 136, 0.25);
        transition: all 0.3s ease;
    }

    .gradient-btn:hover {
        background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
        box-shadow: 0 6px 20px rgba(13, 148, 136, 0.4);
        transform: translateY(-1px);
    }

    /* custom scrollbar */
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.02);
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.15);
        border-radius: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.3);
    }
</style>

<div class="admin-dash-container pb-12">
    <!-- Header Section -->
    <div class="mb-8 flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
        <div>
            <h2 class="text-3xl font-extrabold tracking-tight text-slate-900 flex items-center gap-2">
                <span>Admin Dashboard</span>
                <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-500 animate-ping"></span>
            </h2>
            <div class="text-sm font-semibold text-slate-500 mt-1 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span>Overview of users, sales, and funding · <span class="text-teal-700 font-extrabold">{{ now()->format('D, M j, Y') }}</span></span>
            </div>
        </div>

        <!-- Quick actions -->
        <div class="grid grid-cols-2 sm:grid-cols-3 xl:flex xl:flex-wrap gap-2.5 w-full xl:w-auto">
            <a href="/admin/orders" class="gradient-btn inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-xs font-black text-white uppercase tracking-wider">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 11h14l1 12H4L5 11z"></path></svg>
                Orders & Refunds
            </a>
            <a href="/admin/funding" class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-slate-900 border border-slate-800 hover:bg-slate-800 text-white px-4 py-2.5 text-xs font-black uppercase tracking-wider transition">
                💵 Funding
            </a>
            <a href="/admin/boosting" class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-indigo-950 border border-indigo-800 hover:bg-indigo-900 text-indigo-300 px-4 py-2.5 text-xs font-black uppercase tracking-wider transition">
                🚀 Boosting
            </a>
            <a href="/admin/mail" class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-amber-400 hover:bg-amber-500 text-slate-900 px-4 py-2.5 text-xs font-black uppercase tracking-wider transition shadow-md">
                ✉️ Mail Sender
            </a>
            <a href="/admin/tickets" class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 px-4 py-2.5 text-xs font-black uppercase tracking-wider transition">
                🎫 Tickets
            </a>
            <a href="/admin/settings" class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 px-4 py-2.5 text-xs font-black uppercase tracking-wider transition">
                ⚙️ Settings
            </a>
        </div>
    </div>

    <!-- 5 Main Stats Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6">
        <!-- Card 1: Admin Net Gain -->
        <div class="glass-card text-white overflow-hidden relative group border-t-2 border-emerald-500">
            <div class="absolute -top-12 -right-12 w-32 h-32 bg-emerald-500/10 rounded-full blur-2xl pointer-events-none"></div>
            <div class="p-6 relative z-10">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="text-[10px] font-black uppercase tracking-widest text-emerald-400 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Admin Net Gain
                        </div>
                        <div class="mt-2 text-3xl font-extrabold tracking-tight neon-text-green">${{ number_format((float) ($totalAllTimeGain ?? 0), 2) }}</div>
                        <div class="mt-2 text-[11px] text-slate-400 font-bold">
                            Today: <span class="text-white font-extrabold">+${{ number_format((float) ($todayTotalGain ?? 0), 2) }}</span>
                        </div>
                    </div>
                    <div class="rounded-xl bg-emerald-500/10 p-2.5 border border-emerald-500/20 text-emerald-400">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
                <!-- Breakdown -->
                <div class="mt-5 pt-4 border-t border-slate-800/80 grid grid-cols-3 gap-1 text-[9px] font-bold text-slate-400">
                    <div>SMS<span class="block text-white font-extrabold mt-0.5">${{ number_format((float) ($totalSmsGain ?? 0), 2) }}</span></div>
                    <div>Boost<span class="block text-white font-extrabold mt-0.5">${{ number_format((float) ($totalBoostGain ?? 0), 2) }}</span></div>
                    <div>Logs<span class="block text-white font-extrabold mt-0.5">${{ number_format((float) ($totalLogsGain ?? 0), 2) }}</span></div>
                </div>
            </div>
        </div>

        <!-- Card 2: Total Users -->
        <div class="glass-card text-white overflow-hidden relative group border-t-2 border-teal-500">
            <div class="absolute -top-12 -right-12 w-32 h-32 bg-teal-500/10 rounded-full blur-2xl pointer-events-none"></div>
            <div class="p-6 relative z-10">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="text-[10px] font-black uppercase tracking-widest text-teal-400 flex items-center gap-1">
                            👥 Total Users
                        </div>
                        <div class="mt-2 text-3xl font-extrabold tracking-tight neon-text-teal">{{ number_format((int) ($totalUsers ?? 0)) }}</div>
                        <div class="mt-2 text-[11px] text-slate-400 font-bold">
                            Today: <span class="text-white font-extrabold">+{{ number_format((int) ($todayUsers ?? 0)) }}</span>
                        </div>
                    </div>
                    <div class="rounded-xl bg-teal-500/10 p-2.5 border border-teal-500/20 text-teal-400">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                </div>
                <div class="mt-5 pt-4 border-t border-slate-800/80 text-[10px] text-slate-400 font-bold">
                    Last 7 Days: <span class="text-teal-300 font-extrabold">{{ number_format((int) ($newUsers7d ?? 0)) }} registrations</span>
                </div>
            </div>
        </div>

        <!-- Card 3: Today Logins -->
        <div class="glass-card text-white overflow-hidden relative group border-t-2 border-indigo-500">
            <div class="absolute -top-12 -right-12 w-32 h-32 bg-indigo-500/10 rounded-full blur-2xl pointer-events-none"></div>
            <div class="p-6 relative z-10">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="text-[10px] font-black uppercase tracking-widest text-indigo-400">
                            🔑 Today Logins
                        </div>
                        <div class="mt-2 text-3xl font-extrabold tracking-tight text-indigo-100">{{ number_format((int) ($todayLogins ?? 0)) }}</div>
                        <div class="mt-2 text-[11px] text-slate-400 font-bold">
                            Open Tickets: <span class="text-rose-400 font-extrabold">{{ number_format((int) ($openTickets ?? 0)) }}</span>
                        </div>
                    </div>
                    <div class="rounded-xl bg-indigo-500/10 p-2.5 border border-indigo-500/20 text-indigo-400">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                    </div>
                </div>
                <div class="mt-5 pt-4 border-t border-slate-800/80 text-[10px] text-slate-400 font-bold flex items-center justify-between">
                    <span>Admin Session Active</span>
                    <span class="inline-block w-2 h-2 rounded-full bg-indigo-400 animate-pulse"></span>
                </div>
            </div>
        </div>

        <!-- Card 4: Users Status -->
        <div class="glass-card text-white overflow-hidden relative group border-t-2 border-rose-500">
            <div class="absolute -top-12 -right-12 w-32 h-32 bg-rose-500/10 rounded-full blur-2xl pointer-events-none"></div>
            <div class="p-6 relative z-10">
                <div class="flex items-start justify-between gap-2">
                    <div class="w-full">
                        <div class="text-[10px] font-black uppercase tracking-widest text-rose-400">
                            🛡️ Users Status
                        </div>
                        <div class="mt-3 grid grid-cols-3 gap-1 text-center">
                            <div class="bg-emerald-950/30 border border-emerald-800/30 rounded-lg py-1.5 px-1">
                                <span class="block text-[8px] font-black text-emerald-400 uppercase">Active</span>
                                <span class="text-xs font-black text-white">{{ number_format((int) ($verifiedUsers ?? 0)) }}</span>
                            </div>
                            <div class="bg-amber-950/30 border border-amber-800/30 rounded-lg py-1.5 px-1">
                                <span class="block text-[8px] font-black text-amber-400 uppercase">Pending</span>
                                <span class="text-xs font-black text-white">{{ number_format((int) ($pendingUsers ?? 0)) }}</span>
                            </div>
                            <div class="bg-rose-950/30 border border-rose-800/30 rounded-lg py-1.5 px-1">
                                <span class="block text-[8px] font-black text-rose-400 uppercase">Blocked</span>
                                <span class="text-xs font-black text-white">{{ number_format((int) ($blockedUsers ?? 0)) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-3.5 pt-3 border-t border-slate-800/80 flex items-center justify-between text-[9px] text-slate-400 font-bold">
                    <span>Verified Users Ratio</span>
                    @php 
                        $totalUsrVal = max(1, (int) ($totalUsers ?? 0));
                        $activeRatio = round(((int) ($verifiedUsers ?? 0) / $totalUsrVal) * 100);
                    @endphp
                    <span class="text-emerald-400 font-extrabold">{{ $activeRatio }}%</span>
                </div>
            </div>
        </div>

        <!-- Card 5: Funding Confirmed -->
        <div class="glass-card text-white overflow-hidden relative group border-t-2 border-amber-500">
            <div class="absolute -top-12 -right-12 w-32 h-32 bg-amber-500/10 rounded-full blur-2xl pointer-events-none"></div>
            <div class="p-6 relative z-10">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="text-[10px] font-black uppercase tracking-widest text-amber-400">
                            💵 Funding Confirmed
                        </div>
                        <div class="mt-2 text-3xl font-extrabold tracking-tight neon-text-amber">${{ number_format((float) ($todayFundingUsd ?? 0), 2) }}</div>
                        <div class="mt-2 text-[11px] text-slate-400 font-bold">
                            Deposits: <span class="text-white font-extrabold">{{ number_format((int) ($todayFundingCount ?? 0)) }} today</span>
                        </div>
                    </div>
                    <div class="rounded-xl bg-amber-500/10 p-2.5 border border-amber-500/20 text-amber-400">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    </div>
                </div>
                <div class="mt-5 pt-4 border-t border-slate-800/80 text-[10px] text-slate-400 font-bold">
                    Pending Invoices: <span class="text-rose-400 font-extrabold">{{ number_format((int) ($pendingFundingCount ?? 0)) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales & Net Profit Gain Section -->
    <div class="mt-8">
        <div class="glass-card text-white overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-800/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-slate-950/20">
                <div>
                    <h3 class="text-lg font-bold text-white tracking-wide">Today Sales & Admin Profit Gain</h3>
                    <div class="text-xs font-semibold text-slate-400 mt-0.5">Real-time revenue versus markup margins</div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="text-xs font-bold text-slate-300">Today's Volume: <span class="text-white font-extrabold">${{ number_format($todaySpend, 2) }}</span></div>
                    <div class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-black">
                        💰 Profit Gain: ${{ number_format((float) ($todayTotalGain ?? 0), 2) }}
                    </div>
                </div>
            </div>
            
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
                <!-- Box 1: Store Logs -->
                <div class="bg-slate-900/40 border border-slate-800/60 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-700 transition duration-300">
                    <div class="flex items-center justify-between text-xs font-extrabold text-slate-400">
                        <span class="flex items-center gap-1">🛒 Store Logs</span>
                        <span class="text-emerald-400 font-black">+${{ number_format((float) ($todayLogsGain ?? 0), 2) }} Profit</span>
                    </div>
                    <div class="mt-3 text-3xl font-black text-white">${{ number_format((float) ($todayLogsUsd ?? 0), 2) }}</div>
                    <div class="mt-2 text-xs font-semibold text-slate-400 flex items-center gap-1.5">
                        <span>{{ number_format((int) ($todayLogsCount ?? 0)) }} orders</span>
                        <span class="text-slate-600">•</span>
                        <span>{{ number_format((int) ($todayLogsItems ?? 0)) }} items bought</span>
                    </div>
                </div>

                <!-- Box 2: Virtual SMS -->
                <div class="bg-slate-900/40 border border-slate-800/60 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-700 transition duration-300">
                    <div class="flex items-center justify-between text-xs font-extrabold text-slate-400">
                        <span class="flex items-center gap-1">📱 Virtual SMS</span>
                        <span class="text-emerald-400 font-black">+${{ number_format((float) ($todaySmsGain ?? 0), 2) }} Profit</span>
                    </div>
                    <div class="mt-3 text-3xl font-black text-white">${{ number_format((float) ($todaySmsUsd ?? 0), 2) }}</div>
                    <div class="mt-2 text-xs font-semibold text-slate-400 flex items-center gap-1.5">
                        <span>{{ number_format((int) ($todaySmsCount ?? 0)) }} orders</span>
                        <span class="text-slate-600">•</span>
                        <span>All-time Profit: ${{ number_format((float) ($totalSmsGain ?? 0), 2) }}</span>
                    </div>
                </div>

                <!-- Box 3: Boosting -->
                <div class="bg-slate-900/40 border border-slate-800/60 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-700 transition duration-300">
                    <div class="flex items-center justify-between text-xs font-extrabold text-slate-400">
                        <span class="flex items-center gap-1">🚀 Boosting</span>
                        <span class="text-emerald-400 font-black">+${{ number_format((float) ($todayBoostGain ?? 0), 2) }} Profit</span>
                    </div>
                    <div class="mt-3 text-3xl font-black text-white">${{ number_format((float) ($todayBoostUsd ?? 0), 2) }}</div>
                    <div class="mt-2 text-xs font-semibold text-slate-400 flex items-center gap-1.5">
                        <span>{{ number_format((int) ($todayBoostCount ?? 0)) }} orders</span>
                        <span class="text-slate-600">•</span>
                        <span>All-time Profit: ${{ number_format((float) ($totalBoostGain ?? 0), 2) }}</span>
                    </div>
                </div>

                <!-- Box 4: Funding Confirmed -->
                <div class="bg-slate-900/40 border border-slate-800/60 rounded-2xl p-5 relative overflow-hidden group hover:border-slate-700 transition duration-300">
                    <div class="flex items-center justify-between text-xs font-extrabold text-slate-400">
                        <span class="flex items-center gap-1">💳 Funding Confirmed</span>
                        <span class="text-teal-400 font-black">${{ number_format((float) ($todayFundingUsd ?? 0), 2) }}</span>
                    </div>
                    <div class="mt-3 text-3xl font-black text-white">${{ number_format((float) ($todayFundingUsd ?? 0), 2) }}</div>
                    <div class="mt-2 text-xs font-semibold text-slate-400 flex items-center gap-1.5">
                        <span>{{ number_format((int) ($todayFundingCount ?? 0)) }} deposits processed today</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="mt-8 grid grid-cols-1 xl:grid-cols-2 gap-6">
        <!-- Chart 1: Registrations -->
        <div class="glass-card text-white overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-800/80 flex items-center justify-between">
                <h3 class="text-base font-bold text-white">Last 7 Days Registrations</h3>
                <div class="text-xs font-bold text-slate-400">{{ number_format($weekRegs) }} total users</div>
            </div>
            <div class="p-6">
                <div class="h-28 flex items-end gap-2.5 mt-2">
                    @foreach($series as $row)
                        @php $h = (int) round(((int) $row['registrations'] / $maxRegs) * 100); @endphp
                        <div class="flex-1 flex flex-col items-center">
                            <div class="w-full bg-slate-800/50 rounded-xl h-24 flex items-end overflow-hidden">
                                <div class="w-full bg-gradient-to-t from-[#0d9488] to-[#2dd4bf] rounded-xl hover:from-[#14b8a6] hover:to-[#5eead4] transition-all duration-300" style="height: {{ $h }}%" title="{{ (int) ($row['registrations'] ?? 0) }}"></div>
                            </div>
                            <div class="mt-2 text-[10px] font-bold text-slate-400">{{ \Illuminate\Support\Carbon::parse($row['date'])->format('D') }}</div>
                            <div class="mt-0.5 text-[10px] font-black text-white">{{ number_format((int) ($row['registrations'] ?? 0)) }}</div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-6 text-xs text-slate-400 font-semibold flex items-center justify-between bg-slate-900/40 p-3 rounded-xl border border-slate-800/60">
                    <span>Registered Today</span>
                    <span class="text-white font-extrabold">{{ number_format((int) ($todayUsers ?? 0)) }} users</span>
                </div>
            </div>
        </div>

        <!-- Chart 2: Sales Spend & Funding -->
        <div class="glass-card text-white overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-800/80 flex items-center justify-between">
                <h3 class="text-base font-bold text-white">Last 7 Days Revenue & Funding</h3>
                <div class="text-xs font-bold text-slate-400">Total: ${{ number_format($weekSpend + $weekFunding, 2) }}</div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Wallet Spend (Logs + SMS + Boost)</div>
                        <div class="h-28 flex items-end gap-2.5">
                            @foreach($series as $row)
                                @php $h = (int) round(((float) $row['sales_usd'] / $maxSales) * 100); @endphp
                                <div class="flex-1 flex flex-col items-center">
                                    <div class="w-full bg-slate-800/50 rounded-xl h-24 flex items-end overflow-hidden">
                                        <div class="w-full bg-gradient-to-t from-teal-500 to-emerald-400 rounded-xl hover:from-teal-400 hover:to-emerald-300 transition-all duration-300" style="height: {{ $h }}%" title="${{ number_format((float) ($row['sales_usd'] ?? 0), 2) }}"></div>
                                    </div>
                                    <div class="mt-2 text-[10px] font-bold text-slate-400 text-center">{{ \Illuminate\Support\Carbon::parse($row['date'])->format('D') }}</div>
                                    <div class="mt-0.5 text-[9px] font-black text-white text-center">${{ number_format((float) ($row['sales_usd'] ?? 0), 0) }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Funding Confirmed (Deposits)</div>
                        <div class="h-28 flex items-end gap-2.5">
                            @foreach($series as $row)
                                @php $h = (int) round(((float) $row['funding_usd'] / $maxFunding) * 100); @endphp
                                <div class="flex-1 flex flex-col items-center">
                                    <div class="w-full bg-slate-800/50 rounded-xl h-24 flex items-end overflow-hidden">
                                        <div class="w-full bg-gradient-to-t from-amber-500 to-yellow-400 rounded-xl hover:from-amber-400 hover:to-yellow-300 transition-all duration-300" style="height: {{ $h }}%" title="${{ number_format((float) ($row['funding_usd'] ?? 0), 2) }}"></div>
                                    </div>
                                    <div class="mt-2 text-[10px] font-bold text-slate-400 text-center">{{ \Illuminate\Support\Carbon::parse($row['date'])->format('D') }}</div>
                                    <div class="mt-0.5 text-[9px] font-black text-white text-center">${{ number_format((float) ($row['funding_usd'] ?? 0), 0) }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-3.5">
                    <div class="rounded-xl border border-slate-800 bg-slate-900/40 p-3 flex items-center justify-between">
                        <span class="text-[10px] font-bold text-slate-400">Today Spend</span>
                        <span class="text-xs font-black text-white">${{ number_format($todaySpend, 2) }}</span>
                    </div>
                    <div class="rounded-xl border border-slate-800 bg-slate-900/40 p-3 flex items-center justify-between">
                        <span class="text-[10px] font-bold text-slate-400">Today Funding</span>
                        <span class="text-xs font-black text-white">${{ number_format((float) ($todayFundingUsd ?? 0), 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Tables Grid -->
    <div class="mt-8 grid grid-cols-1 xl:grid-cols-2 gap-6">
        <!-- Table 1: Latest Users -->
        <div class="glass-card text-white overflow-hidden flex flex-col">
            <div class="px-6 py-5 border-b border-slate-800/80 bg-slate-950/20 flex items-center justify-between">
                <h3 class="text-base font-bold text-white">Latest Users</h3>
                @if(method_exists($latestUsers, 'total'))
                    <span class="text-xs font-bold text-slate-400 bg-slate-800/80 px-2.5 py-1 rounded-full border border-slate-700/50">{{ number_format($latestUsers->total()) }} users total</span>
                @endif
            </div>
            <div class="overflow-x-auto custom-scrollbar flex-1">
                <table class="min-w-full text-xs">
                    <thead>
                        <tr class="border-b border-slate-800/50 text-slate-400">
                            <th class="text-left px-6 py-3.5 font-bold uppercase tracking-wider">User Details</th>
                            <th class="text-left px-6 py-3.5 font-bold uppercase tracking-wider">Status</th>
                            <th class="text-left px-6 py-3.5 font-bold uppercase tracking-wider">Joined Date</th>
                            <th class="text-left px-6 py-3.5 font-bold uppercase tracking-wider">Last Activity</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-850 bg-slate-900/10">
                        @foreach(($latestUsers ?? []) as $u)
                            <tr class="hover:bg-slate-800/30 transition">
                                <td class="px-6 py-3.5 font-bold">
                                    <div class="font-black text-white flex items-center gap-1.5">
                                        <div class="w-6 h-6 rounded-full bg-teal-500/10 border border-teal-500/30 flex items-center justify-center text-[10px] text-teal-400 uppercase">
                                            {{ substr($u->name, 0, 1) }}
                                        </div>
                                        <span>{{ $u->name }}</span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-semibold mt-0.5 ml-8">{{ $u->email }}</div>
                                </td>
                                <td class="px-6 py-3.5">
                                    @php
                                        $st = (string) ($u->account_status ?? '');
                                        $badge = $st === 'active' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/25' : ($st === 'blocked' ? 'bg-rose-500/10 text-rose-400 border-rose-500/25' : 'bg-amber-500/10 text-amber-400 border-amber-500/25');
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full border text-[9px] font-black uppercase tracking-wider {{ $badge }}">{{ $st ?: 'unknown' }}</span>
                                </td>
                                <td class="px-6 py-3.5 font-bold text-slate-300">{{ $u->created_at ? \Illuminate\Support\Carbon::parse($u->created_at)->format('M j, Y') : '—' }}</td>
                                <td class="px-6 py-3.5 font-bold text-slate-300">{{ $u->last_login_at ? \Illuminate\Support\Carbon::parse($u->last_login_at)->format('M j, H:i') : '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if(method_exists($latestUsers, 'links'))
                <div class="px-6 py-4 border-t border-slate-800 bg-slate-950/20">
                    {{ $latestUsers->onEachSide(1)->links() }}
                </div>
            @endif
        </div>

        <!-- Table 2: Latest Activity Timeline -->
        <div class="glass-card text-white overflow-hidden flex flex-col">
            <div class="px-6 py-5 border-b border-slate-800/80 bg-slate-950/20 flex items-center justify-between">
                <h3 class="text-base font-bold text-white">Latest Activity Feed</h3>
                @if(method_exists($latestActivity, 'total'))
                    <span class="text-xs font-bold text-slate-400 bg-slate-800/80 px-2.5 py-1 rounded-full border border-slate-700/50">{{ number_format($latestActivity->total()) }} events</span>
                @endif
            </div>
            
            <div class="p-6 overflow-y-auto custom-scrollbar flex-1 max-h-[460px]">
                @if(($latestActivity ?? collect())->count() === 0)
                    <div class="text-center text-slate-500 py-16">No recent user activity recorded.</div>
                @else
                    <div class="space-y-4">
                        @foreach($latestActivity as $a)
                            @php
                                $type = (string) ($a->type ?? '');
                                $status = (string) ($a->status ?? '');
                                $typeLabel = $type === 'funding' ? 'Funding' : ($type === 'logs' ? 'Logs' : ($type === 'sms' ? 'SMS' : ($type === 'boost' ? 'Boost' : strtoupper($type))));
                                
                                $typeBadge = $type === 'funding' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : ($type === 'logs' ? 'bg-slate-800 text-slate-300 border-slate-700' : ($type === 'sms' ? 'bg-amber-500/10 text-amber-400 border-amber-500/20' : 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20'));
                                
                                $statusLower = strtolower($status);
                                $statusBadge = in_array($statusLower, ['approved', 'completed', 'success', 'successful', 'done'], true) ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : (in_array($statusLower, ['pending', 'processing', 'inprogress', 'in_progress'], true) ? 'bg-amber-500/10 text-amber-400 border-amber-500/20' : (in_array($statusLower, ['rejected', 'failed', 'cancelled', 'canceled', 'blocked'], true) ? 'bg-rose-500/10 text-rose-400 border-rose-500/20' : 'bg-slate-800 text-slate-400 border-slate-700'));
                                
                                $meta = (string) ($a->meta ?? '');
                                $dt = $a->created_at ? \Illuminate\Support\Carbon::parse($a->created_at) : null;
                            @endphp
                            <div class="rounded-xl border border-slate-800/80 bg-slate-900/30 p-4 hover:border-slate-700 transition duration-300">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full border text-[9px] font-black uppercase tracking-wider {{ $typeBadge }}">{{ $typeLabel }}</span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full border text-[9px] font-black uppercase tracking-wider {{ $statusBadge }}">{{ $status ?: 'UNKNOWN' }}</span>
                                            @if($meta !== '')
                                                <span class="text-[10px] font-bold text-slate-400 truncate">{{ $meta }}</span>
                                            @endif
                                        </div>
                                        <div class="mt-2.5 flex flex-wrap items-center gap-x-2.5 gap-y-0.5 text-xs">
                                            <span class="font-black text-white">{{ $a->user_name ?? 'User' }}</span>
                                            <span class="text-slate-600">#{{ (int) ($a->user_id ?? 0) }}</span>
                                            <span class="text-slate-700">•</span>
                                            <span class="text-slate-500 font-mono text-[10px]">TX: {{ (int) ($a->id ?? 0) }}</span>
                                        </div>
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <div class="text-sm font-extrabold tracking-tight text-white">${{ number_format((float) ($a->amount_usd ?? 0), 2) }}</div>
                                        <div class="mt-1 text-[10px] font-semibold text-slate-400">{{ $dt ? $dt->format('M j, H:i') : '—' }}</div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            
            @if(method_exists($latestActivity, 'links'))
                <div class="px-6 py-4 border-t border-slate-800 bg-slate-950/20">
                    {{ $latestActivity->onEachSide(1)->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
