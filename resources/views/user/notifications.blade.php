@extends('layouts.dashboard')

@section('page_title', 'Notifications')

@section('dashboard_content')
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-6 py-5 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
        <div>
            <h3 class="text-lg font-bold text-gray-800">Notifications</h3>
            <p class="text-sm text-gray-500">Updates about your account and orders.</p>
        </div>
        <form action="/notifications/mark-all-read" method="POST">
            @csrf
            <button type="submit" class="bg-gray-900 text-white px-4 py-2 rounded-lg font-bold hover:bg-gray-800 transition">Mark all read</button>
        </form>
    </div>

    @if($notifications->count() === 0)
        <div class="p-10 text-center text-gray-500">No notifications yet.</div>
    @else
        <div class="divide-y divide-gray-100">
            @foreach($notifications as $n)
                @php($data = is_array($n->data ?? null) ? $n->data : [])
                <div class="p-6 flex items-start justify-between gap-4 {{ $n->read_at ? 'bg-white' : 'bg-teal-50/40' }}">
                    <div>
                        <div class="flex items-center gap-2">
                            <div class="font-bold text-gray-900">{{ $data['title'] ?? 'Notification' }}</div>
                            @if(!$n->read_at)
                                <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-[#0d9488] text-white">NEW</span>
                            @endif
                        </div>
                        <div class="text-sm text-gray-600 mt-1">{{ $data['message'] ?? '' }}</div>
                        <div class="text-xs text-gray-500 mt-2">{{ $n->created_at ? \Illuminate\Support\Carbon::parse($n->created_at)->format('M d, Y H:i') : '' }}</div>
                    </div>
                    <div class="flex items-center gap-2">
                        @if(!empty($data['action_url']))
                            <a href="{{ $data['action_url'] }}" class="bg-[#facc15] text-black px-3 py-2 rounded-lg font-bold hover:bg-yellow-400 transition">Open</a>
                        @endif
                        @if(!$n->read_at)
                            <form action="/notifications/mark-read" method="POST">
                                @csrf
                                <input type="hidden" name="id" value="{{ $n->id }}">
                                <button type="submit" class="bg-gray-900 text-white px-3 py-2 rounded-lg font-bold hover:bg-gray-800 transition">Mark read</button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        <div class="px-6 py-5 border-t border-gray-100 bg-gray-50">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection
