<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Route as RouteModel;
use Config;
use DB;
class CheckRoutePermission
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
        $user = auth()->user();

        // ❌ If not logged in
        if (!$user) {
            abort(403, 'Unauthorized');
        }

        // Get current route name
        $routeName = $request->route()->getName();

        // ❗ If route has no name, allow or block (your choice)
        if (!$routeName) {
            return $next($request);
        }

        // Get route ID from DB
        $route = DB::table('routes')->where('name', $routeName)->first();
        if (!$route) {
            abort(403, 'Route not registered');
        }

        // Check permission
        $permission = DB::table('designation_route')
            ->where('designation_id', $user->designation_id)
            ->where('route_id', $route->id)
            ->where('is_allowed', 1)
            ->exists();

        if (!$permission) {
            abort(403, 'Access denied');
        }

        return $next($request);
    }



    // public function oldhandle($request, Closure $next)
    // {
    //     $user = Auth::user();
    //     $designationId = $user->designation_id;
    //     // Match current route by URL and Method
    //     if (in_array($designationId, \Config::get('constants.admin_Supervisor_superuser'))) {
    //         $route = RouteModel::where('name', Route::currentRouteName())
    //             ->where('method', $request->method())
    //             ->first();
    //         if (!$route) {
    //             return abort(403, 'Route not registered in ACL.');
    //         }
    //         // Check if this route is allowed for the user's designation
    //         if (!$route->designations()->where('designation_id', $designationId)->where('is_allowed', 1)->exists()) {
    //             return abort(403, 'Unauthorized access.');
    //         }
    //     }
    //     return $next($request);
    // }
}
