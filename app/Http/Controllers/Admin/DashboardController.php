<?php

namespace App\Http\Controllers\Admin;
use App\Helpers\Settings;
use App\Http\Controllers\Controller;

use App\Models\BulkOffer;
use App\Models\Countries;
use App\Models\Customer;
use App\Models\LocalGovernment;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderDetail;
use App\Models\OrderPaymentDetail;
use App\Models\OrderPaymentHistory;
use App\Models\State;
use App\Models\User;
use App\Models\UserDetail;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Helpers\CommonHelper;
use Redirect;
use PDF;


class DashboardController extends Controller
{

    protected $breadcrumbDashboard;
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth'); 
        $this->breadcrumbDashboard = ['title' => __('translation.dashboard'), 'route' => 'dashboard', 'routeTitle' => __('translation.add_order'), 'add_new_route_title' => __('translation.add_order')];
    }  

    public function index(Request $request)
    {
        $breadcrumb = $this->breadcrumbDashboard;
        return view('backend.admin.dashboard.index',compact('breadcrumb'));
    }
}
