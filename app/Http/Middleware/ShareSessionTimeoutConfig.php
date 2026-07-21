<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class ShareSessionTimeoutConfig
{
    public function handle(Request $request, Closure $next)
    {
        View::share('sessionTimeout', [
            'enabled' => config('session_timeout.enabled'),
            'idle_minutes' => config('session_timeout.idle_minutes'),
            'warning_seconds' => config('session_timeout.warning_seconds'),
            'keep_alive_url' => route('session.keepalive'),
            'logout_url' => route('session.logout'),
        ]);

        return $next($request);
    }
}