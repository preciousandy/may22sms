<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AdminAuthController extends Controller
{
    public function showLogin()
    {
        return view('admin.auth.login');
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
        $isStaff = (bool) ($user && ((bool) $user->is_admin || (bool) ($user->is_support ?? false) || (bool) ($user->is_editor ?? false)));
        if (! $user || ! Hash::check($credentials['password'], (string) $user->password) || ! $isStaff) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');
        }

        if ($user->account_status !== 'active') {
            return back()->withErrors([
                'email' => 'This staff account is not active.',
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
                Log::warning('Admin 2FA email failed to send: '.$e->getMessage());
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

        return redirect()->intended('admin/dashboard');
    }
}
