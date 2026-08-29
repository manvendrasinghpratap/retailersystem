<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Settings;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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

            'breadcrumb' => [
                [
                    'route' => 'admin.dashboard',
                    'title' => __('translation.dashboard')
                ],
                [
                    'route' => 'billing.index',
                    'title' => __('translation.billing')
                ]
            ],

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

    public function index(Request $request)
    {
        $breadcrumb = $this->breadcrumbDashboard;

        /*
        |--------------------------------------------------------------------------
        | Selected Date
        |--------------------------------------------------------------------------
        */

        $date = $request->date
            ? Settings::formatDate($request->date, 'Y-m-d')
            : Carbon::now()->format('Y-m-d');

        $selectedDate = Carbon::parse($date);

        $accountId = auth()->user()->account_id;


        /*
        |--------------------------------------------------------------------------
        | HOURLY SALES
        |--------------------------------------------------------------------------
        */

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

            if ($i == 0) {

                $label = '12 AM';

            } elseif ($i < 12) {

                $label = $i . ' AM';

            } elseif ($i == 12) {

                $label = '12 Noon';

            } else {

                $label = ($i - 12) . ' PM';
            }

            $hours[] = $label;
        }

        foreach ($sales as $sale) {

            $totals[$sale->hour] = (float) $sale->total_sales;
        }


        /*
        |--------------------------------------------------------------------------
        | WEEKLY SALES
        |--------------------------------------------------------------------------
        */

        $weekStart = $selectedDate->copy()->startOfWeek();

        $weekEnd = $selectedDate->copy()->endOfWeek();

        $weeklySales = Sale::where('account_id', $accountId)
            ->visibleToUser()
            ->whereBetween('created_at', [
                $weekStart,
                $weekEnd
            ])
            ->select(
                DB::raw('DATE(created_at) as sale_date'),
                DB::raw('SUM(total) as total_sales')
            )
            ->groupBy('sale_date')
            ->orderBy('sale_date')
            ->get()
            ->keyBy('sale_date');

        $weekLabels = [];

        $weeklyTotals = [];

        for ($i = 0; $i < 7; $i++) {

            $day = $weekStart->copy()->addDays($i);

            $key = $day->format('Y-m-d');

            $weekLabels[] = $day->format('D');

            $weeklyTotals[] = isset($weeklySales[$key])
                ? (float) $weeklySales[$key]->total_sales
                : 0;
        }


        /*
        |--------------------------------------------------------------------------
        | MONTHLY SALES
        |--------------------------------------------------------------------------
        */

        $monthStart = $selectedDate->copy()->startOfMonth();

        $monthEnd = $selectedDate->copy()->endOfMonth();

        $monthlySales = Sale::where('account_id', $accountId)
            ->visibleToUser()
            ->whereBetween('created_at', [
                $monthStart,
                $monthEnd
            ])
            ->select(
                DB::raw('DAY(created_at) as day_no'),
                DB::raw('SUM(total) as total_sales')
            )
            ->groupBy('day_no')
            ->orderBy('day_no')
            ->get()
            ->keyBy('day_no');

        $monthLabels = [];

        $monthlyTotals = [];

        for ($i = 1; $i <= $selectedDate->daysInMonth; $i++) {

            $monthLabels[] = $i;

            $monthlyTotals[] = isset($monthlySales[$i])
                ? (float) $monthlySales[$i]->total_sales
                : 0;
        }


        /*
        |--------------------------------------------------------------------------
        | PRODUCT DAILY
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | Group by master_item_id instead of products.id.
        |
        | This ensures that multiple product records belonging
        | to the same master item are combined.
        |
        */

        $productDaily = DB::table('sale_items')
            ->join(
                'sales',
                'sales.id',
                '=',
                'sale_items.sale_id'
            )
            ->join(
                'products',
                'products.id',
                '=',
                'sale_items.product_id'
            )
            ->where(
                'sales.account_id',
                $accountId
            )
            ->whereDate(
                'sales.created_at',
                $selectedDate
            )
            ->select(
                'products.master_item_id',

                DB::raw(
                    'MAX(products.name) as name'
                ),

                DB::raw(
                    'SUM(sale_items.quantity) as total_items_sold'
                ),

                DB::raw(
                    'SUM(sale_items.total) as total_revenue'
                )
            )
            ->groupBy(
                'products.master_item_id'
            )
            ->orderByDesc(
                'total_items_sold'
            )
            ->get();


        /*
        |--------------------------------------------------------------------------
        | PRODUCT DAILY - FORMAT PRODUCT NAME
        |--------------------------------------------------------------------------
        */

        $productDaily->transform(function ($item) {

            $item->name = ucwords($item->name);

            return $item;
        });


        /*
        |--------------------------------------------------------------------------
        | PRODUCT WEEKLY
        |--------------------------------------------------------------------------
        */

        $productWeekly = DB::table('sale_items')
            ->join(
                'sales',
                'sales.id',
                '=',
                'sale_items.sale_id'
            )
            ->join(
                'products',
                'products.id',
                '=',
                'sale_items.product_id'
            )
            ->where(
                'sales.account_id',
                $accountId
            )
            ->whereBetween(
                'sales.created_at',
                [
                    $weekStart,
                    $weekEnd
                ]
            )
            ->select(
                'products.master_item_id',

                DB::raw(
                    'MAX(products.name) as name'
                ),

                DB::raw(
                    'SUM(sale_items.quantity) as total_items_sold'
                ),

                DB::raw(
                    'SUM(sale_items.total) as total_revenue'
                )
            )
            ->groupBy(
                'products.master_item_id'
            )
            ->orderByDesc(
                'total_items_sold'
            )
            ->get();


        /*
        |--------------------------------------------------------------------------
        | PRODUCT WEEKLY - FORMAT PRODUCT NAME
        |--------------------------------------------------------------------------
        */

        $productWeekly->transform(function ($item) {

            $item->name = ucwords($item->name);

            return $item;
        });


        /*
        |--------------------------------------------------------------------------
        | PRODUCT MONTHLY
        |--------------------------------------------------------------------------
        */

        $productMonthly = DB::table('sale_items')
            ->join(
                'sales',
                'sales.id',
                '=',
                'sale_items.sale_id'
            )
            ->join(
                'products',
                'products.id',
                '=',
                'sale_items.product_id'
            )
            ->where(
                'sales.account_id',
                $accountId
            )
            ->whereBetween(
                'sales.created_at',
                [
                    $monthStart,
                    $monthEnd
                ]
            )
            ->select(
                'products.master_item_id',

                DB::raw(
                    'MAX(products.name) as name'
                ),

                DB::raw(
                    'SUM(sale_items.quantity) as total_items_sold'
                ),

                DB::raw(
                    'SUM(sale_items.total) as total_revenue'
                )
            )
            ->groupBy(
                'products.master_item_id'
            )
            ->orderByDesc(
                'total_items_sold'
            )
            ->get();


        /*
        |--------------------------------------------------------------------------
        | PRODUCT MONTHLY - FORMAT PRODUCT NAME
        |--------------------------------------------------------------------------
        */

        $productMonthly->transform(function ($item) {

            $item->name = ucwords($item->name);

            return $item;
        });


        /*
        |--------------------------------------------------------------------------
        | KPI - TOTAL REVENUE
        |--------------------------------------------------------------------------
        */

        $totalRevenue = Sale::where(
                'account_id',
                $accountId
            )
            ->whereDate(
                'created_at',
                $date
            )
            ->sum('total');


        /*
        |--------------------------------------------------------------------------
        | KPI - TOTAL ORDERS
        |--------------------------------------------------------------------------
        */

        $totalOrders = Sale::where(
                'account_id',
                $accountId
            )
            ->whereDate(
                'created_at',
                $date
            )
            ->count();


        /*
        |--------------------------------------------------------------------------
        | KPI - TOTAL ITEMS SOLD
        |--------------------------------------------------------------------------
        */

        $totalItemsSold = DB::table('sale_items')
            ->join(
                'sales',
                'sales.id',
                '=',
                'sale_items.sale_id'
            )
            ->where(
                'sales.account_id',
                $accountId
            )
            ->whereDate(
                'sales.created_at',
                $date
            )
            ->sum('sale_items.quantity');


        /*
        |--------------------------------------------------------------------------
        | KPI - TOTAL CUSTOMERS
        |--------------------------------------------------------------------------
        */

        $totalCustomers = Customer::where(
            'account_id',
            $accountId
        )->count();


        /*
        |--------------------------------------------------------------------------
        | KPI - TOTAL PRODUCTS
        |--------------------------------------------------------------------------
        */

        $totalProducts = Product::where('account_id', $accountId)->whereNotNull('master_item_id')->distinct('master_item_id')->count('master_item_id');


        /*
        |--------------------------------------------------------------------------
        | RETURN DASHBOARD VIEW
        |--------------------------------------------------------------------------
        */

        return view(
            'backend.admin.dashboard.chart',
            compact(
                'hours',
                'totals',

                'weekLabels',
                'weeklyTotals',

                'monthLabels',
                'monthlyTotals',

                'date',

                'productDaily',
                'productWeekly',
                'productMonthly',

                'totalRevenue',
                'totalOrders',
                'totalItemsSold',
                'totalCustomers',
                'totalProducts',

                'breadcrumb'
            )
        );
    }
}