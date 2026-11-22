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

@php
    $needsPhoneSetup = empty(trim((string) (auth()->user()->phone_number ?? '')));
@endphp

<div class="max-w-3xl mx-auto space-y-6">
    @if($needsPhoneSetup)
        <!-- Step-by-Step Guide Card for Google Sign-Up Users -->
        <div class="rounded-2xl border-2 border-[#0d9488] bg-gradient-to-br from-teal-900 via-slate-900 to-emerald-950 text-white p-6 shadow-xl relative overflow-hidden">
            <div class="absolute -right-12 -top-12 w-48 h-48 bg-teal-500/20 rounded-full blur-2xl pointer-events-none"></div>
            <div class="relative z-10">
                <div class="flex items-center gap-2.5 mb-2 flex-wrap">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-amber-400 text-amber-950 shadow-xs">
                        <span>👋</span> Quick Setup Guide
                    </span>
                    <span class="text-xs font-bold text-teal-200">Signed up with Google</span>
                </div>

                <h3 class="text-lg sm:text-xl font-black text-white">Add Your Phone Number to Complete Your Profile</h3>
                <p class="text-xs sm:text-sm text-teal-100/90 mt-1">
                    Because Google Sign-Up doesn't share your mobile number automatically, follow these 3 quick steps below:
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-4">
                    <div class="rounded-xl bg-white/10 border border-white/15 p-3.5">
                        <div class="text-[11px] font-black uppercase text-amber-300 mb-1">Step 1</div>
                        <div class="text-xs font-bold text-white">Confirm your Country</div>
                        <p class="text-[11px] text-teal-100/80 mt-0.5">Check that your country (e.g. Nigeria) is set in the form below.</p>
                    </div>
                    <div class="rounded-xl bg-white/10 border border-emerald-400/40 p-3.5 ring-1 ring-emerald-400/30">
                        <div class="text-[11px] font-black uppercase text-emerald-300 mb-1">Step 2</div>
                        <div class="text-xs font-bold text-white">Enter Phone Number</div>
                        <p class="text-[11px] text-teal-100/80 mt-0.5">Type your active mobile number in the highlighted <strong class="text-white">Phone Number</strong> box below.</p>
                    </div>
                    <div class="rounded-xl bg-white/10 border border-white/15 p-3.5">
                        <div class="text-[11px] font-black uppercase text-amber-300 mb-1">Step 3</div>
                        <div class="text-xs font-bold text-white">Click "Save Changes"</div>
                        <p class="text-[11px] text-teal-100/80 mt-0.5">Hit the yellow <strong class="text-amber-300">Save Changes</strong> button to unlock instant wallet funding &amp; alerts.</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

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

                    <div id="phone_number_box">
                        <label for="phone_number_input" class="block text-sm font-bold text-gray-700 mb-2 flex items-center justify-between">
                            <span>Phone Number</span>
                            @if($needsPhoneSetup)
                                <span class="text-[11px] font-black text-amber-800 bg-amber-100 border border-amber-300 px-2 py-0.5 rounded-full animate-pulse">Required — Enter Here 👇</span>
                            @endif
                        </label>
                        <input type="tel"
                               id="phone_number_input"
                               name="phone_number"
                               value="{{ old('phone_number', auth()->user()->phone_number) }}"
                               class="w-full rounded-xl border-2 {{ $needsPhoneSetup ? 'border-[#0d9488] bg-teal-50/40 ring-4 ring-[#0d9488]/15' : 'border-gray-200' }} px-4 py-3 focus:ring-0 focus:border-[#0d9488] font-bold text-gray-900"
                               placeholder="e.g. +2348012345678"
                               {{ $needsPhoneSetup ? 'autofocus required' : '' }}>
                        @if($needsPhoneSetup)
                            <p class="text-xs font-semibold text-[#0d9488] mt-1.5">Enter your active mobile number, then click <strong>Save Changes</strong> below.</p>
                        @endif
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

