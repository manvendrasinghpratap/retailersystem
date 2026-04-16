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

        $date = $request->date
            ? Settings::formatDate($request->date, 'Y-m-d')
            : Carbon::now()->format('Y-m-d');

        $selectedDate = Carbon::parse($date);
        $accountId = auth()->user()->account_id;

        // =============================
        // HOURLY SALES (Revenue-based)
        // =============================
        $sales = Sale::where('account_id', $accountId)
            ->visibleToUser()
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

        // =============================
        // PRODUCT DAILY (Items Sold)
        // =============================
        $productDaily = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->where('sales.account_id', $accountId)
            ->whereDate('sales.created_at', $selectedDate)
            ->select(
                'products.name',
                DB::raw('SUM(sale_items.quantity) as total_items_sold'),
                DB::raw('SUM(sale_items.total) as total_revenue')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_items_sold') // ✅ sort by quantity
            ->get();

        // =============================
        // PRODUCT WEEKLY (Items Sold)
        // =============================
        $productWeekly = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->where('sales.account_id', $accountId)
            ->whereBetween('sales.created_at', [
                $selectedDate->copy()->startOfWeek(),
                $selectedDate->copy()->endOfWeek()
            ])
            ->select(
                'products.name',
                DB::raw('SUM(sale_items.quantity) as total_items_sold'),
                DB::raw('SUM(sale_items.total) as total_revenue')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_items_sold')
            ->get();

        // =============================
        // PRODUCT MONTHLY (Items Sold)
        // =============================
        $productMonthly = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->where('sales.account_id', $accountId)
            ->whereBetween('sales.created_at', [
                $selectedDate->copy()->startOfMonth(),
                $selectedDate->copy()->endOfMonth()
            ])
            ->select(
                'products.name',
                DB::raw('SUM(sale_items.quantity) as total_items_sold'),
                DB::raw('SUM(sale_items.total) as total_revenue')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_items_sold')
            ->get();

        // =============================
        // KPI
        // =============================
        $totalRevenue = Sale::where('account_id', $accountId)
            ->whereDate('created_at', $date)
            ->sum('total');

        $totalOrders = Sale::where('account_id', $accountId)
            ->whereDate('created_at', $date)
            ->count();

        // ✅ NEW: Total Items Sold (important KPI)
        $totalItemsSold = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.account_id', $accountId)
            ->whereDate('sales.created_at', $date)
            ->sum('sale_items.quantity');

        $totalCustomers = Customer::where('account_id', $accountId)->count();
        $totalProducts = Product::where('account_id', $accountId)->count();

        return view('backend.admin.dashboard.chart', compact(
            'hours',
            'totals',
            'date',
            'productDaily',
            'productWeekly',
            'productMonthly',
            'totalRevenue',
            'totalOrders',
            'totalItemsSold', // ✅ added
            'totalCustomers',
            'totalProducts',
            'breadcrumb'
        ));
    }

    public function indexper(Request $request)
    {
        $breadcrumb = $this->breadcrumbDashboard;

        $date = $request->date
            ? Settings::formatDate($request->date, 'Y-m-d')
            : Carbon::now()->format('Y-m-d');

        $selectedDate = Carbon::parse($date);
        $accountId = auth()->user()->account_id;

        // =============================
        // HOURLY SALES
        // =============================
        $sales = Sale::where('account_id', $accountId)
            ->visibleToUser()
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

        // =============================
        // PRODUCT DAILY
        // =============================
        $productDaily = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->where('sales.account_id', $accountId)
            ->whereDate('sales.created_at', $selectedDate)
            ->select(
                'products.name',
                DB::raw('SUM(sale_items.quantity) as qty'),
                DB::raw('SUM(sale_items.total) as amount')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('amount')
            ->get();

        // =============================
        // PRODUCT WEEKLY
        // =============================
        $productWeekly = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->where('sales.account_id', $accountId)
            ->whereBetween('sales.created_at', [
                $selectedDate->copy()->startOfWeek(),
                $selectedDate->copy()->endOfWeek()
            ])
            ->select(
                'products.name',
                DB::raw('SUM(sale_items.quantity) as qty'),
                DB::raw('SUM(sale_items.total) as amount')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('amount')
            ->get();

        // =============================
        // PRODUCT MONTHLY
        // =============================
        $productMonthly = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->where('sales.account_id', $accountId)
            ->whereBetween('sales.created_at', [
                $selectedDate->copy()->startOfMonth(),
                $selectedDate->copy()->endOfMonth()
            ])
            ->select(
                'products.name',
                DB::raw('SUM(sale_items.quantity) as qty'),
                DB::raw('SUM(sale_items.total) as amount')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('amount')
            ->get();

        // =============================
        // KPI
        // =============================
        $totalRevenue = Sale::where('account_id', $accountId)
            ->whereDate('created_at', $date)
            ->sum('total');

        $totalOrders = Sale::where('account_id', $accountId)
            ->whereDate('created_at', $date)
            ->count();

        $totalCustomers = Customer::where('account_id', $accountId)->count();
        $totalProducts = Product::where('account_id', $accountId)->count();

        return view('backend.admin.dashboard.chart', compact(
            'hours',
            'totals',
            'date',
            'productDaily',
            'productWeekly',
            'productMonthly',
            'totalRevenue',
            'totalOrders',
            'totalCustomers',
            'totalProducts',
            'breadcrumb'
        ));
    }
    public function indeold(Request $request)
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
