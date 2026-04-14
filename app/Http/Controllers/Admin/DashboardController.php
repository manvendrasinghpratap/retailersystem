<?php

namespace App\Http\Controllers\Admin;
use App\Helpers\Settings;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Redirect;
use PDF;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Customer;
use App\Models\Product;


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
        $this->breadcrumbDashboard = [
            'title' => __('translation.dashboard'),
            'route1' => "admin.dashboard",
            'route1Title' => __('translation.dashboard'),
            'route2Title' => __('translation.dashboard'),
            'route2' => 'admin.dashboard',
            'route3Title' => __('translation.dashboard'),
            'route3' => 'admin.dashboard',
            'reset_route' => 'admin.sales.index',
            'reset_route_title' => __('translation.cancel'),
            'route4' => 'billing.index',
            'route4Title' => __('translation.billing'),

        ];
    }

    public function indexold(Request $request)
    {
        $breadcrumb = $this->breadcrumbDashboard;
        return view('backend.admin.dashboard.index', compact('breadcrumb'));
    }


    public function index(Request $request)
    {
        $breadcrumb = $this->breadcrumbDashboard;
        $date = $request->date ? Settings::formatDate($request->date, 'Y-m-d') : Carbon::now();
        // Hourly Sales
        $sales = Sale::where('account_id', auth()->user()->account_id)->visibleToUser()
            ->whereDate('created_at', $date)
            ->select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('SUM(total) as total_sales')
            )
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        $hours = [];
        $totals = array_fill(0, 24, 0);

        for ($i = 0; $i < 24; $i++) {
            $hours[] = str_pad($i, 2, '0', STR_PAD_LEFT) . ':00';
        }

        foreach ($sales as $sale) {
            $totals[$sale->hour] = (float) $sale->total_sales;
        }

        // KPI Data
        $totalRevenue = Sale::where('account_id', auth()->user()->account_id)->visibleToUser()
            ->whereDate('created_at', $date)
            ->sum('total');

        $totalOrders = Sale::where('account_id', auth()->user()->account_id)->visibleToUser()
            ->whereDate('created_at', $date)
            ->count();

        $totalCustomers = Customer::where('account_id', auth()->user()->account_id)->count();

        $totalProducts = Product::where('account_id', auth()->user()->account_id)->count();

        return view('backend.admin.dashboard.chart', compact(
            'hours',
            'totals',
            'date',
            'totalRevenue',
            'totalOrders',
            'totalCustomers',
            'totalProducts',
            'breadcrumb'
        ));
    }

}
