<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Helpers\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use App\Models\PurchaseItemTracking;
use DB;

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
                'title' => __('translation.stock_adjustments'),

                'breadcrumb' => [
                    [
                        'route' => 'admin.dashboard',
                        'title' => __('translation.dashboard')
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
        $requisition_item_id = '';
        if ($request->has('requisition_item_id')) {
            $requisition_item_id = $request->requisition_item_id;
        }
        $adjustmentData = Settings::getEncodeCode(1);

        $payload = Crypt::encrypt([
            'adjustment' => $adjustmentData,
            'barcode' => $request->input('barcode'),
            'product_id' => null,
            'requisition_item_id' => $requisition_item_id
        ]);

        return redirect()->route('admin.products.create', $payload);
    }

    /**
     * Show barcode reader page
     */
    public function index(Request $request)
    {
        if (!$request->has('requisition_item_id') || !$request->has('purchase_item_tracking_barcode')) {
            return redirect()->route('admin.requisitions.pending.posting')->with('error', __("translation.invalid_barcode"));
        }
        $breadcrumb = $this->breadcrumbBarcodeReader;
        return view('backend.admin.product.barcodereader.index', [
            'breadcrumb' => $breadcrumb,
            'categories' => Category::getCategoriesPluck(),
            'products' => Product::getProductPluck(),
        ]);
    }
    public function returnBarcode(Request $request)
    {
        $breadcrumb = $this->breadcrumbBarcodeReader;
        return view('backend.admin.product.barcodereader', [
            'breadcrumb' => $breadcrumb,
            'categories' => Category::getCategoriesPluck(),
            'products' => Product::getProductPluck(),
        ]);
    }
    public function salesBarcode(Request $request)
    {
        $breadcrumb = $this->breadcrumbBarcodeReader;
        return view('backend.admin.product.barcodereader', [
            'breadcrumb' => $breadcrumb,
            'categories' => Category::getCategoriesPluck(),
            'products' => Product::getProductPluck(),
        ]);
    }
    public function deductBarcode(Request $request)
    {
        $breadcrumb = $this->breadcrumbBarcodeReader;
        return view('backend.admin.product.barcodereader', ['breadcrumb' => $breadcrumb, 'categories' => Category::getCategoriesPluck(), 'products' => Product::getProductPluck(),
        ]);
    }
    public function damageBarcode(Request $request)
    {
        $breadcrumb = $this->breadcrumbBarcodeReader;
        return view('backend.admin.product.barcodereader', ['breadcrumb' => $breadcrumb, 'categories' => Category::getCategoriesPluck(), 'products' => Product::getProductPluck(),
        ]);
    }


    public function validateBarcodeRequisitionId(Request $request)
    {
            /*
            |--------------------------------------------------------------------------
            | STEP 1: VALIDATE REQUEST
            |--------------------------------------------------------------------------
            */
        //$this->pr($request->all()); die();
            $validator = Validator::make($request->all(), [
                'barcode' => ['required', 'string'],
                'routeName' => ['required', 'string'],
                'requisition_item_id' => ['required', 'integer'],
                'purchase_item_tracking_barcode' => ['required', 'string'],
                'returnRoute' => ['required', 'string'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'validation' => true,
                    'redirect' => $request->input('returnRoute'),
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors(),
                ], 422);
            }

            $validated = $validator->validated();

            $barcode = trim($validated['barcode']);
            $routeName = $validated['routeName'];
            $requisition_item_id = $validated['requisition_item_id'];
            $purchase_item_tracking_barcode = $validated['purchase_item_tracking_barcode'];
            $returnRoute = html_entity_decode($validated['returnRoute']);


            /*
            |--------------------------------------------------------------------------
            | STEP 2: DECODE PURCHASE TRACKING BARCODE
            |--------------------------------------------------------------------------
            */
            try {
                
                $requisition_item_id = Settings::getDecodeCode($requisition_item_id);
                $purchase_item_tracking_barcode = Settings::getDecodeCode($purchase_item_tracking_barcode);
                
            } catch (\Exception $e) {
                
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid purchase tracking barcode.',
                    'returnRoute' => $returnRoute
                ]);
            }
           

            /*
            |--------------------------------------------------------------------------
            | STEP 3: GET ADJUSTMENT TYPE
            |--------------------------------------------------------------------------
            */

            $adjustmentType =
                Settings::getAdjustmentIdFromRoute($routeName);

            $adjustmentData =
                Settings::getEncodeCode($adjustmentType);


            /*
            |--------------------------------------------------------------------------
            | STEP 4: VALIDATE BARCODE
            |--------------------------------------------------------------------------
            */

            if (!Settings::isValidBarcode($barcode)) {

                return response()->json([
                    'status' => false,
                    'message' => 'Barcode must be a valid 12 or 13 digit barcode.',
                    'adjustmentType' => $adjustmentType,
                    'returnRoute' => $returnRoute
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | STEP 5: VALIDATE CHECKSUM
            |--------------------------------------------------------------------------
            */

            if (strlen($barcode) === 12) {

                if (!Settings::isValidBarcode($barcode)) {

                    return response()->json([
                        'status' => false,
                        'message' => 'Invalid UPC-A barcode checksum.',
                        'adjustmentType' => $adjustmentType,
                        'returnRoute' => $returnRoute
                    ]);
                }

            } elseif (strlen($barcode) === 13) {

                if (!Settings::isValidEAN13($barcode)) {

                    return response()->json([
                        'status' => false,
                        'message' => 'Invalid EAN-13 barcode checksum.',
                        'adjustmentType' => $adjustmentType,
                        'returnRoute' => $returnRoute
                    ]);
                }
            }

 
            /*
            |--------------------------------------------------------------------------
            | STEP 6: VERIFY SCANNED BARCODE
            |--------------------------------------------------------------------------
            */

            if ($purchase_item_tracking_barcode != $barcode) {

                return response()->json([
                    'status' => false,
                    'message' => 'Barcode is not matching with purchase item tracking barcode.',
                    'adjustmentType' => $adjustmentType,
                    'returnRoute' => $returnRoute
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | STEP 7: FIND EXACT PURCHASE ITEM TRACKING
            |--------------------------------------------------------------------------
            |
            | IMPORTANT:
            |
            | DO NOT search Product by barcode alone.
            |
            | We first identify the exact tracking record using:
            |
            | barcode
            | +
            | requisition_item_id
            |
            */

            $tracking = PurchaseItemTracking::with([
                'purchaseItem.masterItem'
            ])
                ->where('barcode', $barcode)
                ->where(
                    'requisition_item_id',
                    $requisition_item_id
                )
                ->first();

// echo '===================>'.$purchase_item_tracking_barcode; 
// echo '<br><br>requisition_item_id----->'.$requisition_item_id; 
//             //'095723022590'
//             $this->pr($tracking);
//             die('ssssssssssssssoxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxoooooo');
            /*
            |--------------------------------------------------------------------------
            | TRACKING NOT FOUND
            |--------------------------------------------------------------------------
            */

            if (!$tracking) {

                return response()->json([
                    'status' => false,
                    'message' => 'Barcode is not assigned to this requisition item.',
                    'adjustmentType' => $adjustmentType,
                    'returnRoute' => $returnRoute
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | STEP 8: GET MASTER ITEM
            |--------------------------------------------------------------------------
            */

            $masterItemId =
                $tracking->purchaseItem->master_item_id ?? null;


            if (!$masterItemId) {

                return response()->json([
                    'status' => false,
                    'message' => 'Unable to identify product for this barcode.',
                    'adjustmentType' => $adjustmentType,
                    'returnRoute' => $returnRoute
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | STEP 9: FIND EXACT PRODUCT
            |--------------------------------------------------------------------------
            |
            | IMPORTANT:
            |
            | This depends on your Product model/table having master_item_id.
            |
            */

            $product = Product::query()
                ->where(
                    'account_id',
                    auth()->user()->account_id
                )
                ->where(
                    'master_item_id',
                    $masterItemId
                )
                ->where(
                    'barcode',
                    $barcode
                )
                ->select([
                    'id',
                    'master_item_id',
                    'barcode',
                    'name'
                ])
                ->first();
 
// $product = Product::query()->where('barcode', $barcode)->select(['id', 'barcode', 'name'])->first();
            /*
            |--------------------------------------------------------------------------
            | PRODUCT NOT FOUND
            |--------------------------------------------------------------------------
            */

            // if (!$product) {

            //     return response()->json([
            //         'status' => false,
            //         'message' => 'Product not found for this requisition item.',
            //         'adjustmentType' => $adjustmentType,
            //         'returnRoute' => html_entity_decode($returnRoute)
            //     ]);
            // }


            /*
            |--------------------------------------------------------------------------
            | STEP 10: PREPARE PAYLOAD
            |--------------------------------------------------------------------------
            */

            $payloadDatadata = [
                'adjustment' => $adjustmentData,
                'adjustmentType' => $adjustmentType,
                'barcode' => $barcode,

                // EXACT PRODUCT
                'product_id' => $product?->id,

                // REQUISITION ITEM
                'requisition_item_id' => $requisition_item_id,

                // TRACKING RECORD
                'purchase_item_tracking_id' => $tracking->id,

                'returnRoute' => $returnRoute
            ];

            $payloadData = [
            'adjustment' => $adjustmentData,
            'adjustmentType' => $adjustmentType,
            'barcode' => $barcode,
            'product_id' => $product?->id,
            'requisition_item_id' => Settings::getEncodeCode($requisition_item_id),
            'purchase_item_tracking_id' => Settings::getEncodeCode($tracking->id),
            'returnRoute' => $returnRoute
            ];


            /*
            |--------------------------------------------------------------------------
            | STEP 11: ENCRYPT PAYLOAD
            |--------------------------------------------------------------------------
            */

            $payload = Crypt::encrypt($payloadData);


            /*
            |--------------------------------------------------------------------------
            | STEP 12: RESPONSE
            |--------------------------------------------------------------------------
            */
           return response()->json([
            'status' => !is_null($product),
            'message' => $product
                ? 'Product found.'
                : 'Product not found. Please add product first.',
            'product' => $product,
            'payload' => $payload,
            'adjustmentType' => $adjustmentType,
            'returnRoute' => $returnRoute
        ]);
    }

    public function validateBarcodeRequisitionId_working(Request $request)
    { 
        // ✅ Step 1: Validate request

        $validator = Validator::make($request->all(), [
            'barcode' => ['required', 'string'],
            'routeName' => ['required', 'string'],
            'requisition_item_id' => ['required', 'integer'],
            'purchase_item_tracking_barcode' => ['required', 'string'],
            'returnRoute' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'validation' => true,
                'redirect' => $request->input('returnRoute'),
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        $barcode = trim($validated['barcode']);
        $routeName = $validated['routeName'];
        $requisition_item_id = $validated['requisition_item_id'];
        $purchase_item_tracking_barcode = $validated['purchase_item_tracking_barcode'];
        $returnRoute = $validated['returnRoute'];

        // $requisition_item_id = Settings::getDecodeCode($requisition_item_id);
        $purchase_item_tracking_barcode = Settings::getDecodeCode($purchase_item_tracking_barcode);


        // ✅ Step 2: Get adjustment type
        $adjustmentType = Settings::getAdjustmentIdFromRoute($routeName);
        $adjustmentData = Settings::getEncodeCode($adjustmentType);      

        // Clean barcode received from scanner
        $barcode = trim($barcode);

        // Step 3: Allow only UPC-A (12 digits) or EAN-13 (13 digits)
        if (!Settings::isValidBarcode($barcode)) {
            return response()->json([
                'status' => false,
                'message' => 'Barcode must be a valid 12 or 13 digit barcode.',
                'adjustmentType' => $adjustmentType,
                'returnRoute' => $returnRoute
            ]);
        }
        
        // Step 4: Validate checksum
        if (strlen($barcode) === 12) {

            // UPC-A validation
            if (!Settings::isValidBarcode($barcode)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid UPC-A barcode checksum.',
                    'adjustmentType' => $adjustmentType,
                    'returnRoute' => $returnRoute
                ]);
            }

        } elseif (strlen($barcode) === 13) {

            // EAN-13 validation
            if (!Settings::isValidEAN13($barcode)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid EAN-13 barcode checksum. validateBarcodeRequisitionId',
                    'adjustmentType' => $adjustmentType,
                    'returnRoute' => $returnRoute
                ]);
            }
        }

        if ($purchase_item_tracking_barcode != $barcode) {
            return response()->json([
                'status' => false,
                'message' => 'Barcode is not matching with purchase item tracking barcode.',
                'adjustmentType' => $adjustmentType,
                'returnRoute' => $returnRoute
            ]);
        }

        // ✅ Step 5: Find product (optimized query)
        $product = Product::query()->where('barcode', $barcode)->select(['id', 'barcode', 'name'])->first();

        // ✅ Step 6: Prepare payload
        $payloadData = [
            'adjustment' => $adjustmentData,
            'adjustmentType' => $adjustmentType,
            'barcode' => $barcode,
            'product_id' => $product?->id,
            'requisition_item_id' => $requisition_item_id,
            'returnRoute' => $returnRoute
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
            'returnRoute' => $returnRoute
        ]);
    }
    /**
     * Validate barcode and return product info
     */
    public function validateBarcode(Request $request)
    {
        $requisitionData = [];
        // ✅ Step 1: Validate request
        $validated = $request->validate([
            'barcode' => ['required', 'string'],
            'routeName' => ['required', 'string'],
        ]);
        $requisition_item_id = '';
        if ($request->has('requisition_item_id')) {
            $requisition_item_id = $request->requisition_item_id;
        }
        $barcode = trim($validated['barcode']);
        $routeName = $validated['routeName'];

        // ✅ Step 2: Get adjustment type
        $adjustmentType = Settings::getAdjustmentIdFromRoute($routeName);
        $adjustmentData = Settings::getEncodeCode($adjustmentType);
        // Clean barcode received from scanner
        $barcode = trim($barcode);

        // Step 3: Allow only UPC-A (12 digits) or EAN-13 (13 digits)
        if (!Settings::isValidBarcode($barcode)) {
            return response()->json([
                'status' => false,
                'message' => 'Barcode must be a valid 12 or 13 digit barcode.',
                'adjustmentType' => $adjustmentType,
            ]);
        }

        // Step 4: Validate checksum
        if (strlen($barcode) === 12) {

            // UPC-A validation
            if (!Settings::isValidBarcode($barcode)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid UPC-A barcode checksum.',
                    'adjustmentType' => $adjustmentType,
                ]);
            }

        } elseif (strlen($barcode) === 13) {
            // EAN-13 validation
            if (!Settings::isValidEAN13($barcode)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid EAN-13 barcode checksum. validateBarcode',
                    'adjustmentType' => $adjustmentType,
                ]);
            }
        } 
 
        // ✅ Step 5: Find product (optimized query) 
        $product = Product::query()->where('barcode', $barcode)->select(['id', 'barcode', 'name','master_item_id'])->first();
        // $this->pr($product);
        if($product && $product->master_item_id){
                $requisitionData = DB::table('purchase_item_trackings as pit')
                    ->join('purchase_items as pi', 'pit.purchase_item_id', '=', 'pi.id')
                    ->where('pi.master_item_id', $product->master_item_id)
                    ->where('pit.is_reserved', 1)
                    ->where('pit.barcode', $barcode)
                    ->where('pit.is_sold', 0)
                    ->select('pit.requisition_id', 'pit.requisition_item_id','pit.barcode')
                    ->get(); 
                //$this->pr($requisitionData);
                if(!empty($requisitionData)){
                    $requisition_item_id = Settings::getEncodeCode($requisitionData->first()->requisition_item_id);
                }
        }
        
        // ✅ Step 6: Prepare payload
        $payloadData = [
            'adjustment' => $adjustmentData,
            'adjustmentType' => $adjustmentType,
            'barcode' => $barcode,
            'product_id' => $product?->id,
            'requisition_item_id' => $requisition_item_id
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

    public function validatePurchaseBarcode(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), ['barcode' => 'required|string','routeName' => 'required|string',]);

            if ($validator->fails()) {
                return response()->json(['status' => false,'message' => $validator->errors()->first(),], 422);
            }

            $barcode = trim($request->barcode);
            // Clean barcode received from scanner
            $barcode = trim($barcode);

            // Step 3: Allow only UPC-A (12 digits) or EAN-13 (13 digits)
            if (!Settings::isValidBarcode($barcode)) {
                return response()->json(['status' => false,'message' => 'Barcode must be a valid 12 or 13 digit barcode.',]);
            }

            // Step 4: Validate checksum
            if (strlen($barcode) === 12) {

                // UPC-A validation
                if (!Settings::isValidBarcode($barcode)) {
                    return response()->json(['status' => false,'message' => 'Invalid UPC-A barcode checksum.',]);
                }

            } elseif (strlen($barcode) === 13) {

                // EAN-13 validation
                if (!Settings::isValidEAN13($barcode)) {
                    return response()->json(['status' => false,'message' => 'Invalid EAN-13 barcode checksum.',]);
                }
            }

            // SUCCESS
            return response()->json(['status' => true,'message' => 'Barcode is valid.','barcode' => $barcode]);

        } catch (\Throwable $e) {

            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
}