<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckSubscription
{
    public function handle(Request $request, Closure $next)
    {
        // Skip if not logged in
        if (!auth()->check()) {
            return $next($request);
        }
        $account = auth()->user()->account;

        if (auth()->user()->id != 1) {
            if (!$account || !$account->isActive()) {

                auth()->logout();

                return redirect()->route('login')
                    ->with('error', 'Your subscription has expired.');
            }
            if (!$account) {
                auth()->logout();

                return redirect()->route('login')
                    ->with('error', 'Account not found.');
            }
        }

        return $next($request);
    }
}