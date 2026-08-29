<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\StockAdjustment;
use App\Models\PurchaseItem;
use App\Models\MasterItem;
use App\Models\RequisitionItem;
use App\Models\Requisition;
use App\Helpers\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    /**
     * Product List
     *
     * GET /api/products
     */
    public function index(Request $request)
    {
        try {

            $accountId = auth()->user()->account_id;

            /*
            |--------------------------------------------------------------------------
            | Base Product Query
            |--------------------------------------------------------------------------
            |
            | Product will be shown only when at least one eligible
            | purchase tracking record exists:
            |
            | is_sold = 0
            | is_reserved = 1
            | returned_quantity = 0
            | status = 1
            |
            */

            $products = Product::query()
                ->with('category')
                ->where('account_id', $accountId)
                ->where('is_deleted', 0)
                ->whereExists(function ($query) {

                    $query->select(DB::raw(1))
                        ->from('purchase_item_trackings')
                        ->whereColumn(
                            'purchase_item_trackings.barcode',
                            'products.barcode'
                        )
                        ->where(
                            'purchase_item_trackings.is_sold',
                            0
                        )
                        ->where(
                            'purchase_item_trackings.is_reserved',
                            1
                        )
                        ->where(
                            'purchase_item_trackings.returned_quantity',
                            0
                        )
                        ->where(
                            'purchase_item_trackings.status',
                            1
                        );
                });

            /*
            |--------------------------------------------------------------------------
            | Product Name Filter
            |--------------------------------------------------------------------------
            */

            if ($request->filled('name')) {

                $products->where(
                    'products.name',
                    'LIKE',
                    '%' . trim($request->name) . '%'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Category Filter
            |--------------------------------------------------------------------------
            */

            if ($request->filled('category_id')) {

                $products->where(
                    'products.category_id',
                    $request->category_id
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Status Filter
            |--------------------------------------------------------------------------
            */

            if ($request->filled('is_active')) {

                $products->where(
                    'products.status',
                    $request->is_active
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Barcode Filter
            |--------------------------------------------------------------------------
            */

            if ($request->filled('barcode')) {

                $products->where(
                    'products.barcode',
                    $request->barcode
                );
            }

            /*
            |--------------------------------------------------------------------------
            | SKU Filter
            |--------------------------------------------------------------------------
            */

            if ($request->filled('sku')) {

                $products->where(
                    'products.sku',
                    'LIKE',
                    '%' . trim($request->sku) . '%'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Pagination
            |--------------------------------------------------------------------------
            */

            $perPage = (int) $request->get(
                'per_page',
                account_setting('general.pagination')
            );

            $perPage = min(
                max($perPage, 1),
                100
            );

            $products = $products
                ->latest('products.id')
                ->paginate($perPage)
                ->withQueryString();

            /*
            |--------------------------------------------------------------------------
            | Response
            |--------------------------------------------------------------------------
            */

            return response()->json([

                'success' => true,

                'message' => 'Products fetched successfully.',

                'data' => [

                    'products' => $products->items(),

                    'pagination' => [
                        'current_page' => $products->currentPage(),
                        'last_page' => $products->lastPage(),
                        'per_page' => $products->perPage(),
                        'total' => $products->total(),
                        'from' => $products->firstItem(),
                        'to' => $products->lastItem(),
                    ],

                ],

            ]);

        } catch (\Exception $e) {

            return response()->json([

                'success' => false,

                'message' => 'Unable to fetch products.',

                'error' => $e->getMessage(),

            ], 500);
        }
    }


    /**
     * Show Product
     *
     * GET /api/products/{id}
     */
    public function show($id)
    {
        try {

            $product = Product::with('category')
                ->where('account_id', auth()->user()->account_id)
                ->where('is_deleted', 0)
                ->find($id);

            if (!$product) {

                return response()->json([
                    'success' => false,
                    'message' => 'Product not found.',
                ], 404);
            }

            return response()->json([

                'success' => true,

                'message' => 'Product fetched successfully.',

                'data' => [
                    'product' => $product,
                ],

            ]);

        } catch (\Exception $e) {

            return response()->json([

                'success' => false,
                'message' => 'Unable to fetch product.',
                'error' => $e->getMessage(),

            ], 500);
        }
    }


    /**
     * Create Product Data
     *
     * Replaces the old Blade create() method.
     *
     * GET /api/products/create
     */
    public function create(Request $request)
    {
        try {

            $data = [

                'categories' => Category::ofAccount()
                    ->notDeleted()
                    ->pluck('name', 'id'),

                'barcode' => null,

                'product_id' => null,

                'route' => null,

                'adjustment' => null,

                'requisition_item_id' => null,

                'master_item_name' => null,

                'description' => null,

                'quantity' => null,

                'category_id' => null,

                'cost_price' => 0,
            ];

            /*
            |--------------------------------------------------------------------------
            | Token Processing
            |--------------------------------------------------------------------------
            */

            if ($request->filled('token')) {

                try {

                    $decoded = Crypt::decrypt($request->token);

                } catch (\Exception $e) {

                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid token.',
                    ], 422);
                }

                $adjustmentData = Settings::getInventoryAdjustment(
                    $decoded['adjustment'] ?? null
                );

                if (empty($adjustmentData['adjustment'])) {

                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid inventory adjustment.',
                    ], 422);
                }

                $data['route'] =
                    $adjustmentData['route'] ?? null;

                $data['adjustment'] =
                    $adjustmentData['adjustment'] ?? null;

                $data['barcode'] =
                    $decoded['barcode'] ?? null;

                $data['product_id'] =
                    $decoded['product_id'] ?? null;

                $data['requisition_item_id'] =
                    $decoded['requisition_item_id'] ?? null;

                /*
                |--------------------------------------------------------------------------
                | Requisition Item
                |--------------------------------------------------------------------------
                */

                if (!empty($decoded['requisition_item_id'])) {

                    $requisitionItemId =
                        Settings::getDecodeCode(
                            $decoded['requisition_item_id']
                        );

                    $requisitionItem =
                        RequisitionItem::with([
                            'masterItem',
                            'purchaseItemTracking.purchaseItem'
                        ])
                        ->find($requisitionItemId);

                    if ($requisitionItem) {

                        if (
                            $response =
                            $this->checkRequisitionStatus(
                                $requisitionItem
                            )
                        ) {
                            return $response;
                        }

                        $data['master_item_name'] =
                            $requisitionItem->masterItem->name ?? null;

                        $data['description'] =
                            $requisitionItem->masterItem->description ?? null;

                        $data['quantity'] =
                            $requisitionItem->qty;

                        $data['category_id'] =
                            $requisitionItem->masterItem->category_id ?? null;

                        $data['cost_price'] =
                            $requisitionItem
                                ->purchaseItemTracking
                                ?->purchaseItem
                                ?->cost_price ?? 0;
                    }
                }
            }

            return response()->json([

                'success' => true,

                'message' => 'Product creation data fetched successfully.',

                'data' => $data,

            ]);

        } catch (\Exception $e) {

            return response()->json([

                'success' => false,

                'message' => 'Unable to load product creation data.',

                'error' => $e->getMessage(),

            ], 500);
        }
    }


    /**
     * Store Product
     *
     * POST /api/products
     */
    public function store(Request $request)
    {
        try {

            $accountId = auth()->user()->account_id;

            /*
            |--------------------------------------------------------------------------
            | Basic Validation
            |--------------------------------------------------------------------------
            */

            $request->validate([

                'name' => 'required|string|max:255',

                'selling_price' =>
                    'required|numeric|min:0',

                'status' =>
                    'nullable|in:0,1',

                'category_id' =>
                    'nullable|exists:categories,id',

                'description' =>
                    'nullable|string',

                'barcode' =>
                    'nullable|string|max:255',

                'sku' =>
                    'nullable|string|max:255',

                'requisition_item_id' =>
                    'required',

                'route' =>
                    'nullable|string',

                'quantity' =>
                    'nullable|numeric|min:0',

            ]);

            /*
            |--------------------------------------------------------------------------
            | Barcode / SKU
            |--------------------------------------------------------------------------
            */

            $prefix = strtoupper(
                substr($request->name, 0, 3)
            );

            $barcode = $request->filled('barcode')
                ? $request->barcode
                : Settings::generateEan13();

            $sku = $request->filled('sku')
                ? $request->sku
                : $prefix . '-' . time() . rand(100, 999);


            $product = DB::transaction(function () use (
                $request,
                $accountId,
                $barcode,
                $sku
            ) {

                /*
                |--------------------------------------------------------------------------
                | Requisition Item
                |--------------------------------------------------------------------------
                */

                $requisitionItemId =
                    Settings::getDecodeCode(
                        $request->requisition_item_id
                    );

                $requisitionItem =
                    RequisitionItem::with([
                        'masterItem',
                        'requisition'
                    ])
                    ->lockForUpdate()
                    ->find($requisitionItemId);

                if (!$requisitionItem) {

                    throw new \Exception(
                        'Invalid requisition item.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Requisition Status
                |--------------------------------------------------------------------------
                */

                if ((int) $requisitionItem->status === 3) {

                    throw new \Exception(
                        __('translation.requisition_already_completed')
                    );
                }

                if (
                    $requisitionItem->requisition &&
                    (int) $requisitionItem->requisition->status === 0
                ) {

                    throw new \Exception(
                        __('translation.requisition_rejected')
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Duplicate Acceptance Check
                |--------------------------------------------------------------------------
                */

                if (!empty($requisitionItem->accepted_by)) {

                    throw new \Exception(
                        'This requisition item is already accepted by another user.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Master Item
                |--------------------------------------------------------------------------
                */

                $masterItemId =
                    $requisitionItem->master_item_id;

                /*
                |--------------------------------------------------------------------------
                | Product Data
                |--------------------------------------------------------------------------
                */

                $productData = [

                    'account_id' => $accountId,

                    'master_item_id' => $masterItemId,

                    'name' => $request->name,

                    'barcode' => $barcode,

                    'sku' => $sku,

                    'selling_price' =>
                        $request->selling_price,

                    /*
                     * Existing application logic sets cost price
                     * equal to selling price during creation.
                     */
                    'cost_price' =>
                        $request->selling_price,

                    'category_id' =>
                        $request->category_id,

                    'description' =>
                        $request->description,

                    'status' =>
                        $request->status ?? 1,

                    'created_by' =>
                        auth()->id(),
                ];

                /*
                |--------------------------------------------------------------------------
                | Create Product
                |--------------------------------------------------------------------------
                */

                $product = Product::create(
                    $productData
                );

                /*
                |--------------------------------------------------------------------------
                | Generate SKU
                |--------------------------------------------------------------------------
                */

                $product->update([

                    'sku' => strtoupper(
                        substr(
                            $product->category->name ?? 'PRD',
                            0,
                            3
                        )
                    ) . '-' . $product->id,

                ]);

                /*
                |--------------------------------------------------------------------------
                | Update Requisition Item
                |--------------------------------------------------------------------------
                */

                $requisitionItem->update([

                    'accepted_by' =>
                        auth()->id(),

                ]);

                /*
                |--------------------------------------------------------------------------
                | Update Requisition Status
                |--------------------------------------------------------------------------
                */

                if ($requisitionItem->requisition) {

                    $requisition =
                        Requisition::find(
                            $requisitionItem->requisition_id
                        );

                    if ($requisition) {

                        $requisition->updateStatusByItems();
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Initial Stock
                |--------------------------------------------------------------------------
                */

                if ($request->route === 'Add') {

                    StockAdjustment::create([

                        'product_id' =>
                            $product->id,

                        'type' =>
                            'add',

                        'quantity' =>
                            $request->quantity ?? 0,

                        'note' =>
                            'Initial stock added',

                    ]);
                }

                return $product->fresh([
                    'category'
                ]);
            });

            return response()->json([

                'success' => true,

                'message' =>
                    'Product added successfully.',

                'data' => [
                    'product' => $product,
                ],

            ], 201);

        } catch (ValidationException $e) {

            return response()->json([

                'success' => false,

                'message' => 'Validation failed.',

                'errors' => $e->errors(),

            ], 422);

        } catch (\Exception $e) {

            return response()->json([

                'success' => false,

                'message' => $e->getMessage(),

            ], 422);
        }
    }


    /**
     * Edit Product
     *
     * GET /api/products/{id}/edit
     */
    public function edit($id)
    {
        $id = Settings::getDecodeCode($id);

        try {

            $product = Product::where(
                'account_id',
                auth()->user()->account_id
            )
            ->where('is_deleted', 0)
            ->with('category')
            ->find($id);

            if (!$product) {

                return response()->json([
                    'success' => false,
                    'message' => 'Product not found.',
                ], 404);
            }

            $categories = Category::ofAccount()
                ->notDeleted()
                ->pluck('name', 'id');

            return response()->json([

                'success' => true,

                'message' =>
                    'Product edit data fetched successfully.',

                'data' => [

                    'product' => $product,

                    'categories' => $categories,

                ],

            ]);

        } catch (\Exception $e) {

            return response()->json([

                'success' => false,

                'message' => 'Unable to fetch product.',

                'error' => $e->getMessage(),

            ], 500);
        }
    }


    /**
     * Update Product
     *
     * PUT /api/products/{id}
     */
    public function update(Request $request, $id)
    {
       $id = Settings::getDecodeCode($id);
        try {

            $product = Product::where(
                'account_id',
                auth()->user()->account_id
            )
            ->where('is_deleted', 0)
            ->find($id);

            if (!$product) {

                return response()->json([

                    'success' => false,

                    'message' => 'Product not found.',

                ], 404);
            }

            $request->validate([

                'name' =>
                    'required|string|max:255',

                'selling_price' =>
                    'required|numeric|min:0',

                'status' =>
                    'nullable|in:0,1',

                'description' =>
                    'nullable|string',

                'category_id' =>
                    'nullable|exists:categories,id',

            ]);

            $product->update([

                // 'name' => $request->name,
                'selling_price' => $request->selling_price,
                'status' => $request->status ?? $product->status,
                'description' => $request->description,
                'category_id' => $request->category_id,
                'updated_by' => auth()->id(),
            ]);

            return response()->json([

                'success' => true,

                'message' =>
                    'Product updated successfully.',

                'data' => [

                    'product' =>
                        $product->fresh('category'),

                ],

            ]);

        } catch (ValidationException $e) {

            return response()->json([

                'success' => false,

                'message' => 'Validation failed.',

                'errors' => $e->errors(),

            ], 422);

        } catch (\Exception $e) {

            return response()->json([

                'success' => false,

                'message' =>
                    'Unable to update product.',

                'error' =>
                    $e->getMessage(),

            ], 500);
        }
    }


    /**
     * Hard Delete Product
     *
     * DELETE /api/products/{id}
     *
     * Kept because it exists in your current controller.
     */
    public function destroy($id)
    {
        try {

            $product = Product::where(
                'account_id',
                auth()->user()->account_id
            )
            ->find($id);

            if (!$product) {

                return response()->json([

                    'success' => false,

                    'message' => 'Product not found.',

                ], 404);
            }

            $product->delete();

            return response()->json([

                'success' => true,

                'message' =>
                    'Product deleted successfully.',

            ]);

        } catch (\Exception $e) {

            return response()->json([

                'success' => false,

                'message' =>
                    'Unable to delete product.',

                'error' =>
                    $e->getMessage(),

            ], 500);
        }
    }


    /**
     * Soft Delete Product
     *
     * PATCH /api/products/{id}/delete
     */
    public function softdelete($id)
    {
        try {
            $id = Settings::getDecodeCode($id);
            $product = Product::where(['account_id' => auth()->user()->account_id,'id' => $id,'is_deleted' => 0])->first();
            if (!$product) {
                return response()->json(['success' => false,'message' => 'Product not found.',], 404);
            }
            $product->update(['is_deleted' => 1,'deleted_by' => auth()->id(),'deleted_at' => now()]);
            return response()->json(['success' => true,'message' =>'Product deleted successfully.',]);
        } catch (\Exception $e) {
            return response()->json(['success' => false,'message' =>'Unable to delete product.', 'error' => $e->getMessage(),], 500);
        }
    }


    /**
     * Get Last Purchase Price
     *
     * GET /api/products/last-price
     */
    public function getLastPrice(Request $request)
    {
        try {

            $request->validate([

                'master_item_id' =>
                    'required|integer',

                'vendor_id' =>
                    'nullable|integer',

                'warehouse_id' =>
                    'nullable|integer',

            ]);

            $masterItemId =
                $request->master_item_id;

            $vendorId =
                $request->vendor_id;

            $warehouseId =
                $request->warehouse_id;

            $accountId =
                auth()->user()->account_id;

            /*
            |--------------------------------------------------------------------------
            | Base Query
            |--------------------------------------------------------------------------
            */

            $baseQuery = PurchaseItem::query()
                ->where(
                    'master_item_id',
                    $masterItemId
                )
                ->whereHas('purchase', function ($q) use ($accountId) {

                    $q->where(
                        'account_id',
                        $accountId
                    );
                });

            /*
            |--------------------------------------------------------------------------
            | 1. Same Item + Vendor + Warehouse
            |--------------------------------------------------------------------------
            */

            $last = (clone $baseQuery)
                ->whereHas('purchase', function ($q) use (
                    $vendorId,
                    $warehouseId
                ) {

                    $q->when(
                        $vendorId,
                        function ($sq) use ($vendorId) {

                            $sq->where(
                                'vendor_id',
                                $vendorId
                            );
                        }
                    );

                    $q->when(
                        $warehouseId,
                        function ($sq) use ($warehouseId) {

                            $sq->where(
                                'warehouse_id',
                                $warehouseId
                            );
                        }
                    );

                })
                ->latest('id')
                ->first();

            /*
            |--------------------------------------------------------------------------
            | 2. Same Item + Vendor
            |--------------------------------------------------------------------------
            */

            if (!$last && $vendorId) {

                $last = (clone $baseQuery)
                    ->whereHas(
                        'purchase',
                        function ($q) use ($vendorId) {

                            $q->where(
                                'vendor_id',
                                $vendorId
                            );
                        }
                    )
                    ->latest('id')
                    ->first();
            }

            /*
            |--------------------------------------------------------------------------
            | 3. Same Item + Warehouse
            |--------------------------------------------------------------------------
            */

            if (!$last && $warehouseId) {

                $last = (clone $baseQuery)
                    ->whereHas(
                        'purchase',
                        function ($q) use ($warehouseId) {

                            $q->where(
                                'warehouse_id',
                                $warehouseId
                            );
                        }
                    )
                    ->latest('id')
                    ->first();
            }

            /*
            |--------------------------------------------------------------------------
            | 4. Item Only
            |--------------------------------------------------------------------------
            */

            if (!$last) {

                $last = (clone $baseQuery)
                    ->latest('id')
                    ->first();
            }

            return response()->json([

                'success' => true,

                'message' =>
                    'Last purchase price fetched successfully.',

                'data' => [

                    'price' =>
                        $last?->cost_price ?? 0,

                ],

            ]);

        } catch (ValidationException $e) {

            return response()->json([

                'success' => false,

                'message' =>
                    'Validation failed.',

                'errors' =>
                    $e->errors(),

            ], 422);

        } catch (\Exception $e) {

            return response()->json([

                'success' => false,

                'message' =>
                    'Unable to fetch last purchase price.',

                'error' =>
                    $e->getMessage(),

            ], 500);
        }
    }


    /**
     * Search Master Items
     *
     * GET /api/products/search
     */
    public function search(Request $request)
    {
        try {

            $search =
                trim($request->q ?? '');

            $warehouseId =
                $request->warehouse_id;

            $items = MasterItem::query()
                ->ofAccount()
                ->where('is_deleted', 0)

                /*
                |--------------------------------------------------------------------------
                | Search
                |--------------------------------------------------------------------------
                */

                ->when(
                    $search,
                    function ($q) use ($search) {

                        $q->where(
                            'name',
                            'LIKE',
                            '%' . $search . '%'
                        );
                    }
                )

                /*
                |--------------------------------------------------------------------------
                | Warehouse Stock
                |--------------------------------------------------------------------------
                */

                ->when(
                    $warehouseId,
                    function ($q) use ($warehouseId) {

                        $q->whereHas(
                            'stocks',
                            function ($sq) use ($warehouseId) {

                                $sq->where(
                                    'warehouse_id',
                                    $warehouseId
                                )
                                ->where(
                                    'stock',
                                    '>',
                                    0
                                );
                            }
                        );
                    }
                )

                ->orderBy('name', 'asc')
                ->limit(20)
                ->get();

            return response()->json([

                'success' => true,

                'message' =>
                    'Items fetched successfully.',

                'data' => [

                    'items' => $items->map(
                        function ($item) {

                            return [

                                'id' =>
                                    $item->id,

                                'text' =>
                                    $item->name,

                            ];
                        }
                    )->values(),

                ],

            ]);

        } catch (\Exception $e) {

            return response()->json([

                'success' => false,

                'message' =>
                    'Unable to search items.',

                'error' =>
                    $e->getMessage(),

            ], 500);
        }
    }


    /**
     * Check Requisition Status
     */
    private function checkRequisitionStatus(
        RequisitionItem $requisitionItem
    ) {

        $requisition =
            $requisitionItem->requisition;

        if (
            (int) $requisitionItem->status === 3
        ) {

            return response()->json([

                'success' => false,

                'message' =>
                    __('translation.requisition_already_completed'),

            ], 422);
        }

        if (
            $requisition &&
            (int) $requisition->status === 0
        ) {

            return response()->json([

                'success' => false,

                'message' =>
                    __('translation.requisition_rejected'),

            ], 422);
        }

        return null;
    }
}