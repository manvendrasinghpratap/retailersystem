<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Helpers\Settings;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\Product;

class DashboardController extends Controller
{
    /**
     * Dashboard data for mobile application.
     *
     * Authentication:
     * - auth:api
     * - api.request
     */
    public function index(Request $request)
    {
        try {
            /*
            |--------------------------------------------------------------------------
            | Date
            |--------------------------------------------------------------------------
            */

            $date = $request->date
                ? Settings::formatDate($request->date, 'Y-m-d')
                : Carbon::now()->format('Y-m-d');

            $selectedDate = Carbon::parse($date);

            /*
            |--------------------------------------------------------------------------
            | Logged-in User / Account
            |--------------------------------------------------------------------------
            */

            $user = auth('api')->user();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized.'
                ], 401);
            }

            $accountId = $user->account_id;

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
            $hourlyTotals = array_fill(0, 24, 0);

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
                $hourlyTotals[(int) $sale->hour] = (float) $sale->total_sales;
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
                ->whereBetween('created_at', [$weekStart, $weekEnd])
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
                ->whereBetween('created_at', [$monthStart, $monthEnd])
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
            */

            $productDaily = DB::table('sale_items')
                ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
                ->join('products', 'products.id', '=', 'sale_items.product_id')
                ->where('sales.account_id', $accountId)
                ->whereDate('sales.created_at', $selectedDate)
                ->select(
                    'products.id',
                    'products.name',
                    DB::raw('SUM(sale_items.quantity) as total_items_sold'),
                    DB::raw('SUM(sale_items.total) as total_revenue')
                )
                ->groupBy('products.id', 'products.name')
                ->orderByDesc('total_items_sold')
                ->get();

            /*
            |--------------------------------------------------------------------------
            | PRODUCT WEEKLY
            |--------------------------------------------------------------------------
            */

            $productWeekly = DB::table('sale_items')
                ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
                ->join('products', 'products.id', '=', 'sale_items.product_id')
                ->where('sales.account_id', $accountId)
                ->whereBetween('sales.created_at', [
                    $weekStart,
                    $weekEnd
                ])
                ->select(
                    'products.id',
                    'products.name',
                    DB::raw('SUM(sale_items.quantity) as total_items_sold'),
                    DB::raw('SUM(sale_items.total) as total_revenue')
                )
                ->groupBy('products.id', 'products.name')
                ->orderByDesc('total_items_sold')
                ->get();

            /*
            |--------------------------------------------------------------------------
            | PRODUCT MONTHLY
            |--------------------------------------------------------------------------
            */

            $productMonthly = DB::table('sale_items')
                ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
                ->join('products', 'products.id', '=', 'sale_items.product_id')
                ->where('sales.account_id', $accountId)
                ->whereBetween('sales.created_at', [
                    $monthStart,
                    $monthEnd
                ])
                ->select(
                    'products.id',
                    'products.name',
                    DB::raw('SUM(sale_items.quantity) as total_items_sold'),
                    DB::raw('SUM(sale_items.total) as total_revenue')
                )
                ->groupBy('products.id', 'products.name')
                ->orderByDesc('total_items_sold')
                ->get();

            /*
            |--------------------------------------------------------------------------
            | KPI
            |--------------------------------------------------------------------------
            */

            $totalRevenue = Sale::where('account_id', $accountId)
                ->visibleToUser()
                ->whereDate('created_at', $date)
                ->sum('total');

            $totalOrders = Sale::where('account_id', $accountId)
                ->visibleToUser()
                ->whereDate('created_at', $date)
                ->count();

            $totalItemsSold = DB::table('sale_items')
                ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
                ->where('sales.account_id', $accountId)
                ->whereDate('sales.created_at', $date)
                ->sum('sale_items.quantity');

            $totalCustomers = Customer::where('account_id', $accountId)
                ->count();

            $totalProducts = Product::where('account_id', $accountId)
                ->count();

            /*
            |--------------------------------------------------------------------------
            | API RESPONSE
            |--------------------------------------------------------------------------
            */

            return response()->json([
                'status' => true,

                'message' => 'Dashboard data retrieved successfully.',

                'data' => [

                    /*
                    |--------------------------------------------------------------------------
                    | Selected Date
                    |--------------------------------------------------------------------------
                    */

                    'date' => $date,

                    /*
                    |--------------------------------------------------------------------------
                    | KPI
                    |--------------------------------------------------------------------------
                    */

                    'kpi' => [
                        'total_revenue' => (float) $totalRevenue,
                        'total_orders' => (int) $totalOrders,
                        'total_items_sold' => (float) $totalItemsSold,
                        'total_customers' => (int) $totalCustomers,
                        'total_products' => (int) $totalProducts,
                    ],

                    /*
                    |--------------------------------------------------------------------------
                    | Hourly Sales Chart
                    |--------------------------------------------------------------------------
                    */

                    'hourly_sales' => [
                        'categories' => $hours,
                        'series' => [
                            [
                                'name' => 'Sales',
                                'data' => $hourlyTotals,
                            ]
                        ],
                    ],

                    /*
                    |--------------------------------------------------------------------------
                    | Weekly Sales Chart
                    |--------------------------------------------------------------------------
                    */

                    'weekly_sales' => [
                        'categories' => $weekLabels,
                        'series' => [
                            [
                                'name' => 'Sales',
                                'data' => $weeklyTotals,
                            ]
                        ],
                    ],

                    /*
                    |--------------------------------------------------------------------------
                    | Monthly Sales Chart
                    |--------------------------------------------------------------------------
                    */

                    'monthly_sales' => [
                        'categories' => $monthLabels,
                        'series' => [
                            [
                                'name' => 'Sales',
                                'data' => $monthlyTotals,
                            ]
                        ],
                    ],

                    /*
                    |--------------------------------------------------------------------------
                    | Product Sales
                    |--------------------------------------------------------------------------
                    */

                    'products' => [
                        'daily' => $productDaily,
                        'weekly' => $productWeekly,
                        'monthly' => $productMonthly,
                    ],
                ],
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'status' => false,
                'message' => 'Unable to retrieve dashboard data.',
                'error' => config('app.debug')
                    ? $e->getMessage()
                    : null,
            ], 500);
        }
    }
}
