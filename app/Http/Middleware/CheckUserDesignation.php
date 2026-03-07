<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Config;

class CheckUserDesignation
{
    public function handle($request, Closure $next)
    {
           if (!(Auth::check() && Auth::user()->user_type == 10)) {
            $user = auth()->user();
            if ($user && !in_array(auth()->user()->designation_id,Config::get('constants.admin_Supervisor'))) {
                return redirect()->route('index');
            }
            return $next($request);
        }

        return abort(403); // or redirect to a different error page
    }
}
