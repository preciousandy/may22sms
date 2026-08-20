<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create()
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->input('email'))->first();

        if (! $user) {
            return back()->with('status', __(Password::RESET_LINK_SENT));
        }

        try {
            $status = Password::sendResetLink(
                $request->only('email')
            );

            return $status === Password::RESET_LINK_SENT
                ? back()->with('status', __($status))
                : back()->withErrors(['email' => __($status)]);
        } catch (\Throwable $e) {
            Log::error('Password reset email failed (SMTP Error): ' . $e->getMessage(), [
                'email' => $request->input('email'),
            ]);

            // Fallback: Generate token & log reset URL so link is accessible even if SMTP auth fails
            try {
                $token = Password::createToken($user);
                $resetUrl = url(route('password.reset', [
                    'token' => $token,
                    'email' => $user->email,
                ], false));

                Log::info('PASSWORD RESET LINK GENERATED (SMTP Fallback Log):', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'reset_url' => $resetUrl,
                ]);

                return back()->with('status', __(Password::RESET_LINK_SENT));
            } catch (\Throwable $tokenErr) {
                Log::error('Failed to create fallback reset token: ' . $tokenErr->getMessage());

                return back()->withErrors([
                    'email' => 'Unable to send password reset email at this time. Please try again later.'
                ]);
            }
        }
    }

    /**
     * Display the password reset view.
     */
    public function edit(Request $request, $token = null)
    {
        $token = $token ?: $request->route('token');

        return view('auth.reset-password', [
            'request' => $request,
            'token' => $token,
            'email' => $request->query('email', old('email', '')),
        ]);
    }

    /**
     * Handle an incoming new password request.
     */
    public function update(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        try {
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    $user->forceFill([
                        'password' => Hash::make($password),
                    ])->setRememberToken(Str::random(60));

                    $user->save();

                    event(new PasswordReset($user));
                }
            );

            return $status === Password::PASSWORD_RESET
                ? redirect()->route('login')->with('status', __($status))
                : back()->withErrors(['email' => __($status)]);
        } catch (\Throwable $e) {
            Log::error('Password reset update error: ' . $e->getMessage());

            return back()->withErrors([
                'email' => 'An error occurred while resetting your password. Please try again.'
            ]);
        }
    }
}
