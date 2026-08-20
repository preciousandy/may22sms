<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class BlockIp
{
    public function handle(Request $request, Closure $next)
    {
        if (! Schema::hasTable('settings')) {
            return $next($request);
        }

        $ips = Cache::remember('security:blocked_ips:v1', now()->addMinutes(1), function () {
            $raw = (string) (Setting::where('key', 'blocked_ips')->value('value') ?? '');
            $ips = [];
            if ($raw !== '') {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $ips = array_values(array_filter(array_map(function ($v) {
                        return is_string($v) ? trim($v) : null;
                    }, $decoded)));
                }
            }
            return $ips;
        });

        if (! empty($ips)) {
            $ip = (string) $request->ip();
            if ($ip !== '' && in_array($ip, $ips, true)) {
                abort(403, 'Access denied.');
            }
        }

        return $next($request);
    }
}
