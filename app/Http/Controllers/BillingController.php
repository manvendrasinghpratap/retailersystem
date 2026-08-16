<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\SaleItem;
use App\Models\Category;
use App\Models\Inventory;
use Illuminate\Support\Facades\DB;
use App\Helpers\EmailHelper;
use App\Mail\CustomerInvoiceMail;
use Illuminate\Support\Facades\Mail;
use App\Models\PaymentType;
use App\Models\CreditDuration;
use App\Models\SalePayment;
use App\Models\PaymentMethod;
use App\Models\PurchaseItemTracking;
use App\Models\SaleItemTracking;
use App\Helpers\Settings;
use Exception;
use App\Models\StockAdjustment;

class BillingController extends Controller
{
    /**
     * Breadcrumb configuration for billing page
     */
    protected array $breadcrumbBarcodeReader;

    public function __construct()
    {
        $this->middleware('auth');

        $this->breadcrumbBarcodeReader = [
            'title' => __('translation.billing'),
            'breadcrumb' => [
                [
                    'route' => 'admin.dashboard',
                    'title' => __('translation.dashboard')
                ],
                [
                    'route' => 'billing.index',
                    'title' => __('translation.billing')
                ],
                [
                    'route' => 'admin.sales.index',
                    'title' => __('translation.sales_list')
                ]
            ],
            'route1' => "admin.barcode", // ✅ fixed
            'route1Title' => __('translation.add_stock'),
            'route2Title' => __('translation.add_stock'),
            'route2' => 'admin.products',
            'route3Title' => __('translation.billing'),
            'route3' => 'admin.products.edit',
            'reset_route' => 'admin.products',
            'reset_route_title' => __('translation.cancel')
        ];
    }

    /**
     * Show billing (POS) page
     */
    public function index(Request $request)
    {
        return view('backend.billing.index', [
            'breadcrumb' => $this->breadcrumbBarcodeReader,
            'categories' => Category::getCategoriesPluck(),
            'products' => Product::getProductPluck(),
            'paymentTypes' => PaymentType::getSelectable(),
            'creditDurations' => CreditDuration::getSelectable(),
            'paymentMethods' => PaymentMethod::getSelectable(),
            'taxPercentage' => account_setting('general.tax')
        ]);
    }

