@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-[#0d9488]">Verify Account</h1>
            <p class="text-gray-500 mt-2">Enter the OTP sent to your phone/email.</p>
            
            @if(config('app.debug'))
                <div class="mt-4 p-3 bg-yellow-50 text-yellow-800 rounded-md text-sm border border-yellow-200">
                    <strong>Test Mode:</strong> Your OTP is <code>{{ auth()->user()->otp }}</code>
                </div>
            @endif

            @if(session('success'))
                <div class="mt-4 bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded-md text-sm">
                    {{ session('success') }}
                </div>
            @endif
        </div>
        
        <form action="/verify-otp" method="POST">
            @csrf
            
            @if($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-md text-sm">
                    {{ $errors->first() }}
                </div>
            @endif
            
            <div class="mb-6">
                <label for="otp" class="block text-sm font-medium text-gray-700 text-center mb-2">6-Digit OTP</label>
                <input type="text" name="otp" id="otp" class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-md focus:ring-[#0d9488] focus:border-[#0d9488] text-center text-2xl tracking-widest font-bold" placeholder="••••••" maxlength="6" required>
            </div>
            
            <div>
                <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-black bg-[#facc15] hover:bg-yellow-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#facc15]">
                    Verify Now
                </button>
            </div>
        </form>

        <form action="/resend-otp" method="POST" class="mt-4">
            @csrf
            <button type="submit" class="w-full flex justify-center py-3 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0d9488]">
                Resend OTP
            </button>
        </form>

        <div class="mt-6 text-center text-sm">
            <p class="text-gray-600">
                <a href="/logout" class="font-medium text-red-500 hover:text-red-700">
                    Log out instead
                </a>
            </p>
        </div>
    </div>
</div>
@endsection