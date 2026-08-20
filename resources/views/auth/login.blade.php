@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-950 text-white relative overflow-hidden">
    <!-- Animated background -->
    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute -top-40 -left-40 w-[40rem] h-[40rem] rounded-full bg-[#0d9488]/30 blur-3xl animate-pulse"></div>
        <div class="absolute top-1/2 -right-40 w-[45rem] h-[45rem] rounded-full bg-[#facc15]/25 blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
        <div class="absolute bottom-0 left-1/3 w-[35rem] h-[35rem] rounded-full bg-gray-700/25 blur-3xl animate-pulse" style="animation-delay: 2s;"></div>
    </div>

    <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <a href="/" class="inline-flex items-center gap-4 text-white/90 hover:text-white">
            <div class="h-12 w-12 rounded-2xl bg-gradient-to-br from-[#0d9488] to-gray-900 flex items-center justify-center font-black text-2xl shadow-xl shadow-[#0d9488]/30">
                {{ strtoupper(substr((string) ($siteName ?? 'M'), 0, 1)) }}
            </div>
            <div class="font-extrabold tracking-tight text-xl">{{ $siteName ?? 'may22sms' }}</div>
        </a>

        <div class="mt-12 grid grid-cols-1 lg:grid-cols-2 gap-8 items-stretch">
            <!-- Left side - info -->
            <div class="hidden lg:block relative rounded-3xl border border-white/10 bg-white/5 backdrop-blur-xl p-10">
                <div class="inline-flex items-center gap-2 bg-gradient-to-r from-[#0d9488]/20 to-transparent border border-[#0d9488]/30 rounded-full px-5 py-2.5 mb-8">
                    <span class="relative flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#0d9488] opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-[#0d9488]"></span>
                    </span>
                    <span class="text-xs font-extrabold text-[#0d9488] uppercase tracking-widest">Secure login</span>
                </div>
                
                <h1 class="text-5xl font-black tracking-tight leading-tight mb-6">
                    Welcome back to <span class="bg-gradient-to-r from-[#0d9488] to-[#facc15] bg-clip-text text-transparent">{{ $siteName ?? 'may22sms' }}</span>
                </h1>
                
                <p class="text-lg text-white/65 mb-10 leading-relaxed">
                    Sign in to fund your wallet, buy SMS numbers, purchase logs, and place boosting orders.
                </p>

                <div class="grid grid-cols-1 gap-6">
                    <div class="group rounded-2xl border border-white/10 bg-gradient-to-br from-[#0d9488]/10 to-transparent p-6 hover:border-[#0d9488]/30 transition-all">
                        <div class="text-2xl font-black bg-gradient-to-r from-[#0d9488] to-teal-500 bg-clip-text text-transparent mb-2">Promo codes supported</div>
                        <div class="text-sm text-white/65">Funding · Logs · SMS · Boosting</div>
                    </div>
                    <div class="group rounded-2xl border border-white/10 bg-gradient-to-br from-[#facc15]/10 to-transparent p-6 hover:border-[#facc15]/30 transition-all">
                        <div class="text-2xl font-black bg-gradient-to-r from-[#facc15] to-orange-400 bg-clip-text text-transparent mb-2">Wallet ledger</div>
                        <div class="text-sm text-white/65">Track credits and debits for every action.</div>
                    </div>
                </div>
            </div>

            <!-- Right side - login form -->
            <div class="relative rounded-3xl border border-white/10 bg-white text-gray-900 shadow-2xl overflow-hidden">
                <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-br from-[#0d9488]/20 to-[#facc15]/20 rounded-bl-3xl blur-2xl"></div>
                <div class="relative p-8 sm:p-10" x-data="{ showPw: false }">
                    <div class="flex items-start justify-between gap-6 mb-10">
                        <div>
                            <div class="text-3xl font-black tracking-tight">Sign in</div>
                            <div class="mt-2 text-base text-gray-600">Use your email and password to access your dashboard.</div>
                        </div>
                        <a href="/register" class="text-base font-extrabold bg-gradient-to-r from-[#0d9488] to-[#facc15] bg-clip-text text-transparent hover:from-teal-500 hover:to-yellow-300 transition-all">
                            Create account
                        </a>
                    </div>

                    <form action="/login" method="POST" class="space-y-6">
                        @csrf

                        <div style="display: none;">
                            <label for="username_trap">Leave this field blank if you are human</label>
                            <input type="text" name="username_trap" id="username_trap" tabindex="-1" autocomplete="off">
                        </div>

                        @if($errors->any())
                            <div class="rounded-2xl border border-red-200 bg-red-50 text-red-700 px-5 py-4 text-sm font-extrabold">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <div class="space-y-1.5">
                            <label for="email" class="block text-sm font-extrabold text-gray-700">Email address</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" class="w-full rounded-2xl border-2 border-gray-200 px-5 py-4 focus:ring-0 focus:border-[#0d9488] font-extrabold text-gray-900 transition-all" placeholder="you@example.com" autocomplete="email" required>
                        </div>

                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <label for="password" class="block text-sm font-extrabold text-gray-700">Password</label>
                                <a href="{{ route('password.request') }}" class="text-sm font-extrabold bg-gradient-to-r from-[#0d9488] to-[#facc15] bg-clip-text text-transparent">
                                    Forgot password?
                                </a>
                            </div>
                            <div class="relative">
                                <input :type="showPw ? 'text' : 'password'" name="password" id="password" class="w-full rounded-2xl border-2 border-gray-200 px-5 py-4 pr-14 focus:ring-0 focus:border-[#0d9488] font-extrabold text-gray-900 transition-all" placeholder="••••••••" autocomplete="current-password" required>
                                <button type="button" class="absolute inset-y-0 right-0 px-5 text-gray-500 hover:text-[#0d9488] transition-colors" @click="showPw = !showPw" aria-label="Toggle password visibility">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" x-show="!showPw">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" x-show="showPw" style="display:none;">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-2">
                            <label class="inline-flex items-center gap-3 text-sm font-extrabold text-gray-700 cursor-pointer">
                                <input id="remember_me" name="remember" type="checkbox" class="h-5 w-5 rounded border-gray-300 text-[#0d9488] focus:ring-[#0d9488] cursor-pointer">
                                Remember me
                            </label>
                        </div>

                        <button type="submit" class="w-full bg-gradient-to-r from-[#0d9488] to-teal-700 hover:from-teal-500 hover:to-teal-600 text-white px-8 py-4 rounded-2xl font-black text-lg shadow-xl shadow-[#0d9488]/40 transition-all hover:scale-[1.02]">
                            Sign in
                        </button>
                    </form>

                    <div class="mt-10 text-center text-base text-gray-600">
                        New here? <a href="/register" class="font-extrabold bg-gradient-to-r from-[#0d9488] to-[#facc15] bg-clip-text text-transparent hover:from-teal-500 hover:to-yellow-300 transition-all">Create an account</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