    /**
     * Summary of getCreditDuration
     * @param mixed $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCreditDuration($id)
    {
        $duration = CreditDuration::ofAccount()
            ->active()
            ->findOrFail($id);

        return response()->json([
            'id' => $duration->id,
            'name' => $duration->name,
            'duration_days' => $duration->duration_days,
            'interest' => $duration->interest,
        ]);
    }

    /**
     * Scan product by barcode
     */
    public function scanProduct(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string'
        ]);

        $barcode = trim($request->barcode);

        $product = Product::with(['stock', 'category:id,name'])
            ->where('barcode', $barcode)
            ->ofAccount() // ✅ correct usage
            ->active()
            ->select('id', 'name', 'selling_price', 'category_id', 'barcode')
            ->first();
        if (!$product) {
            return response()->json([
                'status' => false,
                'message' => __('translation.product_not_found') . ' OR ' . __('translation.this_barcode_is_not_allowed_for_this_operation')
            ], 200);
        }
        // =========================================================
        // FIND AVAILABLE TRACKING
        // =========================================================

        $tracking = PurchaseItemTracking::where('barcode', $barcode)
            ->where('status', 1)
            ->where('is_sold', 0)
            ->first();

        if (!$tracking) {
            return response()->json([
                'status' => false,
                'message' => 'This barcode is not available for sale.',
            ], 200);
        }
        return response()->json([
            'status' => true,
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'price' => (float) $product->selling_price, // ✅ convert to number
                'category_name' => $product->category->name ?? '-',
                'stock' => $product->stock->stock ?? 0,
                'tracking_id' => $tracking->id,
                'barcode' => $tracking->barcode,
            ]
        ]);
    }

    /**
     * Complete Sale (POS)
     * - Creates sale
     * - Validates stock
     * - Deducts inventory
     * - Stores sale items
     */
    public function completeSale(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.tracking_ids' => 'nullable|array',
            'items.*.tracking_ids.*' => 'integer|exists:purchase_item_trackings,id',
            'items.*.barcodes' => 'nullable|array',
            'items.*.barcodes.*' => 'nullable|string',

            'subtotal' => 'required|numeric',
            'tax' => 'nullable|numeric',
            'discount' => 'nullable|numeric',
            'total' => 'required|numeric',

            'payment_type' => 'required|in:full,partial,credit',

            'payments' => 'nullable|array',
            'payments.*.amount' => 'required|numeric|min:0',
            'payments.*.method' => 'required_with:payments.*.amount',

            'customer_id' => 'nullable|integer',
            'credit_duration_id' => 'nullable|required_if:payment_type,credit|integer',

            // 🔹 Delivery / Fulfillment Validation
            'fulfillment_type' => 'required|in:pickup,delivery',
            'delivery_address' => 'required_if:fulfillment_type,delivery|nullable|string',
            'delivery_amount' => 'required_if:fulfillment_type,delivery|nullable|numeric|min:0',
            'delivery_notes' => 'nullable|string',
        ]);

        try {
            $storeId = auth()->user()->store_id ?? 0;
            if ($storeId == 0) {
                return response()->json(['status' => false, 'message' => 'Store not found.']);
            }

            if ($request->payment_type === 'credit' && empty($request->customer_id)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'customer_id' => 'Customer is required for credit sales.'
                ]);
            }

            $sale = DB::transaction(function () use ($request) {
                $total = round($request->total, 2);
                $totalPaid = round(collect($request->payments ?? [])->sum('amount'), 2);

                $creditDuration = null;
                $dueDate = null;
                $interestRate = 0;
                $interestAmount = 0;
                $payableAmount = $total;
                $balanceAmount = 0;
                $status = 'completed';
                $payment_status = 'paid';
                $payment_approval_status = 'approve';
                $payment_approved_by = auth()->id();
                $payment_approved_at = now();

                // 🔹 Check if credit duration was selected
                if (!empty($request->credit_duration_id)) {
                    $creditDuration = CreditDuration::ofAccount()->active()->find($request->credit_duration_id);
                    if ($creditDuration) {
                        $interestRate = $creditDuration->interest;
                        $interestAmount = $request->interest_amount ?? 0;
                        $payableAmount = $request->payable_amount ?? $total;
                        $dueDate = now()->addDays($creditDuration->duration_days);
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Credit Sale
                |--------------------------------------------------------------------------
                */
                if ($request->payment_type === 'credit') {
                    $balanceAmount = $payableAmount;
                    $totalPaid = 0;
                    $status = 'pending'; // Stays pending until manager approves
                    $payment_status = 'unpaid';
                    $payment_approval_status = 'pending';
                    $payment_approved_by = null;
                    $payment_approved_at = null;
                }
                /*
                |--------------------------------------------------------------------------
                | Full Payment
                |--------------------------------------------------------------------------
                */ elseif ($request->payment_type === 'full') {
                    if (abs($totalPaid - $total) > 0.01) {
                        throw new \Exception('Payment total mismatch.');
                    }
                    $balanceAmount = 0;
                }
                /*
                |--------------------------------------------------------------------------
                | Partial Payment
                |--------------------------------------------------------------------------
                */ elseif ($request->payment_type === 'partial') {
                    if (empty($request->payments)) {
                        throw new \Exception('At least one payment method is required.');
                    }
                    if (abs($totalPaid - $total) > 0.01) {
                        throw new \Exception('Split payment total must equal invoice total.');
                    }
                    $balanceAmount = 0;
                }

                /*
                |--------------------------------------------------------------------------
                | Create Sale Record
                |--------------------------------------------------------------------------
                */
                $sale = Sale::create([
                    'invoice_no' => 'INV' . now()->timestamp,
                    'store_id' => auth()->user()->store_id,
                    'customer_id' => $request->customer_id,
                    'account_id' => auth()->user()->account_id,
                    'subtotal' => $request->subtotal,
                    'tax' => $request->tax ?? 0,
                    'discount' => $request->discount ?? 0,
                    'total' => $total,
                    'paid_amount' => $totalPaid,
                    'balance_amount' => $balanceAmount,
                    'change_amount' => 0,
                    'payment_method' => $request->payment_type === 'full' ? ($request->payments[0]['method'] ?? null) : null,
                    'status' => $status,
                    'payment_status' => $payment_status,
                    'payment_type' => $request->payment_type,

                    // 🔹 Fulfillment / Delivery Fields
                    'delivery_type' => $request->fulfillment_type,
                    'delivery_address' => $request->fulfillment_type === 'delivery' ? $request->delivery_address : null,
                    'delivery_charge' => $request->fulfillment_type === 'delivery' ? ($request->delivery_amount ?? 0) : 0,
                    'delivery_notes' => $request->delivery_notes,

                    // Credit / Duration Fields
                    'credit_duration_id' => $creditDuration?->id,
                    'due_date' => $dueDate,
                    'interest_rate' => $interestRate,
                    'interest_amount' => $interestAmount,
                    'payable_amount' => $payableAmount,
                    'payment_approval_status' => $payment_approval_status,
                    'user_id' => auth()->id(),
                    'payment_approved_by' => $payment_approved_by,
                    'payment_approved_at' => $payment_approved_at,
                ]);

                /*
                |--------------------------------------------------------------------------
                | Sale Items & Stock Handling
                |--------------------------------------------------------------------------
                */
                foreach ($request->items as $item) {
                    $inventory = Inventory::where('product_id', $item['id'])->lockForUpdate()->first();

                    if (!$inventory) {
                        throw new \Exception('Inventory not found for product ID: ' . $item['id']);
                    }

                    if ($inventory->stock < $item['quantity']) {
                        throw new \Exception('Insufficient stock for product ID: ' . $item['id']);
                    }

                    $saleItem = SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $item['id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'total' => $item['quantity'] * $item['price'],
                    ]);

                    // 🔹 Standard (Non-Credit) Sale: Deduct inventory stock immediately
                    if ($request->payment_type !== 'credit') {
                        //$inventory->decrement('stock', $item['quantity']);
                    }

                    if (!empty($item['tracking_ids'])) {
                        foreach ($item['tracking_ids'] as $trackingId) {
                            $tracking = PurchaseItemTracking::where('id', $trackingId)->lockForUpdate()->firstOrFail();

                            if ((int) $tracking->is_sold !== 0) {
                                throw new \Exception("Barcode {$tracking->barcode} is already sold.");
                            }

                            SaleItemTracking::create([
                                'sale_item_id' => $saleItem->id,
                                'purchase_item_tracking_id' => $tracking->id,
                            ]);

                            // 🔹 Standard (Non-Credit) Sale: Mark barcode as sold immediately
                            // if ($request->payment_type !== 'credit') {   //// the reason of this we sold the product if sale is is rejected then we reverse the prosesses
                            $tracking->update([
                                'is_sold' => 1,
                                'sold_at' => now(),
                                'store_id' => auth()->user()->store_id,
                            ]);
                            // }
                        }
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Sale Payments
                |--------------------------------------------------------------------------
                */
                if (!empty($request->payments) && $request->payment_type !== 'credit') {
                    foreach ($request->payments as $pay) {
                        $methodName = $pay['method'];
                        if (is_numeric($pay['method'])) {
                            $paymentMethod = PaymentMethod::find($pay['method']);
                            $methodName = $paymentMethod ? $paymentMethod->short_name : $pay['method'];
                        }

                        SalePayment::create([
                            'sale_id' => $sale->id,
                            'method' => $methodName,
                            'amount' => $pay['amount'],
                            'payment_received_by' => auth()->id(),
                        ]);
                    }
                }

                return $sale;
            });

            // 🔹 Custom Response Message depending on payment type

            $paymentType = $request->input('payment_type') ?? $request->input('payments.0.method');
            $isCredit = strtolower(trim($paymentType)) === 'credit';
            $message = $isCredit
                ? 'Credit sale recorded. Manager approval is required to confirm and mark this sale as sold.'
                : __('translation.sale_completed');


            return response()->json([
                'success' => true,
                'sale_id' => $sale->id,
                'is_credit' => $isCredit,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'sale_id' => null,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Handle Manager Approval or Rejection for Credit Sales
     */
    public function approveCreditSale_old(Request $request, $id)
    {
        $saleDetails = Settings::getDecodeCodeWithHashids($id);
        $saleId = $saleDetails[0];
        $sale = Sale::with('items.trackings')->findOrFail($saleId);
        $request->validate([
            'action' => 'required|in:approve,reject',
            'note' => 'required|string|max:1000',
        ]);

        try {
            $saleDetails = Settings::getDecodeCodeWithHashids($id);
            $saleId = $saleDetails[0];
            $sale = Sale::with('items.trackings')->findOrFail($saleId);
            // Case-insensitive check to avoid mismatches (e.g., "Credit" vs "credit")
            if (strtolower($sale->payment_type) !== 'credit' || strtolower($sale->payment_approval_status) !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'This sale does not require approval or has already been processed.'
                ], 422);
            }

            DB::transaction(function () use ($request, $sale) {
                if ($request->action === 'reject') {
                    // 1. Process Stock Deduction & Barcode Tracking Updates
                    foreach ($sale->items as $item) {
                        // Mark tracked barcodes as sold
                        if ($item->trackings && $item->trackings->isNotEmpty()) {
                            foreach ($item->trackings as $saleTracking) {
                                // Resolve the actual tracking record ID safely
                                $trackingId = $saleTracking->purchase_item_tracking_id ?? $saleTracking->id;
                                $tracking = PurchaseItemTracking::where('id', $trackingId)->lockForUpdate()->first();

                                if ($tracking) {
                                    if ((int) $tracking->is_sold !== 1) {
                                        throw new Exception("Barcode '{$tracking->barcode}' is not sold.");
                                    }

                                    $tracking->update([
                                        'is_sold' => 0,
                                        'sold_at' => null,
                                        'store_id' => null,
                                    ]);
                                }

                                // StockAdjustment::create([
                                //     'account_id' => $accountId,
                                //     'product_id' => $saleItem->product_id,
                                //     'type' => 'add',
                                //     'quantity' => $quantity,
                                //     'reference_id' => $saleReturn->id,
                                //     'note' => 'Customer return ' . $saleReturn->return_no . ' - Store ID: ' . $storeId,
                                //     'created_by' => $userId,
                                // ]);


                            }
                        }
                    }

                    // Reject/Cancel the credit request
                    $sale->update([
                        'status' => 'cancelled',
                        'payment_approval_status' => 'reject',
                        'payment_approved_by' => auth()->id(),
                        'payment_approved_at' => now(),
                        'payment_approval_note' => $request->note,
                    ]);

                } else {
                    // 2. Update Sale Status to Approved
                    $sale->update([
                        'status' => 'completed',
                        'payment_approval_status' => 'approve',
                        'payment_approved_by' => auth()->id(),
                        'payment_approved_at' => now(),
                        'payment_approval_note' => $request->note,
                    ]);
                }
            });

            $actionText = $request->action === 'approve' ? 'approve' : 'reject';

            return response()->json([
                'success' => true,
                'message' => "Credit sale invoice #{$sale->invoice_no} has been {$actionText} successfully."
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function approveCreditSale(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'note' => 'required|string|max:1000',
        ]);

        try {
            // Decode Hash ID to get actual Sale ID
            $saleDetails = Settings::getDecodeCodeWithHashids($id);
            if (empty($saleDetails)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid sale ID provided.'
                ], 404);
            }

            $saleId = $saleDetails[0];
            $sale = Sale::with('items.trackings')->findOrFail($saleId);

            // Case-insensitive validation check
            if (strtolower($sale->payment_type) !== 'credit' || strtolower($sale->payment_approval_status) !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'This sale does not require approval or has already been processed.'
                ], 422);
            }

            DB::transaction(function () use ($request, $sale) {
                if ($request->action === 'reject') {
                    // Since product was already marked as sold at creation, reverse stock & tracking on rejection
                    foreach ($sale->items as $item) {

                        // 1. Restore Inventory Stock
                        $inventory = Inventory::where('product_id', $item->product_id)
                            // ->where('store_id', $sale->store_id)
                            ->lockForUpdate()
                            ->first();

                        if ($inventory) {
                            // $inventory->increment('stock', $item->quantity); as this is already adjust in StockAdjustment service 
                        }

                        // 2. Mark Tracked Barcodes as Not Sold
                        if ($item->trackings && $item->trackings->isNotEmpty()) {
                            foreach ($item->trackings as $saleTracking) {
                                $trackingId = $saleTracking->purchase_item_tracking_id ?? $saleTracking->id;
                                $tracking = PurchaseItemTracking::where('id', $trackingId)
                                    ->lockForUpdate()
                                    ->first();

                                if ($tracking) {
                                    $tracking->update([
                                        'is_sold' => 0,
                                        'sold_at' => null,
                                        'store_id' => null,
                                    ]);
                                }
                            }
                        }

                        // 3. Create Stock Adjustment Entry for Rejection
                        StockAdjustment::create([
                            'account_id' => $sale->account_id ?? null,
                            'product_id' => $item->product_id,
                            'type' => 'add',
                            'quantity' => $item->quantity,
                            'reference_id' => $sale->id,
                            'note' => 'Credit sale rejected (Invoice #' . $sale->invoice_no . ') - Store ID: ' . $sale->store_id,
                            'created_by' => auth()->id(),
                        ]);
                    }

                    // Mark Sale as Cancelled / Rejected
                    $sale->update([
                        'status' => 'cancelled',
                        'payment_approval_status' => 'reject',
                        'payment_approved_by' => auth()->id(),
                        'payment_approved_at' => now(),
                        'payment_approval_note' => $request->note,
                    ]);

                } else {
                    // Mark Sale as Completed / Approved (Stock was already deducted during sale creation)
                    $sale->update([
                        'status' => 'completed',
                        'payment_approval_status' => 'approve',
                        'payment_approved_by' => auth()->id(),
                        'payment_approved_at' => now(),
                        'payment_approval_note' => $request->note,
                    ]);
                }
            });

            $actionText = $request->action === 'approve' ? 'approved' : 'rejected';

            return response()->json([
                'success' => true,
                'message' => "Credit sale invoice #{$sale->invoice_no} has been {$actionText} successfully."
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }


}