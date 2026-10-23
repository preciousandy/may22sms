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
        margin-bottom: 1.15rem;
    }

    .input-group-modern label {
        display: block;
        font-size: 0.82rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #334155;
        margin-bottom: 0.45rem;
    }

    .input-field-modern {
        width: 100%;
        height: 50px;
        border-radius: 16px;
        border: 1.5px solid #e2e8f0;
        background: #f8fafc;
        padding: 0 16px 0 44px;
        font-size: 0.94rem;
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
        top: 36px;
        left: 16px;
        color: #94a3b8;
        font-size: 0.95rem;
        pointer-events: none;
        transition: color 0.25s ease;
    }

    .input-field-modern:focus ~ .input-icon-left {
        color: var(--primary);
    }

    .password-toggle-btn {
        position: absolute;
        top: 36px;
        right: 16px;
        background: none;
        border: none;
        color: #94a3b8;
        cursor: pointer;
        padding: 0;
        font-size: 0.95rem;
        transition: color 0.25s ease;
    }

    .password-toggle-btn:hover {
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
        margin-top: 1.25rem;
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
                <span class="text-xs font-semibold text-slate-400">Security Update</span>
            </div>
        </a>
    </div>

    <!-- Central Card -->
    <div class="auth-container my-auto relative z-10">
        <div class="auth-card p-8 sm:p-10" x-data="{ showPw: false, showPw2: false }">
            
            <!-- Lock Icon with Halo -->
            <div class="text-center mb-6">
                <div class="icon-halo mb-3">
                    <div class="h-16 w-16 rounded-2xl bg-teal-50 text-teal-600 border border-teal-200/80 flex items-center justify-center text-2xl shadow-sm">
                        <i class="fas fa-lock-open"></i>
                    </div>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Create New Password</h1>
                <p class="text-slate-500 text-xs sm:text-sm mt-1.5 leading-relaxed">
                    Set a new strong password below to regain full access to your account.
                </p>

                <!-- Errors Flash -->
                @if(isset($errors) && $errors->any())
                    <div class="mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-2xl text-xs font-bold flex items-center justify-center gap-2">
                        <i class="fas fa-circle-exclamation text-red-600"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif
            </div>

            <!-- Form -->
            <form action="{{ route('password.update') }}" method="POST">
                @csrf

                <!-- Token -->
                <input type="hidden" name="token" value="{{ $token ?? $request->route('token') }}">

                <!-- Email Address -->
                <div class="input-group-modern">
                    <label for="email">Email Address</label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        value="{{ old('email', $email ?? $request->email) }}"
                        class="input-field-modern"
                        placeholder="name@example.com"
                        required
                        autofocus
                    >
                    <div class="input-icon-left">
                        <i class="fas fa-envelope"></i>
                    </div>
                </div>

                <!-- Password -->
                <div class="input-group-modern">
                    <label for="password">New Password</label>
                    <input
                        :type="showPw ? 'text' : 'password'"
                        name="password"
                        id="password"
                        class="input-field-modern"
                        style="padding-right: 44px;"
                        placeholder="••••••••"
                        required
                    >
                    <div class="input-icon-left">
                        <i class="fas fa-lock"></i>
                    </div>
                    <button
                        type="button"
                        class="password-toggle-btn"
                        @click="showPw = !showPw"
                        aria-label="Toggle password visibility"
                    >
                        <i class="fas" :class="showPw ? 'fa-eye-slash' : 'fa-eye'"></i>
                    </button>
                </div>

                <!-- Confirm Password -->
                <div class="input-group-modern">
                    <label for="password_confirmation">Confirm New Password</label>
                    <input
                        :type="showPw2 ? 'text' : 'password'"
                        name="password_confirmation"
                        id="password_confirmation"
                        class="input-field-modern"
                        style="padding-right: 44px;"
                        placeholder="••••••••"
                        required
                    >
                    <div class="input-icon-left">
                        <i class="fas fa-shield-check"></i>
                    </div>
                    <button
                        type="button"
                        class="password-toggle-btn"
                        @click="showPw2 = !showPw2"
                        aria-label="Toggle password visibility"
                    >
                        <i class="fas" :class="showPw2 ? 'fa-eye-slash' : 'fa-eye'"></i>
                    </button>
                </div>

                <button type="submit" class="btn-auth-submit">
                    <span>Update Password</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>

            <div class="mt-6 pt-5 border-t border-slate-100 text-center text-xs text-slate-500 font-semibold">
                Back to
                <a href="/login" class="font-bold text-teal-600 hover:text-teal-700 hover:underline ml-1">
                    Sign in &rarr;
                </a>
            </div>
        </div>

        <div class="mt-8 flex items-center justify-center gap-6 text-center text-xs text-slate-400 font-medium">
            <span><i class="fas fa-shield-halved text-teal-600 mr-1.5"></i> Encrypted Credentials</span>
        </div>
    </div>

    <!-- Page Footer -->
    <div class="relative z-10 text-center text-xs text-slate-400 mt-8">
        &copy; {{ date('Y') }} {{ $siteName ?? 'May22SMS' }}. All rights reserved.
    </div>
</div>
@endsection