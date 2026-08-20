@extends('layouts.admin')

@section('admin_content')
@php
    $dt = $tx->created_at ? \Illuminate\Support\Carbon::parse($tx->created_at) : null;
    $revertedAt = $tx->reverted_at ? \Illuminate\Support\Carbon::parse($tx->reverted_at) : null;
    $approvedAt = $tx->approved_at ? \Illuminate\Support\Carbon::parse($tx->approved_at) : null;
    $meta = is_array($tx->meta ?? null) ? $tx->meta : [];
@endphp

<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <div class="text-sm font-bold text-gray-500"><a href="/admin/transactions" class="hover:text-gray-900">Transactions</a> / #{{ $tx->id }}</div>
        <h2 class="text-2xl font-extrabold text-gray-900">{{ $tx->type }} · {{ $tx->action }}</h2>
        <div class="text-sm font-bold text-gray-500">{{ $dt ? $dt->format('M j, Y H:i') : '—' }}</div>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="/admin/users/{{ $tx->user_id }}" class="rounded-xl bg-gray-100 px-4 py-2 text-sm font-extrabold text-gray-900 hover:bg-gray-200 transition">User</a>
        @if($tx->reversible && !$tx->reverted_at)
            <form action="/admin/transactions/{{ $tx->id }}/revert" method="POST">
                @csrf
                <button type="submit" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-extrabold text-white hover:bg-red-700 transition" onclick="return confirm('Revert this transaction?');">Revert</button>
            </form>
        @endif
        @if(!$tx->approved_at)
            <form action="/admin/transactions/{{ $tx->id }}/approve" method="POST">
                @csrf
                <button type="submit" class="rounded-xl bg-[#0d9488] px-4 py-2 text-sm font-extrabold text-white hover:bg-[#0f766e] transition">Approve</button>
            </form>
        @endif
        <form action="/admin/transactions/{{ $tx->id }}" method="POST" onsubmit="return confirm('Remove this record from the log?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="rounded-xl bg-gray-900 px-4 py-2 text-sm font-extrabold text-white hover:bg-gray-800 transition">Delete</button>
        </form>
    </div>
</div>

@if(session('success'))
    <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl shadow-sm">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl shadow-sm">
        {{ session('error') }}
    </div>
@endif

@if($errors->any())
    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl shadow-sm">
        <ul class="list-disc pl-5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <div class="xl:col-span-2 space-y-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <div class="text-lg font-extrabold text-gray-900">Details</div>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="rounded-2xl border border-gray-100 bg-gray-50 p-5">
                    <div class="text-xs font-extrabold text-gray-500 uppercase">User</div>
                    <div class="mt-2 text-sm font-extrabold text-gray-900">{{ $tx->user->name ?? ('#' . ($tx->user_id ?? '')) }}</div>
                    <div class="text-xs font-bold text-gray-500">{{ $tx->user->email ?? '' }}</div>
                </div>
                <div class="rounded-2xl border border-gray-100 bg-gray-50 p-5">
                    <div class="text-xs font-extrabold text-gray-500 uppercase">Actor</div>
                    <div class="mt-2 text-sm font-extrabold text-gray-900">{{ $tx->actor ? $tx->actor->name : '—' }}</div>
                    <div class="text-xs font-bold text-gray-500">{{ $tx->actor ? $tx->actor->email : '' }}</div>
                </div>
                <div class="rounded-2xl border border-gray-100 bg-gray-50 p-5">
                    <div class="text-xs font-extrabold text-gray-500 uppercase">Reference</div>
                    <div class="mt-2 text-sm font-extrabold text-gray-900 break-all">{{ $tx->reference ?? '—' }}</div>
                </div>
                <div class="rounded-2xl border border-gray-100 bg-gray-50 p-5">
                    <div class="text-xs font-extrabold text-gray-500 uppercase">Amount</div>
                    <div class="mt-2 text-sm font-extrabold text-gray-900">
                        @if($tx->amount_usd !== null)
                            {{ (float) $tx->amount_usd >= 0 ? '+' : '' }}${{ number_format((float) $tx->amount_usd, 2) }}
                        @else
                            —
                        @endif
                    </div>
                </div>
                <div class="rounded-2xl border border-gray-100 bg-gray-50 p-5">
                    <div class="text-xs font-extrabold text-gray-500 uppercase">Subject</div>
                    <div class="mt-2 text-xs font-bold text-gray-700 break-all">{{ $tx->subject_type ?? '—' }}</div>
                    <div class="text-sm font-extrabold text-gray-900">{{ $tx->subject_id ?? '—' }}</div>
                </div>
                <div class="rounded-2xl border border-gray-100 bg-gray-50 p-5">
                    <div class="text-xs font-extrabold text-gray-500 uppercase">Status</div>
                    <div class="mt-2 text-sm font-extrabold text-gray-900">{{ $tx->status ?? '—' }}</div>
                    <div class="text-xs font-bold text-gray-500">
                        Approved: {{ $approvedAt ? $approvedAt->format('M j, Y H:i') : '—' }} ·
                        Reverted: {{ $revertedAt ? $revertedAt->format('M j, Y H:i') : '—' }}
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <div class="text-lg font-extrabold text-gray-900">Payload</div>
                <div class="text-sm font-bold text-gray-500">Before / After / Meta</div>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <div class="text-xs font-extrabold text-gray-500 uppercase mb-2">Before</div>
                    <pre class="text-xs bg-gray-50 border border-gray-100 rounded-xl p-4 overflow-x-auto">{{ json_encode($tx->before, JSON_PRETTY_PRINT) }}</pre>
                </div>
                <div>
                    <div class="text-xs font-extrabold text-gray-500 uppercase mb-2">After</div>
                    <pre class="text-xs bg-gray-50 border border-gray-100 rounded-xl p-4 overflow-x-auto">{{ json_encode($tx->after, JSON_PRETTY_PRINT) }}</pre>
                </div>
                <div>
                    <div class="text-xs font-extrabold text-gray-500 uppercase mb-2">Meta</div>
                    <pre class="text-xs bg-gray-50 border border-gray-100 rounded-xl p-4 overflow-x-auto">{{ json_encode($meta, JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>
        </div>
    </div>

    <div class="xl:col-span-1 space-y-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <div class="text-lg font-extrabold text-gray-900">Manage</div>
            </div>
            <div class="p-6">
                <form action="/admin/transactions/{{ $tx->id }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <div class="text-xs font-extrabold text-gray-500 uppercase mb-1">Status</div>
                        <input name="status" value="{{ old('status', $tx->status) }}" class="w-full rounded-xl border-gray-300 focus:border-[#0d9488] focus:ring focus:ring-teal-200 focus:ring-opacity-50 font-bold" placeholder="pending, approved, reverted...">
                    </div>
                    <div>
                        <div class="text-xs font-extrabold text-gray-500 uppercase mb-1">Admin Note</div>
                        <textarea name="note" rows="3" class="w-full rounded-xl border-gray-300 focus:border-[#0d9488] focus:ring focus:ring-teal-200 focus:ring-opacity-50 font-bold" placeholder="Why you edited this record...">{{ old('note', (string) ($meta['admin_note'] ?? '')) }}</textarea>
                    </div>
                    <button type="submit" class="w-full bg-gray-900 text-white px-4 py-3 rounded-xl font-extrabold hover:bg-gray-800 transition">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

