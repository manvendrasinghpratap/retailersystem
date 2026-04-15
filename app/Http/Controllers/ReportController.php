<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Sale;
use App\Models\User;
use App\Helpers\Settings;
class ReportController extends Controller
{

    protected $breadcrumbDailySales;
    protected $breadcrumbListing;

    public function __construct()
    {
        $this->middleware('auth');

        $this->breadcrumbDailySales = [
            'title' => __('translation.daily_sales_report'),
            'route1' => "reports.daily.sales",
            'route1Title' => __('translation.daily_sales_report'),
            'route2Title' => __('translation.daily_sales_report'),
            'route2' => 'reports.daily.sales',
            'reset_route' => 'reports.daily.sales',
            'reset_route_title' => __('translation.cancel')
        ];

        $this->breadcrumbListing = [
            'title' => __('translation.customers'),
            'route1' => "admin.customers.index",
            'route1Title' => __('translation.customers'),
            'route2Title' => __('translation.add_new_customer'),
            'route2' => 'admin.customers.create',
            'route3Title' => __('translation.add_new_customer'),
            'route3' => 'admin.customers.edit',
            'reset_route' => 'admin.customers.index',
            'reset_route_title' => __('translation.cancel')
        ];
    }

    public function dailySales(Request $request)
    {
        $breadcrumb = $this->breadcrumbDailySales;
        $accountId = auth()->user()->account_id;


        $staffId = $request->staff_id;

        // Query
        $query = Sale::where('account_id', $accountId)
            ->visibleToUser();
        $query = Settings::applyDateRange($query, $request, 'created_at', true);

        if ($staffId) {
            $query->where('user_id', $staffId);
        }

        $sales = $query->latest()->paginate(\Config::get('pagination'));

        // Summary
        $totalSales = $sales->sum('total');
        $totalOrders = $sales->count();

        // Staff dropdown
        $staffs = User::where('account_id', $accountId)
            ->visibleToUser()
            ->pluck('name', 'id');

        return view('backend.admin.reports.daily_sales', compact(
            'sales',
            'totalSales',
            'totalOrders',
            'staffs',
            'staffId',
            'breadcrumb'
        ));
    }
}
