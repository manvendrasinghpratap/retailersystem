<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        
        $apiKey = $request->header('X-API-Key'); 
        if ($apiKey !== 'ZWZNn1FC8F0NCO7Ac7YZojlUITPupOXsDV3BLvS3W2M=') { 
            return response()->json(['status'=>'401','error' => 'Unauthorized']);
        }
        return $next($request);
    }
}
