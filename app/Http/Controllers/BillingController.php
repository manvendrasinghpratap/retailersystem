<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Category;
use App\Models\Inventory;
use Illuminate\Support\Facades\DB;


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
            ->select('id', 'name', 'selling_price', 'category_id')
            ->first();
        if (!$product) {
            return response()->json([
                'status' => false,
                'message' => __('translation.product_not_found') . ' OR ' . __('translation.this_barcode_is_not_allowed_for_this_operation')
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
    public function completeSaleOld(Request $request)
    {
        // ✅ Validation (updated for new structure)
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.price' => 'required|numeric|min:0',

            'subtotal' => 'required|numeric',
            'tax' => 'nullable|numeric',
            'discount' => 'nullable|numeric',
            'total' => 'required|numeric',

            'payment_type' => 'required|in:full,partial',

            // ✅ Payments required
            'payments' => 'required|array|min:1',

            // ✅ Amount always required
            'payments.*.amount' => 'required|numeric|min:0',

            // 🔥 Method required ONLY if amount > 0
            'payments.*.method' => 'required_with:payments.*.amount|string',
        ]);
        try {
            $saleId = null;
            DB::transaction(function () use ($request) {

                // ✅ 1. Validate payment total
                $totalPaid = collect($request->payments)->sum('amount');

                $total = round($request->total, 2);
                $totalPaid = round($totalPaid, 2);

                if ($totalPaid !== $total) {
                    throw new \Exception('Payment total mismatch');
                }

                // ✅ 2. Create Sale
                $sale = Sale::create([
                    'invoice_no' => 'INV' . now()->timestamp,
                    'subtotal' => $request->subtotal,
                    'account_id' => 1,
                    // 'account_id' => auth()->user()->account_id,
                    'tax' => $request->tax ?? 0,
                    'discount' => $request->discount ?? 0,
                    'total' => $total,
                    'paid_amount' => $totalPaid,
                    'change_amount' => 0,

                    // ✅ Only store method if FULL
                    'payment_method' => $request->payment_type === 'full'
                        ? $request->payments[0]['method']
                        : null,

                    'status' => 'completed',
                    'user_id' => auth()->id(),
                ]);
                // ✅ Now this will work
                $saleId = $sale->id;
                // ✅ 3. Process Items
                foreach ($request->items as $item) {

                    $productId = $item['id']; // 🔥 FIXED (was product_id)

                    // 🔒 Lock inventory
                    $inventory = Inventory::where('product_id', $productId)
                        ->lockForUpdate()
                        ->first();

                    if (!$inventory) {
                        throw new \Exception('Inventory not found for product ID: ' . $productId);
                    }

                    if ($inventory->stock < $item['quantity']) {
                        throw new \Exception('Insufficient stock for product ID: ' . $productId);
                    }

                    // ✅ Save Sale Item
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $productId,
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'total' => $item['quantity'] * $item['price'],
                    ]);

                }

                // ✅ 4. Save Payment Breakdown (IMPORTANT)
                foreach ($request->payments as $pay) {

                    \App\Models\SalePayment::create([
                        'sale_id' => $sale->id,
                        'method' => $pay['method'],
                        'amount' => $pay['amount'],
                    ]);

                }


            });

            return response()->json([
                'success' => true,
                'sale_id' => $saleId,
                'message' => 'Sale completed successfully'
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'sale_id' => '',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function completeSale(Request $request)
    {
        // ✅ Validation
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.price' => 'required|numeric|min:0',

            'subtotal' => 'required|numeric',
            'tax' => 'nullable|numeric',
            'discount' => 'nullable|numeric',
            'total' => 'required|numeric',

            'payment_type' => 'required|in:full,partial',

            'payments' => 'required|array|min:1',
            'payments.*.amount' => 'required|numeric|min:0',
            'payments.*.method' => 'required_with:payments.*.amount|string',
        ]);

        try {

            // ✅ Use transaction RETURN (IMPORTANT FIX)
            $sale = DB::transaction(function () use ($request) {

                // ✅ 1. Validate payment total
                $total = round($request->total, 2);
                $totalPaid = round(collect($request->payments)->sum('amount'), 2);

                if ($totalPaid !== $total) {
                    throw new \Exception('Payment total mismatch');
                }

                // ✅ 2. Create Sale
                $sale = Sale::create([
                    'invoice_no' => 'INV' . now()->timestamp,
                    'account_id' => auth()->user()->account_id,
                    'subtotal' => $request->subtotal,
                    'tax' => $request->tax ?? 0,
                    'discount' => $request->discount ?? 0,
                    'total' => $total,
                    'paid_amount' => $totalPaid,
                    'change_amount' => 0,
                    'payment_method' => $request->payment_type === 'full'
                        ? $request->payments[0]['method']
                        : null,
                    'status' => 'completed',
                    'user_id' => auth()->id(),
                ]);

                // ✅ 3. Process Items + Reduce Stock
                foreach ($request->items as $item) {

                    $inventory = Inventory::where('product_id', $item['id'])
                        ->lockForUpdate()
                        ->first();

                    if (!$inventory) {
                        throw new \Exception('Inventory not found for product ID: ' . $item['id']);
                    }

                    if ($inventory->stock < $item['quantity']) {
                        throw new \Exception('Insufficient stock for product ID: ' . $item['id']);
                    }

                    // ✅ Save Sale Item
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $item['id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'total' => $item['quantity'] * $item['price'],
                    ]);

                    // 🔥 IMPORTANT: Reduce stock
                    $inventory->decrement('stock', $item['quantity']);
                }

                // ✅ 4. Save Payments
                foreach ($request->payments as $pay) {
                    \App\Models\SalePayment::create([
                        'sale_id' => $sale->id,
                        'method' => $pay['method'],
                        'amount' => $pay['amount'],
                    ]);
                }

                return $sale; // ✅ CRITICAL FIX
            });

            // ✅ FINAL RESPONSE
            return response()->json([
                'success' => true,
                'sale_id' => $sale->id, // ✅ NOW WORKS
                'message' => 'Sale completed successfully'
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
     * OLD METHOD (kept for reference)
     * Uses StockAdjustment model instead of direct inventory update
     */
    // public function completeSaleOld(Request $request)
    // {
    //     DB::transaction(function () use ($request) {

    //         $sale = Sale::create([
    //             'invoice_no' => 'INV' . now()->timestamp,
    //             'subtotal' => $request->subtotal,
    //             'tax' => $request->tax,
    //             'discount' => $request->discount,
    //             'total' => $request->total,
    //             'paid_amount' => $request->paid_amount,
    //             'change_amount' => $request->change_amount,
    //             'payment_method' => $request->payment_method,
    //             'status' => 'completed',
    //             'user_id' => auth()->id(),
    //         ]);

    //         foreach ($request->items as $item) {

    //             $inventory = Inventory::where('product_id', $item['product_id'])
    //                 ->lockForUpdate()
    //                 ->first();

    //             if (!$inventory || $inventory->stock < $item['quantity']) {
    //                 throw new \Exception('Insufficient stock');
    //             }

    //             SaleItem::create([
    //                 'sale_id' => $sale->id,
    //                 'product_id' => $item['product_id'],
    //                 'quantity' => $item['quantity'],
    //                 'price' => $item['price'],
    //                 'total' => $item['total'],
    //             ]);

    //             // Stock adjustment log
    //             \App\Models\StockAdjustment::create([
    //                 'product_id' => $item['product_id'],
    //                 'type' => 'sale',
    //                 'quantity' => $item['quantity'],
    //                 'reference_id' => $sale->id,
    //                 'note' => 'POS Sale',
    //             ]);
    //         }
    //     });

    //     return response()->json(['success' => true]);
    // }
}