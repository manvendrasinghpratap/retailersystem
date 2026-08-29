<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\PaymentType;
use App\Models\SalePayment;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\CustomerInvoiceMail;
use App\Helpers\Settings;

class SaleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /*
    |--------------------------------------------------------------------------
    | SALE LIST
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
{
    try {

        /*
        |--------------------------------------------------------------------------
        | Current User / Account
        |--------------------------------------------------------------------------
        */

        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $accountId = $user->account_id;


        /*
        |--------------------------------------------------------------------------
        | Base Sales Query
        |--------------------------------------------------------------------------
        */

        $query = Sale::query()
            ->with([
                'user',
                'payments',
                'customer',
            ])
            ->visibleToUser()
            ->ofAccount()
            ->select('sales.*');


        /*
        |--------------------------------------------------------------------------
        | Returned Amount Subquery
        |--------------------------------------------------------------------------
        */

        $returnedAmountSubquery = DB::table('sale_returns')
            ->selectRaw('COALESCE(SUM(sale_returns.total_amount), 0)')
            ->whereColumn(
                'sale_returns.sale_id',
                'sales.id'
            )
            ->where(
                'sale_returns.status',
                'completed'
            );


        /*
        |--------------------------------------------------------------------------
        | Returned Quantity Subquery
        |--------------------------------------------------------------------------
        */

        $returnedQtySubquery = DB::table('sale_return_items')
            ->join(
                'sale_returns',
                'sale_returns.id',
                '=',
                'sale_return_items.sale_return_id'
            )
            ->selectRaw('COALESCE(SUM(sale_return_items.quantity), 0)')
            ->whereColumn(
                'sale_returns.sale_id',
                'sales.id'
            )
            ->where(
                'sale_returns.status',
                'completed'
            );


        /*
        |--------------------------------------------------------------------------
        | Add Returned Amount
        |--------------------------------------------------------------------------
        */

        $query->selectSub(
            $returnedAmountSubquery,
            'returned_amount'
        );


        /*
        |--------------------------------------------------------------------------
        | Add Returned Quantity
        |--------------------------------------------------------------------------
        */

        $query->selectSub(
            $returnedQtySubquery,
            'returned_quantity'
        );


        /*
        |--------------------------------------------------------------------------
        | Net Sale
        |--------------------------------------------------------------------------
        |
        | Since this expression contains no dynamic bindings, there is no
        | HY093 parameter binding problem.
        |
        */

        $query->selectRaw(
            '
            GREATEST(
                0,
                sales.total - COALESCE(
                    (
                        SELECT SUM(sr.total_amount)
                        FROM sale_returns sr
                        WHERE sr.sale_id = sales.id
                        AND sr.status = "completed"
                    ),
                    0
                )
            ) AS net_sale
            '
        );


        /*
        |--------------------------------------------------------------------------
        | Return Status
        |--------------------------------------------------------------------------
        */

        $query->selectRaw(
            '
            CASE

                WHEN COALESCE(
                    (
                        SELECT SUM(sr.total_amount)
                        FROM sale_returns sr
                        WHERE sr.sale_id = sales.id
                        AND sr.status = "completed"
                    ),
                    0
                ) <= 0
                    THEN "Completed"

                WHEN COALESCE(
                    (
                        SELECT SUM(sr.total_amount)
                        FROM sale_returns sr
                        WHERE sr.sale_id = sales.id
                        AND sr.status = "completed"
                    ),
                    0
                ) >= sales.total
                    THEN "Fully Returned"

                ELSE "Partially Returned"

            END AS return_status
            '
        );


        /*
        |--------------------------------------------------------------------------
        | Date Filter
        |--------------------------------------------------------------------------
        |
        | Supported:
        |
        | ?from_date=2026-08-01
        | ?to_date=2026-08-29
        |
        */

        if ($request->filled('from_date')) {

            $fromDate = trim($request->input('from_date'));

            $query->whereDate(
                'sales.created_at',
                '>=',
                $fromDate
            );
        }


        if ($request->filled('to_date')) {

            $toDate = trim($request->input('to_date'));

            $query->whereDate(
                'sales.created_at',
                '<=',
                $toDate
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Invoice Number Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('invoice_no')) {

            $invoiceNo = trim(
                $request->input('invoice_no')
            );

            $query->where(
                'sales.invoice_no',
                'like',
                '%' . $invoiceNo . '%'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Payment Type Filter
        |--------------------------------------------------------------------------
        |
        | Example:
        | ?payment_type=full
        | ?payment_type=partial
        | ?payment_type=credit
        |
        */

        if ($request->filled('payment_type')) {

            $query->where(
                'sales.payment_type',
                $request->input('payment_type')
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Payment Approval Status Filter
        |--------------------------------------------------------------------------
        |
        | Example:
        |
        | ?approval_status=approve
        | ?approval_status=pending
        | ?approval_status=rejected
        |
        */

        if ($request->filled('approval_status')) {

            $query->where(
                'sales.payment_approval_status',
                $request->input('approval_status')
            );

        } else {

            /*
            |------------------------------------------------------------------
            | Default
            |------------------------------------------------------------------
            |
            | Don't show rejected sales unless explicitly requested.
            |
            */

            $query->whereIn(
                'sales.payment_approval_status',
                [
                    'approve',
                    'pending',
                ]
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Customer Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('customer_id')) {

            $query->where(
                'sales.customer_id',
                $request->input('customer_id')
            );
        }


        /*
        |--------------------------------------------------------------------------
        | User / Cashier Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('user_id')) {

            $query->where(
                'sales.user_id',
                $request->input('user_id')
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Store Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('store_id')) {

            $query->where(
                'sales.store_id',
                $request->input('store_id')
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Warehouse Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('warehouse_id')) {

            $query->where(
                'sales.warehouse_id',
                $request->input('warehouse_id')
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Summary Query
        |--------------------------------------------------------------------------
        |
        | Clone BEFORE pagination.
        |
        */

        $summaryQuery = clone $query;


        /*
        |--------------------------------------------------------------------------
        | Total Sales
        |--------------------------------------------------------------------------
        */

        $totalSales = (float) $summaryQuery->sum(
            'sales.total'
        );


        /*
        |--------------------------------------------------------------------------
        | Total Returned
        |--------------------------------------------------------------------------
        */

        $summarySaleIds = $summaryQuery
            ->reorder()
            ->select('sales.id');


        $totalReturned = (float) DB::table('sale_returns')
            ->where(
                'sale_returns.status',
                'completed'
            )
            ->whereIn(
                'sale_returns.sale_id',
                $summarySaleIds
            )
            ->sum(
                'sale_returns.total_amount'
            );


        /*
        |--------------------------------------------------------------------------
        | Net Sales
        |--------------------------------------------------------------------------
        */

        $netSales = $totalSales - $totalReturned;


        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $defaultPerPage = 10;

        try {
            $defaultPerPage = (int) account_setting(
                'general.pagination'
            );
        } catch (\Throwable $e) {
            $defaultPerPage = 10;
        }


        $perPage = (int) $request->input(
            'per_page',
            $defaultPerPage
        );


        /*
        | Prevent extremely large requests
        */

        $perPage = min(
            max($perPage, 1),
            100
        );


        /*
        |--------------------------------------------------------------------------
        | Get Sales
        |--------------------------------------------------------------------------
        */

        $sales = $query
            ->latest('sales.created_at')
            ->paginate($perPage);


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,

            'message' => 'Sales fetched successfully.',

            'data' => [

                /*
                |--------------------------------------------------------------------------
                | Sales
                |--------------------------------------------------------------------------
                */

                'sales' => $sales->items(),


                /*
                |--------------------------------------------------------------------------
                | Summary
                |--------------------------------------------------------------------------
                */

                'summary' => [

                    'total_sales' => round(
                        $totalSales,
                        2
                    ),

                    'total_returned' => round(
                        $totalReturned,
                        2
                    ),

                    'net_sales' => round(
                        $netSales,
                        2
                    ),
                ],


                /*
                |--------------------------------------------------------------------------
                | Pagination
                |--------------------------------------------------------------------------
                */

                'pagination' => [

                    'current_page' => $sales->currentPage(),

                    'last_page' => $sales->lastPage(),

                    'per_page' => $sales->perPage(),

                    'total' => $sales->total(),

                    'from' => $sales->firstItem(),

                    'to' => $sales->lastItem(),
                ],
            ],
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {

        return response()->json([
            'success' => false,
            'message' => 'Validation failed.',
            'errors' => $e->errors(),
        ], 422);

    } catch (\Throwable $e) {

        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
}

    /*
    |--------------------------------------------------------------------------
    | SALE DETAILS
    |--------------------------------------------------------------------------
    */

    public function show($id)
    {
        try {

            $accountId = auth()->user()->account_id;

            $sale = Sale::with([
                'customer',
                'user',
                'creditDuration',
                'items.product',
                'payments.paymentMethod',
                'payments.paymentReceivedBy'
            ])
                ->where('account_id', $accountId)
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Sale details fetched successfully.',
                'data' => $sale,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Sale not found.',
            ], 404);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | PAYMENT DETAILS
    |--------------------------------------------------------------------------
    */

    public function paymentDetails($saleId)
    {
        try {

            $accountId = auth()->user()->account_id;

            $sale = Sale::with([
                'customer',
                'payments' => function ($query) {

                    $query->with('paymentReceivedBy')
                        ->orderBy(
                            'created_at',
                            'desc'
                        );
                }
            ])
                ->where('account_id', $accountId)
                ->findOrFail($saleId);

            $sale->formatted_due_date = $sale->due_date
                ? Settings::getFormattedDate(
                    $sale->due_date
                )
                : '-';

            $sale->formatted_created_at = $sale->created_at
                ? Settings::getFormattedDate(
                    $sale->created_at
                )
                : '-';

            return response()->json([
                'success' => true,
                'message' => 'Payment details fetched successfully.',
                'data' => [
                    'sale' => $sale,
                    'payment_methods' => PaymentMethod::getSelectable(),
                ],
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Sale not found.',
            ], 404);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SAVE CREDIT PAYMENT
    |--------------------------------------------------------------------------
    */

    public function saveCreditPayment(Request $request)
    {
        $validated = $request->validate([
            'sale_id' => [
                'required',
                'integer',
                'exists:sales,id'
            ],

            'payment_method_id' => [
                'required',
                'integer',
                'exists:payment_methods,id'
            ],

            'amount' => [
                'required',
                'numeric',
                'min:0.01'
            ],
        ]);

        try {

            $accountId = auth()->user()->account_id;

            /*
            |--------------------------------------------------------------------------
            | Get Sale
            |--------------------------------------------------------------------------
            */

            $sale = Sale::where(
                'account_id',
                $accountId
            )->findOrFail(
                $validated['sale_id']
            );

            /*
            |--------------------------------------------------------------------------
            | Payment Method
            |--------------------------------------------------------------------------
            */

            $paymentMethod = PaymentMethod::find(
                $validated['payment_method_id']
            );

            if (!$paymentMethod) {

                return response()->json([
                    'success' => false,
                    'message' => __('translation.invalid_payment_method'),
                ], 422);
            }

            /*
            |--------------------------------------------------------------------------
            | Validate Amount
            |--------------------------------------------------------------------------
            */

            $amount = (float) $validated['amount'];

            if ($amount > (float) $sale->balance_amount) {

                return response()->json([
                    'success' => false,
                    'message' => __('translation.payment_amount_cannot_exceed_pending_balance'),
                ], 422);
            }

            /*
            |--------------------------------------------------------------------------
            | Duplicate Payment Protection
            |--------------------------------------------------------------------------
            */

            $existingPayment = SalePayment::where(
                'sale_id',
                $sale->id
            )
                ->where(
                    'method',
                    $paymentMethod->short_name
                )
                ->where(
                    'amount',
                    $amount
                )
                ->where(
                    'payment_received_by',
                    auth()->id()
                )
                ->where(
                    'created_at',
                    '>=',
                    now()->subSeconds(10)
                )
                ->exists();

            if ($existingPayment) {

                return response()->json([
                    'success' => false,
                    'message' => 'Duplicate payment request detected.',
                ], 409);
            }

            /*
            |--------------------------------------------------------------------------
            | Save Payment
            |--------------------------------------------------------------------------
            */

            DB::transaction(function () use (
                $sale,
                $paymentMethod,
                $amount
            ) {

                SalePayment::create([
                    'sale_id' => $sale->id,
                    'method' => $paymentMethod->short_name,
                    'amount' => $amount,
                    'payment_received_by' => auth()->id(),
                ]);

                $sale->paid_amount =
                    (float) $sale->paid_amount + $amount;

                $sale->balance_amount =
                    (float) $sale->payable_amount
                    - (float) $sale->paid_amount;

                if ($sale->balance_amount <= 0) {

                    $sale->balance_amount = 0;
                    $sale->payment_status = 'paid';

                } elseif ($sale->paid_amount > 0) {

                    $sale->payment_status = 'partial';
                }

                $sale->save();
            });

            return response()->json([
                'success' => true,
                'message' => __('translation.payment_received_successfully'),
                'data' => [
                    'sale_id' => $sale->id,
                    'paid_amount' => $sale->paid_amount,
                    'balance_amount' => $sale->balance_amount,
                    'payment_status' => $sale->payment_status,
                ],
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Sale not found.',
            ], 404);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SEND INVOICE EMAIL
    |--------------------------------------------------------------------------
    */

    public function sendInvoiceEmail(Request $request)
    {
        $validated = $request->validate([
            'sale_id' => [
                'required',
                'integer',
                'exists:sales,id'
            ],
        ]);

        try {

            $accountId = auth()->user()->account_id;

            $sale = Sale::with([
                'items.product'
            ])
                ->where(
                    'account_id',
                    $accountId
                )
                ->findOrFail(
                    $validated['sale_id']
                );

            $customer = Customer::where(
                'account_id',
                $accountId
            )->find(
                $sale->customer_id
            );

            if (!$customer || empty($customer->email)) {

                return response()->json([
                    'success' => false,
                    'message' => 'Customer email not available.',
                ], 422);
            }

            Mail::to(
                $customer->email
            )->send(
                new CustomerInvoiceMail(
                    $sale,
                    $customer
                )
            );

            return response()->json([
                'success' => true,
                'message' => 'Invoice email sent successfully.',
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Sale not found.',
            ], 404);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}