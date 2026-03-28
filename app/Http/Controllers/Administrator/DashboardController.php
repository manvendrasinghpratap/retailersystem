<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use App\Models\Account;
use App\Http\Requests\SubscriptionRequest;
use App\Models\SubscriptionPlan;

class DashboardController extends Controller
{

    /**
     * Breadcrumb configuration
     */
    protected $breadcrumb;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->breadcrumb = [
            'title' => __('translation.dashboard'),
            'route' => '',
            'routeTitle' => '',
            'route1' => '',
            'route1Title' => '',
        ];
    }

    /**
     * Show dashboard view dynamically.
     */
    public function index(Request $request)
    {
        $this->dashboard($request);
    }

    /**
     * Root redirect logic for admin users.
     */
    public function dashboard(Request $request)
    {
        $totalSubscriptions = SubscriptionPlan::count();
        $totalClients = Account::count();
        return view('backend.administrator.dashboard.index', [
            'breadcrumb' => $this->breadcrumb,
            'title' => 'Dashboard',
            'totalSubscriptions' => $totalSubscriptions,
            'totalClients' => $totalClients,
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
