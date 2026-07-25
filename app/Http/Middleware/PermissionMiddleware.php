<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(
        Request $request,
        Closure $next,
        string $permission
    ): Response {
        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | User Authentication Check
        |--------------------------------------------------------------------------
        */

        if (!$user) {
            abort(401);
        }

        /*
        |--------------------------------------------------------------------------
        | Permission Check
        |--------------------------------------------------------------------------
        */

        if (!$user->hasPermission($permission)) {
            abort(403);
        }

        return $next($request);
    }
}