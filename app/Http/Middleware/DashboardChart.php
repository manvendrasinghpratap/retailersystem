<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Config;
class DashboardChart
{
    public function handle($request, Closure $next)
    {		
           if (Auth::check() && in_array(Auth::user()->designation_id, Config::get('constants.only_admin')) && request()->route()->getName() == 'salesperday') {
			   return $next($request);
			}
		return redirect()->route('itemOrder');
    }

    
}
