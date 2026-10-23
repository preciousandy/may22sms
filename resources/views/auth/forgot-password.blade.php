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

    .input-group-modern {
        position: relative;
        margin-bottom: 1.25rem;
    }

    .input-group-modern label {
        display: block;
        font-size: 0.82rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #334155;
        margin-bottom: 0.5rem;
    }

    .input-field-modern {
        width: 100%;
        height: 52px;
        border-radius: 16px;
        border: 1.5px solid #e2e8f0;
        background: #f8fafc;
        padding: 0 16px 0 46px;
        font-size: 0.95rem;
        font-weight: 600;
        color: #0f172a;
        transition: all 0.25s ease;
        outline: none;
    }

    .input-field-modern:focus {
        border-color: var(--primary);
        background: #ffffff;
        box-shadow: 0 0 0 4px rgba(13, 148, 136, 0.14);
    }

    .input-icon-left {
        position: absolute;
        top: 38px;
        left: 16px;
        color: #94a3b8;
        font-size: 1rem;
        pointer-events: none;
        transition: color 0.25s ease;
    }

    .input-field-modern:focus ~ .input-icon-left {
        color: var(--primary);
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
                <span class="text-xs font-semibold text-slate-400">Password Recovery</span>
            </div>
        </a>
    </div>

    <!-- Central Card -->
    <div class="auth-container my-auto relative z-10">
        <div class="auth-card p-8 sm:p-10">
            
            <!-- Key Icon with Halo -->
            <div class="text-center mb-6">
                <div class="icon-halo mb-3">
                    <div class="h-16 w-16 rounded-2xl bg-teal-50 text-teal-600 border border-teal-200/80 flex items-center justify-center text-2xl shadow-sm">
                        <i class="fas fa-key"></i>
                    </div>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Forgot password?</h1>
                <p class="text-slate-500 text-xs sm:text-sm mt-1.5 leading-relaxed">
                    No problem. Enter your account email below and we'll send a secure password reset link.
                </p>

                <!-- Status Flash -->
                @if(session('status'))
                    <div class="mt-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-2xl text-xs font-bold flex items-center justify-center gap-2">
                        <i class="fas fa-circle-check text-emerald-600"></i>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                <!-- Errors Flash -->
                @if(isset($errors) && $errors->any())
                    <div class="mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-2xl text-xs font-bold flex items-center justify-center gap-2">
                        <i class="fas fa-circle-exclamation text-red-600"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif
            </div>

            <!-- Form -->
            <form action="{{ route('password.email') }}" method="POST" data-loader="true" data-loader-title="Sending Reset Link..." data-loader-subtitle="Dispatching secure recovery link to your email">
                @csrf

                <div class="input-group-modern">
                    <label for="email">Email Address</label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        value="{{ old('email') }}"
                        class="input-field-modern"
                        placeholder="name@example.com"
                        autocomplete="email"
                        required
                        autofocus
                    >
                    <div class="input-icon-left">
                        <i class="fas fa-envelope"></i>
                    </div>
                </div>

                <button type="submit" class="btn-auth-submit">
                    <span>Email Password Reset Link</span>
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>

            <!-- Card Bottom Switch -->
            <div class="mt-6 pt-5 border-t border-slate-100 text-center text-xs text-slate-500 font-semibold">
                Remember your password?
                <a href="/login" class="font-bold text-teal-600 hover:text-teal-700 hover:underline ml-1">
                    Sign in here &rarr;
                </a>
            </div>
        </div>

        <div class="mt-8 flex items-center justify-center gap-6 text-center text-xs text-slate-400 font-medium">
            <span><i class="fas fa-shield-halved text-teal-600 mr-1.5"></i> Secure Link</span>
            <span><i class="fas fa-clock text-amber-500 mr-1.5"></i> 60-Minute Expiry</span>
        </div>
    </div>

    <!-- Page Footer -->
    <div class="relative z-10 text-center text-xs text-slate-400 mt-8">
        &copy; {{ date('Y') }} {{ $siteName ?? 'May22SMS' }}. All rights reserved.
    </div>
</div>
@endsection