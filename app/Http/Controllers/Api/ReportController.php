<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Sale;
use App\Models\User;
use App\Models\SalePayment;
use App\Models\PaymentType;
use App\Models\PaymentMethod;
use App\Helpers\Settings;

class ReportController extends Controller
{
    /**
     * Daily Sales Report
     *
     * API endpoint:
     * GET /api/reports/daily-sales
     *
     * Supported filters:
     * - from_date
     * - to_date
     * - invoice_no
     * - staff_id
     */
    public function dailySales(Request $request)
    {
        try {

            /*
            |--------------------------------------------------------------------------
            | AUTHENTICATED USER
            |--------------------------------------------------------------------------
            */

            $user = auth('api')->user();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized.',
                ], 401);
            }

            $accountId = $user->account_id;


            /*
            |--------------------------------------------------------------------------
            | FILTERS
            |--------------------------------------------------------------------------
            */

            $staffId = $request->staff_id;

            $fromDate = Settings::checkAndformatDate(
                $request->from_date,
                'Y-m-d'
            );

            $toDate = Settings::checkAndformatDate(
                $request->to_date,
                'Y-m-d'
            );


            /*
            |--------------------------------------------------------------------------
            | DATE RANGE
            |--------------------------------------------------------------------------
            */

            if ($fromDate && $toDate) {

                $start = Carbon::parse($fromDate)
                    ->startOfDay();

                $end = Carbon::parse($toDate)
                    ->endOfDay();

            } elseif ($fromDate) {

                $start = Carbon::parse($fromDate)
                    ->startOfDay();

                $end = Carbon::now()
                    ->endOfDay();

            } elseif ($toDate) {

                $start = Carbon::parse($toDate)
                    ->startOfDay();

                $end = Carbon::parse($toDate)
                    ->endOfDay();

            } else {

                $start = Carbon::today()
                    ->startOfDay();

                $end = Carbon::today()
                    ->endOfDay();
            }


            /*
            |--------------------------------------------------------------------------
            | BASE SALES QUERY
            |--------------------------------------------------------------------------
            */

            $query = Sale::query()
                ->where('account_id', $accountId)
                ->visibleToUser()
                ->whereBetween(
                    'created_at',
                    [$start, $end]
                );


            /*
            |--------------------------------------------------------------------------
            | INVOICE FILTER
            |--------------------------------------------------------------------------
            */

            if ($request->filled('invoice_no')) {

                $query->where(
                    'invoice_no',
                    $request->invoice_no
                );
            }


            /*
            |--------------------------------------------------------------------------
            | STAFF FILTER
            |--------------------------------------------------------------------------
            */

            if (!empty($staffId)) {

                $query->where(
                    'user_id',
                    $staffId
                );
            }


            /*
            |--------------------------------------------------------------------------
            | TOTAL SALES
            |--------------------------------------------------------------------------
            */

            $totalSales = (clone $query)
                ->sum('total');


            /*
            |--------------------------------------------------------------------------
            | TOTAL ORDERS
            |--------------------------------------------------------------------------
            */

            $totalOrders = (clone $query)
                ->count();


            /*
            |--------------------------------------------------------------------------
            | SALE IDS
            |--------------------------------------------------------------------------
            |
            | Get only IDs from the filtered sales query.
            | This makes sure payment totals respect:
            |
            | - Account
            | - Date range
            | - Invoice filter
            | - Staff filter
            | - visibleToUser()
            |
            */

            $saleIds = (clone $query)
                ->pluck('id');


            /*
            |--------------------------------------------------------------------------
            | PAYMENT TOTALS
            |--------------------------------------------------------------------------
            */

            $paymentTotals = SalePayment::whereIn(
                    'sale_id',
                    $saleIds
                )
                ->selectRaw(
                    'method, SUM(amount) as total'
                )
                ->groupBy('method')
                ->pluck(
                    'total',
                    'method'
                )
                ->toArray();


            /*
            |--------------------------------------------------------------------------
            | PAYMENT METHODS
            |--------------------------------------------------------------------------
            */

            $paymentMethods = PaymentMethod::getSelectableWithData()
                ->toArray();


            /*
            |--------------------------------------------------------------------------
            | PAYMENT TYPES
            |--------------------------------------------------------------------------
            */

            $paymentTypes = PaymentType::getSelectable();


            /*
            |--------------------------------------------------------------------------
            | STAFF LIST
            |--------------------------------------------------------------------------
            */

            $staffs = User::where(
                    'account_id',
                    $accountId
                )
                ->visibleToUser()
                ->pluck(
                    'name',
                    'id'
                );


            /*
            |--------------------------------------------------------------------------
            | SALES LIST
            |--------------------------------------------------------------------------
            */

            $sales = (clone $query)
                ->with([
                    'customer:id,name',
                    'user:id,name',
                    'payments:id,sale_id,method,amount',
                ])
                ->latest()
                ->paginate(
                    config('pagination')
                );


            /*
            |--------------------------------------------------------------------------
            | FORMAT SALES DATA
            |--------------------------------------------------------------------------
            */

            $sales->getCollection()
                ->transform(function ($sale) {

                    /*
                    |--------------------------------------------------------------------------
                    | PAYMENT SUMMARY
                    |--------------------------------------------------------------------------
                    */

                    $paymentSummary = $sale->payments
                        ->groupBy('method')
                        ->map(function ($items) {

                            return (float) $items->sum(
                                'amount'
                            );

                        });


                    /*
                    |--------------------------------------------------------------------------
                    | PAYMENT METHOD
                    |--------------------------------------------------------------------------
                    */

                    $paymentMethod = $sale->payment_method
                        ? 'full_payment'
                        : 'partial_payment';


                    return [

                        'id' => $sale->id,

                        'invoice_no' =>
                            $sale->invoice_no ?? '-',

                        'customer' => [
                            'id' =>
                                $sale->customer->id ?? null,

                            'name' =>
                                $sale->customer->name ?? '-',
                        ],

                        'staff' => [
                            'id' =>
                                $sale->user->id ?? null,

                            'name' =>
                                $sale->user->name ?? '-',
                        ],

                        'payment_type' =>
                            $paymentMethod,

                        'total' =>
                            (float) ($sale->total ?? 0),

                        'payment_summary' =>
                            $paymentSummary,

                        'payments' =>
                            $sale->payments->map(
                                function ($payment) {

                                    return [
                                        'id' =>
                                            $payment->id,

                                        'method' =>
                                            $payment->method,

                                        'amount' =>
                                            (float) $payment->amount,
                                    ];
                                }
                            )->values(),

                        'created_at' =>
                            $sale->created_at
                                ? Settings::getFormattedDatetime(
                                    $sale->created_at
                                )
                                : null,

                        'created_at_raw' =>
                            $sale->created_at
                                ? $sale->created_at->format(
                                    'Y-m-d H:i:s'
                                )
                                : null,
                    ];
                });


            /*
            |--------------------------------------------------------------------------
            | PAYMENT TOTAL RESPONSE
            |--------------------------------------------------------------------------
            */

            $formattedPaymentTotals = [];

            foreach ($paymentMethods as $method) {

                $shortName =
                    $method['short_name'] ?? null;

                if (!$shortName) {
                    continue;
                }

                $formattedPaymentTotals[] = [

                    'name' =>
                        $method['name'] ?? $shortName,

                    'short_name' =>
                        $shortName,

                    'total' =>
                        (float) (
                            $paymentTotals[$shortName]
                            ?? 0
                        ),
                ];
            }


            /*
            |--------------------------------------------------------------------------
            | RESPONSE
            |--------------------------------------------------------------------------
            */

            return response()->json([

                'status' => true,

                'message' =>
                    'Daily sales report retrieved successfully.',

                'data' => [

                    /*
                    |--------------------------------------------------------------------------
                    | FILTER
                    |--------------------------------------------------------------------------
                    */

                    'filters' => [

                        'from_date' =>
                            $start->format('Y-m-d'),

                        'to_date' =>
                            $end->format('Y-m-d'),

                        'invoice_no' =>
                            $request->invoice_no ?? null,

                        'staff_id' =>
                            $staffId
                                ? (int) $staffId
                                : null,
                    ],


                    /*
                    |--------------------------------------------------------------------------
                    | SUMMARY
                    |--------------------------------------------------------------------------
                    */

                    'summary' => [

                        'total_sales' =>
                            (float) $totalSales,

                        'total_orders' =>
                            (int) $totalOrders,

                        'payment_totals' =>
                            $formattedPaymentTotals,
                    ],


                    /*
                    |--------------------------------------------------------------------------
                    | PAYMENT TYPES
                    |--------------------------------------------------------------------------
                    */

                    'payment_types' =>
                        $paymentTypes,


                    /*
                    |--------------------------------------------------------------------------
                    | STAFF
                    |--------------------------------------------------------------------------
                    */

                    'staffs' =>
                        $staffs,


                    /*
                    |--------------------------------------------------------------------------
                    | SALES
                    |--------------------------------------------------------------------------
                    */

                    'sales' => $sales,
                ],
            ]);

        } catch (\Throwable $e) {

            return response()->json([

                'status' => false,

                'message' =>
                    'Unable to retrieve daily sales report.',

                'error' => config('app.debug')
                    ? $e->getMessage()
                    : null,

            ], 500);
        }
    }
}