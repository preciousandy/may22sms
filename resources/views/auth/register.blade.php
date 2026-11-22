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
        max-width: 500px !important;
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
        transition: transform 0.3s ease;
    }

    .auth-tabs {
        display: flex;
        background: #f1f5f9;
        padding: 5px;
        border-radius: 50px;
        margin-bottom: 2rem;
        border: 1px solid #e2e8f0;
    }

    .auth-tab {
        flex: 1;
        text-align: center;
        padding: 10px 16px;
        font-size: 0.92rem;
        font-weight: 700;
        border-radius: 50px;
        text-decoration: none;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        color: var(--text-muted);
    }

    .auth-tab.active {
        background: #ffffff;
        color: var(--primary);
        box-shadow: 0 4px 12px rgba(13, 148, 136, 0.15);
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

    .trust-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 0.8rem;
        color: #64748b;
        font-weight: 600;
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
                <span class="text-xs font-semibold text-slate-400">Automated Digital Hub</span>
            </div>
        </a>
    </div>

    <!-- Central Modern Auth Card -->
    <div class="auth-container my-auto relative z-10">
        <div class="auth-card p-8 sm:p-10" x-data="{ showPw: false, showPw2: false }">
            
            <!-- Segment Tabs: Sign In / Create Account -->
            <div class="auth-tabs">
                <a href="/login" class="auth-tab">
                    <i class="fas fa-arrow-right-to-bracket mr-1.5"></i> Sign In
                </a>
                <a href="/register" class="auth-tab active">
                    <i class="fas fa-user-plus mr-1.5"></i> Create Account
                </a>
            </div>

            <!-- Card Header -->
            <div class="mb-6">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Create your account</h1>
                <p class="text-slate-500 text-sm mt-1.5">Get immediate access to virtual numbers, logs, and boosting services.</p>
            </div>

            <!-- Referral Tag if present -->
            @if(request()->query('ref'))
                <input type="hidden" name="ref" value="{{ request()->query('ref') }}">
                <div class="mb-5 p-3.5 bg-teal-50 border border-teal-200 text-teal-800 rounded-2xl text-xs font-bold flex items-center gap-2.5">
                    <i class="fas fa-gift text-teal-600 text-base"></i>
                    <span>Referred by <strong>{{ request()->query('ref') }}</strong> — welcome bonus active!</span>
                </div>
            @endif

            <!-- Error Banner -->
            @if(isset($errors) && $errors->any())
                <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 text-red-700 px-4 py-3.5 text-xs font-semibold">
                    <div class="font-bold mb-1 flex items-center gap-2">
                        <i class="fas fa-circle-exclamation text-red-500 text-sm"></i>
                        <span>Please correct the errors below:</span>
                    </div>
                    <ul class="list-disc pl-5 space-y-0.5 text-xs">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Google OAuth Button -->
            <div class="mb-5">
                <a href="{{ route('auth.google', request()->query('ref') ? ['ref' => request()->query('ref')] : []) }}"
                   class="w-full h-[52px] rounded-2xl border-2 border-slate-200 hover:border-teal-500 bg-white hover:bg-teal-50/30 text-slate-800 font-extrabold text-sm flex items-center justify-center gap-3 shadow-sm hover:shadow transition-all duration-200">
                    <svg class="w-5 h-5" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    <span>Sign up with Google (1-Click)</span>
                </a>

                <div class="relative flex py-4 items-center">
                    <div class="flex-grow border-t border-slate-200"></div>
                    <span class="flex-shrink mx-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">or register with email</span>
                    <div class="flex-grow border-t border-slate-200"></div>
                </div>
            </div>

            <!-- Form -->
            <form action="/register" method="POST" data-loader="true" data-loader-title="Creating Account..." data-loader-subtitle="Setting up your secure May22SMS profile">
                @csrf

                <!-- Honeypot -->
                <div style="display: none;">
                    <label for="username_trap">Leave this blank</label>
                    <input type="text" name="username_trap" id="username_trap" tabindex="-1" autocomplete="off">
                </div>

                @if(request()->query('ref'))
                    <input type="hidden" name="ref" value="{{ request()->query('ref') }}">
                @endif

                <!-- Full Name -->
                <div class="input-group-modern">
                    <label for="name">Full Name</label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        value="{{ old('name') }}"
                        class="input-field-modern"
                        placeholder="John Doe"
                        autocomplete="name"
                        required
                        autofocus
                    >
                    <div class="input-icon-left">
                        <i class="fas fa-user"></i>
                    </div>
                </div>

                <!-- Email Address -->
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
                    >
                    <div class="input-icon-left">
                        <i class="fas fa-envelope"></i>
                    </div>
                </div>

                <!-- Country & Phone Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <!-- Country -->
                    <div class="input-group-modern">
                        <label for="country">Country</label>
                        <select
                            name="country"
                            id="country"
                            class="input-field-modern appearance-none cursor-pointer pr-10"
                            required
                        >
                            <option value="">Select country</option>
                            <option value="Nigeria" {{ old('country', 'Nigeria') === 'Nigeria' ? 'selected' : '' }}>Nigeria</option>
                            <option value="United States" {{ old('country') === 'United States' ? 'selected' : '' }}>United States</option>
                            <option value="United Kingdom" {{ old('country') === 'United Kingdom' ? 'selected' : '' }}>United Kingdom</option>
                            <option value="Canada" {{ old('country') === 'Canada' ? 'selected' : '' }}>Canada</option>
                            <option value="Ghana" {{ old('country') === 'Ghana' ? 'selected' : '' }}>Ghana</option>
                            <option value="Kenya" {{ old('country') === 'Kenya' ? 'selected' : '' }}>Kenya</option>
                            <option value="South Africa" {{ old('country') === 'South Africa' ? 'selected' : '' }}>South Africa</option>
                            <option value="Australia" {{ old('country') === 'Australia' ? 'selected' : '' }}>Australia</option>
                            <option value="India" {{ old('country') === 'India' ? 'selected' : '' }}>India</option>
                            <option value="Germany" {{ old('country') === 'Germany' ? 'selected' : '' }}>Germany</option>
                            <option value="Other" {{ old('country') === 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                        <div class="input-icon-left">
                            <i class="fas fa-earth-africa"></i>
                        </div>
                    </div>

                    <!-- Phone Number -->
                    <div class="input-group-modern">
                        <label for="phone_number">Phone Number</label>
                        <input
                            type="tel"
                            name="phone_number"
                            id="phone_number"
                            value="{{ old('phone_number') }}"
                            class="input-field-modern"
                            placeholder="+2348012345678"
                            autocomplete="tel"
                            required
                        >
                        <div class="input-icon-left">
                            <i class="fas fa-phone"></i>
                        </div>
                    </div>
                </div>

                <!-- Password & Confirm Password Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <!-- Password -->
                    <div class="input-group-modern">
                        <label for="password">Password</label>
                        <input
                            :type="showPw ? 'text' : 'password'"
                            name="password"
                            id="password"
                            class="input-field-modern"
                            style="padding-right: 44px;"
                            placeholder="••••••••"
                            autocomplete="new-password"
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
                        <label for="password_confirmation">Confirm Password</label>
                        <input
                            :type="showPw2 ? 'text' : 'password'"
                            name="password_confirmation"
                            id="password_confirmation"
                            class="input-field-modern"
                            style="padding-right: 44px;"
                            placeholder="••••••••"
                            autocomplete="new-password"
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
                </div>

                <!-- Terms Text -->
                <p class="text-[11px] text-slate-400 mt-2 leading-relaxed">
                    By clicking below, you agree to our Terms of Service and automated refund policy.
                </p>

                <!-- Submit Button -->
                <button type="submit" class="btn-auth-submit">
                    <span>Create My Free Account</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>

            <!-- Card Bottom Switch -->
            <div class="mt-6 pt-5 border-t border-slate-100 text-center text-xs text-slate-500 font-semibold">
                Already registered?
                <a href="/login" class="font-bold text-teal-600 hover:text-teal-700 hover:underline ml-1">
                    Sign in here &rarr;
                </a>
            </div>
        </div>

        <!-- Trust Badges Strip -->
        <div class="mt-8 flex flex-wrap items-center justify-center gap-6 text-center text-xs text-slate-400 font-medium">
            <span class="trust-pill"><i class="fas fa-bolt text-teal-600"></i> Instant Account Activation</span>
            <span class="trust-pill"><i class="fas fa-gift text-amber-500"></i> Daily Login Rewards</span>
            <span class="trust-pill"><i class="fas fa-lock text-teal-600"></i> 100% Privacy Protected</span>
        </div>
    </div>

    <!-- Page Footer -->
    <div class="relative z-10 text-center text-xs text-slate-400 mt-8">
        &copy; {{ date('Y') }} {{ $siteName ?? 'May22SMS' }}. Fast, secure, and automated.
    </div>
</div>
@endsection
