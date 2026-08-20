@extends('layouts.admin')

@section('admin_content')
@push('header_title')
    Mail Broadcast Center
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

<div class="mb-6">
    <h2 class="text-xl font-black text-gray-900">Email & Broadcast Center</h2>
    <p class="text-xs font-bold text-gray-500 mt-0.5">Send targeted single notifications or broadcast newsletters to user groups.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Single Email Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col justify-between">
        <div>
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/80">
                <h3 class="text-base font-black text-gray-900">Single User Email</h3>
            </div>
            <form method="POST" action="{{ url('/admin/mail/send-single') }}" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Recipient Email</label>
                    <input type="email" name="email" required class="w-full text-xs font-semibold rounded-xl border border-gray-200 px-3.5 py-2.5 focus:border-[#0d9488] focus:ring-0" placeholder="user@example.com">
                </div>
                <div>
                    <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Subject</label>
                    <input type="text" name="subject" required class="w-full text-xs font-semibold rounded-xl border border-gray-200 px-3.5 py-2.5 focus:border-[#0d9488] focus:ring-0" placeholder="Important notice regarding your account">
                </div>
                <div>
                    <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Email Body</label>
                    <textarea name="body" required rows="6" class="w-full text-xs font-semibold rounded-xl border border-gray-200 p-3 focus:border-[#0d9488] focus:ring-0" placeholder="Type message body here..."></textarea>
                </div>
                <button type="submit" class="w-full bg-[#0d9488] hover:bg-[#0f766e] text-white px-4 py-3 rounded-xl font-black text-xs uppercase tracking-wider transition shadow-md">
                    Send Single Email
                </button>
            </form>
        </div>
    </div>

    <!-- Bulk Broadcast Email Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col justify-between">
        <div>
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/80">
                <h3 class="text-base font-black text-gray-900">Bulk Broadcast Email</h3>
            </div>
            <form method="POST" action="{{ url('/admin/mail/send-bulk') }}" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Target Recipient Group</label>
                    <select name="recipient_group" required class="w-full text-xs font-bold rounded-xl border border-gray-200 px-3.5 py-2.5 focus:border-[#0d9488] focus:ring-0">
                        <option value="all">All Users (Full Audience)</option>
                        <option value="active">Active Accounts Only</option>
                        <option value="verified">Verified Accounts Only</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Subject</label>
                    <input type="text" name="subject" required class="w-full text-xs font-semibold rounded-xl border border-gray-200 px-3.5 py-2.5 focus:border-[#0d9488] focus:ring-0" placeholder="System announcement / Update">
                </div>
                <div>
                    <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Broadcast Body</label>
                    <textarea name="body" required rows="6" class="w-full text-xs font-semibold rounded-xl border border-gray-200 p-3 focus:border-[#0d9488] focus:ring-0" placeholder="Type broadcast message..."></textarea>
                </div>
                <button type="submit" class="w-full bg-gray-900 hover:bg-gray-800 text-white px-4 py-3 rounded-xl font-black text-xs uppercase tracking-wider transition shadow-md">
                    Send Bulk Broadcast
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Email Audit Logs Table -->
@if($logs->count() > 0)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/80 flex items-center justify-between">
            <h3 class="text-base font-black text-gray-900">Recent Broadcast Logs</h3>
            @if(method_exists($logs, 'total'))
                <span class="text-xs font-extrabold text-gray-500">{{ number_format($logs->total()) }} total dispatches</span>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Target</th>
                        <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Recipients</th>
                        <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Subject</th>
                        <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3.5 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Sent Time</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @foreach($logs as $log)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-6 py-4 text-xs">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase {{ $log->recipient_type === 'bulk' ? 'bg-amber-100 text-amber-800' : 'bg-teal-100 text-teal-800' }}">
                                    {{ strtoupper($log->recipient_type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs font-bold text-gray-800">
                                @php
                                    $recipients = is_array($log->recipients) ? $log->recipients : [];
                                    $count = count($recipients);
                                @endphp
                                @if($count === 1)
                                    {{ $recipients[0] ?? '' }}
                                @else
                                    {{ number_format($count) }} recipients
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm font-extrabold text-gray-900">{{ $log->subject }}</td>
                            <td class="px-6 py-4 text-xs">
                                @php
                                    $st = strtolower((string) $log->status);
                                    $badge = $st === 'sent' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : ($st === 'failed' ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-amber-50 text-amber-700 border-amber-200');
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full border text-[10px] font-black {{ $badge }}">{{ strtoupper($st) }}</span>
                            </td>
                            <td class="px-6 py-4 text-xs font-bold text-gray-600 whitespace-nowrap">{{ $log->created_at ? \Illuminate\Support\Carbon::parse($log->created_at)->format('M j, Y H:i') : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if(method_exists($logs, 'links'))
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                {{ $logs->onEachSide(1)->links() }}
            </div>
        @endif
    </div>
@endif
@endsection
