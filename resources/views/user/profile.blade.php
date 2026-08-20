@extends('layouts.dashboard')

@section('page_title', 'Profile')

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

<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-gray-800">Account Details</h3>
                <p class="text-sm text-gray-500">Update your profile information and security.</p>
            </div>
            <div class="text-xs font-bold px-3 py-1 rounded-full {{ (auth()->user()->account_status ?? 'pending') === 'active' ? 'bg-green-50 text-green-700' : 'bg-yellow-50 text-yellow-700' }}">
                {{ strtoupper(auth()->user()->account_status ?? 'pending') }}
            </div>
        </div>

        <div class="p-8">
            <form action="/profile" method="POST" class="space-y-8">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Full Name</label>
                        <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" class="w-full rounded-xl border-2 border-gray-200 px-4 py-3 focus:ring-0 focus:border-[#0d9488] font-bold text-gray-900" required>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Email</label>
                        <input type="email" value="{{ auth()->user()->email }}" class="w-full rounded-xl border-2 border-gray-200 px-4 py-3 bg-gray-50 text-gray-700" disabled>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Country</label>
                        <input type="text" name="country" value="{{ old('country', auth()->user()->country) }}" class="w-full rounded-xl border-2 border-gray-200 px-4 py-3 focus:ring-0 focus:border-[#0d9488] font-bold text-gray-900" placeholder="e.g. Nigeria">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Phone Number</label>
                        <input type="text" name="phone_number" value="{{ old('phone_number', auth()->user()->phone_number) }}" class="w-full rounded-xl border-2 border-gray-200 px-4 py-3 focus:ring-0 focus:border-[#0d9488] font-bold text-gray-900" placeholder="e.g. +234...">
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-8">
                    <h4 class="text-md font-bold text-gray-800 mb-1">Change Password</h4>
                    <p class="text-sm text-gray-500 mb-6">Leave blank if you don’t want to change it.</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Current Password</label>
                            <input type="password" name="current_password" class="w-full rounded-xl border-2 border-gray-200 px-4 py-3 focus:ring-0 focus:border-[#0d9488] font-bold text-gray-900">
                        </div>

                        <div></div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">New Password</label>
                            <input type="password" name="password" class="w-full rounded-xl border-2 border-gray-200 px-4 py-3 focus:ring-0 focus:border-[#0d9488] font-bold text-gray-900">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Confirm New Password</label>
                            <input type="password" name="password_confirmation" class="w-full rounded-xl border-2 border-gray-200 px-4 py-3 focus:ring-0 focus:border-[#0d9488] font-bold text-gray-900">
                        </div>
                    </div>
                </div>

                <div class="pt-2 flex items-center justify-end">
                    <button type="submit" class="bg-[#facc15] text-black px-8 py-3 rounded-xl font-bold shadow-md hover:bg-yellow-400 hover:shadow-lg transition transform hover:-translate-y-0.5">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

