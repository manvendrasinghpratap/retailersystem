<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\Models\ProductStock;
use App\Models\MasterItem;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Helpers\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;

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
                ],
                [
                'route' => 'admin.warehouses.stock.listing',
                'title' => __('translation.warehouse_stock_listing')
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
        $staffs = User::activeByAccountAndStaff()->pluck('name', 'id');
        return view('backend.admin.warehouses.form', [
            'breadcrumb' => $this->breadcrumb,
            'staffs' => $staffs
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
        'staff_id' => $request->staff_id,
        'manager_name' => optional(User::find($request->staff_id))->name,
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
        $staffs = User::activeByAccountAndStaff()->pluck('name', 'id');
        $warehouse = Warehouse::ofAccount()
            ->findOrFail($id);

        return view('backend.admin.warehouses.form', [
            'warehouse' => $warehouse,
            'staffs' => $staffs,
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
        'staff_id' => $request->staff_id,
        'manager_name' => optional(User::find($request->staff_id))->name,
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

    public function getWarehouseProductsOld(Request $request, $id)
    {
        $warehouseId = Settings::getDecodeCode($id);
        $breadcrumb = $this->breadcrumb;
       
        $warehouse = Warehouse::ofAccount()->findOrFail($warehouseId);

        $query = Product::ofAccount()
            ->whereHas('stocks', function ($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId);
            })
            ->with(['stocks' => function ($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId);
            }]);

        if ($request->filled('item_name')) {
            $query->where('name', 'LIKE', '%' . trim($request->item_name) . '%');
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

        $products = $query->orderBy('name')->paginate(config('constants.pagination'))->withQueryString(); 

            return view('backend.admin.warehouses.products', compact('breadcrumb', 'warehouse', 'products'));
    }

    public function getWarehouseProducts(Request $request, $id)
    {
        $warehouseId = Settings::getDecodeCode($id);
        $breadcrumb = $this->breadcrumb;

        $warehouse = Warehouse::ofAccount()->findOrFail($warehouseId);

        $query = MasterItem::ofAccount()

            // ✅ Only items that exist in this warehouse
            ->whereHas('stocks', function ($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId);
            })

            // ✅ Load stock for this warehouse only
            ->with(['stocks' => function ($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId);
            }]);

        // =========================
        // 🔍 FILTER: NAME
        // =========================
        if ($request->filled('item_name')) {
            $query->where('name', 'LIKE', '%' . trim($request->item_name) . '%');
        }

        // =========================
        // 📦 FILTER: IN STOCK
        // =========================
        if ($request->stock_filter === 'in_stock') {
            $query->whereHas('stocks', function ($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId)
                ->where('stock', '>', 0);
            });
        }

        // =========================
        // ⚠️ FILTER: LOW STOCK
        // =========================
        if ($request->stock_filter === 'low_stock') {
            $query->whereHas('stocks', function ($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId)
                ->whereColumn('stock', '<=', 'low_stock_alert');
            });
        }

        $items = $query
            ->orderBy('name')
            ->paginate(config('constants.pagination'))
            ->withQueryString();

        return view(
            'backend.admin.warehouses.warehouseProducts', // you can rename later
            compact('breadcrumb', 'warehouse', 'items')
        );
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

    public function stockListing(Request $request)
    {    
        $this->breadcrumb['title'] = __('translation.warehouse_stock_listing');
        $this->breadcrumb['route1'] = 'admin.warehouses.index';
        $this->breadcrumb['route1Title'] = __('translation.warehouse_list');
        $this->breadcrumb['route2'] = 'admin.warehouses.stock.listing';
        $this->breadcrumb['route2Title'] = __('translation.warehouse_stock_listing');
        $this->breadcrumb['reset_route'] = 'admin.warehouses.stock.listing';
        $this->breadcrumb['reset_route_title'] = __('translation.reset');

        $breadcrumb =  $this->breadcrumb;

        $query = ProductStock::with([
                'warehouse',
                'masterItem'
            ])
            ->whereHas('warehouse', function ($q) {
                $q->ofAccount();
            });

        // =========================
        // FILTER : PRODUCT NAME
        // =========================
        if ($request->filled('product_name')) {

            $query->whereHas('masterItem', function ($q) use ($request) {

                $q->where(
                    'name',
                    'LIKE',
                    '%' . trim($request->product_name) . '%'
                );
            });
        }

        // =========================
        // FILTER : WAREHOUSE
        // =========================
        if ($request->filled('warehouse_id')) {

            $query->where('warehouse_id', $request->warehouse_id);
        }

        // =========================
        // FILTER : STOCK
        // =========================
        if ($request->stock_filter == 'in_stock') {

            $query->where('stock', '>', 0);
        }

        // =========================
        // FILTER : LOW STOCK
        // =========================
        if ($request->stock_filter == 'low_stock') {

            $query->whereColumn('stock', '<=', 'low_stock_alert');
        }

        $stocks = $query
            ->latest()
            ->paginate(config('constants.pagination'))
            ->withQueryString();

        $warehouses = Warehouse::dropdown();
        $items = MasterItem::dropdown();

        return view(
            'backend.admin.warehouses.stock_listing',
            compact('breadcrumb', 'stocks', 'warehouses', 'items')
        );
    }
}