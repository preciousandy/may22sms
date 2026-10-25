@extends('layouts.dashboard')

@section('page_title', 'Support & Communication Center')

@section('dashboard_content')
@php
    $currentTicket = $activeTicket ?? null;
@endphp

<div class="space-y-6 pb-12" x-data="ticketsHub({
    activeTicketId: @js($currentTicket ? $currentTicket->id : null),
    initialMode: @js(request()->query('sell_logs') ? 'sell_logs' : ($currentTicket ? 'view_ticket' : 'new_ticket')),
    ticketsListUrl: '/tickets',
})">

    <!-- Flash Notifications -->
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-3.5 rounded-2xl shadow-sm flex items-center justify-between gap-3 animate-fade-in">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="font-bold text-sm">{{ session('success') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 p-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-rose-50 border border-rose-200 text-rose-800 px-5 py-3.5 rounded-2xl shadow-sm flex items-center justify-between gap-3 animate-fade-in">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="font-bold text-sm">{{ session('error') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-rose-500 hover:text-rose-700 p-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-800 px-5 py-4 rounded-2xl shadow-sm">
            <div class="font-bold text-sm mb-2 flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Please check the form inputs:
            </div>
            <ul class="list-disc pl-8 text-xs font-semibold space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Support Hub Hero Header & Instant Channels -->
    <div class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-gray-900 to-slate-950 p-6 sm:p-8 rounded-3xl border border-gray-800 text-white shadow-xl">
        <!-- Ambient decorative glow -->
        <div class="absolute -right-16 -top-16 w-72 h-72 rounded-full bg-teal-500/10 blur-3xl pointer-events-none"></div>
        <div class="absolute -left-16 -bottom-16 w-72 h-72 rounded-full bg-indigo-500/10 blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div>
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-[#0d9488] to-teal-400 text-white flex items-center justify-center shadow-lg shadow-teal-900/40">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2.5 flex-wrap">
                            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">Support &amp; Resolution Hub</h1>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                Support Agents Online
                            </span>
                        </div>
                        <p class="text-xs font-medium text-slate-300 mt-1 flex items-center gap-2 flex-wrap">
                            <span>Average reply time: <strong class="text-teal-400 font-bold">&lt; 15 minutes</strong></span>
                            <span>•</span>
                            <span>Tracked 1-on-1 conversations &amp; live assistance</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Instant Alternative Live Channels -->
            <div class="flex flex-wrap items-center gap-3">
                <a href="https://chat.whatsapp.com/FCD1Iqs14iPBG4DpiT4Vec?mode=gi_t" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2.5 px-4 py-2.5 rounded-xl bg-emerald-600/90 hover:bg-emerald-600 text-white text-xs font-black transition active:scale-95 shadow-md shadow-emerald-900/40 border border-emerald-500/30">
                    <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.669-.699c.969.54 1.771.82 2.788.82 3.185 0 5.772-2.587 5.773-5.767.001-3.182-2.585-5.805-5.77-5.805zm3.376 8.21c-.14.394-.712.729-1.02.775-.297.045-.67.073-2.138-.535-1.748-.724-2.859-2.5-2.946-2.617-.087-.116-.708-.941-.708-1.794 0-.853.447-1.272.607-1.447.16-.175.349-.219.465-.219.117 0 .233.001.335.006.107.005.251-.041.393.3.146.35.496 1.21.54 1.297.044.088.073.19.015.306-.059.117-.088.19-.175.292-.087.102-.185.228-.264.306-.088.087-.179.182-.077.357.102.175.454.748.974 1.211.67.597 1.235.782 1.41.87.175.088.277.073.38-.044.102-.117.437-.51.554-.685.117-.175.234-.146.394-.088.16.059 1.02.481 1.195.568.175.088.291.131.335.204.044.073.044.423-.096.817z"/>
                    </svg>
                    <span>WhatsApp Community</span>
                </a>

                <a href="https://t.me/may22sms" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2.5 px-4 py-2.5 rounded-xl bg-sky-500/90 hover:bg-sky-500 text-white text-xs font-black transition active:scale-95 shadow-md shadow-sky-900/40 border border-sky-400/30">
                    <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.52 2.77-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .34z"/>
                    </svg>
                    <span>Telegram Support</span>
                </a>

                <button type="button" @click="startNewTicket()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white text-gray-900 hover:bg-teal-50 text-xs font-black transition active:scale-95 shadow-sm">
                    <svg class="w-4 h-4 text-[#0d9488]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                    <span>+ New Ticket</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Quick Communication Metric Pills -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-gray-200/80 shadow-sm flex items-center justify-between">
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-2xl bg-teal-50 text-[#0d9488] flex items-center justify-center font-black">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Total Conversations</div>
                    <div class="text-2xl font-black text-gray-900 mt-0.5">{{ $totalCount ?? 0 }}</div>
                </div>
            </div>
            <a href="/tickets?status=all" class="text-xs font-extrabold text-teal-700 hover:text-teal-900">View All →</a>
        </div>

        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-gray-200/80 shadow-sm flex items-center justify-between">
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-black">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Awaiting Response / Open</div>
                    <div class="text-2xl font-black text-emerald-600 mt-0.5">{{ $openCount ?? 0 }}</div>
                </div>
            </div>
            <a href="/tickets?status=open" class="text-xs font-extrabold text-emerald-700 hover:text-emerald-900">Filter Open →</a>
        </div>

        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-gray-200/80 shadow-sm flex items-center justify-between">
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-2xl bg-gray-100 text-gray-600 flex items-center justify-center font-black">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Resolved &amp; Closed</div>
                    <div class="text-2xl font-black text-gray-700 mt-0.5">{{ $closedCount ?? 0 }}</div>
                </div>
            </div>
            <a href="/tickets?status=closed" class="text-xs font-extrabold text-gray-600 hover:text-gray-900">Filter Closed →</a>
        </div>
    </div>

    <!-- Main Dual-Pane Communicator Interface -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- LEFT COLUMN: Ticket Inbox & Filter Sidebar (4 cols on lg) -->
        <div class="lg:col-span-5 xl:col-span-4 bg-white rounded-3xl border border-gray-200/90 shadow-sm overflow-hidden flex flex-col min-h-[580px]">
            <!-- Inbox Header & Actions -->
            <div class="p-5 border-b border-gray-100 bg-gray-50/70">
                <div class="flex items-center justify-between gap-3 mb-3.5">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-teal-500"></span>
                        <h2 class="text-base font-black text-gray-900 tracking-tight">Your Inbox</h2>
                    </div>
                    <button type="button" @click="startNewTicket()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#0d9488] hover:bg-teal-700 text-white text-xs font-bold transition shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                        <span>Compose</span>
                    </button>
                </div>

                <!-- Search Input -->
                <form action="/tickets" method="GET" class="relative">
                    @if(request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                    <div class="relative">
                        <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        <input type="text" name="q" value="{{ $searchQuery ?? '' }}" placeholder="Search tickets by subject, #ID..." class="w-full pl-10 pr-9 py-2.5 bg-white rounded-xl border border-gray-200 text-xs font-semibold text-gray-900 placeholder:text-gray-400 focus:outline-none focus:border-[#0d9488] focus:ring-1 focus:ring-[#0d9488] transition">
                        @if($searchQuery)
                            <a href="/tickets{{ request('status') ? '?status='.request('status') : '' }}" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 p-0.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </a>
                        @endif
                    </div>
                </form>

                <!-- Status Filter Pills -->
                <div class="flex items-center gap-1.5 mt-3 pt-2 border-t border-gray-200/60 overflow-x-auto text-[11px] font-bold">
                    <a href="/tickets{{ $searchQuery ? '?q='.$searchQuery : '' }}" class="px-3 py-1.5 rounded-lg transition whitespace-nowrap {{ ($statusFilter === 'all') ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                        All ({{ $totalCount }})
                    </a>
                    <a href="/tickets?status=open{{ $searchQuery ? '&q='.$searchQuery : '' }}" class="px-3 py-1.5 rounded-lg transition whitespace-nowrap {{ ($statusFilter === 'open') ? 'bg-emerald-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                        Active ({{ $openCount }})
                    </a>
                    <a href="/tickets?status=closed{{ $searchQuery ? '&q='.$searchQuery : '' }}" class="px-3 py-1.5 rounded-lg transition whitespace-nowrap {{ ($statusFilter === 'closed') ? 'bg-gray-700 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                        Closed ({{ $closedCount }})
                    </a>
                </div>
            </div>

            <!-- Ticket Items List -->
            <div class="divide-y divide-gray-100 overflow-y-auto max-h-[580px] flex-1">
                @if($tickets->count() === 0)
                    <div class="p-8 text-center flex flex-col items-center justify-center my-auto">
                        <div class="w-12 h-12 rounded-2xl bg-teal-50 text-[#0d9488] flex items-center justify-center mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                        </div>
                        <h4 class="text-sm font-black text-gray-900 mb-1">No Tickets Found</h4>
                        <p class="text-xs text-gray-500 mb-4 max-w-xs">Have a question or need assistance with your numbers or account? Start a new conversation!</p>
                        <button type="button" @click="startNewTicket()" class="px-4 py-2 rounded-xl bg-[#0d9488] text-white text-xs font-bold hover:bg-teal-700 transition">
                            Create First Ticket
                        </button>
                    </div>
                @else
                    @foreach($tickets as $t)
                        @php
                            $isSelected = ($currentTicket && $currentTicket->id === $t->id);
                            $latestMsg = $t->messages->first();
                            $adminReplied = ($latestMsg && $latestMsg->is_admin);
                        @endphp
                        <a href="/tickets?ticket={{ $t->id }}{{ $statusFilter !== 'all' ? '&status='.$statusFilter : '' }}{{ $searchQuery ? '&q='.$searchQuery : '' }}" 
                           class="block p-4 sm:p-4.5 transition relative {{ $isSelected ? 'bg-teal-50/70 border-l-4 border-l-[#0d9488]' : 'hover:bg-gray-50 border-l-4 border-l-transparent' }}">
                            
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-[10px] font-black px-2 py-0.5 rounded-md {{ $isSelected ? 'bg-[#0d9488] text-white' : 'bg-gray-100 text-gray-700' }}">
                                        #{{ $t->id }}
                                    </span>

                                    @if($t->category)
                                        <span class="text-[10px] font-bold text-gray-500 bg-gray-100/80 px-2 py-0.5 rounded capitalize">
                                            {{ str_replace('_', ' ', $t->category) }}
                                        </span>
                                    @endif

                                    @if($adminReplied && $t->status === 'open')
                                        <span class="inline-flex items-center gap-1 text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 animate-pulse">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                            Support Replied
                                        </span>
                                    @endif
                                </div>

                                <span class="text-[10px] font-black px-2.5 py-0.5 rounded-full {{ $t->status === 'open' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/80' : 'bg-gray-100 text-gray-600 border border-gray-200' }}">
                                    {{ strtoupper($t->status) }}
                                </span>
                            </div>

                            <h3 class="text-xs sm:text-sm font-black text-gray-900 mt-2 line-clamp-1 {{ $isSelected ? 'text-teal-900' : '' }}">
                                {{ $t->subject }}
                            </h3>

                            @if($latestMsg && $latestMsg->message)
                                <p class="text-[11px] text-gray-500 line-clamp-1 mt-1 font-medium">
                                    @if($latestMsg->is_admin)
                                        <span class="font-bold text-teal-700">Support:</span>
                                    @else
                                        <span class="font-bold text-gray-700">You:</span>
                                    @endif
                                    {{ $latestMsg->message }}
                                </p>
                            @endif

                            <div class="flex items-center justify-between text-[10px] text-gray-400 font-bold mt-2.5 pt-2 border-t border-gray-100">
                                <span class="flex items-center gap-1">
                                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    {{ ($t->last_message_at ?? $t->updated_at)->diffForHumans() }}
                                </span>

                                <span class="flex items-center gap-1 text-gray-500">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                                    {{ $t->messages_count ?? 1 }} {{ Str::plural('msg', $t->messages_count ?? 1) }}
                                </span>
                            </div>
                        </a>
                    @endforeach
                @endif
            </div>

            @if($tickets->hasPages())
                <div class="p-3 border-t border-gray-100 bg-gray-50/70 text-xs">
                    {{ $tickets->links() }}
                </div>
            @endif
        </div>

        <!-- RIGHT COLUMN: Interactive Conversation View OR Ticket Composer (8 cols on lg) -->
        <div class="lg:col-span-7 xl:col-span-8">

            <!-- STATE 1: ACTIVE TICKET CONVERSATION THREAD -->
            @if($currentTicket)
                <div class="bg-white rounded-3xl border border-gray-200/90 shadow-sm overflow-hidden flex flex-col min-h-[580px]">
                    <!-- Ticket Header Bar -->
                    <div class="p-5 sm:p-6 border-b border-gray-100 bg-gray-50/70 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2.5 flex-wrap">
                                <span class="text-xs font-black px-2.5 py-0.5 rounded-md bg-[#0d9488] text-white">
                                    Ticket #{{ $currentTicket->id }}
                                </span>
                                @if($currentTicket->category)
                                    <span class="text-xs font-bold bg-gray-200 text-gray-700 px-2.5 py-0.5 rounded capitalize">
                                        {{ str_replace('_', ' ', $currentTicket->category) }}
                                    </span>
                                @endif
                                <span class="text-xs font-black px-3 py-0.5 rounded-full {{ $currentTicket->status === 'open' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-200 text-gray-700' }}">
                                    {{ strtoupper($currentTicket->status) }}
                                </span>
                            </div>

                            <div class="flex items-center gap-2 mt-2">
                                <h2 class="text-lg sm:text-xl font-black text-gray-900 tracking-tight">
                                    {{ $currentTicket->subject }}
                                </h2>
                                <button type="button" @click="copyTicketRef('Ticket #{{ $currentTicket->id }}: {{ $currentTicket->subject }}')" title="Copy ticket reference" class="p-1 text-gray-400 hover:text-gray-700 rounded-lg hover:bg-gray-200/60 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                </button>
                            </div>

                            <p class="text-xs text-gray-400 font-medium mt-1 flex items-center gap-2">
                                <span>Opened {{ $currentTicket->created_at->format('M d, Y · h:i A') }}</span>
                                <span>•</span>
                                <span>{{ $currentTicket->messages->count() }} {{ Str::plural('message', $currentTicket->messages->count()) }}</span>
                            </p>
                        </div>

                        <!-- Ticket Action Buttons (Close / Reopen / New) -->
                        <div class="flex items-center gap-2.5 flex-wrap">
                            @if($currentTicket->status === 'open')
                                <form action="/tickets/{{ $currentTicket->id }}/close" method="POST" onsubmit="return confirm('Are you sure you want to close this ticket? You can reopen it later if needed.');">
                                    @csrf
                                    <button type="submit" class="px-3.5 py-2 rounded-xl border border-gray-300 hover:border-gray-400 text-gray-700 bg-white hover:bg-gray-50 text-xs font-bold transition shadow-sm flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        <span>Mark as Resolved</span>
                                    </button>
                                </form>
                            @else
                                <form action="/tickets/{{ $currentTicket->id }}/reopen" method="POST">
                                    @csrf
                                    <button type="submit" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-sm flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                        <span>Reopen Ticket</span>
                                    </button>
                                </form>
                            @endif

                            <button type="button" @click="startNewTicket()" class="px-3.5 py-2 rounded-xl bg-[#0d9488] hover:bg-teal-700 text-white text-xs font-bold transition shadow-sm flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                                <span>New Ticket</span>
                            </button>
                        </div>
                    </div>

                    <!-- Conversation Message Feed -->
                    <div id="messagesFeed" class="p-6 space-y-5 bg-slate-50/60 flex-1 overflow-y-auto max-h-[460px] min-h-[300px]">
                        @foreach($currentTicket->messages as $m)
                            <div class="flex flex-col {{ $m->is_admin ? 'items-start' : 'items-end' }}">
                                <div class="flex items-center gap-2 mb-1 px-1">
                                    <span class="text-[11px] font-black {{ $m->is_admin ? 'text-teal-800' : 'text-gray-500' }}">
                                        {{ $m->is_admin ? 'May22SMS Support' : 'You' }}
                                    </span>
                                    <span class="text-[10px] text-gray-400 font-medium">• {{ $m->created_at->format('M d, Y · h:i A') }}</span>
                                </div>

                                <div class="max-w-[88%] sm:max-w-[78%] rounded-2xl p-4 shadow-sm {{ $m->is_admin ? 'bg-white border border-gray-200 text-gray-900 rounded-tl-sm' : 'bg-gradient-to-r from-[#0d9488] to-teal-700 text-white rounded-tr-sm' }}">
                                    @if($m->message)
                                        <div class="text-xs sm:text-sm leading-relaxed whitespace-pre-wrap font-medium">{{ $m->message }}</div>
                                    @endif

                                    <!-- Attachment Box inside bubble -->
                                    @if($m->attachment_path)
                                        <div class="mt-3 pt-3 border-t {{ $m->is_admin ? 'border-gray-100' : 'border-teal-600/40' }}">
                                            @if($m->isImage())
                                                <div class="relative group cursor-pointer overflow-hidden rounded-xl border {{ $m->is_admin ? 'border-gray-200 bg-gray-50' : 'border-teal-600 bg-teal-800/30' }}" @click="lightboxImg = '{{ $m->attachment_url }}'">
                                                    <img src="{{ $m->attachment_url }}" alt="{{ $m->attachment_name }}" class="max-h-56 w-full object-cover group-hover:scale-105 transition duration-300">
                                                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs font-bold gap-1">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path></svg>
                                                        Click to Enlarge
                                                    </div>
                                                </div>
                                                <div class="mt-1 text-[10px] opacity-80 truncate">{{ $m->attachment_name }}</div>
                                            @else
                                                <a href="{{ $m->attachment_url }}" target="_blank" download="{{ $m->attachment_name }}" class="inline-flex items-center gap-3 p-2.5 rounded-xl border text-xs font-bold transition {{ $m->is_admin ? 'bg-gray-50 border-gray-200 text-gray-800 hover:bg-gray-100' : 'bg-teal-800/40 border-teal-500 text-white hover:bg-teal-800/60' }}">
                                                    <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center flex-shrink-0">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                    </div>
                                                    <div class="overflow-hidden">
                                                        <div class="truncate text-xs font-bold">{{ $m->attachment_name }}</div>
                                                        <div class="text-[10px] opacity-75 font-normal">Click to Download</div>
                                                    </div>
                                                </a>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Reply Composer Bar -->
                    <div class="p-4 sm:p-5 border-t border-gray-100 bg-white">
                        @if($currentTicket->status !== 'open')
                            <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200/80 text-center text-xs font-bold text-amber-900 flex flex-col sm:flex-row items-center justify-center gap-3">
                                <span>This ticket is currently marked as closed.</span>
                                <form action="/tickets/{{ $currentTicket->id }}/reopen" method="POST">
                                    @csrf
                                    <button type="submit" class="px-4 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-black shadow-sm transition">
                                        Reopen Ticket to Reply
                                    </button>
                                </form>
                            </div>
                        @else
                            <!-- 1-Click Quick Reply Suggestions -->
                            <div class="flex items-center gap-1.5 pb-3 overflow-x-auto text-[11px] font-semibold text-gray-600">
                                <span class="text-gray-400 font-bold whitespace-nowrap text-[10px] uppercase">Quick reply:</span>
                                <button type="button" @click="insertQuickReply('Thank you so much, this has been resolved!')" class="px-2.5 py-1 rounded-full bg-gray-100 hover:bg-teal-50 hover:text-teal-800 transition whitespace-nowrap">
                                    "Resolved, thank you!"
                                </button>
                                <button type="button" @click="insertQuickReply('I have uploaded the screenshot and proof above for your verification.')" class="px-2.5 py-1 rounded-full bg-gray-100 hover:bg-teal-50 hover:text-teal-800 transition whitespace-nowrap">
                                    "Screenshot uploaded"
                                </button>
                                <button type="button" @click="insertQuickReply('Still experiencing the issue, please check the status.')" class="px-2.5 py-1 rounded-full bg-gray-100 hover:bg-teal-50 hover:text-teal-800 transition whitespace-nowrap">
                                    "Still experiencing issue"
                                </button>
                                <button type="button" @click="insertQuickReply('Could you please verify my wallet balance and funding transaction?')" class="px-2.5 py-1 rounded-full bg-gray-100 hover:bg-teal-50 hover:text-teal-800 transition whitespace-nowrap">
                                    "Check wallet status"
                                </button>
                            </div>

                            <form action="/tickets/{{ $currentTicket->id }}/reply" method="POST" enctype="multipart/form-data" class="space-y-3" @submit="isSubmitting = true">
                                @csrf
                                <div class="relative">
                                    <textarea name="message" x-model="replyText" @paste="handleClipboardPaste($event)" rows="3" class="w-full rounded-2xl border-2 border-gray-200 px-4 py-3 focus:outline-none focus:border-[#0d9488] font-medium text-xs sm:text-sm text-gray-900 placeholder:text-gray-400 transition" placeholder="Type your response here... (You can also press Ctrl+V / Cmd+V to paste screenshots directly)"></textarea>
                                </div>

                                <!-- File attachment selector & actions -->
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <label class="cursor-pointer inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold transition">
                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                            <span x-text="replyFileName ? 'File attached' : 'Attach file/photo'"></span>
                                            <input type="file" name="attachment" x-ref="replyFileInput" @change="handleFileSelect($event)" class="hidden" accept="image/*,.pdf,.doc,.docx,.zip,.txt">
                                        </label>

                                        <!-- Attachment preview chip -->
                                        <template x-if="replyFileName">
                                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-teal-50 border border-teal-200 text-teal-800 text-xs font-bold max-w-[200px]">
                                                <span class="truncate" x-text="replyFileName"></span>
                                                <button type="button" @click="removeReplyFile()" class="text-rose-500 hover:text-rose-700">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                </button>
                                            </div>
                                        </template>
                                    </div>

                                    <button type="submit" :disabled="isSubmitting" class="px-6 py-2.5 rounded-xl bg-[#0d9488] hover:bg-teal-700 text-white text-xs font-black shadow-md transition flex items-center justify-center gap-2 active:scale-95 disabled:opacity-60">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                                        <span x-text="isSubmitting ? 'Sending...' : 'Send Reply'"></span>
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            @endif

            <!-- STATE 2: NEW TICKET COMPOSER (WHEN NO TICKET IS ACTIVE OR CLICKED "+ NEW TICKET") -->
            <div x-show="!activeTicketId || mode === 'new_ticket'" class="bg-white rounded-3xl border border-gray-200/90 shadow-sm overflow-hidden" style="{{ $currentTicket ? 'display: none;' : '' }}">
                
                <!-- Composer Header & Switcher -->
                <div class="p-6 border-b border-gray-100 bg-gray-50/70">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-teal-500"></span>
                                <h2 class="text-lg font-black text-gray-900 tracking-tight">Create Support Request</h2>
                            </div>
                            <p class="text-xs text-gray-500 font-medium mt-1">Select an issue shortcut below or write your question. We typically reply in under 15 minutes.</p>
                        </div>

                        <!-- Mode Switcher (General vs Sell Logs) -->
                        <div class="flex items-center gap-1.5 p-1 bg-gray-200/70 rounded-xl self-start sm:self-auto">
                            <button type="button" @click="composerMode = 'general'" class="px-3.5 py-1.5 rounded-lg text-xs font-black transition" :class="composerMode === 'general' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900'">
                                General Support
                            </button>
                            <button type="button" @click="composerMode = 'sell_logs'" class="px-3.5 py-1.5 rounded-lg text-xs font-black transition" :class="composerMode === 'sell_logs' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900'">
                                Sell Logs to May22
                            </button>
                        </div>
                    </div>

                    <!-- 1-TAP ISSUE PRESETS (FOR FAST CREATION) -->
                    <div x-show="composerMode === 'general'" class="mt-4 pt-3.5 border-t border-gray-200/60">
                        <div class="text-[11px] font-black uppercase tracking-wider text-gray-400 mb-2">1-Click Common Topic Shortcuts:</div>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" @click="applyPreset('sms_delay')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-gray-200 bg-white hover:border-[#0d9488] hover:bg-teal-50/50 text-xs font-bold text-gray-800 transition active:scale-95 shadow-2xs">
                                <svg class="w-3.5 h-3.5 text-[#0d9488]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                <span>OTP / SMS Not Arrived</span>
                            </button>

                            <button type="button" @click="applyPreset('funding_delay')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-gray-200 bg-white hover:border-[#0d9488] hover:bg-teal-50/50 text-xs font-bold text-gray-800 transition active:scale-95 shadow-2xs">
                                <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span>Wallet Top-Up Pending</span>
                            </button>

                            <button type="button" @click="applyPreset('log_replace')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-gray-200 bg-white hover:border-[#0d9488] hover:bg-teal-50/50 text-xs font-bold text-gray-800 transition active:scale-95 shadow-2xs">
                                <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                <span>Logs Account Issue</span>
                            </button>

                            <button type="button" @click="applyPreset('social_boost')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-gray-200 bg-white hover:border-[#0d9488] hover:bg-teal-50/50 text-xs font-bold text-gray-800 transition active:scale-95 shadow-2xs">
                                <svg class="w-3.5 h-3.5 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                <span>Social Boost Progress</span>
                            </button>

                            <button type="button" @click="applyPreset('utilities')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-gray-200 bg-white hover:border-[#0d9488] hover:bg-teal-50/50 text-xs font-bold text-gray-800 transition active:scale-95 shadow-2xs">
                                <svg class="w-3.5 h-3.5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                <span>Airtime / Data / Electricity</span>
                            </button>
                        </div>
                    </div>

                    <!-- 1-CLICK RECENT ORDER LINKER -->
                    @php
                        $hasRecentOrders = ($recentSms->isNotEmpty() || $recentFunding->isNotEmpty() || $recentLogs->isNotEmpty() || $recentUtilities->isNotEmpty());
                    @endphp
                    @if($hasRecentOrders)
                        <div x-show="composerMode === 'general'" class="mt-3.5 pt-3 border-t border-gray-200/60">
                            <div class="text-[11px] font-black uppercase tracking-wider text-gray-400 mb-2">1-Click Link Recent Order to Ticket:</div>
                            <div class="flex flex-wrap gap-2">
                                @foreach($recentSms as $ord)
                                    <button type="button" @click="linkOrder('SMS Order: {{ $ord->order_id }} ({{ $ord->product }} - {{ $ord->phone_number }})')" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-teal-50 border border-teal-200/80 text-teal-900 text-[11px] font-bold hover:bg-teal-100 transition">
                                        <svg class="w-3 h-3 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                        <span>SMS: {{ $ord->product }} (#{{ $ord->order_id }})</span>
                                    </button>
                                @endforeach

                                @foreach($recentFunding as $f)
                                    <button type="button" @click="linkOrder('Funding Deposit Ref: {{ $f->reference }} (${{ number_format($f->amount_usd, 2) }} - {{ $f->payment_method }})')" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-50 border border-amber-200/80 text-amber-900 text-[11px] font-bold hover:bg-amber-100 transition">
                                        <svg class="w-3 h-3 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <span>Deposit: ${{ number_format($f->amount_usd, 2) }} (#{{ Str::limit($f->reference, 10) }})</span>
                                    </button>
                                @endforeach

                                @foreach($recentLogs as $lg)
                                    <button type="button" @click="linkOrder('Log Order #{{ $lg->id }} ({{ $lg->total_items }} items - ${{ number_format($lg->total_amount, 2) }})')" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-50 border border-indigo-200/80 text-indigo-900 text-[11px] font-bold hover:bg-indigo-100 transition">
                                        <svg class="w-3 h-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                        <span>Logs: Order #{{ $lg->id }}</span>
                                    </button>
                                @endforeach

                                @foreach($recentUtilities as $u)
                                    <button type="button" @click="linkOrder('Utility Purchase Ref: {{ $u->reference }} ({{ $u->product_name }} - ${{ number_format($u->total_charged_usd, 2) }})')" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-teal-50 border border-teal-200/80 text-teal-900 text-[11px] font-bold hover:bg-teal-100 transition">
                                        <svg class="w-3 h-3 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                        <span>VTU: {{ $u->product_name }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- GENERAL SUPPORT FORM -->
                <form action="/tickets" method="POST" enctype="multipart/form-data" class="p-6 space-y-4" x-show="composerMode === 'general'">
                    @csrf
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">Category</label>
                            <select name="category" x-model="category" class="w-full rounded-2xl border-2 border-gray-200 px-4 py-3 focus:outline-none focus:border-[#0d9488] font-bold text-xs sm:text-sm text-gray-900 transition">
                                <option value="virtual_numbers">Virtual Numbers / SMS Delivery</option>
                                <option value="sms_delivery">OTP &amp; Countdown Issues</option>
                                <option value="billing">Billing &amp; Wallet Funding</option>
                                <option value="account">Account &amp; Security</option>
                                <option value="general">General Question</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">Priority</label>
                            <select name="priority" x-model="priority" class="w-full rounded-2xl border-2 border-gray-200 px-4 py-3 focus:outline-none focus:border-[#0d9488] font-bold text-xs sm:text-sm text-gray-900 transition">
                                <option value="normal">Normal Priority</option>
                                <option value="high">High Priority</option>
                                <option value="urgent">Urgent Resolution</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">Subject</label>
                        <input type="text" name="subject" x-model="subject" class="w-full rounded-2xl border-2 border-gray-200 px-4 py-3 focus:outline-none focus:border-[#0d9488] font-bold text-xs sm:text-sm text-gray-900 placeholder:text-gray-400 transition" placeholder="Brief summary of your question or issue" required>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">Message / Details</label>
                        <textarea name="message" x-model="message" rows="5" class="w-full rounded-2xl border-2 border-gray-200 px-4 py-3 focus:outline-none focus:border-[#0d9488] font-medium text-xs sm:text-sm text-gray-900 placeholder:text-gray-400 transition" placeholder="Provide full details, order references, or phone numbers so we can investigate immediately..." required></textarea>
                    </div>

                    <!-- Attachment Upload with Drag-and-Drop & Clipboard Preview -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">Attach Screenshot or Document (Optional)</label>
                        <div class="relative border-2 border-dashed border-gray-300 rounded-2xl p-4 text-center hover:border-[#0d9488] transition bg-gray-50/50">
                            <input type="file" name="attachment" x-ref="newTicketFileInput" @change="handleNewFileSelect($event)" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" accept="image/*,.pdf,.doc,.docx,.zip,.txt">
                            
                            <template x-if="!newFileName">
                                <div class="flex flex-col items-center justify-center gap-1.5 py-2">
                                    <div class="w-10 h-10 rounded-xl bg-teal-50 text-[#0d9488] flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </div>
                                    <span class="text-xs font-bold text-gray-700">Click or drag screenshot / document here</span>
                                    <span class="text-[10px] text-gray-400">PNG, JPG, PDF, TXT (Max 10MB)</span>
                                </div>
                            </template>

                            <template x-if="newFileName">
                                <div class="flex items-center justify-between gap-3 bg-white p-3 rounded-xl border border-teal-200 z-20 relative">
                                    <div class="flex items-center gap-3 overflow-hidden">
                                        <template x-if="newFileIsImage && newFilePreview">
                                            <img :src="newFilePreview" class="w-11 h-11 object-cover rounded-lg border border-gray-200 flex-shrink-0">
                                        </template>
                                        <template x-if="!newFileIsImage">
                                            <div class="w-11 h-11 rounded-lg bg-teal-50 text-[#0d9488] flex items-center justify-center font-black text-xs flex-shrink-0">DOC</div>
                                        </template>
                                        <span class="text-xs font-bold text-gray-900 truncate" x-text="newFileName"></span>
                                    </div>
                                    <button type="button" @click.stop="removeNewFile()" class="p-1.5 rounded-full hover:bg-rose-50 text-rose-500 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full bg-[#0d9488] hover:bg-teal-700 text-white px-6 py-3.5 rounded-2xl font-black text-sm shadow-md transition flex items-center justify-center gap-2 active:scale-98">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                            <span>Send Support Request to Agents</span>
                        </button>
                    </div>
                </form>

                <!-- SELL LOGS FORM -->
                <form action="/tickets" method="POST" enctype="multipart/form-data" class="p-6 space-y-4" x-show="composerMode === 'sell_logs'" style="display:none;">
                    @csrf
                    <input type="hidden" name="subject" :value="sellLogsSubject">
                    <input type="hidden" name="message" :value="sellLogsMessage">
                    <input type="hidden" name="category" value="sell_logs">
                    <input type="hidden" name="priority" value="high">

                    <div class="p-4 rounded-2xl bg-teal-50 border border-teal-200 text-teal-900 text-xs font-semibold leading-relaxed">
                        May22SMS purchases high quality social logs (Facebook, Instagram, Twitter/X, TikTok, YouTube). Fill in your batch details below for fast admin appraisal and payout.
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">Platform</label>
                            <select x-model="sellPlatform" class="w-full rounded-2xl border-2 border-gray-200 px-4 py-3 focus:outline-none focus:border-[#0d9488] font-bold text-xs sm:text-sm text-gray-900 transition" required>
                                <option value="">Select platform</option>
                                <option value="facebook">Facebook</option>
                                <option value="instagram">Instagram</option>
                                <option value="twitter">Twitter / X</option>
                                <option value="youtube">YouTube</option>
                                <option value="tiktok">TikTok</option>
                                <option value="telegram">Telegram</option>
                                <option value="other">Other Platform</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">Type of Logs</label>
                            <input type="text" x-model="sellLogType" class="w-full rounded-2xl border-2 border-gray-200 px-4 py-3 focus:outline-none focus:border-[#0d9488] font-bold text-xs sm:text-sm text-gray-900 placeholder:text-gray-400 transition" placeholder="e.g. aged accounts, cookies, 2FA tokens" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">Quantity</label>
                            <input type="number" min="1" x-model="sellQty" class="w-full rounded-2xl border-2 border-gray-200 px-4 py-3 focus:outline-none focus:border-[#0d9488] font-bold text-xs sm:text-sm text-gray-900 placeholder:text-gray-400 transition" placeholder="e.g. 50" required>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">Expected Price per Account (USD)</label>
                            <input type="number" min="0" step="0.01" x-model="sellPriceEach" class="w-full rounded-2xl border-2 border-gray-200 px-4 py-3 focus:outline-none focus:border-[#0d9488] font-bold text-xs sm:text-sm text-gray-900 placeholder:text-gray-400 transition" placeholder="e.g. 0.50" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">Delivery Method</label>
                        <select x-model="sellDelivery" class="w-full rounded-2xl border-2 border-gray-200 px-4 py-3 focus:outline-none focus:border-[#0d9488] font-bold text-xs sm:text-sm text-gray-900 transition">
                            <option value="paste_now">Paste sample now (in form)</option>
                            <option value="send_later">Send full batch after admin confirms price</option>
                        </select>
                    </div>

                    <div x-show="sellDelivery === 'paste_now'">
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">Sample Logs Format</label>
                        <textarea x-model="sellSample" rows="3" class="w-full rounded-2xl border-2 border-gray-200 px-4 py-3 focus:outline-none focus:border-[#0d9488] font-medium text-xs sm:text-sm text-gray-900 placeholder:text-gray-400 transition" placeholder="Paste sample logs format (one per line)"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">Additional Notes (Optional)</label>
                        <textarea x-model="sellNotes" rows="2" class="w-full rounded-2xl border-2 border-gray-200 px-4 py-3 focus:outline-none focus:border-[#0d9488] font-medium text-xs sm:text-sm text-gray-900 placeholder:text-gray-400 transition" placeholder="Country, age, guarantees, cookies..."></textarea>
                    </div>

                    <!-- Attachment proof for Sell Logs -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-1.5">Attach Sample File / Proof (Optional)</label>
                        <div class="relative border-2 border-dashed border-gray-300 rounded-2xl p-4 text-center hover:border-[#0d9488] transition bg-gray-50/50">
                            <input type="file" name="attachment" x-ref="sellTicketFileInput" @change="handleNewFileSelect($event)" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" accept="image/*,.pdf,.doc,.docx,.zip,.txt">
                            <template x-if="!newFileName">
                                <span class="text-xs font-bold text-gray-600">Attach sample TXT, ZIP, or proof screenshot (Max 10MB)</span>
                            </template>
                            <template x-if="newFileName">
                                <span class="text-xs font-bold text-teal-800" x-text="newFileName"></span>
                            </template>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full bg-[#0d9488] hover:bg-teal-700 text-white px-6 py-3.5 rounded-2xl font-black text-sm shadow-md transition flex items-center justify-center gap-2 active:scale-98" :disabled="!sellPlatform || !sellLogType || !sellQty || !sellPriceEach">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                            <span>Send Sell Request to May22SMS Admin</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Self-Help & Instant Answers Accordion Section -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-200/90 shadow-sm mt-8" x-data="{ openFaq: null }">
        <div class="flex items-center gap-2 pb-4 border-b border-gray-100">
            <span class="w-2.5 h-2.5 rounded-full bg-teal-500"></span>
            <h3 class="text-base sm:text-lg font-black text-gray-900 tracking-tight">Instant Self-Help Answers</h3>
            <span class="text-xs font-bold text-gray-400 ml-auto">Frequently Asked Questions</span>
        </div>

        <div class="divide-y divide-gray-100 mt-2">
            <!-- FAQ 1 -->
            <div class="py-3.5">
                <button type="button" @click="openFaq = (openFaq === 1 ? null : 1)" class="w-full flex items-center justify-between text-left text-xs sm:text-sm font-black text-gray-800 hover:text-[#0d9488] transition">
                    <span>What happens if an SMS OTP doesn't arrive before the countdown timer expires?</span>
                    <svg class="w-4 h-4 text-gray-400 transition-transform" :class="openFaq === 1 ? 'rotate-180 text-teal-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div x-show="openFaq === 1" class="mt-2 text-xs text-gray-600 font-medium leading-relaxed pl-2 border-l-2 border-teal-500" style="display:none;">
                    If the virtual phone number does not receive a code within the countdown window (usually 15–20 minutes), your wallet balance is <strong>automatically refunded 100%</strong>. You are never charged for unreceived verification codes.
                </div>
            </div>

            <!-- FAQ 2 -->
            <div class="py-3.5">
                <button type="button" @click="openFaq = (openFaq === 2 ? null : 2)" class="w-full flex items-center justify-between text-left text-xs sm:text-sm font-black text-gray-800 hover:text-[#0d9488] transition">
                    <span>How long does wallet funding take to reflect on my account?</span>
                    <svg class="w-4 h-4 text-gray-400 transition-transform" :class="openFaq === 2 ? 'rotate-180 text-teal-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div x-show="openFaq === 2" class="mt-2 text-xs text-gray-600 font-medium leading-relaxed pl-2 border-l-2 border-teal-500" style="display:none;">
                    Automated gateways (Monnify, Paystack, Korapay) credit your wallet instantly within 30–60 seconds. Cryptocurrency payments reflect once required blockchain confirmations are achieved (typically 1–3 network blocks).
                </div>
            </div>

            <!-- FAQ 3 -->
            <div class="py-3.5">
                <button type="button" @click="openFaq = (openFaq === 3 ? null : 3)" class="w-full flex items-center justify-between text-left text-xs sm:text-sm font-black text-gray-800 hover:text-[#0d9488] transition">
                    <span>What is the replacement policy for purchased social accounts (Logs)?</span>
                    <svg class="w-4 h-4 text-gray-400 transition-transform" :class="openFaq === 3 ? 'rotate-180 text-teal-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div x-show="openFaq === 3" class="mt-2 text-xs text-gray-600 font-medium leading-relaxed pl-2 border-l-2 border-teal-500" style="display:none;">
                    All accounts in the Logs Store are tested for valid credentials prior to delivery. If you experience an invalid login upon initial receipt, please file a ticket with your Order Reference within 24 hours of purchase for prompt replacement or credit.
                </div>
            </div>
        </div>
    </div>

    <!-- Image Lightbox Modal -->
    <template x-if="lightboxImg">
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" @click="lightboxImg = null">
            <div class="relative max-w-4xl max-h-[90vh] overflow-hidden rounded-3xl bg-black p-2 shadow-2xl" @click.stop>
                <button type="button" @click="lightboxImg = null" class="absolute top-4 right-4 z-10 rounded-full bg-black/70 p-2 text-white hover:bg-black transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
                <img :src="lightboxImg" class="max-h-[85vh] w-auto object-contain rounded-2xl mx-auto">
            </div>
        </div>
    </template>
</div>

<script>
function ticketsHub(config) {
    return {
        activeTicketId: config.activeTicketId,
        mode: config.initialMode,
        composerMode: 'general',
        category: 'virtual_numbers',
        priority: 'normal',
        subject: '',
        message: '',
        replyText: '',
        replyFileName: '',
        newFileName: '',
        newFilePreview: null,
        newFileIsImage: false,
        lightboxImg: null,
        isSubmitting: false,

        // Sell logs fields
        sellPlatform: '',
        sellLogType: '',
        sellQty: '',
        sellPriceEach: '',
        sellDelivery: 'paste_now',
        sellNotes: '',
        sellSample: '',

        init() {
            // Scroll conversation feed to bottom if open
            this.$nextTick(() => {
                this.scrollToBottom();
            });

            // Start polling if an active ticket is open
            if (this.activeTicketId) {
                this.startPolling();
            }
        },

        startNewTicket() {
            this.mode = 'new_ticket';
            this.activeTicketId = null;
            // Update URL without full refresh if browser supports pushState
            if (window.history.pushState) {
                const newUrl = window.location.pathname;
                window.history.pushState({ path: newUrl }, '', newUrl);
            }
        },

        scrollToBottom() {
            const el = document.getElementById('messagesFeed');
            if (el) {
                el.scrollTop = el.scrollHeight;
            }
        },

        startPolling() {
            // Poll for new messages every 12 seconds
            setInterval(() => {
                if (!this.activeTicketId) return;
                fetch(`/tickets/${this.activeTicketId}/messages`, {
                    headers: { 'Accept': 'application/json' }
                })
                .then(r => r.json())
                .then(data => {
                    if (data && data.status === 'success' && data.messages) {
                        // Compare count of rendered messages with server count
                        const feed = document.getElementById('messagesFeed');
                        if (!feed) return;
                        const currentCount = feed.querySelectorAll('.max-w-\\[88\\%\\]').length;
                        if (data.messages.length > currentCount) {
                            // Reload ticket view smoothly to render updated conversation
                            window.location.reload();
                        }
                    }
                })
                .catch(() => {});
            }, 12000);
        },

        applyPreset(presetKey) {
            if (presetKey === 'sms_delay') {
                this.category = 'virtual_numbers';
                this.priority = 'high';
                this.subject = 'OTP Delivery Issue on Virtual Phone Number';
                this.message = 'Target Service (e.g. WhatsApp / Telegram):\nPhone Number:\nCountdown status:\nIssue description: I did not receive the SMS code within the timer...';
            } else if (presetKey === 'funding_delay') {
                this.category = 'billing';
                this.priority = 'high';
                this.subject = 'Wallet Funding / Top-up Deposit Delay';
                this.message = 'Payment Method (Monnify / Paystack / Crypto / Korapay):\nDeposit Amount (USD / NGN):\nTransaction Reference / Hash:\nTime Sent:';
            } else if (presetKey === 'log_replace') {
                this.category = 'general';
                this.priority = 'normal';
                this.subject = 'Logs Account Warranty & Login Issue';
                this.message = 'Log Order Reference:\nAccount Username / Email:\nIssue Encountered (Wrong Password / 2FA Locked / Disabled):';
            } else if (presetKey === 'social_boost') {
                this.category = 'general';
                this.priority = 'normal';
                this.subject = 'Social Boosting Order Progress Inquiry';
                this.message = 'Boost Order ID:\nPlatform & Target Profile Link:\nIssue (Stuck / Speed / Drop):';
            } else if (presetKey === 'utilities') {
                this.category = 'billing';
                this.priority = 'normal';
                this.subject = 'VTU Airtime / Data / Electricity Purchase';
                this.message = 'Utility Service (Airtime / Data / Meter / Betting):\nBeneficiary Phone / Meter Number:\nTransaction Reference:';
            }
        },

        linkOrder(orderInfo) {
            if (!this.subject) {
                this.subject = 'Inquiry: ' + orderInfo;
            } else if (!this.subject.includes(orderInfo)) {
                this.subject += ' [' + orderInfo + ']';
            }

            if (!this.message.includes(orderInfo)) {
                this.message = 'Reference: ' + orderInfo + '\n\n' + this.message;
            }
        },

        insertQuickReply(text) {
            this.replyText = (this.replyText ? this.replyText + ' ' : '') + text;
        },

        copyTicketRef(text) {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(() => {
                    alert('Copied to clipboard: ' + text);
                });
            }
        },

        handleClipboardPaste(e) {
            const items = (e.clipboardData || e.originalEvent.clipboardData).items;
            for (let index in items) {
                const item = items[index];
                if (item.kind === 'file' && item.type.startsWith('image/')) {
                    const blob = item.getAsFile();
                    if (this.$refs.replyFileInput) {
                        const file = new File([blob], 'screenshot_pasted_' + Date.now() + '.png', { type: blob.type });
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(file);
                        this.$refs.replyFileInput.files = dataTransfer.files;
                        this.replyFileName = file.name;
                    }
                    break;
                }
            }
        },

        handleFileSelect(e) {
            const file = e.target.files[0];
            this.replyFileName = file ? file.name : '';
        },

        removeReplyFile() {
            this.replyFileName = '';
            if (this.$refs.replyFileInput) {
                this.$refs.replyFileInput.value = '';
            }
        },

        handleNewFileSelect(e) {
            const file = e.target.files[0];
            if (!file) {
                this.newFileName = '';
                this.newFilePreview = null;
                this.newFileIsImage = false;
                return;
            }
            this.newFileName = file.name;
            if (file.type.startsWith('image/')) {
                this.newFileIsImage = true;
                const reader = new FileReader();
                reader.onload = (evt) => {
                    this.newFilePreview = evt.target.result;
                };
                reader.readAsDataURL(file);
            } else {
                this.newFileIsImage = false;
                this.newFilePreview = null;
            }
        },

        removeNewFile() {
            this.newFileName = '';
            this.newFilePreview = null;
            this.newFileIsImage = false;
            if (this.$refs.newTicketFileInput) {
                this.$refs.newTicketFileInput.value = '';
            }
            if (this.$refs.sellTicketFileInput) {
                this.$refs.sellTicketFileInput.value = '';
            }
        },

        get sellLogsSubject() {
            const p = (this.sellPlatform || 'Logs').trim();
            const q = (this.sellQty || '').toString().trim();
            const t = (this.sellLogType || 'Accounts').trim();
            return `Sell Logs - ${p}${q ? ' (' + q + ' items)' : ''} - ${t}`;
        },

        get sellLogsMessage() {
            const lines = [];
            lines.push('=== SELL LOGS PROPOSAL ===');
            lines.push('Platform: ' + (this.sellPlatform || '-'));
            lines.push('Log Type: ' + (this.sellLogType || '-'));
            lines.push('Quantity: ' + (this.sellQty || '-'));
            lines.push('Asking Price per Item (USD): $' + (this.sellPriceEach || '-'));
            lines.push('Delivery Mode: ' + (this.sellDelivery === 'paste_now' ? 'Paste sample format now' : 'Send full batch after admin confirms'));
            lines.push('');
            if (this.sellNotes) {
                lines.push('Notes & Guarantees: ' + this.sellNotes);
                lines.push('');
            }
            if (this.sellDelivery === 'paste_now' && this.sellSample) {
                lines.push('Sample Format:');
                lines.push(this.sellSample);
                lines.push('');
            }
            lines.push('Please review and reply with price confirmation and delivery instructions.');
            return lines.join('\n');
        }
    };
}
</script>
@endsection
