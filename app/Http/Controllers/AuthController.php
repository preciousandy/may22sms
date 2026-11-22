<?php

namespace App\Http\Controllers;

use App\Mail\SendOtpMail;
use App\Mail\WelcomeEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        if ($request->filled('username_trap')) {
            abort(403, 'Automated behavior detected.');
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $credentials['email'])->first();
        if (! $user || ! Hash::check($credentials['password'], (string) $user->password)) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');
        }

        $remember = $request->boolean('remember');
        if ($user->two_factor_enabled) {
            $code = (string) random_int(100000, 999999);
            $user->two_factor_code = Hash::make($code);
            $user->two_factor_expires_at = now()->addMinutes(10);
            $user->save();

            try {
                Mail::raw("Your may22sms login code is: {$code}\n\nThis code expires in 10 minutes.", function ($message) use ($user) {
                    $message->to($user->email)->subject('Your login code');
                });
            } catch (\Throwable $e) {
                Log::warning('2FA email failed to send: '.$e->getMessage());
            }

            $request->session()->put('two_factor_user_id', $user->id);
            $request->session()->put('two_factor_remember', $remember);

            return redirect('/two-factor');
        }

        Auth::login($user, $remember);
        $request->session()->regenerate();

        $user->last_login_at = now();
        $user->last_login_ip = (string) $request->ip();
        $user->last_login_user_agent = (string) $request->userAgent();
        $user->save();

        if ($user->is_admin) {
            return redirect()->intended('admin/dashboard');
        }

        return redirect()->intended('dashboard');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        if ($request->filled('username_trap')) {
            abort(403, 'Automated behavior detected.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'country' => ['required', 'string'],
            'phone_number' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'ref' => ['nullable', 'string', 'max:20'],
        ]);

        $otp = (string) rand(100000, 999999);

        $referrerId = null;
        $ref = strtoupper(trim((string) $request->ref));
        if ($ref !== '') {
            $referrerId = User::where('referral_code', $ref)->value('id');
        }

        do {
            $referralCode = strtoupper(Str::random(10));
        } while (User::where('referral_code', $referralCode)->exists());

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'country' => $request->country,
            'phone_number' => $request->phone_number,
            'password' => Hash::make($request->password),
            'walletBalance' => 0.00,
            'account_status' => 'pending',
            'otp' => $otp,
            'is_admin' => false,
            'referral_code' => $referralCode,
            'referred_by' => $referrerId,
        ]);

        try {
            Mail::to($user->email)->send(new SendOtpMail($otp, $user));
        } catch (\Exception $e) {
            Log::error('OTP Email failed to send: '.$e->getMessage());
        }

        Auth::login($user);

        return redirect('/verify-otp');
    }

    public function showVerifyOtp()
    {
        if (auth()->user()->account_status === 'active') {
            return redirect('/dashboard');
        }

        return view('auth.verify-otp');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'string'],
        ]);

        $user = auth()->user();

        if ($user->otp === $request->otp) {
            $user->update([
                'account_status' => 'active',
                'otp' => null,
            ]);

            try {
                Mail::to($user->email)->send(new WelcomeEmail($user));
            } catch (\Exception $e) {
                Log::error('Welcome Email failed to send: '.$e->getMessage());
            }

            return redirect('/dashboard')->with('success', 'Account verified successfully!');
        }

        return back()->withErrors(['otp' => 'Invalid OTP code entered.']);
    }

    public function resendOtp(Request $request)
    {
        $user = auth()->user();

        if ($user->account_status === 'active') {
            return redirect('/dashboard');
        }

        $otp = (string) rand(100000, 999999);
        $user->update(['otp' => $otp]);

        try {
            Mail::to($user->email)->send(new SendOtpMail($otp, $user));
        } catch (\Exception $e) {
            Log::error('OTP Email failed to send: '.$e->getMessage());
        }

        return back()->with('success', 'A new OTP has been sent to your email address.');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Redirect the user to Google's OAuth 2.0 consent page.
     */
    public function redirectToGoogle(Request $request)
    {
        $clientId = trim((string) config('services.google.client_id'));
        if ($clientId === '') {
            return redirect('/login')->withErrors([
                'email' => 'Google Sign-In is not configured yet.',
            ]);
        }

        if ($request->filled('ref')) {
            $request->session()->put('oauth_referral_code', strtoupper(trim((string) $request->query('ref'))));
        }

        $state = Str::random(40);
        $request->session()->put('google_oauth_state', $state);

        $redirectUri = trim((string) config('services.google.redirect')) ?: url('/auth/google/callback');

        $query = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'access_type' => 'online',
            'prompt' => 'select_account',
        ]);

        return redirect()->away('https://accounts.google.com/o/oauth2/v2/auth?' . $query);
    }

    /**
     * Handle the OAuth 2.0 callback from Google.
     */
    public function handleGoogleCallback(Request $request)
    {
        if ($request->filled('error')) {
            return redirect('/login')->withErrors([
                'email' => 'Google Sign-In was canceled or denied.',
            ]);
        }

        $expectedState = (string) $request->session()->pull('google_oauth_state', '');
        $incomingState = (string) $request->query('state', '');
        if ($expectedState === '' || ! hash_equals($expectedState, $incomingState)) {
            return redirect('/login')->withErrors([
                'email' => 'Invalid Google Sign-In state. Please try again.',
            ]);
        }

        $code = trim((string) $request->query('code', ''));
        if ($code === '') {
            return redirect('/login')->withErrors([
                'email' => 'Authorization code missing from Google callback.',
            ]);
        }

        $clientId = trim((string) config('services.google.client_id'));
        $clientSecret = trim((string) config('services.google.client_secret'));
        $redirectUri = trim((string) config('services.google.redirect')) ?: url('/auth/google/callback');

        try {
            $tokenResponse = Http::asForm()->timeout(15)->post('https://oauth2.googleapis.com/token', [
                'code' => $code,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri' => $redirectUri,
                'grant_type' => 'authorization_code',
            ]);

            if (! $tokenResponse->successful()) {
                Log::warning('Google OAuth token exchange failed', [
                    'status' => $tokenResponse->status(),
                    'body' => $tokenResponse->body(),
                ]);
                return redirect('/login')->withErrors([
                    'email' => 'Failed to authenticate with Google. Please try again.',
                ]);
            }

            $accessToken = (string) ($tokenResponse->json('access_token') ?? '');
            if ($accessToken === '') {
                return redirect('/login')->withErrors([
                    'email' => 'Could not retrieve access token from Google.',
                ]);
            }

            $userResponse = Http::withToken($accessToken)->timeout(15)->get('https://www.googleapis.com/oauth2/v3/userinfo');
            if (! $userResponse->successful()) {
                return redirect('/login')->withErrors([
                    'email' => 'Failed to fetch your Google profile.',
                ]);
            }

            $googleUser = $userResponse->json();
            $googleId = trim((string) ($googleUser['sub'] ?? ''));
            $email = strtolower(trim((string) ($googleUser['email'] ?? '')));
            $name = trim((string) ($googleUser['name'] ?? ''));

            if ($email === '') {
                return redirect('/login')->withErrors([
                    'email' => 'Your Google account did not provide an email address.',
                ]);
            }

            if ($name === '') {
                $name = ucfirst(strstr($email, '@', true) ?: 'User');
            }

            $hasGoogleIdCol = Schema::hasColumn('users', 'google_id');
            $user = null;

            if ($hasGoogleIdCol && $googleId !== '') {
                $user = User::where('google_id', $googleId)->first();
            }
            if (! $user) {
                $user = User::where('email', $email)->first();
            }

            $isNewUser = false;

            if ($user) {
                if (in_array(strtolower((string) $user->account_status), ['banned', 'suspended'], true)) {
                    return redirect('/login')->withErrors([
                        'email' => 'Your account has been suspended. Please contact support.',
                    ]);
                }

                $updates = [];
                if ($hasGoogleIdCol && $googleId !== '' && empty($user->google_id)) {
                    $updates['google_id'] = $googleId;
                }
                if ($user->account_status === 'pending') {
                    $updates['account_status'] = 'active';
                    $updates['otp'] = null;
                }
                if (empty($user->email_verified_at)) {
                    $updates['email_verified_at'] = now();
                }
                if (! empty($updates)) {
                    $user->update($updates);
                }
            } else {
                $isNewUser = true;
                $referrerId = null;
                $ref = strtoupper(trim((string) $request->session()->pull('oauth_referral_code', '')));
                if ($ref !== '') {
                    $referrerId = User::where('referral_code', $ref)->value('id');
                }

                do {
                    $referralCode = strtoupper(Str::random(10));
                } while (User::where('referral_code', $referralCode)->exists());

                $createData = [
                    'name' => $name,
                    'email' => $email,
                    'country' => 'Nigeria',
                    'phone_number' => null,
                    'password' => Hash::make(Str::random(32)),
                    'walletBalance' => 0.00,
                    'account_status' => 'active',
                    'otp' => null,
                    'email_verified_at' => now(),
                    'is_admin' => false,
                    'referral_code' => $referralCode,
                    'referred_by' => $referrerId,
                ];

                if ($hasGoogleIdCol && $googleId !== '') {
                    $createData['google_id'] = $googleId;
                }

                $user = User::create($createData);

                try {
                    Mail::to($user->email)->send(new WelcomeEmail($user));
                } catch (\Throwable $e) {
                    Log::warning('Welcome Email failed to send on Google signup: ' . $e->getMessage());
                }
            }

            if ($user->two_factor_enabled) {
                $code2fa = (string) random_int(100000, 999999);
                $user->two_factor_code = Hash::make($code2fa);
                $user->two_factor_expires_at = now()->addMinutes(10);
                $user->save();

                try {
                    Mail::raw("Your may22sms login code is: {$code2fa}\n\nThis code expires in 10 minutes.", function ($message) use ($user) {
                        $message->to($user->email)->subject('Your login code');
                    });
                } catch (\Throwable $e) {
                    Log::warning('2FA email failed to send: ' . $e->getMessage());
                }

                $request->session()->put('two_factor_user_id', $user->id);
                $request->session()->put('two_factor_remember', true);

                return redirect('/two-factor');
            }

            Auth::login($user, true);
            $request->session()->regenerate();

            $user->last_login_at = now();
            $user->last_login_ip = (string) $request->ip();
            $user->last_login_user_agent = (string) $request->userAgent();
            $user->save();

            if ($user->is_admin) {
                return redirect()->intended('admin/dashboard');
            }

            if ($isNewUser || empty(trim((string) $user->phone_number))) {
                $request->session()->put('google_phone_setup_guide', true);
                return redirect('/profile')->with(
                    'success',
                    'Welcome to May22SMS! One quick final step: please enter your phone number below and click Save Changes.'
                );
            }

            return redirect()->intended('dashboard')->with(
                'success',
                'Signed in with Google successfully!'
            );
        } catch (\Throwable $e) {
            Log::error('Google OAuth callback exception: ' . $e->getMessage());
            return redirect('/login')->withErrors([
                'email' => 'An error occurred during Google Sign-In. Please try again.',
            ]);
        }
    }
}
