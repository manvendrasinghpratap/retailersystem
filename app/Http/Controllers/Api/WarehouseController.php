<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\Models\ProductStock;
use App\Models\MasterItem;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Helpers\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use PDF;

class WarehouseController extends Controller
{
    /**
     * Warehouse List
     */
    public function index(Request $request)
    {
        try {
            $query = Warehouse::ofAccount();

            /*
            |--------------------------------------------------------------------------
            | Name Filter
            |--------------------------------------------------------------------------
            */
            if ($request->filled('name')) {
                $query->where(
                    'name',
                    'like',
                    '%' . trim($request->name) . '%'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Status Filter
            |--------------------------------------------------------------------------
            */
            if ($request->has('status') && $request->status !== '') {
                $query->where('status', $request->status);
            } else {
                // Same default behavior as web controller
                $query->where('status', 1);
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

            $perPage = min(max($perPage, 1), 100);

            $warehouses = $query
                ->latest()
                ->paginate($perPage)
                ->withQueryString();

            return response()->json([
                'success' => true,
                'message' => 'Warehouses fetched successfully.',
                'data' => [
                    'warehouses' => $warehouses->items(),

                    'pagination' => [
                        'current_page' => $warehouses->currentPage(),
                        'last_page' => $warehouses->lastPage(),
                        'per_page' => $warehouses->perPage(),
                        'total' => $warehouses->total(),
                        'from' => $warehouses->firstItem(),
                        'to' => $warehouses->lastItem(),
                    ],
                ],
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create Warehouse - API
     *
     * Returns active staff for warehouse manager selection.
     */
    public function create()
    {
        try {
            $staffs = User::activeByAccountAndStaff()
                ->select('id', 'name')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Warehouse form data fetched successfully.',
                'data' => [
                    'staffs' => $staffs,
                ],
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store Warehouse
     */
    public function store(Request $request)
    {
        try {

            $request->validate([
                'name' => 'required|string|max:150',
                'staff_id' => 'nullable|integer|exists:users,id',
                'phone' => 'nullable|max:30',
                'email' => 'nullable|email|max:150',
                'address' => 'nullable|string',
                'status' => 'nullable|in:0,1',
            ]);

            $accountId = auth()->user()->account_id;

            /*
            |--------------------------------------------------------------------------
            | Validate Staff Belongs To Account
            |--------------------------------------------------------------------------
            */
            $staff = null;

            if ($request->filled('staff_id')) {

                $staff = User::where('id', $request->staff_id)
                    ->where('account_id', $accountId)
                    ->first();

                if (!$staff) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid staff selected.',
                    ], 422);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Generate Unique Warehouse Code
            |--------------------------------------------------------------------------
            */
            do {
                $warehouseCode = 'WH' . rand(10000, 99999);
            } while (
                Warehouse::where('warehouse_code', $warehouseCode)->exists()
            );

            /*
            |--------------------------------------------------------------------------
            | Create Warehouse
            |--------------------------------------------------------------------------
            */
            $warehouse = Warehouse::create([
                'account_id' => $accountId,
                'warehouse_code' => $warehouseCode,
                'name' => $request->name,
                'staff_id' => $request->staff_id,
                'manager_name' => $staff?->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'address' => $request->address,
                'status' => $request->status ?? 1,
                'created_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Warehouse created successfully.',
                'data' => [
                    'warehouse' => $warehouse,
                ],
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {

            throw $e;

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Edit Warehouse
     */
    public function edit($id)
    {
        try {

            $warehouse = Warehouse::ofAccount()
                ->findOrFail($id);

            $staffs = User::activeByAccountAndStaff()
                ->select('id', 'name')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Warehouse fetched successfully.',
                'data' => [
                    'warehouse' => $warehouse,
                    'staffs' => $staffs,
                ],
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Warehouse not found.',
            ], 404);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update Warehouse
     */
    public function update(Request $request, $id)
    {
        try {

            $warehouse = Warehouse::ofAccount()
                ->findOrFail($id);

            $request->validate([
                'name' => 'required|string|max:150',
                'staff_id' => 'nullable|integer|exists:users,id',
                'phone' => 'nullable|max:30',
                'email' => 'nullable|email|max:150',
                'address' => 'nullable|string',
                'status' => 'nullable|in:0,1',
            ]);

            $accountId = auth()->user()->account_id;

            /*
            |--------------------------------------------------------------------------
            | Validate Staff
            |--------------------------------------------------------------------------
            */
            $staff = null;

            if ($request->filled('staff_id')) {

                $staff = User::where('id', $request->staff_id)
                    ->where('account_id', $accountId)
                    ->first();

                if (!$staff) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid staff selected.',
                    ], 422);
                }
            }

            $warehouse->update([
                'name' => $request->name,
                'staff_id' => $request->staff_id,
                'manager_name' => $staff?->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'address' => $request->address,
                'status' => $request->status ?? $warehouse->status,
                'updated_by' => auth()->id(),
            ]);

            $warehouse->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Warehouse updated successfully.',
                'data' => [
                    'warehouse' => $warehouse,
                ],
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Warehouse not found.',
            ], 404);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete Warehouse
     */
    public function softdelete(Request $request, $id)
    {
        try {

            $warehouse = Warehouse::ofAccount()
                ->findOrFail($id);

            $warehouse->delete();

            return response()->json([
                'success' => true,
                'message' => 'Warehouse deleted successfully.',
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Warehouse not found.',
            ], 404);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update Warehouse Status
     */
    public function statusUpdate(Request $request)
    {
        try {

            $request->validate([
                'warehouse_id' => 'required|integer',
                'status' => 'required|in:0,1',
            ]);

            $warehouse = Warehouse::ofAccount()
                ->findOrFail($request->warehouse_id);

            $warehouse->update([
                'status' => $request->status,
                'updated_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Warehouse status updated successfully.',
                'data' => [
                    'warehouse_id' => $warehouse->id,
                    'status' => $warehouse->status,
                ],
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {

            throw $e;

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Warehouse not found.',
            ], 404);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Warehouse Products
     */
    public function getWarehouseProducts(Request $request, $id)
    {
        try {

            $warehouse = Warehouse::ofAccount()
                ->findOrFail($id);

            $warehouseId = $warehouse->id;

            $query = MasterItem::ofAccount()
                ->whereHas('stocks', function ($q) use ($warehouseId) {
                    $q->where('warehouse_id', $warehouseId);
                })
                ->with([
                    'stocks' => function ($q) use ($warehouseId) {
                        $q->where('warehouse_id', $warehouseId);
                    }
                ]);

            /*
            |--------------------------------------------------------------------------
            | Product Name Filter
            |--------------------------------------------------------------------------
            */
            if ($request->filled('item_name')) {

                $query->where(
                    'name',
                    'LIKE',
                    '%' . trim($request->item_name) . '%'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | In Stock
            |--------------------------------------------------------------------------
            */
            if ($request->stock_filter === 'in_stock') {

                $query->whereHas('stocks', function ($q) use ($warehouseId) {
                    $q->where('warehouse_id', $warehouseId)
                        ->where('stock', '>', 0);
                });
            }

            /*
            |--------------------------------------------------------------------------
            | Low Stock
            |--------------------------------------------------------------------------
            */
            if ($request->stock_filter === 'low_stock') {

                $query->whereHas('stocks', function ($q) use ($warehouseId) {
                    $q->where('warehouse_id', $warehouseId)
                        ->whereColumn('stock', '<=', 'low_stock_alert');
                });
            }

            $perPage = (int) $request->get(
                'per_page',
                account_setting('general.pagination')
            );

            $perPage = min(max($perPage, 1), 100);

            $items = $query
                ->orderBy('name')
                ->paginate($perPage)
                ->withQueryString();

            /*
            |--------------------------------------------------------------------------
            | Add Available Stock
            |--------------------------------------------------------------------------
            */
            $items->getCollection()->transform(function ($item) {

                $stock = $item->stocks->first();

                $item->available_stock = $stock
                    ? (float) $stock->stock
                    : 0;

                return $item;
            });

            return response()->json([
                'success' => true,
                'message' => 'Warehouse products fetched successfully.',
                'data' => [
                    'warehouse' => $warehouse,
                    'items' => $items->items(),

                    'pagination' => [
                        'current_page' => $items->currentPage(),
                        'last_page' => $items->lastPage(),
                        'per_page' => $items->perPage(),
                        'total' => $items->total(),
                        'from' => $items->firstItem(),
                        'to' => $items->lastItem(),
                    ],
                ],
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Warehouse not found.',
            ], 404);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Product Stock
     */
    public function getProductStock(Request $request)
    {
        try {

            $request->validate([
                'warehouse_id' => 'required|integer',
                'product_id' => 'required|integer',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Verify Warehouse Belongs To Account
            |--------------------------------------------------------------------------
            */
            Warehouse::ofAccount()
                ->findOrFail($request->warehouse_id);

            $stock = ProductStock::ofAccount()
                ->where('warehouse_id', $request->warehouse_id)
                ->where('product_id', $request->product_id)
                ->first();

            return response()->json([
                'success' => true,
                'message' => 'Product stock fetched successfully.',
                'data' => [
                    'warehouse_id' => (int) $request->warehouse_id,
                    'product_id' => (int) $request->product_id,
                    'stock' => $stock ? (float) $stock->stock : 0,
                ],
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Warehouse not found.',
            ], 404);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Warehouse Stock Listing
     */
    public function stockListing(Request $request)
    {
        try {

            $query = ProductStock::with([
                'warehouse',
                'masterItem'
            ])
                ->whereHas('warehouse', function ($q) {
                    $q->ofAccount();
                });

            /*
            |--------------------------------------------------------------------------
            | Product Name
            |--------------------------------------------------------------------------
            */
            if ($request->filled('product_name')) {

                $query->whereHas('masterItem', function ($q) use ($request) {

                    $q->where(
                        'name',
                        'LIKE',
                        '%' . trim($request->product_name) . '%'
                    );
                });
            }

            /*
            |--------------------------------------------------------------------------
            | Warehouse
            |--------------------------------------------------------------------------
            */
            if ($request->filled('warehouse_id')) {

                $query->where('warehouse_id', $request->warehouse_id);
            }

            /*
            |--------------------------------------------------------------------------
            | In Stock
            |--------------------------------------------------------------------------
            */
            if ($request->stock_filter === 'in_stock') {

                $query->where('stock', '>', 0);
            }

            /*
            |--------------------------------------------------------------------------
            | Low Stock
            |--------------------------------------------------------------------------
            */
            if ($request->stock_filter === 'low_stock') {

                $query->whereColumn(
                    'stock',
                    '<=',
                    'low_stock_alert'
                );
            }

            $perPage = (int) $request->get(
                'per_page',
                account_setting('general.pagination')
            );

            $perPage = min(max($perPage, 1), 100);

            $stocks = $query
                ->latest()
                ->paginate($perPage)
                ->withQueryString();

            /*
            |--------------------------------------------------------------------------
            | Summary
            |--------------------------------------------------------------------------
            */
            $totalStock = (float) $query->sum('stock');

            return response()->json([
                'success' => true,
                'message' => 'Warehouse stock fetched successfully.',
                'data' => [
                    'stocks' => $stocks->items(),

                    'summary' => [
                        'total_stock' => $totalStock,
                    ],

                    'pagination' => [
                        'current_page' => $stocks->currentPage(),
                        'last_page' => $stocks->lastPage(),
                        'per_page' => $stocks->perPage(),
                        'total' => $stocks->total(),
                        'from' => $stocks->firstItem(),
                        'to' => $stocks->lastItem(),
                    ],
                ],
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Active Warehouses
     *
     * Useful for dropdowns in API.
     */
    public function dropdown()
    {
        try {

            $warehouses = Warehouse::ofAccount()
                ->where('status', 1)
                ->select([
                    'id',
                    'warehouse_code',
                    'name'
                ])
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Warehouses fetched successfully.',
                'data' => [
                    'warehouses' => $warehouses,
                ],
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store Stock Transfer
     */
    public function transferStore(Request $request)
    {
        try {

            $request->validate([
                'from_warehouse_id' => 'required|integer|different:to_warehouse_id',
                'to_warehouse_id' => 'required|integer',
                'products' => 'required|array|min:1',
                'products.*.product_id' => 'required|integer',
                'products.*.qty' => 'required|numeric|min:0.01',
                'remarks' => 'nullable|string',
            ]);

            $accountId = auth()->user()->account_id;

            /*
            |--------------------------------------------------------------------------
            | Validate Warehouses
            |--------------------------------------------------------------------------
            */
            $fromWarehouse = Warehouse::ofAccount()
                ->where('status', 1)
                ->findOrFail($request->from_warehouse_id);

            $toWarehouse = Warehouse::ofAccount()
                ->where('status', 1)
                ->findOrFail($request->to_warehouse_id);

            $transfer = DB::transaction(function () use (
                $request,
                $accountId,
                $fromWarehouse,
                $toWarehouse
            ) {

                /*
                |--------------------------------------------------------------------------
                | Create Transfer
                |--------------------------------------------------------------------------
                */
                $transfer = StockTransfer::create([
                    'account_id' => $accountId,
                    'from_warehouse_id' => $fromWarehouse->id,
                    'to_warehouse_id' => $toWarehouse->id,
                    'date' => date('Y-m-d'),
                    'status' => 1,
                    'remarks' => $request->remarks,
                    'created_by' => auth()->id(),
                ]);

                foreach ($request->products as $item) {

                    /*
                    |--------------------------------------------------------------------------
                    | Lock Source Stock
                    |--------------------------------------------------------------------------
                    */
                    $sourceStock = ProductStock::ofAccount()
                        ->where('warehouse_id', $fromWarehouse->id)
                        ->where('product_id', $item['product_id'])
                        ->lockForUpdate()
                        ->first();

                    if (!$sourceStock) {
                        throw new \Exception(
                            "Product stock not found for product ID {$item['product_id']}."
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Check Available Stock
                    |--------------------------------------------------------------------------
                    */
                    if ((float) $sourceStock->stock < (float) $item['qty']) {

                        throw new \Exception(
                            "Insufficient stock for product ID {$item['product_id']}. Available: {$sourceStock->stock}, Requested: {$item['qty']}."
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Deduct Source
                    |--------------------------------------------------------------------------
                    */
                    $sourceStock->decrement(
                        'stock',
                        $item['qty']
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Add Destination
                    |--------------------------------------------------------------------------
                    */
                    $destinationStock = ProductStock::ofAccount()
                        ->where('warehouse_id', $toWarehouse->id)
                        ->where('product_id', $item['product_id'])
                        ->lockForUpdate()
                        ->first();

                    if ($destinationStock) {

                        $destinationStock->increment(
                            'stock',
                            $item['qty']
                        );

                    } else {

                        ProductStock::create([
                            'account_id' => $accountId,
                            'warehouse_id' => $toWarehouse->id,
                            'product_id' => $item['product_id'],
                            'stock' => $item['qty'],
                        ]);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Transfer Item
                    |--------------------------------------------------------------------------
                    */
                    StockTransferItem::create([
                        'transfer_id' => $transfer->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['qty'],
                    ]);
                }

                return $transfer;
            });

            $transfer->load([
                'items'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Stock transferred successfully.',
                'data' => [
                    'transfer' => $transfer,
                ],
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {

            throw $e;

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Invalid warehouse selected.',
            ], 404);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}