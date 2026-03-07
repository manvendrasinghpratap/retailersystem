<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class OnlyStaffCanAccessMenu
{
    public function handle($request, Closure $next)
    {
        // If not logged-in → redirect to our-products
        if (!Auth::check()) {
            return redirect()->route('our-products');
        }

        // If logged-in user is NOT staff → redirect also
        if (!Auth::user()->is_staff) {   // change field name if different
            return redirect()->route('our-products');
        }

        return $next($request);
    }
}
