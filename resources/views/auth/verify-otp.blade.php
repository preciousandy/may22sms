@extends('layouts.app')

@section('content')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
    .auth-wrapper {
        --primary: #0d9488;
        --primary-dark: #0f766e;
        --accent: #facc15;
        --text-main: #0f172a;
        --text-muted: #64748b;
        --bg-body: #f8fafc;
        --radius: 28px;
        --shadow-elevated: 0 25px 60px -15px rgba(13, 148, 136, 0.15), 0 10px 25px -5px rgba(15, 23, 42, 0.04);
        font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background-color: var(--bg-body);
        color: var(--text-main);
        min-height: 100vh;
        position: relative;
        overflow-x: hidden;
    }

    .auth-glow-1 {
        position: absolute;
        top: -15%;
        left: 50%;
        transform: translateX(-50%);
        width: 650px;
        height: 650px;
        background: radial-gradient(circle, rgba(13, 148, 136, 0.14) 0%, rgba(13, 148, 136, 0.03) 50%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
        z-index: 0;
    }

    .auth-glow-2 {
        position: absolute;
        bottom: -10%;
        right: 10%;
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, rgba(250, 204, 21, 0.12) 0%, transparent 60%);
        border-radius: 50%;
        pointer-events: none;
        z-index: 0;
    }

    .auth-container {
        max-width: 460px !important;
        width: 100% !important;
        margin-left: auto !important;
        margin-right: auto !important;
    }

    .auth-card {
        background: #ffffff;
        border-radius: var(--radius);
        border: 1px solid rgba(13, 148, 136, 0.15);
        box-shadow: var(--shadow-elevated);
        position: relative;
        z-index: 10;
    }

    /* 6-Digit Individual OTP Boxes */
    .otp-digits-wrapper {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin: 1.5rem 0;
    }

    @media (min-width: 480px) {
        .otp-digits-wrapper {
            gap: 12px;
        }
    }

    .otp-digit-box {
        width: 48px;
        height: 60px;
        border-radius: 16px;
        border: 2px solid #e2e8f0;
        background: #f8fafc;
        text-align: center;
        font-size: 1.6rem;
        font-weight: 800;
        color: #0f172a;
        font-family: 'Outfit', monospace;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        outline: none;
    }

    @media (min-width: 480px) {
        .otp-digit-box {
            width: 54px;
            height: 64px;
            font-size: 1.8rem;
        }
    }

    .otp-digit-box:focus {
        border-color: var(--primary);
        background: #ffffff;
        box-shadow: 0 0 0 4px rgba(13, 148, 136, 0.16);
        transform: translateY(-2px);
    }

    .otp-digit-box.filled {
        border-color: var(--primary);
        background: #f0fdfa;
        color: var(--primary-dark);
    }

    .btn-auth-submit {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: 100%;
        height: 54px;
        background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
        color: #ffffff;
        border-radius: 50px;
        font-weight: 800;
        font-size: 1rem;
        letter-spacing: 0.02em;
        box-shadow: 0 10px 25px -4px rgba(13, 148, 136, 0.45);
        border: none;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
        margin-top: 1rem;
    }

    .btn-auth-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 30px -4px rgba(13, 148, 136, 0.55);
        background: linear-gradient(135deg, #0f766e 0%, #115e59 100%);
    }

    .icon-halo {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .icon-halo::before {
        content: '';
        position: absolute;
        inset: -8px;
        border-radius: 28px;
        background: rgba(13, 148, 136, 0.10);
        z-index: -1;
    }
</style>

<div class="auth-wrapper flex flex-col justify-between py-10 px-4 sm:px-6">
    <div class="auth-glow-1"></div>
    <div class="auth-glow-2"></div>

    <!-- Top Brand Link -->
    <div class="relative z-10 text-center mb-8">
        <a href="/" class="inline-flex items-center gap-3 text-decoration-none group">
            <div class="h-12 w-12 rounded-2xl bg-white shadow-md shadow-teal-500/15 flex items-center justify-center border border-teal-500/20 group-hover:scale-105 transition-transform">
                <img src="{{ asset('logo/favicon-512.png') }}" alt="{{ $siteName ?? 'May22SMS' }}" class="h-8 w-8 object-contain">
            </div>
            <div class="text-left">
                <span class="font-black text-2xl text-slate-900 tracking-tight block leading-none">
                    {{ $siteName ?? 'May22SMS' }}<span class="text-teal-600">.</span>
                </span>
                <span class="text-xs font-semibold text-slate-400">Security Verification</span>
            </div>
        </a>
    </div>

    <!-- Central Card -->
    <div class="auth-container my-auto relative z-10">
        <div class="auth-card p-8 sm:p-10" x-data="otpForm()">
            
            <!-- Shield Icon with Halo -->
            <div class="text-center mb-6">
                <div class="icon-halo mb-3">
                    <div class="h-16 w-16 rounded-2xl bg-teal-50 text-teal-600 border border-teal-200/80 flex items-center justify-center text-2xl shadow-sm">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Verify Your Account</h1>
                <p class="text-slate-500 text-xs sm:text-sm mt-1.5 leading-relaxed">
                    We sent a 6-digit confirmation code to your phone or email. Enter the code below to finish setup.
                </p>

                <!-- Debug helper -->
                @if(config('app.debug') && auth()->check() && !empty(auth()->user()->otp))
                    <div class="mt-4 p-3 bg-amber-50 text-amber-900 rounded-2xl text-xs font-bold border border-amber-200/80 flex items-center justify-center gap-2">
                        <i class="fas fa-bug text-amber-600"></i>
                        <span>Test Mode OTP: <code class="bg-white px-2 py-0.5 rounded border border-amber-300 font-mono text-sm">{{ auth()->user()->otp }}</code></span>
                    </div>
                @endif

                <!-- Flash Success -->
                @if(session('success'))
                    <div class="mt-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-2xl text-xs font-bold flex items-center justify-center gap-2">
                        <i class="fas fa-circle-check text-emerald-600"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                <!-- Flash Errors -->
                @if(isset($errors) && $errors->any())
                    <div class="mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-2xl text-xs font-bold flex items-center justify-center gap-2">
                        <i class="fas fa-circle-exclamation text-red-600"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif
            </div>

            <!-- Form -->
            <form action="/verify-otp" method="POST" id="verifyOtpForm" data-loader="true" data-loader-title="Verifying Security Code..." data-loader-subtitle="Confirming your login securely">
                @csrf

                <!-- Hidden actual input submitted -->
                <input type="hidden" name="otp" id="hiddenOtpInput" x-model="combinedOtp">

                <!-- 6 Interactive Split Boxes -->
                <div class="otp-digits-wrapper" @paste.prevent="handlePaste($event)">
                    <template x-for="(digit, index) in digits" :key="index">
                        <input
                            type="text"
                            inputmode="numeric"
                            pattern="[0-9]*"
                            maxlength="1"
                            class="otp-digit-box"
                            :class="{ 'filled': digit !== '' }"
                            :id="'otp-box-' + index"
                            x-model="digits[index]"
                            @input="handleInput(index, $event)"
                            @keydown.backspace="handleBackspace(index, $event)"
                            @keydown.left="focusPrev(index)"
                            @keydown.right="focusNext(index)"
                            autocomplete="off"
                        >
                    </template>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-auth-submit" :disabled="combinedOtp.length !== 6" :style="combinedOtp.length === 6 ? '' : 'opacity: 0.7; cursor: not-allowed;'">
                    <span>Verify &amp; Continue</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>

            <!-- Resend Section with Countdown -->
            <div class="mt-6 pt-5 border-t border-slate-100 text-center" x-data="{ countdown: 45, canResend: false, timer: null }" x-init="
                timer = setInterval(() => {
                    if (countdown > 0) {
                        countdown--;
                    } else {
                        canResend = true;
                        clearInterval(timer);
                    }
                }, 1000);
            ">
                <template x-if="!canResend">
                    <p class="text-xs text-slate-400 font-semibold">
                        Resend code in <span class="font-mono text-teal-600 font-bold" x-text="countdown + 's'"></span>
                    </p>
                </template>

                <template x-if="canResend">
                    <form action="/resend-otp" method="POST">
                        @csrf
                        <button type="submit" class="text-xs font-bold text-teal-600 hover:text-teal-700 hover:underline inline-flex items-center gap-1.5 cursor-pointer">
                            <i class="fas fa-rotate-right"></i>
                            <span>Resend verification code</span>
                        </button>
                    </form>
                </template>
            </div>

            <!-- Logout Link -->
            <div class="mt-6 text-center text-xs">
                <a href="/logout" class="font-bold text-slate-400 hover:text-red-500 transition-colors">
                    <i class="fas fa-arrow-right-from-bracket mr-1"></i> Log out instead
                </a>
            </div>
        </div>

        <!-- Trust Badges Strip -->
        <div class="mt-8 flex items-center justify-center gap-6 text-center text-xs text-slate-400 font-medium">
            <span><i class="fas fa-lock text-teal-600 mr-1.5"></i> One-Time Password</span>
            <span><i class="fas fa-stopwatch text-amber-500 mr-1.5"></i> 10-Minute Validity</span>
        </div>
    </div>

    <!-- Page Footer -->
    <div class="relative z-10 text-center text-xs text-slate-400 mt-8">
        &copy; {{ date('Y') }} {{ $siteName ?? 'May22SMS' }}. All rights reserved.
    </div>
</div>

<script>
    function otpForm() {
        return {
            digits: ['', '', '', '', '', ''],
            get combinedOtp() {
                return this.digits.join('');
            },
            init() {
                this.$nextTick(() => {
                    const first = document.getElementById('otp-box-0');
                    if (first) first.focus();
                });
            },
            handleInput(index, event) {
                const val = event.target.value.replace(/[^0-9]/g, '');
                this.digits[index] = val ? val[val.length - 1] : '';
                
                if (val && index < 5) {
                    this.focusNext(index);
                }

                // If all 6 digits are typed, auto submit form for ultra-smooth experience
                if (this.combinedOtp.length === 6) {
                    setTimeout(() => {
                        const form = document.getElementById('verifyOtpForm');
                        if (form) form.submit();
                    }, 200);
                }
            },
            handleBackspace(index, event) {
                if (this.digits[index] === '' && index > 0) {
                    this.focusPrev(index);
                } else {
                    this.digits[index] = '';
                }
            },
            focusPrev(index) {
                if (index > 0) {
                    const prev = document.getElementById('otp-box-' + (index - 1));
                    if (prev) {
                        prev.focus();
                        prev.select();
                    }
                }
            },
            focusNext(index) {
                if (index < 5) {
                    const next = document.getElementById('otp-box-' + (index + 1));
                    if (next) {
                        next.focus();
                        next.select();
                    }
                }
            },
            handlePaste(event) {
                const pasteData = (event.clipboardData || window.clipboardData).getData('text');
                const cleanDigits = pasteData.replace(/[^0-9]/g, '').slice(0, 6);
                for (let i = 0; i < cleanDigits.length; i++) {
                    this.digits[i] = cleanDigits[i];
                }
                const focusIndex = Math.min(cleanDigits.length, 5);
                const target = document.getElementById('otp-box-' + focusIndex);
                if (target) target.focus();

                if (this.combinedOtp.length === 6) {
                    setTimeout(() => {
                        const form = document.getElementById('verifyOtpForm');
                        if (form) form.submit();
                    }, 200);
                }
            }
        };
    }
</script>
@endsection