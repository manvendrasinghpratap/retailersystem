<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\Models\ProductStock;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Helpers\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WarehouseController extends Controller
{
    protected $breadcrumb;

    public function __construct()
    {
        $this->middleware('auth');

        $this->breadcrumb = [
            'title' => __('translation.warehouses'),
            'breadcrumb' => [
                [
                    'route' => 'admin.dashboard',
                    'title' => __('translation.dashboard')
                ],
                [
                    'route' => 'admin.warehouses.index',
                    'title' => __('translation.warehouses')
                ],
                [
                    'route' => 'admin.warehouses.create',
                    'title' => __('translation.add_new_warehouse')
                ]
            ],
            'route1' => 'admin.warehouses.create',
            'route1Title' => __('translation.add_warehouse'),
            'route2' => 'admin.warehouses.index',
            'route2Title' => __('translation.warehouse_list'),
            'reset_route' => 'admin.warehouses.index',
            'reset_route_title' => __('translation.cancel'),
            'route3Title' => __('translation.update_warehouse'),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Warehouse List
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $breadcrumb = $this->breadcrumb;
        $warehouses = Warehouse::ofAccount();
        if ($request->name) {
            $warehouses->where('name', 'like', '%' . trim($request->name) . '%');
        }
        if ($request->status !== '' && $request->status !== null) {
            $warehouses->where('status', $request->status);
        }
        $warehouses = $warehouses->latest()->paginate(config('constants.pagination'));
        return view('backend.admin.warehouses.index', compact('warehouses', 'breadcrumb'));
    }

    /*
    |--------------------------------------------------------------------------
    | Create Warehouse
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return view('backend.admin.warehouses.form', [
            'breadcrumb' => $this->breadcrumb
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Store Warehouse
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:150',
            'phone' => 'nullable|max:30',
            'email' => 'nullable|email|max:150',
        ]);

        Warehouse::create([
            'account_id' => auth()->user()->account_id,
            'warehouse_code' => 'WH' . rand(10000, 99999),
            'name' => $request->name,
            'manager_name' => $request->manager_name,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'status' => $request->status ?? 1,
            'created_by' => auth()->id(),
        ]);

        return Settings::roleRedirect('warehouses.index', 'Warehouse Created Successfully');
    }

    /*
    |--------------------------------------------------------------------------
    | Edit Warehouse
    |--------------------------------------------------------------------------
    */

    public function edit($id)
    {
        $id = Settings::getDecodeCode($id);

        $warehouse = Warehouse::ofAccount()
            ->findOrFail($id);

        return view('backend.admin.warehouses.form', [
            'warehouse' => $warehouse,
            'breadcrumb' => $this->breadcrumb
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Update Warehouse
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {
        $id = Settings::getDecodeCode($request->warehouse_id);

        $warehouse = Warehouse::where('account_id', auth()->user()->account_id)
            ->findOrFail($id);

        $request->validate([
            'name' => 'required|max:150'
        ]);

        $warehouse->update([
            'name' => $request->name,
            'manager_name' => $request->manager_name,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'status' => $request->status ?? 1,
            'updated_by' => auth()->id(),
        ]);

        return Settings::roleRedirect('warehouses.index', 'Warehouse Updated Successfully');
    }

    /*
    |--------------------------------------------------------------------------
    | Stock Transfer Form
    |--------------------------------------------------------------------------
    */

    public function transferForm()
    {
        $breadcrumb = $this->breadcrumb;
        $breadcrumb['title'] = __('translation.stock_transfer');

        $warehouses = Warehouse::active()
            ->where('account_id', auth()->user()->account_id)
            ->pluck('name', 'id');

        return view('backend.admin.warehouse.transfer', compact('breadcrumb', 'warehouses'));
    }

    /*
    |--------------------------------------------------------------------------
    | Store Stock Transfer
    |--------------------------------------------------------------------------
    */

    public function transferStore(Request $request)
    {
        $request->validate([
            'from_warehouse_id' => 'required|different:to_warehouse_id',
            'to_warehouse_id' => 'required',
            'products' => 'required|array',
            'products.*.product_id' => 'required',
            'products.*.qty' => 'required|numeric|min:0.01',
        ]);

        DB::transaction(function () use ($request) {

            $transfer = StockTransfer::create([
                'account_id' => auth()->user()->account_id,
                'from_warehouse_id' => $request->from_warehouse_id,
                'to_warehouse_id' => $request->to_warehouse_id,
                'date' => date('Y-m-d'),
                'status' => 1,
                'remarks' => $request->remarks,
                'created_by' => auth()->id(),
            ]);

            foreach ($request->products as $item) {

                // Deduct from source
                ProductStock::where([
                    'warehouse_id' => $request->from_warehouse_id,
                    'product_id' => $item['product_id']
                ])->decrement('stock', $item['qty']);

                // Add to destination
                ProductStock::updateOrCreate(
                    [
                        'warehouse_id' => $request->to_warehouse_id,
                        'product_id' => $item['product_id']
                    ],
                    [
                        'account_id' => auth()->user()->account_id,
                        'stock' => DB::raw('stock + ' . $item['qty'])
                    ]
                );

                StockTransferItem::create([
                    'transfer_id' => $transfer->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['qty']
                ]);
            }
        });

        return Settings::roleRedirect('warehouses.index', 'Stock Transferred Successfully');
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Warehouse
    |--------------------------------------------------------------------------
    */

    public function softdelete(Request $request)
        {
            $id = Settings::getDecodeCode($request->id);

            $warehouse = Warehouse::ofAccount()
                ->where('id', $id)
                ->first();

            $deleted = $warehouse ? $warehouse->delete() : false;

            return response()->json([
                'success' => $deleted ? true : false
            ]);
        }

    /*
    |--------------------------------------------------------------------------
    | Status Update
    |--------------------------------------------------------------------------
    */

    public function statusUpdate(Request $request)
    {
        $id = Settings::getDecodeCode($request->id);

        $updated = Warehouse::ofAccount()
            ->where('id', $id)
            ->update(['status' => $request->status]);

        return response()->json(['success' => $updated ? true : false]);
    }

    public function getWarehouseProductsold($id)
    {
        $warehouseId = Settings::getDecodeCode($id);
        $accountId = auth()->user()->account_id;
        $breadcrumb['title'] = __('translation.warehouse_products');
        $breadcrumb['route'] = 'admin.warehouses.products';
        $breadcrumb['route2'] = 'admin.warehouses.index';
        $breadcrumb['values'] = [
            Settings::getEncodeCode($warehouseId),
        ];
        $breadcrumb['reset_route'] = 'admin.warehouses.products'; 
        $breadcrumb['reset_value'] = Settings::getEncodeCode($warehouseId);

        $warehouse = Warehouse::where('account_id', $accountId)
            ->findOrFail($warehouseId);

        $products = Product::where('account_id', $accountId)
            ->whereHas('stocks', function ($q) use ($warehouseId, $accountId) {
                $q->where('warehouse_id', $warehouseId)
                ->where('account_id', $accountId)
                ->where('stock', '>', 0);
            })
            ->with(['stocks' => function ($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId);
            }])
            ->orderBy('name')
            ->paginate(20);

        return view('backend.admin.warehouses.products', compact('products', 'warehouse'));
    }

    public function getWarehouseProducts(Request $request, $id)
    {
        $warehouseId = Settings::getDecodeCode($id);
        $breadcrumb = $this->breadcrumb;
       
        $warehouse = Warehouse::ofAccount()
            ->findOrFail($warehouseId);

        $query = Product::ofAccount()
            ->whereHas('stocks', function ($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId);
            })
            ->with(['stocks' => function ($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId);
            }]);

        if ($request->filled('product_name')) {
            $query->where('name', 'LIKE', '%' . trim($request->product_name) . '%');
        }

        if ($request->stock_filter == 'in_stock') {
            $query->whereHas('stocks', function ($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId)
                ->where('stock', '>', 0);
            });
        }

        if ($request->stock_filter == 'low_stock') {
            $query->whereHas('stocks', function ($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId)
                ->whereColumn('stock', '<=', 'low_stock_alert');
            });
        }

        $products = $query->orderBy('name')
            ->paginate(config('constants.pagination'))
            ->withQueryString(); // important

            return view('backend.admin.warehouses.products', compact('breadcrumb', 'warehouse', 'products'));
    }

    public function getProductStock(Request $request)
    {
        $stock = ProductStock::ofAccount()
            ->where('warehouse_id', $request->warehouse_id)
            ->where('product_id', $request->product_id)
            ->first();

        return response()->json([
            'stock' => $stock->stock ?? 0
        ]);
    }
}