@extends('layouts.dashboard')

@section('page_title', 'Security')

@section('dashboard_content')
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

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 bg-gray-50">
                <h3 class="text-lg font-bold text-gray-800">Two-Factor Authentication</h3>
                <p class="text-sm text-gray-500">Secure your account with email login codes.</p>
            </div>
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div class="text-sm font-bold text-gray-800">Status</div>
                    <div class="text-xs font-bold px-3 py-1 rounded-full {{ auth()->user()->two_factor_enabled ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                        {{ auth()->user()->two_factor_enabled ? 'ENABLED' : 'DISABLED' }}
                    </div>
                </div>

                <div class="mt-6">
                    @if(auth()->user()->two_factor_enabled)
                        <form action="/security/2fa/disable" method="POST" class="space-y-3">
                            @csrf
                            <input type="password" name="current_password" class="w-full rounded-xl border-2 border-gray-200 px-4 py-3 focus:ring-0 focus:border-[#0d9488] font-bold text-gray-900" placeholder="Current password" required>
                            <button type="submit" class="bg-gray-900 text-white px-6 py-3 rounded-xl font-bold hover:bg-gray-800 transition">Disable 2FA</button>
                        </form>
                    @else
                        <form action="/security/2fa/enable" method="POST" class="space-y-3">
                            @csrf
                            <input type="password" name="current_password" class="w-full rounded-xl border-2 border-gray-200 px-4 py-3 focus:ring-0 focus:border-[#0d9488] font-bold text-gray-900" placeholder="Current password" required>
                            <button type="submit" class="bg-[#facc15] text-black px-6 py-3 rounded-xl font-bold hover:bg-yellow-400 transition">Enable 2FA</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 bg-gray-50">
                <h3 class="text-lg font-bold text-gray-800">Device Sessions</h3>
                <p class="text-sm text-gray-500">Logout from other devices.</p>
            </div>
            <div class="p-6">
                <form action="/security/logout-other-devices" method="POST" class="space-y-3">
                    @csrf
                    <input type="password" name="current_password" class="w-full rounded-xl border-2 border-gray-200 px-4 py-3 focus:ring-0 focus:border-[#0d9488] font-bold text-gray-900" placeholder="Current password" required>
                    <button type="submit" class="bg-gray-900 text-white px-6 py-3 rounded-xl font-bold hover:bg-gray-800 transition">Logout other devices</button>
                </form>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 bg-gray-50">
                <h3 class="text-lg font-bold text-gray-800">Login Activity</h3>
            </div>
            <div class="p-6 space-y-2 text-sm text-gray-700">
                <div><span class="font-bold">Last login:</span> {{ auth()->user()->last_login_at ? \Illuminate\Support\Carbon::parse(auth()->user()->last_login_at)->format('M d, Y H:i') : '—' }}</div>
                <div><span class="font-bold">IP:</span> {{ auth()->user()->last_login_ip ?? '—' }}</div>
                <div><span class="font-bold">Current IP:</span> {{ request()->ip() ?? '—' }}</div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 bg-gray-50">
                <h3 class="text-lg font-bold text-gray-800">Referral</h3>
            </div>
            <div class="p-6 text-sm text-gray-700">
                <div class="font-bold mb-2">Your link</div>
                <div class="font-mono text-xs bg-gray-50 border border-gray-100 rounded-xl p-3 break-all">
                    {{ rtrim((string) config('app.url'), '/') }}/register?ref={{ auth()->user()->referral_code ?? '' }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
