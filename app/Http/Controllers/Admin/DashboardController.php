<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

class DashboardController extends Controller
{
    /**
     * Breadcrumb configuration
     */
    protected array $breadcrumb = [
        'title'              => 'Daily Summary Breakdown Report',
        'route'              => 'cumulativereport',
        'routeTitle'         => 'Daily Summary Breakdown Report',
        'route1'             => 'Daily Summary Breakdown Report',
        'route1Title'        => 'cumulativereport',
    ];

    protected array $breadcrumbWeekly = [
        'title'               => 'Weekly Breakdown Report',
        'route'               => 'weeklycumulativereport',
        'routeTitle'          => 'Weekly Breakdown Report',
        'add_new_route_title' => 'Weekly Breakdown Report',
        'add_new_route'       => 'weeklycumulativereport',
    ];

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth'); 
    }

    /**
     * Show dashboard view dynamically.
     */
    public function index(Request $request)
    {
        $view = $request->path();

        if (view()->exists($view)) {
            return view($view);
        }

        abort(404);
    }

    /**
     * Root redirect logic for admin users.
     */
    public function root(Request $request)
    {
        return view('backend.dashboard', [
            'breadcrumb' => [],
            'title' => 'Dashboard',
        ]);
    }

    /**
     * Change application language.
     */
    public function lang(string $locale)
    {
        App::setLocale($locale);

        Session::put('lang', $locale);
        Session::save();

        return redirect()->back();
    }
}
