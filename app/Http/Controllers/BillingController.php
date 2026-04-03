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
            'route1' => "admin.products.create", // ✅ fixed
            'route1Title' => __('translation.billing'),
            'route2Title' => __('translation.billing'),
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
                'message' => 'Product not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'price' => (float) $product->selling_price, // ✅ convert to number
                'category_name' => $product->category->name ?? '-'
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
            'subtotal' => 'required|numeric',
            'tax' => 'nullable|numeric',
            'discount' => 'nullable|numeric',
            'total' => 'required|numeric',
            'paid_amount' => 'required|numeric',
            'change_amount' => 'nullable|numeric',
            'payment_method' => 'required|string',
        ]);

        try {
            DB::transaction(function () use ($request) {

                // ✅ Create Sale
                $sale = Sale::create([
                    'invoice_no' => 'INV' . now()->timestamp,
                    'subtotal' => $request->subtotal,
                    'tax' => $request->tax ?? 0,
                    'discount' => $request->discount ?? 0,
                    'total' => $request->total,
                    'paid_amount' => $request->paid_amount,
                    'change_amount' => $request->change_amount ?? 0,
                    'payment_method' => $request->payment_method,
                    'status' => 'completed',
                    'user_id' => auth()->id(),
                ]);

                foreach ($request->items as $item) {

                    // ✅ Validate item structure
                    if (!isset($item['product_id'], $item['quantity'], $item['price'])) {
                        throw new \Exception('Invalid item data');
                    }

                    // 🔒 Lock inventory row
                    $inventory = Inventory::where('product_id', $item['product_id'])
                        ->lockForUpdate()
                        ->first();

                    if (!$inventory) {
                        throw new \Exception('Inventory not found for product ID: ' . $item['product_id']);
                    }

                    if ($inventory->stock < $item['quantity']) {
                        throw new \Exception('Insufficient stock for product ID: ' . $item['product_id']);
                    }

                    // ✅ Deduct stock (IMPORTANT FIX ⚠️)
                    $inventory->decrement('stock', $item['quantity']);

                    // ✅ Create Sale Item
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'total' => $item['quantity'] * $item['price'],
                    ]);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Sale completed successfully'
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * OLD METHOD (kept for reference)
     * Uses StockAdjustment model instead of direct inventory update
     */
    public function completeSaleOld(Request $request)
    {
        DB::transaction(function () use ($request) {

            $sale = Sale::create([
                'invoice_no' => 'INV' . now()->timestamp,
                'subtotal' => $request->subtotal,
                'tax' => $request->tax,
                'discount' => $request->discount,
                'total' => $request->total,
                'paid_amount' => $request->paid_amount,
                'change_amount' => $request->change_amount,
                'payment_method' => $request->payment_method,
                'status' => 'completed',
                'user_id' => auth()->id(),
            ]);

            foreach ($request->items as $item) {

                $inventory = Inventory::where('product_id', $item['product_id'])
                    ->lockForUpdate()
                    ->first();

                if (!$inventory || $inventory->stock < $item['quantity']) {
                    throw new \Exception('Insufficient stock');
                }

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['total'],
                ]);

                // Stock adjustment log
                \App\Models\StockAdjustment::create([
                    'product_id' => $item['product_id'],
                    'type' => 'sale',
                    'quantity' => $item['quantity'],
                    'reference_id' => $sale->id,
                    'note' => 'POS Sale',
                ]);
            }
        });

        return response()->json(['success' => true]);
    }
}