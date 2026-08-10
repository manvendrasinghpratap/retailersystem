<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\Settings;
use App\Models\Customer;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\SaleReturnTracking;
use App\Models\PurchaseItemTracking;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleReturnController extends Controller
{
    protected $breadcrumbAddUpdate;
    protected $breadcrumbListing;

    public function __construct()
    {
        $this->middleware('auth');

        $this->middleware(function ($request, $next) {

            $role = Settings::getUserRole(); // admin / staff / etc.
            $this->breadcrumbListing = [
                'title' => __('translation.customer_returns'),
                'breadcrumb' => [
                    [
                        'route' => 'admin.dashboard',
                        'title' => __('translation.dashboard')
                    ],
                    [
                        'route' => 'admin.sale-returns',
                        'title' => __('translation.customer_returns')
                    ],
                    [
                        'route' => $role . '.sale-returns.create',
                        'title' => __('translation.create_customer_return_stock')
                    ]
                ],

                'route1' => 'admin.inventory',
                'route1Title' => __('translation.stock_management'),
                'route2' => 'admin.sale-returns',
                'reset_route_title' => __('translation.cancel'),
                'reset_route' => 'admin.sale-returns',
            ];

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $breadcrumb = $this->breadcrumbListing ?? [];

        $query = SaleReturn::with(['sale', 'customer', 'items.product'])->where('account_id', auth()->user()->account_id)->latest();

        if ($request->filled('return_no')) {
            $query->where('return_no', 'LIKE', '%' . trim($request->return_no) . '%');
        }

        if ($request->filled('invoice_no')) {
            $invoiceNo = trim($request->invoice_no);
            $query->whereHas('sale', function ($q) use ($invoiceNo) {
                $q->where('invoice_no', 'LIKE', '%' . $invoiceNo . '%');
            });
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $returns = $query->paginate(account_setting('general.pagination'))->withQueryString();
        return view('backend.admin.sale-return.index', compact('returns', 'breadcrumb'));
    }

    public function create()
    {
        $breadcrumb = $this->breadcrumbListing ?? [];
        $paymentMethods = PaymentMethod::query()->where('status', 1)->pluck('name', 'id');
        return view('backend.admin.sale-return.create', compact('breadcrumb', 'paymentMethods'));
    }

    /**
     * Search invoice and calculate returnable quantities.
     */
    public function searchInvoice(Request $request)
    {
        $request->validate([
            'invoice_no' => [
                'required',
                'string',
                'max:100',
            ],
        ]);

        try {

            $accountId = auth()->user()->account_id;

            $sale = Sale::with([
                'customer',
                'items.product',
            ])
                ->where('account_id', $accountId)
                ->where(
                    'invoice_no',
                    trim($request->invoice_no)
                )
                ->first();

            if (!$sale) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice not found.',
                ], 404);
            }

            /*
            |--------------------------------------------------------------------------
            | Do not allow cancelled sale
            |--------------------------------------------------------------------------
            */

            if (
                isset($sale->status) &&
                in_array($sale->status, ['cancelled', 'cancel'])
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'This sale has been cancelled.',
                ], 422);
            }

            $items = [];

            foreach ($sale->items as $saleItem) {

                /*
                |--------------------------------------------------------------------------
                | Already Returned Quantity
                |--------------------------------------------------------------------------
                */

                $returnedQty = SaleReturnItem::where(
                    'sale_item_id',
                    $saleItem->id
                )
                    ->whereHas('saleReturn', function ($q) {
                        $q->where('status', 'completed');
                    })
                    ->sum('quantity');

                $soldQty = (float) $saleItem->quantity;

                $returnableQty = max(
                    0,
                    $soldQty - (float) $returnedQty
                );

                /*
                |--------------------------------------------------------------------------
                | Tracking information
                |--------------------------------------------------------------------------
                */

                $trackingType = null;

                if ($saleItem->product) {
                    $trackingType =
                        $saleItem->product->tracking_type ?? null;
                }

                $items[] = [
                    'sale_item_id' => $saleItem->id,
                    'product_id' => $saleItem->product_id,
                    'product_name' =>
                        $saleItem->product->name ?? '-',
                    'sold_qty' => $soldQty,
                    'returned_qty' => (float) $returnedQty,
                    'returnable_qty' => $returnableQty,
                    'price' => (float) $saleItem->price,
                    'tracking_type' => $trackingType,
                ];
            }

            return response()->json([
                'success' => true,

                'sale' => [
                    'id' => $sale->id,
                    'invoice_no' => $sale->invoice_no,
                    'customer' =>
                        $sale->customer->name ?? 'Walk-in',
                    'customer_id' => $sale->customer_id,
                    'total' => $sale->total,
                    'payment_type' => $sale->payment_type,
                ],

                'items' => $items,
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Process customer return.
     */
    public function store(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Validate Request
        |--------------------------------------------------------------------------
        */

        $request->validate([
            'invoice_no' => [
                'required',
                'string',
                'exists:sales,invoice_no',
            ],

            'return_date' => [
                'required',
                'date_format:d/m/Y',
            ],

            'refund_type' => [
                'required',
                'in:refund,cash,credit_adjustment',
            ],

            'payment_method' => [
                'nullable',
                'string',
            ],

            'reason' => [
                'nullable',
                'string',
                'max:255',
            ],

            'note' => [
                'nullable',
                'string',
            ],

            /*
            | Do NOT trust this amount.
            | It will be recalculated from sale items below.
            */
            'return_total' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.sale_item_id' => [
                'required',
                'integer',
                'exists:sale_items,id',
            ],

            'items.*.quantity' => [
                'required',
                'numeric',
                'min:0',
            ],

            'items.*.tracking_ids' => [
                'nullable',
                'array',
            ],

            'items.*.tracking_ids.*' => [
                'integer',
            ],
        ]);

        try {

            /*
            |--------------------------------------------------------------------------
            | Transaction
            |--------------------------------------------------------------------------
            */

            $return = DB::transaction(function () use ($request) {

                $accountId = auth()->user()->account_id;
                $storeId = auth()->user()->store_id;

                if (!$storeId) {
                    throw new \Exception('Store not found for current user.');
                }

                /*
                |--------------------------------------------------------------------------
                | Find & Lock Sale
                |--------------------------------------------------------------------------
                |
                | Form sends invoice_no, not sale_id.
                |
                */

                $sale = Sale::with([
                    'items.product',
                    'customer',
                ])
                    ->where('account_id', $accountId)
                    ->where('store_id', $storeId)
                    ->where('invoice_no', trim($request->invoice_no))
                    ->lockForUpdate()
                    ->first();

                if (!$sale) {
                    throw new \Exception(
                        'Sale invoice "' . $request->invoice_no . '" was not found.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Validate Sale Status
                |--------------------------------------------------------------------------
                */

                if (
                    isset($sale->status)
                    && !in_array($sale->status, ['completed', 'pending'], true)
                ) {
                    throw new \Exception(
                        'This sale is not eligible for return.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Customer
                |--------------------------------------------------------------------------
                */

                $customerId = $sale->customer_id;

                /*
                |--------------------------------------------------------------------------
                | Remove Zero Quantity Items
                |--------------------------------------------------------------------------
                */

                $requestItems = collect($request->items)
                    ->filter(function ($item) {
                        return isset($item['quantity'])
                            && (float) $item['quantity'] > 0;
                    })
                    ->values();

                if ($requestItems->isEmpty()) {
                    throw ValidationException::withMessages([
                        'items' => 'Please select at least one item to return.',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Prepare Return Items
                |--------------------------------------------------------------------------
                */

                $returnTotal = 0;

                $processedItems = [];

                foreach ($requestItems as $returnItem) {

                    $saleItemId = (int) $returnItem['sale_item_id'];

                    /*
                    |--------------------------------------------------------------------------
                    | Sale Item MUST belong to this Sale
                    |--------------------------------------------------------------------------
                    */

                    $saleItem = SaleItem::with('product')
                        ->where('sale_id', $sale->id)
                        ->lockForUpdate()
                        ->find($saleItemId);

                    if (!$saleItem) {
                        throw new \Exception(
                            'Invalid sale item selected for this invoice.'
                        );
                    }

                    $quantity = (float) $returnItem['quantity'];

                    /*
                    |--------------------------------------------------------------------------
                    | Already Returned Quantity
                    |--------------------------------------------------------------------------
                    */

                    $returnedQty = SaleReturnItem::where(
                        'sale_item_id',
                        $saleItem->id
                    )
                        ->whereHas('saleReturn', function ($q) {
                            $q->where('status', 'completed');
                        })
                        ->sum('quantity');

                    $returnableQty = max(
                        0,
                        (float) $saleItem->quantity - (float) $returnedQty
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Validate Return Quantity
                    |--------------------------------------------------------------------------
                    */

                    if ($quantity > $returnableQty) {

                        throw new \Exception(
                            'Return quantity for ' .
                            ($saleItem->product->name ?? 'product') .
                            ' cannot exceed ' .
                            $returnableQty .
                            '.'
                        );
                    }

                    if ($quantity <= 0) {
                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Calculate Line Total
                    |--------------------------------------------------------------------------
                    */

                    $lineTotal = round(
                        $quantity * (float) $saleItem->price,
                        2
                    );

                    $returnTotal += $lineTotal;

                    /*
                    |--------------------------------------------------------------------------
                    | Store Processed Item
                    |--------------------------------------------------------------------------
                    */

                    $processedItems[] = [
                        'sale_item' => $saleItem,
                        'quantity' => $quantity,
                        'line_total' => $lineTotal,
                        'tracking_ids' => $returnItem['tracking_ids'] ?? [],
                    ];
                }

                /*
                |--------------------------------------------------------------------------
                | At Least One Item
                |--------------------------------------------------------------------------
                */

                if (empty($processedItems)) {
                    throw new \Exception(
                        'Please select at least one item to return.'
                    );
                }

                $returnTotal = round($returnTotal, 2);

                /*
                |--------------------------------------------------------------------------
                | Validate Refund
                |--------------------------------------------------------------------------
                */

                if (
                    $request->refund_type === 'refund'
                    && empty($request->payment_method)
                ) {
                    throw ValidationException::withMessages([
                        'payment_method' =>
                            'Refund payment method is required.',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Generate Return Number
                |--------------------------------------------------------------------------
                */

                $returnNo = 'RET-' . now()->format('YmdHis');

                /*
                |--------------------------------------------------------------------------
                | Create Sale Return
                |--------------------------------------------------------------------------
                */

                $saleReturn = SaleReturn::create([
                    'account_id' => $accountId,
                    'store_id' => $storeId,
                    'sale_id' => $sale->id,
                    'customer_id' => $customerId,
                    'return_no' => $returnNo,
                    'total_amount' => $returnTotal,
                    'refund_type' => $request->refund_type,

                    'payment_method' =>
                        $request->refund_type === 'refund'
                        ? $request->payment_method
                        : null,

                    'reason' => $request->reason,
                    'note' => $request->note,
                    'status' => 'completed',
                    'created_by' => auth()->id(),
                ]);

                /*
                |--------------------------------------------------------------------------
                | Stock Service
                |--------------------------------------------------------------------------
                */

                $stockService = app(StockService::class);

                /*
                |--------------------------------------------------------------------------
                | Process Each Returned Item
                |--------------------------------------------------------------------------
                */

                foreach ($processedItems as $processed) {

                    $saleItem = $processed['sale_item'];

                    $quantity = $processed['quantity'];

                    $lineTotal = $processed['line_total'];

                    $trackingIds = $processed['tracking_ids'];

                    /*
                    |--------------------------------------------------------------------------
                    | Create Return Item
                    |--------------------------------------------------------------------------
                    */

                    $returnLine = SaleReturnItem::create([
                        'sale_return_id' => $saleReturn->id,
                        'sale_item_id' => $saleItem->id,
                        'product_id' => $saleItem->product_id,
                        'quantity' => $quantity,
                        'price' => $saleItem->price,
                        'total' => $lineTotal,
                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | Tracking / Barcode Return
                    |--------------------------------------------------------------------------
                    */

                    if (!empty($trackingIds)) {

                        /*
                        | Number of selected barcodes must match quantity
                        */

                        if (count($trackingIds) != (int) $quantity) {
                            throw new \Exception(
                                'Please scan all required barcodes for ' .
                                ($saleItem->product->name ?? 'this product') .
                                '.'
                            );
                        }

                        /*
                        | Prevent duplicate barcode IDs
                        */

                        if (count($trackingIds) !== count(array_unique($trackingIds))) {
                            throw new \Exception(
                                'Duplicate barcode selected.'
                            );
                        }

                        foreach ($trackingIds as $trackingId) {

                            /*
                            |--------------------------------------------------------------------------
                            | Lock Tracking
                            |--------------------------------------------------------------------------
                            */

                            $tracking = PurchaseItemTracking::lockForUpdate()
                                ->find($trackingId);

                            if (!$tracking) {
                                throw new \Exception(
                                    'Selected barcode was not found.'
                                );
                            }

                            /*
                            |--------------------------------------------------------------------------
                            | Barcode Must Be Sold
                            |--------------------------------------------------------------------------
                            */

                            if ((int) $tracking->is_sold !== 1) {
                                throw new \Exception(
                                    'Barcode ' .
                                    $tracking->barcode .
                                    ' is not currently marked as sold.'
                                );
                            }

                            /*
                            |--------------------------------------------------------------------------
                            | Check Purchase Item
                            |--------------------------------------------------------------------------
                            */

                            $purchaseItem = $tracking->purchaseItem;

                            if (!$purchaseItem) {
                                throw new \Exception(
                                    'Purchase information not found for barcode ' .
                                    $tracking->barcode . '.'
                                );
                            }

                            /*
                            |--------------------------------------------------------------------------
                            | Check Product
                            |--------------------------------------------------------------------------
                            */

                            if (
                                !$saleItem->product ||
                                (int) $purchaseItem->master_item_id !==
                                (int) $saleItem->product->master_item_id
                            ) {
                                throw new \Exception(
                                    'Barcode ' .
                                    $tracking->barcode .
                                    ' does not belong to this product.'
                                );
                            }

                            /*
                            |--------------------------------------------------------------------------
                            | Prevent Duplicate Return
                            |--------------------------------------------------------------------------
                            */

                            $alreadyReturned =
                                SaleReturnTracking::where(
                                    'purchase_item_tracking_id',
                                    $tracking->id
                                )->exists();

                            if ($alreadyReturned) {
                                throw new \Exception(
                                    'Barcode ' .
                                    $tracking->barcode .
                                    ' has already been returned.'
                                );
                            }

                            /*
                            |--------------------------------------------------------------------------
                            | Create Return Tracking
                            |--------------------------------------------------------------------------
                            */

                            SaleReturnTracking::create([
                                'sale_return_item_id' => $returnLine->id,
                                'purchase_item_tracking_id' => $tracking->id,
                                'barcode' => $tracking->barcode,
                                'quantity' => 1,
                            ]);

                            /*
                            |--------------------------------------------------------------------------
                            | Make Barcode Available Again
                            |--------------------------------------------------------------------------
                            */

                            $tracking->update([
                                'is_sold' => 0,
                                'is_reserved' => 0,
                                'sold_at' => null,
                                'store_id' => $storeId,
                                'requisition_id' => null,
                                'requisition_item_id' => null,
                            ]);
                        }
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Restore Store Stock
                    |--------------------------------------------------------------------------
                    */

                    $product = $saleItem->product;

                    if (!$product) {
                        throw new \Exception(
                            'Product not found for sale item.'
                        );
                    }


                }

                /*
                |--------------------------------------------------------------------------
                | Credit Adjustment
                |--------------------------------------------------------------------------
                */

                if (
                    $request->refund_type === 'credit_adjustment'
                    && $customerId
                ) {

                    $customer = Customer::where(
                        'account_id',
                        $accountId
                    )
                        ->lockForUpdate()
                        ->findOrFail($customerId);

                    /*
                    |--------------------------------------------------------------------------
                    | Current Balance
                    |--------------------------------------------------------------------------
                    |
                    | Your existing implementation assumes:
                    | current_balance = amount customer owes.
                    |
                    */

                    $newBalance = max(
                        0,
                        (float) $customer->current_balance
                        - $returnTotal
                    );

                    $customer->update([
                        'current_balance' => $newBalance,
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Return Completed
                |--------------------------------------------------------------------------
                */

                return $saleReturn;
            });

            /*
            |--------------------------------------------------------------------------
            | Success Response
            |--------------------------------------------------------------------------
            */

            return response()->json([
                'success' => true,
                'return_no' => $return->return_no,
                'return_id' => $return->id,
                'message' => 'Customer return completed successfully.',
            ]);

        } catch (ValidationException $e) {

            return response()->json([
                'success' => false,
                'message' => collect($e->errors())
                    ->flatten()
                    ->implode('<br>'),
            ], 422);

        } catch (\Throwable $e) {

            report($e);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function show($id)
    {
        $id = Settings::getDecodeCode($id);

        $return = SaleReturn::with([
            'sale',
            'customer',
            'items.product',
            'items.saleItem',
            'items.trackings',
        ])
            ->where(
                'account_id',
                auth()->user()->account_id
            )
            ->findOrFail($id);

        return view(
            'backend.admin.sale-return.show',
            compact('return')
        );
    }

    public function saleDetails(Request $request)
    {
        try {

            $request->validate([
                'invoice_no' => 'required|string|max:255',
            ]);

            $accountId = auth()->user()->account_id;
            $storeId = auth()->user()->store_id;

            $sale = Sale::with([
                'customer',
                'items.product',
            ])
                ->where('account_id', $accountId)
                ->where('store_id', $storeId)
                ->where('invoice_no', trim($request->invoice_no))
                ->first();

            if (!$sale) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sale invoice not found.',
                ], 404);
            }

            $items = $sale->items->map(function ($item) {

                $soldQty = (float) $item->quantity;

                /*
                 * Initially all sold quantity is returnable.
                 *
                 * Later this should be reduced by already-returned
                 * quantity from sale returns.
                 */
                $returnedQty = 0;

                $returnableQty = max(
                    0,
                    $soldQty - $returnedQty
                );

                return [
                    'sale_item_id' => $item->id,
                    'product_id' => $item->product_id,

                    'product_name' => $item->product->name ?? 'Unknown Product',

                    'sold_qty' => $soldQty,
                    'returned_qty' => $returnedQty,
                    'returnable_qty' => $returnableQty,

                    'price' => (float) $item->price,
                    'total' => (float) $item->total,
                ];

            })->filter(function ($item) {
                return $item['returnable_qty'] > 0;
            })->values();

            if ($items->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No items are available for return.',
                ], 422);
            }

            return response()->json([
                'success' => true,

                'sale' => [
                    'id' => $sale->id,
                    'invoice_no' => $sale->invoice_no,
                    'customer_id' => $sale->customer_id,

                    'customer_name' => $sale->customer
                        ? trim(
                            ($sale->customer->first_name ?? '') . ' ' .
                            ($sale->customer->last_name ?? '')
                        )
                        : 'Walk-in Customer',
                ],

                'items' => $items,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first(),
            ], 422);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}