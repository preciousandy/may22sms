<?php

namespace App\Http\Controllers;

use App\Mail\SendOtpMail;
use App\Mail\WelcomeEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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
}
