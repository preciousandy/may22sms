@extends('layouts.admin')

@section('admin_content')
@push('header_title')
    Support Tickets & Inquiries
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

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-xl font-black text-gray-900">User Support Tickets</h2>
        <p class="text-xs font-bold text-gray-500 mt-0.5">Manage customer inquiries, seller applications, and helpdesk tickets.</p>
    </div>

    <div class="flex flex-wrap items-center gap-1.5">
        @php
            $statusQ = (string) ($status ?? 'open');
            $typeQ = (string) ($type ?? 'all');
        @endphp
        <a href="/admin/tickets?status={{ $statusQ }}&type=all" class="px-3.5 py-2 rounded-xl text-xs font-extrabold transition {{ $typeQ === 'all' ? 'bg-gray-900 text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">All Types</a>
        <a href="/admin/tickets?status={{ $statusQ }}&type=support" class="px-3.5 py-2 rounded-xl text-xs font-extrabold transition {{ $typeQ === 'support' ? 'bg-gray-900 text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">Support</a>
        <a href="/admin/tickets?status={{ $statusQ }}&type=sell_logs" class="px-3.5 py-2 rounded-xl text-xs font-extrabold transition {{ $typeQ === 'sell_logs' ? 'bg-gray-900 text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">Sell Logs</a>

        <span class="mx-1 hidden sm:inline text-gray-300">|</span>

        <a href="/admin/tickets?status=open&type={{ $typeQ }}" class="px-3.5 py-2 rounded-xl text-xs font-extrabold transition {{ $statusQ === 'open' ? 'bg-[#0d9488] text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">Open</a>
        <a href="/admin/tickets?status=closed&type={{ $typeQ }}" class="px-3.5 py-2 rounded-xl text-xs font-extrabold transition {{ $statusQ === 'closed' ? 'bg-[#0d9488] text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">Closed</a>
        <a href="/admin/tickets?status=all&type={{ $typeQ }}" class="px-3.5 py-2 rounded-xl text-xs font-extrabold transition {{ $statusQ === 'all' ? 'bg-[#0d9488] text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">All Statuses</a>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    @if($tickets->count() === 0)
        <div class="p-12 text-center text-gray-500 font-bold">No support tickets found.</div>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Last Activity</th>
                        <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">User Account</th>
                        <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Subject & Category</th>
                        <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3.5 text-right text-xs font-extrabold text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @foreach($tickets as $t)
                        @php
                            $isSellLogs = is_string($t->subject) && str_starts_with($t->subject, 'Sell Logs -');
                            $isOpen = $t->status === 'open';
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-6 py-4 text-xs font-bold text-gray-600 whitespace-nowrap">
                                {{ ($t->last_message_at ?? $t->updated_at)->format('M d, Y H:i') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                                <a href="/admin/users/{{ $t->user_id }}" class="font-extrabold text-gray-900 hover:text-[#0d9488] transition">User #{{ $t->user_id }}</a>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <div class="font-extrabold text-gray-900">{{ $t->subject }}</div>
                                @if($isSellLogs)
                                    <span class="inline-flex items-center text-[10px] font-black uppercase px-2 py-0.5 rounded bg-amber-100 text-amber-800 mt-1">Sell Logs Application</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full border text-[10px] font-black {{ $isOpen ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-gray-100 text-gray-600 border-gray-200' }}">
                                    {{ strtoupper($t->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <a href="/admin/tickets/{{ $t->id }}" class="inline-flex items-center gap-1 bg-gray-900 hover:bg-gray-800 text-white px-3.5 py-1.5 rounded-xl text-xs font-extrabold transition shadow-sm">
                                    Open Ticket Thread
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-6 py-5 border-t border-gray-100 bg-gray-50">
            {{ $tickets->links() }}
        </div>
    @endif
</div>
@endsection
