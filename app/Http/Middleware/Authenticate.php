<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    protected function redirectTo($request)
    { 
        return $request->expectsJson() ? null : route('login'); 
    }

    public function authenticated($request, $user)
    {
        return redirect()->route('index');
    }
}
