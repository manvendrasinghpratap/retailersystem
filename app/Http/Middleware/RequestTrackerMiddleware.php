<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class RequestTrackerMiddleware
{
    public function handle($request, Closure $next)
    {  

        
        
        $start_time = microtime(true);
        $request_data = [
            'Method' => $request->getMethod(),
            'URL' => $request->fullUrl(),
            'Headers' => $request->headers->all(),
            'Content' => $request->getContent(),
            'IP' => $request->ip(),
        ];

        //Log::info('Incoming Request', $request_data);

        $response = $next($request);

        $end_time = microtime(true);
        $execution_time = ($end_time - $start_time) * 1000; 

        $response_data = [
            'Status' => $response->getStatusCode(),
            'Headers' => $response->headers->all(),
            'Content' => $response->getContent(),
        ];

        //Log::info('Outgoing Response', $response_data);
        //Log::info("Execution Time: $execution_time ms");

        return $response;
    }
}

