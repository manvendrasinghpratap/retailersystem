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
    /**
     * Breadcrumb configuration for barcode reader pages
     */
    protected array $breadcrumbBarcodeReader;


    public function __construct()
    {
        $this->middleware('auth');

        $this->middleware(function ($request, $next) {

            $role = Settings::getUserRole(); // admin / staff / etc.

            $this->breadcrumbBarcodeReader = [
                'title' => __('translation.stock_management'),

                'breadcrumb' => [
                    [
                        'route' => 'admin.dashboard',
                        'title' => __('translation.dashboard')
                    ],
                    // use route NAME only (not route())
                    [
                        'route' => $role . '.no-barcode',
                        'title' => __('translation.add_product_without_barcode')
                    ],
                    [
                        'route' => $role . '.barcode',
                        'title' => __('translation.add_stock')
                    ],
                    [
                        'route' => $role . '.sales-barcode',
                        'title' => __('translation.sale_stock')
                    ],
                    [
                        'route' => $role . '.return-barcode',
                        'title' => __('translation.return_stock')
                    ],
                    [
                        'route' => $role . '.damage-barcode',
                        'title' => __('translation.damage_stock')
                    ],
                    [
                        'route' => $role . '.deduct-barcode',
                        'title' => __('translation.deduct_stock')
                    ],
                ],

                'route1' => "admin.no-barcode", // ✅ fixed typo
                'route1Title' => __('translation.add_product_without_barcode'),
                'route2Title' => __('translation.add_edit_stock'),
                'route2' => 'admin.products',
                'route3Title' => __('translation.add_update_stock'),
                'route3' => 'admin.products.edit',
                'reset_route' => 'admin.products',
                'reset_route_title' => __('translation.cancel')
            ];

            return $next($request);
        });
    }

    /**
     * Handle case when barcode is not found
     * Redirects to product create page with encrypted payload
     */
    public function nobarcode(Request $request)
    {
        $adjustmentData = Settings::getEncodeCode(1);

        $payload = Crypt::encrypt([
            'adjustment' => $adjustmentData,
            'barcode' => $request->input('barcode'),
            'product_id' => null
        ]);

        return redirect()->route('admin.products.create', $payload);
    }

    /**
     * Show barcode reader page
     */
    public function index(Request $request)
    {
        $breadcrumb = $this->breadcrumbBarcodeReader;

        return view('backend.admin.product.barcodereader', [
            'breadcrumb' => $breadcrumb,
            'categories' => Category::getCategoriesPluck(),
            'products' => Product::getProductPluck(),
        ]);
    }

    /**
     * Validate barcode and return product info
     */
    public function validateBarcode(Request $request)
    {
        // ✅ Step 1: Validate request
        $validated = $request->validate([
            'barcode' => ['required', 'string'],
            'routeName' => ['required', 'string'],
        ]);

        $barcode = trim($validated['barcode']);
        $routeName = $validated['routeName'];

        // ✅ Step 2: Get adjustment type
        $adjustmentType = Settings::getAdjustmentIdFromRoute($routeName);
        $adjustmentData = Settings::getEncodeCode($adjustmentType);

        // ✅ Step 3: Format validation (8–13 digits)
        if (!preg_match('/^[0-9]{8,13}$/', $barcode)) {
            return response()->json([
                'status' => false,
                'message' => 'Barcode must be 8 to 13 digits only.',
                'adjustmentType' => $adjustmentType
            ]);
        }

        // ✅ Step 4: EAN-13 checksum validation
        if (strlen($barcode) === 13 && !Settings::isValidEAN13($barcode)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid EAN-13 barcode checksum.',
                'adjustmentType' => $adjustmentType
            ]);
        }

        // ✅ Step 5: Find product (optimized query)
        $product = Product::query()
            ->where('barcode', $barcode)
            ->select(['id', 'barcode', 'name'])
            ->first();

        // ✅ Step 6: Prepare payload
        $payloadData = [
            'adjustment' => $adjustmentData,
            'adjustmentType' => $adjustmentType,
            'barcode' => $barcode,
            'product_id' => $product?->id
        ];

        $payload = Crypt::encrypt($payloadData);

        // ✅ Step 7: Response
        return response()->json([
            'status' => !is_null($product),
            'message' => $product
                ? 'Product found.'
                : 'Product not found. Please add product first.',
            'product' => $product,
            'payload' => $payload,
            'adjustmentType' => $adjustmentType,
        ]);
    }
}