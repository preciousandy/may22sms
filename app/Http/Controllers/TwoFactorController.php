<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class TwoFactorController extends Controller
{
    public function show()
    {
        if (! session()->has('two_factor_user_id')) {
            return redirect('/login');
        }

        return view('auth.two-factor');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:10',
        ]);

        $userId = session('two_factor_user_id');
        $remember = (bool) session('two_factor_remember', false);
        if (! $userId) {
            return redirect('/login');
        }

        $user = User::find($userId);
        if (! $user || ! $user->two_factor_enabled) {
            return redirect('/login');
        }

        if (! $user->two_factor_expires_at || now()->greaterThan($user->two_factor_expires_at)) {
            return back()->withErrors(['code' => 'Code expired. Please resend a new code.']);
        }

        if (! $user->two_factor_code || ! Hash::check((string) $request->code, (string) $user->two_factor_code)) {
            return back()->withErrors(['code' => 'Invalid code.']);
        }

        $user->two_factor_code = null;
        $user->two_factor_expires_at = null;
        $user->last_login_at = now();
        $user->last_login_ip = (string) request()->ip();
        $user->last_login_user_agent = (string) request()->userAgent();
        $user->save();

        Auth::login($user, $remember);
        $request->session()->forget(['two_factor_user_id', 'two_factor_remember']);
        $request->session()->regenerate();

        if ($user->is_admin) {
            return redirect()->intended('admin/dashboard');
        }

        return redirect()->intended('dashboard');
    }

    public function resend(Request $request)
    {
        $userId = session('two_factor_user_id');
        if (! $userId) {
            return redirect('/login');
        }

        $user = User::find($userId);
        if (! $user || ! $user->two_factor_enabled) {
            return redirect('/login');
        }

        $code = (string) random_int(100000, 999999);
        $user->two_factor_code = Hash::make($code);
        $user->two_factor_expires_at = now()->addMinutes(10);
        $user->save();

        Mail::raw("Your may22sms login code is: {$code}\n\nThis code expires in 10 minutes.", function ($message) use ($user) {
            $message->to($user->email)->subject('Your login code');
        });

        return back()->with('success', 'A new code has been sent to your email.');
    }
}
