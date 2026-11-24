<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        $isStaff = (bool) ($user && ((bool) $user->is_admin || (bool) ($user->is_support ?? false) || (bool) ($user->is_editor ?? false)));
        if (! $isStaff) {
            return redirect('/dashboard')->with('error', 'You are not allowed to access the admin area.');
        }

        if (Schema::hasTable('settings')) {
            $requireStaff2fa = (int) (Setting::where('key', 'require_staff_2fa')->value('value') ?? 0) === 1;
            if ($requireStaff2fa && ! (bool) ($user->two_factor_enabled ?? false)) {
                return redirect('/security')->with('error', 'Two-factor authentication is required for staff accounts. Enable it to continue.');
            }
        }

        if ((bool) $user->is_admin) {
            return $next($request);
        }

        if ($request->is('admin/dashboard')) {
            return $next($request);
        }

        $permissions = (array) ($user->staff_permissions ?? []);
        $required = null;

        if ($request->is('admin/users*')) {
            $required = 'users';
        } elseif ($request->is('admin/transactions*')) {
            $required = 'transactions';
        } elseif ($request->is('admin/orders*')) {
            $required = 'orders';
        } elseif ($request->is('admin/logs*')) {
            $required = 'logs';
        } elseif ($request->is('admin/boosting*')) {
            $required = 'boosting';
        } elseif ($request->is('admin/funding*')) {
            $required = 'funding';
        } elseif ($request->is('admin/tickets*')) {
            $required = 'tickets';
        } elseif ($request->is('admin/settings*') || $request->is('admin/services*') || $request->is('admin/seasonal*')) {
            $required = 'settings';
        } elseif ($request->is('admin/promo-codes*')) {
            $required = 'promos';
        } else {
            $required = '__admin_only__';
        }

        if ($required !== null && ! in_array($required, $permissions, true)) {
            return redirect('/admin/dashboard')->with('error', 'You do not have permission to access that page.');
        }

        return $next($request);
    }
}
