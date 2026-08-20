<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAccountStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();

            if ($user->account_status !== 'active') {
                return redirect('/verify-otp');
            }

            if ($user->force_logout_at) {
                $lastLoginAt = $user->last_login_at ? \Illuminate\Support\Carbon::parse($user->last_login_at) : null;
                $forceLogoutAt = \Illuminate\Support\Carbon::parse($user->force_logout_at);
                if (! $lastLoginAt || $lastLoginAt->lt($forceLogoutAt)) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return redirect('/login')->with('error', 'You have been logged out by an admin. Please login again.');
                }
            }
        }

        return $next($request);
    }
}
