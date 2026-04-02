<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Helpers\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class BarcodeController extends Controller
{
    protected $breadcrumbBarcodeReader;

    public function __construct()
    {
        $this->middleware('auth');

        $this->breadcrumbBarcodeReader = [
            'title' => __('translation.stock_management'),
            'route1-' => "admin.products.create",
            'route1Title' => __('translation.add_edit_stock'),
            'route2Title' => __('translation.add_edit_stock'),
            'route2' => 'admin.products',
            'route3Title' => __('translation.add_update_stock'),
            'route3' => 'admin.products.edit',
            'reset_route' => 'admin.products',
            'reset_route_title' => __('translation.cancel')
        ];
    }

    public function nobarcode(Request $request)
    {
        $adjustmentData = Settings::getEncodeCode(1);
        $payload = Crypt::encrypt([
            'adjustment' => $adjustmentData,
            'barcode' => $request->barcode,
            'product_id' => null
        ]);
        return redirect()->route('admin.products.create', $payload);
    }

    public function index(Request $request)
    {
        $breadcrumb = $this->breadcrumbBarcodeReader;
        $routeName = $request->route()->getName();
        $categories = Category::getCategoriesPluck();
        $products = Product::getProductPluck();
        return view('backend.admin.product.barcodereader', compact('breadcrumb', 'categories', 'products'));
    }

    public function validateBarcode(Request $request)
    {
        // ✅ Step 1: Validate request
        $request->validate([
            'barcode' => 'required|string',
            'routeName' => 'required|string',
        ]);

        $barcode = trim($request->barcode);
        $routeName = $request->routeName;
        $adjustmentType = Settings::getAdjustmentIdFromRoute($routeName);
        $adjustmentData = Settings::getEncodeCode($adjustmentType);
        // ✅ Step 2: Format check (8–13 digits)
        if (!preg_match('/^[0-9]{8,13}$/', $barcode)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid barcode format',
                'adjustmentType' => $adjustmentType
            ]);
        }

        // ✅ Step 3: EAN-13 checksum validation
        if (strlen($barcode) === 13 && !Settings::isValidEAN13($barcode)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid barcode checksum',
                'adjustmentType' => $adjustmentType
            ]);
        }

        // ✅ Step 4: Get adjustment type from route

        // ✅ Step 5: Find product (optimized query)
        $product = Product::where('barcode', $barcode)
            ->select('id', 'barcode', 'name') // adjust fields if needed
            ->first();

        // ✅ Step 6: Prepare payload (single structure)
        $payloadData = [
            'adjustment' => $adjustmentData,
            'adjustmentType' => $adjustmentType,
            'barcode' => $barcode,
            'product_id' => $product->id ?? null
        ];

        $payload = Crypt::encrypt($payloadData);

        // ✅ Step 7: Final response
        return response()->json([
            'status' => (bool) $product,
            'message' => $product
                ? 'Product found'
                : 'Product not found. Please add product first.',
            'product' => $product,
            'payload' => $payload,
            'adjustmentType' => $adjustmentType,
        ]);
    }
}