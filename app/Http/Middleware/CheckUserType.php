<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckUserType
{
    public function handle($request, Closure $next)
    {
            //echo $routeName = $request->route()->getName(); die();
            //echo auth()->user()->designation_id; die();

           if (!(Auth::check() && Auth::user()->user_type == 10)) {
            $user = auth()->user();
            if ($user && $user->is_password_changed === 0 && !$this->shouldExcludeRoute($request)) {
                return redirect()->route('editPassword');
            }
            if ($user && $user->designation_id === 12 && $this->shouldRestrictRoute($request)) {
                return abort(403);
            }

            return $next($request);
        }

        return abort(403); // or redirect to a different error page
    }
	
    private function shouldRestrictRoute($request)
    {
        $routeName = $request->route()->getName();
        $restrictedPrefixes = [
            'staff',
            'itemOrder',
            'cumulativereport',
            'damagelist',
            'damage',
            'inventorydamagelistpdf',
            'inventorydamagelistcsv',
            'kitchen.inventory',
            'guestcomplimentory',

        ];
		$allowRoutes = [
			'staffreport'
		];
		foreach ($allowRoutes as $prefix) {
            if (str_starts_with($routeName, $prefix)) {
                return false;
            }			
        }
        foreach ($restrictedPrefixes as $prefix) {
            if (str_starts_with($routeName, $prefix)) {
                return true;
            }			
        }
		
		
        return false;
    }    
}
