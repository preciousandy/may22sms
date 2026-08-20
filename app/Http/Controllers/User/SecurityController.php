<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SecurityController extends Controller
{
    public function index()
    {
        return view('user.security');
    }

    public function enableTwoFactor(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
        ]);

        $user = $request->user();
        $user->two_factor_enabled = true;
        $user->save();

        return back()->with('success', 'Two-factor authentication enabled.');
    }

    public function disableTwoFactor(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
        ]);

        $user = $request->user();
        $user->two_factor_enabled = false;
        $user->two_factor_code = null;
        $user->two_factor_expires_at = null;
        $user->save();

        return back()->with('success', 'Two-factor authentication disabled.');
    }

    public function logoutOtherDevices(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
        ]);

        Auth::logoutOtherDevices((string) $request->current_password);

        return back()->with('success', 'Logged out of other devices.');
    }
}
