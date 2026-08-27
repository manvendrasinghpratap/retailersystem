<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiRequest
{
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('X-API-Key');
        if (!$apiKey || !hash_equals((string) config('app.api_key'), $apiKey)) {
            return response()->json(['status' => false,'message' => 'Unauthorized.'], 401);
        }
        return $next($request);
    }
}