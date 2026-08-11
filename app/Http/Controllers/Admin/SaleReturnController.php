<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\Settings;
use App\Models\Customer;
use App\Models\PaymentMethod;
use App\Models\StockAdjustment;

use App\Services\StockService;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\Inventory;
use App\Models\PurchaseItemTracking;
use App\Models\SaleItemTracking;
use Illuminate\Validation\Rule;
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
     * @desc Process customer return.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'invoice_no' => ['required', 'string',],
            'return_date' => ['required', 'date_format:d/m/Y',],
            'reason' => ['nullable', 'string', 'max:255',],
            'note' => ['nullable', 'string',],
            'items' => ['required', 'array', 'min:1',],
            'items.*.sale_item_id' => ['required', 'integer',],
            'items.*.quantity' => ['required', 'numeric', 'min:0',],
            'items.*.tracking_ids' => ['nullable', 'array',],
            'items.*.tracking_ids.*' => ['integer',],
        ]);

        try {

            $return = DB::transaction(function () use ($request) {
                $user = auth()->user();
                $accountId = $user->account_id;
                $storeId = $user->store_id;
                $userId = $user->id;
                if (!$accountId) {
                    throw new \Exception('Account not found for current user.');
                }
                if (!$storeId) {
                    throw new \Exception('Store not found for current user.');
                }
                $invoiceNo = trim($request->invoice_no);
                $sale = Sale::with(['items.product', 'customer'])
                    ->where('account_id', $accountId)
                    ->where('store_id', $storeId)
                    ->where('invoice_no', $invoiceNo)
                    ->lockForUpdate()
                    ->first();

                if (!$sale) {
                    throw new \Exception('Sale invoice "' . $invoiceNo . '" was not found.');
                }
                if (isset($sale->status) && !in_array($sale->status, ['completed', 'pending'], true)) {
                    throw new \Exception('This sale is not eligible for return.');
                }
                $customerId = $sale->customer_id;
                if (empty($customerId) || !$sale->customer) {
                    throw new \Exception('This invoice is not linked to a customer. Please add customer information before processing the return.');
                }
                $requestItems = collect($request->items)
                    ->filter(function ($item) {
                        return isset($item['quantity']) && (float) $item['quantity'] > 0;
                    })->values();

                if ($requestItems->isEmpty()) {
                    throw ValidationException::withMessages([
                        'items' => 'Please select at least one item to return.',
                    ]);
                }
                $returnTotal = 0;
                $processedItems = [];
                foreach ($requestItems as $returnItem) {
                    $saleItemId = (int) $returnItem['sale_item_id'];
                    $quantity = (float) $returnItem['quantity'];
                    $saleItem = SaleItem::with('product')
                        ->where('sale_id', $sale->id)
                        ->where('id', $saleItemId)
                        ->lockForUpdate()
                        ->first();
                    if (!$saleItem) {
                        throw new \Exception('Invalid sale item selected for this invoice.');
                    }
                    $product = $saleItem->product;
                    if (!$product) {
                        throw new \Exception('Product information could not be found.');
                    }
                    $returnedQty = SaleReturnItem::where('sale_item_id', $saleItem->id)->whereHas('saleReturn', function ($query) {
                        $query->where('status', 'completed');
                    })->sum('quantity');

                    $returnableQty = max(
                        0,
                        (float) $saleItem->quantity -
                        (float) $returnedQty
                    );

                    if ($quantity > $returnableQty) {
                        throw new \Exception(
                            'Return quantity for "' .
                            ($product->name ?? 'product') .
                            '" cannot exceed ' .
                            $returnableQty .
                            '.'
                        );
                    }
                    if ($quantity <= 0) {
                        continue;
                    }

                    $trackingIds = array_values(
                        array_unique(
                            array_map(
                                'intval',
                                $returnItem['tracking_ids'] ?? []
                            )
                        )
                    );

                    if (!empty($trackingIds)) {
                        if (count($trackingIds) !== (int) $quantity) {
                            throw new \Exception('Please scan all required barcodes for "' . ($product->name ?? 'this product') . '".');
                        }
                    }

                    $lineTotal = round($quantity * (float) $saleItem->price, 2);
                    $returnTotal += $lineTotal;

                    $processedItems[] = [
                        'sale_item' => $saleItem,
                        'quantity' => $quantity,
                        'line_total' => $lineTotal,
                        'tracking_ids' => $trackingIds,
                    ];
                }
                if (empty($processedItems)) {
                    throw new \Exception('Please select at least one item to return.');
                }
                $returnTotal = round($returnTotal, 2);
                $returnNo = 'RET-' . now()->format('YmdHis');

                $returnDate = \Carbon\Carbon::createFromFormat('d/m/Y', $request->return_date)->format('Y-m-d');

                $saleReturn = SaleReturn::create([
                    'account_id' => $accountId,
                    'store_id' => $storeId,
                    'sale_id' => $sale->id,
                    'customer_id' => $customerId,
                    'return_no' => $returnNo,
                    'return_date' => $returnDate,
                    'total_amount' => $returnTotal,
                    'reason' => $request->reason,
                    'note' => $request->note,
                    'status' => 'completed',
                    'created_by' => $userId,
                ]);
                foreach ($processedItems as $processed) {
                    $saleItem = $processed['sale_item'];
                    $quantity = $processed['quantity'];
                    $lineTotal = $processed['line_total'];
                    $trackingIds = $processed['tracking_ids'];
                    $product = $saleItem->product;
                    if (!empty($trackingIds)) {
                        foreach ($trackingIds as $trackingId) {
                            $tracking = PurchaseItemTracking::with('purchaseItem')->lockForUpdate()->find($trackingId);
                            if (!$tracking) {
                                throw new \Exception('Selected barcode was not found.');
                            }
                            if ((int) $tracking->is_sold !== 1) {
                                throw new \Exception('Barcode "' . $tracking->barcode . '" is not currently marked as sold.');
                            }
                            $purchaseItem = $tracking->purchaseItem;
                            if (!$purchaseItem) {
                                throw new \Exception('Purchase information not found for barcode "' . $tracking->barcode . '".');
                            }
                            if (
                                !$product ||
                                !$product->master_item_id ||
                                !$purchaseItem->master_item_id ||
                                (int) $purchaseItem->master_item_id !==
                                (int) $product->master_item_id
                            ) {

                                throw new \Exception('Barcode "' . $tracking->barcode . '" does not belong to this product.');
                            }

                            $saleItemTracking = SaleItemTracking::where('purchase_item_tracking_id', $tracking->id)->where('sale_item_id', $saleItem->id)->exists();
                            if (!$saleItemTracking) {
                                throw new \Exception('Barcode "' . $tracking->barcode . '" does not belong to this sale item.');
                            }
                            $alreadyReturned = SaleReturnItem::where('purchase_item_tracking_id', $tracking->id)->where('sale_item_id', $saleItem->id)->whereHas('saleReturn', function ($query) {
                                $query->where('status', 'completed');
                            })->exists();
                            if ($alreadyReturned) {
                                throw new \Exception('Barcode "' . $tracking->barcode . '" has already been returned.');
                            }
                            SaleReturnItem::create([
                                'sale_return_id' => $saleReturn->id,
                                'sale_item_id' => $saleItem->id,
                                'product_id' => $saleItem->product_id,
                                'purchase_item_tracking_id' => $tracking->id,
                                'quantity' => 1,
                                'price' => $saleItem->price,
                                'total' => $saleItem->price,
                            ]);
                            $tracking->update([
                                'is_sold' => 0,
                                'is_reserved' => 1, /// 1 means reserved, 0 means available
                                'sold_at' => null,
                                'store_id' => $storeId,
                                'requisition_id' => null,
                                'requisition_item_id' => null,
                            ]);
                        }

                    } else {
                        /*
                        |--------------------------------------------------------------------------
                        | Non-Serialized Product
                        |--------------------------------------------------------------------------
                        */

                        SaleReturnItem::create([
                            'sale_return_id' => $saleReturn->id,
                            'sale_item_id' => $saleItem->id,
                            'product_id' => $saleItem->product_id,
                            'purchase_item_tracking_id' => null,
                            'quantity' => $quantity,
                            'price' => $saleItem->price,
                            'total' => $lineTotal,
                        ]);
                    }
                    /*
                    |--------------------------------------------------------------------------
                    | RESTORE STORE INVENTORY
                    |--------------------------------------------------------------------------
                    |
                    | Returned quantity is added back to inventory.
                    |
                    */

                    $inventory = Inventory::where('account_id', $accountId)->where('product_id', $saleItem->product_id)->lockForUpdate()->first();
                    /*
                    |--------------------------------------------------------------------------
                    | Inventory Not Found
                    |--------------------------------------------------------------------------
                    */

                    if (!$inventory) {
                        $inventory = Inventory::create([
                            'account_id' => $accountId,
                            'product_id' => $saleItem->product_id,
                            'stock' => 0,
                        ]);
                    }

                    StockAdjustment::create([
                        'account_id' => $accountId,
                        'product_id' => $saleItem->product_id,
                        'type' => 'return',
                        'quantity' => $quantity,
                        'reference_id' => $saleReturn->id,
                        'note' => 'Customer return ' . $saleReturn->return_no . ' - Store ID: ' . $storeId,
                        'created_by' => $userId,
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Customer Wallet
                |--------------------------------------------------------------------------
                |
                | Return amount is credited to customer wallet.
                |
                */

                $customer = Customer::where('account_id', $accountId)->lockForUpdate()->find($customerId);
                if (!$customer) {
                    throw new \Exception('Customer information could not be found.');
                }
                /*
                |--------------------------------------------------------------------------
                | Credit Wallet
                |--------------------------------------------------------------------------
                */
                $customer->increment('wallet_balance', $returnTotal);
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
                'message' => collect($e->errors())->flatten()->implode('<br>'),
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

    public function scanBarcode_old(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Validate Request
        |--------------------------------------------------------------------------
        */

        try {

            $validated = $request->validate([
                'invoice_no' => [
                    'required',
                    'string',
                ],

                'barcode' => [
                    'required',
                    'string',
                ],
            ]);

            /*
            |--------------------------------------------------------------------------
            | Current User
            |--------------------------------------------------------------------------
            */

            $user = auth()->user();

            if (!$user) {

                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated user.',
                ], 401);
            }

            $accountId = $user->account_id;
            $storeId = $user->store_id;

            /*
            |--------------------------------------------------------------------------
            | Account Validation
            |--------------------------------------------------------------------------
            */

            if (!$accountId) {

                return response()->json([
                    'success' => false,
                    'message' => 'Account not found for current user.',
                ], 422);
            }

            /*
            |--------------------------------------------------------------------------
            | Store Validation
            |--------------------------------------------------------------------------
            */

            if (!$storeId) {

                return response()->json([
                    'success' => false,
                    'message' => 'Store not found for current user.',
                ], 422);
            }

            /*
            |--------------------------------------------------------------------------
            | Clean Values
            |--------------------------------------------------------------------------
            */

            $invoiceNo = trim($validated['invoice_no']);
            $barcode = trim($validated['barcode']);

            /*
            |--------------------------------------------------------------------------
            | Find Sale
            |--------------------------------------------------------------------------
            |
            | Invoice must belong to the current account and store.
            |
            */

            $sale = Sale::with([
                'customer',
            ])
                ->where('account_id', $accountId)
                ->where('store_id', $storeId)
                ->where('invoice_no', $invoiceNo)
                ->first();

            /*
            |--------------------------------------------------------------------------
            | Invoice Not Found
            |--------------------------------------------------------------------------
            */

            if (!$sale) {

                return response()->json([

                    'success' => false,

                    'message' =>
                        'Sale invoice "' .
                        $invoiceNo .
                        '" was not found.',

                ], 422);
            }

            /*
            |--------------------------------------------------------------------------
            | Check Sale Status
            |--------------------------------------------------------------------------
            |
            | Keep this according to your actual Sale status values.
            |
            */

            if (
                isset($sale->status) &&
                !in_array(
                    $sale->status,
                    ['completed', 'pending'],
                    true
                )
            ) {

                return response()->json([

                    'success' => false,

                    'message' =>
                        'This sale is not eligible for return.',

                ], 422);
            }

            /*
            |--------------------------------------------------------------------------
            | Customer Required
            |--------------------------------------------------------------------------
            |
            | Customer is required because the return amount will be
            | credited to the customer wallet.
            |
            */

            if (
                empty($sale->customer_id) ||
                !$sale->customer
            ) {

                return response()->json([

                    'success' => false,

                    'customer_required' => true,

                    'sale_id' => $sale->id,

                    'invoice_no' => $sale->invoice_no,

                    'customer_id' => null,

                    'message' =>
                        'This invoice is not linked to a customer. ' .
                        'Please add customer information before processing the return.',

                ], 422);
            }

            /*
            |--------------------------------------------------------------------------
            | Find Purchase Item Tracking By Barcode
            |--------------------------------------------------------------------------
            */

            $tracking = PurchaseItemTracking::with([
                'purchaseItem.masterItem',
            ])
                ->where('barcode', $barcode)
                ->first();

            /*
            |--------------------------------------------------------------------------
            | Barcode Not Found
            |--------------------------------------------------------------------------
            */

            if (!$tracking) {

                return response()->json([

                    'success' => false,

                    'message' =>
                        'Barcode "' .
                        $barcode .
                        '" was not found.',

                ], 422);
            }

            /*
            |--------------------------------------------------------------------------
            | Barcode Must Be Sold
            |--------------------------------------------------------------------------
            */

            if ((int) $tracking->is_sold !== 1) {

                return response()->json([

                    'success' => false,

                    'message' =>
                        'Barcode "' .
                        $barcode .
                        '" is not currently marked as sold.',

                ], 422);
            }

            /*
            |--------------------------------------------------------------------------
            | Purchase Item
            |--------------------------------------------------------------------------
            */

            $purchaseItem = $tracking->purchaseItem;

            if (!$purchaseItem) {

                return response()->json([

                    'success' => false,

                    'message' =>
                        'Purchase information was not found for barcode "' .
                        $barcode .
                        '".',

                ], 422);
            }

            /*
            |--------------------------------------------------------------------------
            | Find Sale Item Tracking
            |--------------------------------------------------------------------------
            |
            | VERY IMPORTANT:
            |
            | We verify that this exact physical barcode belongs to
            | this exact sale invoice.
            |
            */

            $saleItemTracking = SaleItemTracking::with([
                'saleItem.product',
            ])
                ->where(
                    'purchase_item_tracking_id',
                    $tracking->id
                )
                ->whereHas(
                    'saleItem',
                    function ($query) use ($sale) {

                        $query->where(
                            'sale_id',
                            $sale->id
                        );
                    }
                )
                ->first();

            /*
            |--------------------------------------------------------------------------
            | Barcode Does Not Belong To Invoice
            |--------------------------------------------------------------------------
            */

            if (!$saleItemTracking) {

                return response()->json([

                    'success' => false,

                    'message' =>
                        'Barcode "' .
                        $barcode .
                        '" does not belong to invoice "' .
                        $invoiceNo .
                        '".',

                ], 422);
            }

            /*
            |--------------------------------------------------------------------------
            | Sale Item
            |--------------------------------------------------------------------------
            */

            $saleItem = $saleItemTracking->saleItem;

            if (!$saleItem) {

                return response()->json([

                    'success' => false,

                    'message' =>
                        'Sale item information could not be found.',

                ], 422);
            }

            /*
            |--------------------------------------------------------------------------
            | Product
            |--------------------------------------------------------------------------
            */

            $product = $saleItem->product;

            if (!$product) {

                return response()->json([

                    'success' => false,

                    'message' =>
                        'Product information could not be found.',

                ], 422);
            }

            /*
            |--------------------------------------------------------------------------
            | Validate Master Item
            |--------------------------------------------------------------------------
            |
            | Purchase item and sale product must refer to the same
            | master item.
            |
            */

            if (
                !$purchaseItem->master_item_id ||
                !$product->master_item_id ||
                (int) $purchaseItem->master_item_id !==
                (int) $product->master_item_id
            ) {

                return response()->json([

                    'success' => false,

                    'message' =>
                        'Barcode "' .
                        $barcode .
                        '" does not belong to this product.',

                ], 422);
            }

            /*
            |--------------------------------------------------------------------------
            | Check Barcode Already Returned
            |--------------------------------------------------------------------------
            |
            | We now use sale_return_items.
            |
            | sale_return_trackings table is NOT required.
            |
            */

            $alreadyReturned = SaleReturnItem::where(
                'purchase_item_tracking_id',
                $tracking->id
            )
                ->whereHas(
                    'saleReturn',
                    function ($query) {

                        $query->where(
                            'status',
                            'completed'
                        );
                    }
                )
                ->exists();

            /*
            |--------------------------------------------------------------------------
            | Already Returned
            |--------------------------------------------------------------------------
            */

            if ($alreadyReturned) {

                return response()->json([

                    'success' => false,

                    'message' =>
                        'Barcode "' .
                        $barcode .
                        '" has already been returned.',

                ], 422);
            }

            /*
            |--------------------------------------------------------------------------
            | Calculate Already Returned Quantity
            |--------------------------------------------------------------------------
            |
            | This protects against returning more quantity than was sold.
            |
            */

            $returnedQty = SaleReturnItem::where(
                'sale_item_id',
                $saleItem->id
            )
                ->whereHas(
                    'saleReturn',
                    function ($query) {

                        $query->where(
                            'status',
                            'completed'
                        );
                    }
                )
                ->sum('quantity');

            /*
            |--------------------------------------------------------------------------
            | Calculate Remaining Returnable Quantity
            |--------------------------------------------------------------------------
            */

            $soldQuantity = (float) $saleItem->quantity;

            $returnableQuantity = max(
                0,
                $soldQuantity - (float) $returnedQty
            );

            /*
            |--------------------------------------------------------------------------
            | Nothing Left To Return
            |--------------------------------------------------------------------------
            */

            if ($returnableQuantity <= 0) {

                return response()->json([

                    'success' => false,

                    'message' =>
                        'All quantity of "' .
                        ($product->name ?? 'this product') .
                        '" has already been returned.',

                ], 422);
            }

            /*
            |--------------------------------------------------------------------------
            | Customer
            |--------------------------------------------------------------------------
            */

            $customer = $sale->customer;

            /*
            |--------------------------------------------------------------------------
            | Customer Name
            |--------------------------------------------------------------------------
            */

            $customerName = trim(
                ($customer->first_name ?? '') .
                ' ' .
                ($customer->last_name ?? '')
            );

            /*
            |--------------------------------------------------------------------------
            | SUCCESS RESPONSE
            |--------------------------------------------------------------------------
            */

            return response()->json([

                'success' => true,

                /*
                |--------------------------------------------------------------------------
                | Sale
                |--------------------------------------------------------------------------
                */

                'sale_id' => $sale->id,

                'invoice_no' => $sale->invoice_no,

                /*
                |--------------------------------------------------------------------------
                | Customer
                |--------------------------------------------------------------------------
                */

                'customer_id' => $customer->id,

                'customer_name' => $customerName,

                /*
                |--------------------------------------------------------------------------
                | Sale Item
                |--------------------------------------------------------------------------
                */

                'sale_item_id' => $saleItem->id,

                'product_id' => $saleItem->product_id,

                'product_name' => $product->name ?? 'Product',

                /*
                |--------------------------------------------------------------------------
                | Tracking
                |--------------------------------------------------------------------------
                */

                'tracking_id' => $tracking->id,

                'barcode' => $tracking->barcode,

                /*
                |--------------------------------------------------------------------------
                | Quantity
                |--------------------------------------------------------------------------
                */

                'quantity' => 1,

                'returnable_quantity' => $returnableQuantity,

                /*
                |--------------------------------------------------------------------------
                | Price
                |--------------------------------------------------------------------------
                */

                'price' => (float) $saleItem->price,

                /*
                |--------------------------------------------------------------------------
                | Message
                |--------------------------------------------------------------------------
                */

                'message' =>
                    'Barcode verified successfully.',

            ]);

        } catch (ValidationException $e) {

            return response()->json([

                'success' => false,

                'message' =>
                    collect($e->errors())
                        ->flatten()
                        ->implode('<br>'),

            ], 422);

        } catch (\Throwable $e) {

            report($e);

            return response()->json([

                'success' => false,

                'message' =>
                    config('app.debug')
                    ? $e->getMessage()
                    : 'Unable to verify the barcode.',

            ], 500);
        }
    }

    public function scanBarcode(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Validate Request
        |--------------------------------------------------------------------------
        */

        try {

            $validated = $request->validate([
                'invoice_no' => [
                    'required',
                    'string',
                ],

                'barcode' => [
                    'required',
                    'string',
                ],
            ]);


            /*
            |--------------------------------------------------------------------------
            | Current User
            |--------------------------------------------------------------------------
            */

            $user = auth()->user();

            if (!$user) {

                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated user.',
                ], 401);
            }

            $accountId = $user->account_id;
            $storeId = $user->store_id;


            /*
            |--------------------------------------------------------------------------
            | Account Validation
            |--------------------------------------------------------------------------
            */

            if (!$accountId) {

                return response()->json([
                    'success' => false,
                    'message' => 'Account not found for current user.',
                ], 422);
            }


            /*
            |--------------------------------------------------------------------------
            | Store Validation
            |--------------------------------------------------------------------------
            */

            if (!$storeId) {

                return response()->json([
                    'success' => false,
                    'message' => 'Store not found for current user.',
                ], 422);
            }


            /*
            |--------------------------------------------------------------------------
            | Clean Values
            |--------------------------------------------------------------------------
            */

            $invoiceNo = trim($validated['invoice_no']);
            $barcode = trim($validated['barcode']);


            /*
            |--------------------------------------------------------------------------
            | Find Sale
            |--------------------------------------------------------------------------
            |
            | Invoice must belong to current account and store.
            |
            */

            $sale = Sale::with([
                'customer',
            ])
                ->where('account_id', $accountId)
                ->where('store_id', $storeId)
                ->where('invoice_no', $invoiceNo)
                ->first();


            /*
            |--------------------------------------------------------------------------
            | Invoice Not Found
            |--------------------------------------------------------------------------
            */

            if (!$sale) {

                return response()->json([
                    'success' => false,
                    'message' =>
                        'Sale invoice "' .
                        $invoiceNo .
                        '" was not found.',
                ], 422);
            }


            /*
            |--------------------------------------------------------------------------
            | Check Sale Status
            |--------------------------------------------------------------------------
            */

            if (
                isset($sale->status) &&
                !in_array(
                    $sale->status,
                    ['completed', 'pending'],
                    true
                )
            ) {

                return response()->json([
                    'success' => false,
                    'message' =>
                        'This sale is not eligible for return.',
                ], 422);
            }


            /*
            |--------------------------------------------------------------------------
            | Customer Required
            |--------------------------------------------------------------------------
            |
            | Return amount will be credited to customer wallet.
            |
            */

            if (
                empty($sale->customer_id) ||
                !$sale->customer
            ) {

                return response()->json([

                    'success' => false,

                    'customer_required' => true,

                    'sale_id' =>
                        $sale->id,

                    'invoice_no' =>
                        $sale->invoice_no,

                    'customer_id' =>
                        null,

                    'message' =>
                        'This invoice is not linked to a customer. ' .
                        'Please add customer information before processing the return.',

                ], 422);
            }


            /*
            |--------------------------------------------------------------------------
            | Find Purchase Item Tracking
            |--------------------------------------------------------------------------
            |
            | Find the physical item using barcode.
            |
            */

            $tracking = PurchaseItemTracking::with([
                'purchaseItem.masterItem',
            ])
                ->where('barcode', $barcode)
                ->first();


            /*
            |--------------------------------------------------------------------------
            | Barcode Not Found
            |--------------------------------------------------------------------------
            */

            if (!$tracking) {

                return response()->json([
                    'success' => false,
                    'message' =>
                        'Barcode "' .
                        $barcode .
                        '" was not found.',
                ], 422);
            }


            /*
            |--------------------------------------------------------------------------
            | Barcode Must Currently Be Sold
            |--------------------------------------------------------------------------
            */

            if ((int) $tracking->is_sold !== 1) {

                return response()->json([
                    'success' => false,
                    'message' =>
                        'Barcode "' .
                        $barcode .
                        '" is not currently marked as sold.',
                ], 422);
            }


            /*
            |--------------------------------------------------------------------------
            | Purchase Item
            |--------------------------------------------------------------------------
            */

            $purchaseItem = $tracking->purchaseItem;

            if (!$purchaseItem) {

                return response()->json([
                    'success' => false,
                    'message' =>
                        'Purchase information was not found for barcode "' .
                        $barcode .
                        '".',
                ], 422);
            }


            /*
            |--------------------------------------------------------------------------
            | Find Sale Item Tracking
            |--------------------------------------------------------------------------
            |
            | IMPORTANT:
            |
            | The physical barcode must have been sold
            | on THIS invoice.
            |
            */

            $saleItemTracking = SaleItemTracking::with([
                'saleItem.product',
            ])
                ->where(
                    'purchase_item_tracking_id',
                    $tracking->id
                )
                ->whereHas(
                    'saleItem',
                    function ($query) use ($sale) {

                        $query->where(
                            'sale_id',
                            $sale->id
                        );
                    }
                )
                ->first();


            /*
            |--------------------------------------------------------------------------
            | Barcode Does Not Belong To Invoice
            |--------------------------------------------------------------------------
            */

            if (!$saleItemTracking) {

                return response()->json([
                    'success' => false,
                    'message' =>
                        'Barcode "' .
                        $barcode .
                        '" does not belong to invoice "' .
                        $invoiceNo .
                        '".',
                ], 422);
            }


            /*
            |--------------------------------------------------------------------------
            | Sale Item
            |--------------------------------------------------------------------------
            */

            $saleItem = $saleItemTracking->saleItem;

            if (!$saleItem) {

                return response()->json([
                    'success' => false,
                    'message' =>
                        'Sale item information could not be found.',
                ], 422);
            }


            /*
            |--------------------------------------------------------------------------
            | Product
            |--------------------------------------------------------------------------
            */

            $product = $saleItem->product;

            if (!$product) {

                return response()->json([
                    'success' => false,
                    'message' =>
                        'Product information could not be found.',
                ], 422);
            }


            /*
            |--------------------------------------------------------------------------
            | Validate Master Item
            |--------------------------------------------------------------------------
            */

            if (
                !$purchaseItem->master_item_id ||
                !$product->master_item_id ||
                (int) $purchaseItem->master_item_id !==
                (int) $product->master_item_id
            ) {

                return response()->json([
                    'success' => false,
                    'message' =>
                        'Barcode "' .
                        $barcode .
                        '" does not belong to this product.',
                ], 422);
            }


            /*
            |--------------------------------------------------------------------------
            | Check Barcode Already Returned FOR THIS SALE
            |--------------------------------------------------------------------------
            |
            | IMPORTANT:
            |
            | DO NOT check only by purchase_item_tracking_id.
            |
            | The same physical barcode can be:
            |
            | Sale 1 -> Return
            | Sale 2 -> Return
            | Sale 3 -> Return
            |
            | Therefore we check:
            |
            | barcode + CURRENT SALE
            |
            */

            $alreadyReturned = SaleReturnItem::where(
                'purchase_item_tracking_id',
                $tracking->id
            )
                ->whereHas(
                    'saleReturn',
                    function ($query) use ($sale) {

                        $query->where(
                            'sale_id',
                            $sale->id
                        )
                            ->where(
                                'status',
                                'completed'
                            );
                    }
                )
                ->exists();


            /*
            |--------------------------------------------------------------------------
            | Already Returned For This Invoice
            |--------------------------------------------------------------------------
            */

            if ($alreadyReturned) {

                return response()->json([

                    'success' => false,

                    'message' =>
                        'Barcode "' .
                        $barcode .
                        '" has already been returned for invoice "' .
                        $invoiceNo .
                        '".',

                ], 422);
            }


            /*
            |--------------------------------------------------------------------------
            | Calculate Already Returned Quantity
            |--------------------------------------------------------------------------
            |
            | Also restrict this to the current sale.
            |
            */

            $returnedQty = SaleReturnItem::where(
                'sale_item_id',
                $saleItem->id
            )
                ->whereHas(
                    'saleReturn',
                    function ($query) use ($sale) {

                        $query->where(
                            'sale_id',
                            $sale->id
                        )
                            ->where(
                                'status',
                                'completed'
                            );
                    }
                )
                ->sum('quantity');


            /*
            |--------------------------------------------------------------------------
            | Calculate Returnable Quantity
            |--------------------------------------------------------------------------
            */

            $soldQuantity = (float) $saleItem->quantity;

            $returnableQuantity = max(
                0,
                $soldQuantity - (float) $returnedQty
            );


            /*
            |--------------------------------------------------------------------------
            | Nothing Left To Return
            |--------------------------------------------------------------------------
            */

            if ($returnableQuantity <= 0) {

                return response()->json([

                    'success' => false,

                    'message' =>
                        'All quantity of "' .
                        ($product->name ?? 'this product') .
                        '" has already been returned for invoice "' .
                        $invoiceNo .
                        '".',

                ], 422);
            }


            /*
            |--------------------------------------------------------------------------
            | Customer
            |--------------------------------------------------------------------------
            */

            $customer = $sale->customer;


            /*
            |--------------------------------------------------------------------------
            | Customer Name
            |--------------------------------------------------------------------------
            */

            $customerName = trim(
                ($customer->name ?? '') .
                ' ' .
                ($customer->last_name ?? '')
            );


            /*
            |--------------------------------------------------------------------------
            | SUCCESS RESPONSE
            |--------------------------------------------------------------------------
            */

            return response()->json([

                'success' => true,

                /*
                |--------------------------------------------------------------------------
                | Sale
                |--------------------------------------------------------------------------
                */

                'sale_id' =>
                    $sale->id,

                'invoice_no' =>
                    $sale->invoice_no,


                /*
                |--------------------------------------------------------------------------
                | Customer
                |--------------------------------------------------------------------------
                */

                'customer_id' =>
                    $customer->id,

                'customer_name' =>
                    $customerName,


                /*
                |--------------------------------------------------------------------------
                | Sale Item
                |--------------------------------------------------------------------------
                */

                'sale_item_id' =>
                    $saleItem->id,

                'product_id' =>
                    $saleItem->product_id,

                'product_name' =>
                    $product->name ?? 'Product',


                /*
                |--------------------------------------------------------------------------
                | Tracking
                |--------------------------------------------------------------------------
                */

                'tracking_id' =>
                    $tracking->id,

                'barcode' =>
                    $tracking->barcode,


                /*
                |--------------------------------------------------------------------------
                | Quantity
                |--------------------------------------------------------------------------
                */

                'quantity' =>
                    1,

                'returnable_quantity' =>
                    $returnableQuantity,


                /*
                |--------------------------------------------------------------------------
                | Price
                |--------------------------------------------------------------------------
                */

                'price' =>
                    (float) $saleItem->price,


                /*
                |--------------------------------------------------------------------------
                | Message
                |--------------------------------------------------------------------------
                */

                'message' =>
                    'Barcode verified successfully.',
            ]);


        } catch (ValidationException $e) {

            return response()->json([

                'success' => false,

                'message' =>
                    collect($e->errors())
                        ->flatten()
                        ->implode('<br>'),

            ], 422);


        } catch (\Throwable $e) {

            report($e);

            return response()->json([

                'success' => false,

                'message' =>
                    config('app.debug')
                    ? $e->getMessage()
                    : 'Unable to verify the barcode.',

            ], 500);
        }
    }

    /**
     * Assign customer to an existing sale/invoice.
     *
     * If the invoice does not have a customer,
     * create a new customer and map it to the sale.
     */
    public function assignCustomer(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Current User
        |--------------------------------------------------------------------------
        */

        $user = auth()->user();

        $accountId = $user->account_id;
        $storeId = $user->store_id;


        /*
        |--------------------------------------------------------------------------
        | Validate Account
        |--------------------------------------------------------------------------
        */

        if (!$accountId) {

            return response()->json([
                'success' => false,
                'message' => 'Account not found for current user.',
            ], 422);
        }


        if (!$storeId) {

            return response()->json([
                'success' => false,
                'message' => 'Store not found for current user.',
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | Validate Request
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([

            'sale_id' => [
                'required',
                'integer',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'phone' => [
                'required',
                'string',
                'max:50',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

        ]);


        try {

            /*
            |--------------------------------------------------------------------------
            | Transaction
            |--------------------------------------------------------------------------
            */

            $result = DB::transaction(function () use ($validated, $accountId, $storeId) {

                /*
                |--------------------------------------------------------------------------
                | Find Sale
                |--------------------------------------------------------------------------
                |
                | Important:
                | Sale must belong to current account AND current store.
                |
                */

                $sale = Sale::where('id', $validated['sale_id'])
                    ->where('account_id', $accountId)
                    ->where('store_id', $storeId)
                    ->lockForUpdate()
                    ->first();


                /*
                |--------------------------------------------------------------------------
                | Sale Not Found
                |--------------------------------------------------------------------------
                */

                if (!$sale) {

                    throw new \Exception(
                        'Sale information could not be found.'
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Already Has Customer
                |--------------------------------------------------------------------------
                */

                if (!empty($sale->customer_id)) {

                    $customer = Customer::where('id', $sale->customer_id)
                        ->where('account_id', $accountId)
                        ->first();


                    if ($customer) {

                        return [
                            'sale' => $sale,
                            'customer' => $customer,
                            'existing' => true,
                        ];
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | Clean Customer Values
                |--------------------------------------------------------------------------
                */

                $name = trim($validated['name']);

                $phone = trim($validated['phone']);

                $email = !empty($validated['email'])
                    ? trim($validated['email'])
                    : null;


                /*
                |--------------------------------------------------------------------------
                | Find Existing Customer By Phone
                |--------------------------------------------------------------------------
                |
                | Do NOT blindly create another customer with the same
                | phone number inside the same account.
                |
                */

                $customer = Customer::where(
                    'account_id',
                    $accountId
                )
                    ->where(
                        'phone',
                        $phone
                    )
                    ->lockForUpdate()
                    ->first();


                /*
                |--------------------------------------------------------------------------
                | Create Customer If Not Exists
                |--------------------------------------------------------------------------
                */

                if (!$customer) {

                    $customer = Customer::create([

                        'account_id' =>
                            $accountId,

                        'name' =>
                            $name,

                        'phone' =>
                            $phone,

                        'email' =>
                            $email,

                        'wallet_balance' =>
                            0,

                        'status' =>
                            1,

                    ]);

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | Existing Customer
                    |--------------------------------------------------------------------------
                    |
                    | We don't overwrite the existing customer's information
                    | unnecessarily.
                    |
                    */

                    if (
                        empty($customer->email) &&
                        !empty($email)
                    ) {

                        $customer->email = $email;

                        $customer->save();
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | Link Customer To Sale
                |--------------------------------------------------------------------------
                */

                $sale->customer_id = $customer->id;

                $sale->save();


                /*
                |--------------------------------------------------------------------------
                | Return
                |--------------------------------------------------------------------------
                */

                return [
                    'sale' => $sale,
                    'customer' => $customer,
                    'existing' => false,
                ];

            });


            /*
            |--------------------------------------------------------------------------
            | Customer
            |--------------------------------------------------------------------------
            */

            $customer = $result['customer'];


            /*
            |--------------------------------------------------------------------------
            | Success Response
            |--------------------------------------------------------------------------
            */

            return response()->json([

                'success' => true,

                'message' =>
                    $result['existing']
                    ? 'Customer was already linked to this invoice.'
                    : 'Customer created and linked to the invoice successfully.',

                'sale_id' =>
                    $result['sale']->id,

                'invoice_no' =>
                    $result['sale']->invoice_no,

                'customer_id' =>
                    $customer->id,

                'customer_name' =>
                    $customer->name,

                'phone' =>
                    $customer->phone,

                'email' =>
                    $customer->email,

            ]);

        } catch (ValidationException $e) {

            return response()->json([

                'success' => false,

                'message' =>
                    collect($e->errors())
                        ->flatten()
                        ->implode('<br>'),

                'errors' =>
                    $e->errors(),

            ], 422);

        } catch (\Throwable $e) {

            report($e);

            return response()->json([

                'success' => false,

                'message' =>
                    $e->getMessage(),

            ], 422);
        }
    }



}