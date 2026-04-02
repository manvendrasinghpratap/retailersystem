<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Category;
use DB;

class BillingController extends Controller
{
    protected $breadcrumbBarcodeReader;
    public function __construct()
    {
        $this->middleware('auth');

        $this->breadcrumbBarcodeReader = [
            'title' => __('translation.billing'),
            'route1-' => "admin.products.create",
            'route1Title' => __('translation.billing'),
            'route2Title' => __('translation.billing'),
            'route2' => 'admin.products',
            'route3Title' => __('translation.billing'),
            'route3' => 'admin.products.edit',
            'reset_route' => 'admin.products',
            'reset_route_title' => __('translation.cancel')
        ];
    }
    public function index(Request $request)
    {
        $breadcrumb = $this->breadcrumbBarcodeReader;
        $routeName = $request->route()->getName();
        $categories = Category::getCategoriesPluck();
        $products = Product::getProductPluck();
        return view('backend.billing.index', compact('breadcrumb', 'categories', 'products'));
    }

    // Scan product by barcode
    public function scanProduct(Request $request)
    {
        $product = Product::where('barcode', $request->barcode)->first();

        if (!$product) {
            return response()->json(['error' => 'Product not found']);
        }

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->selling_price
        ]);
    }


    public function completeSale(Request $request)
    {
        try {

            DB::transaction(function () use ($request) {

                // Create Sale
                $sale = Sale::create([
                    'invoice_no' => 'INV' . time(),
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

                    // 🔒 Lock inventory row
                    $inventory = \App\Models\Inventory::where('product_id', $item['product_id'])
                        ->lockForUpdate()
                        ->first();

                    if (!$inventory || $inventory->stock < $item['quantity']) {
                        throw new \Exception('Insufficient stock for product ID: ' . $item['product_id']);
                    }

                    // Only create SaleItem
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                    ]);
                }
            });

            return response()->json(['success' => true]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
    // Complete sale
    public function completeSaleold(Request $request)
    {
        DB::transaction(function () use ($request) {

            // Create Sale
            $sale = Sale::create([
                'invoice_no' => 'INV' . time(),
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

                // 🔥 Check stock before sale
                $inventory = \App\Models\Inventory::where('product_id', $item['product_id'])
                    ->lockForUpdate()
                    ->first();

                if (!$inventory || $inventory->stock < $item['quantity']) {
                    throw new \Exception('Insufficient stock');
                }

                // Create Sale Item
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['total'],
                ]);

                // 🔥 Use StockAdjustment instead
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