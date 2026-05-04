<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockReturn;
use App\Models\StockReturnItem;
use App\Models\Product;
use App\Models\Vendor;

use App\Models\Warehouse;
use App\Models\ProductStock;
use App\Models\VendorLedger;
use App\Services\StockService;
use App\Helpers\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockReturnController extends Controller
{
    protected $breadcrumbAddNew;
    protected $breadcrumbListing;

    public function __construct()
    {
        $this->middleware('auth');

        $this->breadcrumbAddNew = [
            'title' => __('translation.stock_returns'),
            'breadcrumb' => [
                ['route' => 'admin.dashboard', 'title' => __('translation.dashboard')],
                ['route' => 'admin.stock_returns.index', 'title' => __('translation.stock_returns')],
                ['route' => 'admin.stock_returns.create', 'title' => 'Add Stock Return'],
            ],
            'route1' => 'admin.stock_returns.create',
            'route1Title' => 'Add Return',
            'route2' => 'admin.stock_returns.index',
            'route2Title' => 'Stock Return List',
            'reset_route' => 'admin.stock_returns.index',
            'reset_route_title' => __('translation.cancel'),
        ];

        $this->breadcrumbListing = [
            'title' => __('translation.stock_returns'),
            'breadcrumb' => [
                ['route' => 'admin.dashboard', 'title' => __('translation.dashboard')],
                ['route' => 'admin.stock_returns.index', 'title' => __('translation.stock_returns')],
            ],
            'route1' => 'admin.stock_returns.create',
            'route1Title' => 'Add Return',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | LISTING
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $breadcrumb = $this->breadcrumbAddNew;
        $date = date('Y-m-d');
        $vendors = Vendor::ofAccount()->active()->pluck('name', 'id');
        $warehouses = Warehouse::ofAccount()->active()->pluck('name', 'id');
        $returns = StockReturn::with(['vendor', 'warehouse', 'items.product'])->ofAccount()->latest();
        
        if (request()->has('return_no') && request('return_no') != '') {
            $returns = $returns->where('return_no', 'LIKE', '%' . trim(request('return_no')) . '%');
        }
        
        if (request()->has('vendor_id') && request('vendor_id') != '') {
            $returns = $returns->where('vendor_id', request('vendor_id'));
        }
        
        if (request()->has('warehouse_id') && request('warehouse_id') != '') {
            $returns = $returns->where('warehouse_id', request('warehouse_id'));
        }
        
       $returns = Settings::applyDateRange($returns,$request, 'created_at', true); 
        
        $returns = $returns->paginate(config('constants.pagination'));
        return view('backend.admin.stock_return.index', compact('returns', 'breadcrumb', 'vendors', 'warehouses','date'));
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        $vendors = Vendor::where('account_id', auth()->user()->account_id)->pluck('name', 'id');
        $products = Product::where('account_id', auth()->user()->account_id)->pluck('name', 'id');
        $warehouses = Warehouse::where('account_id', auth()->user()->account_id)->pluck('name', 'id');

        return view('backend.admin.stock_return.form', [
            'breadcrumb' => $this->breadcrumbListing,
            'vendors' => $vendors,
            'products' => $products,
            'warehouses' => $warehouses
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        try {

            $validated = $request->validate([
                'vendor_id' => 'required|exists:vendors,id',
                'warehouse_id' => 'required|exists:warehouses,id',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.qty' => 'required|numeric|min:0.01',
                'items.*.price' => 'required|numeric|min:0.01',
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return Settings::roleRedirect('stock_returns.index', $e->getMessage(), 'error');
        }

        try {

            DB::transaction(function () use ($validated) {

                $accountId = auth()->user()->account_id;

                $returnNo = 'RET-' . date('Ymd') . '-' . rand(1000, 9999);

                // ✅ Calculate total
                $totalAmount = collect($validated['items'])->sum(function ($item) {
                    return $item['qty'] * $item['price'];
                });

                // ✅ Create Return
                $return = StockReturn::create([
                    'account_id'   => $accountId,
                    'vendor_id'    => $validated['vendor_id'],
                    'warehouse_id' => $validated['warehouse_id'],
                    'return_no'    => $returnNo,
                    'return_date'  => now()->format('Y-m-d'),
                    'total'        => $totalAmount,
                    'created_by'   => auth()->id(),
                ]);

                $stockService = app(StockService::class);

                // ============================
                // 🔥 VALIDATE STOCK FIRST
                // ============================
                foreach ($validated['items'] as $item) {

                    $stock = ProductStock::where('account_id', $accountId)
                        ->where('warehouse_id', $validated['warehouse_id'])
                        ->where('product_id', $item['product_id'])
                        ->lockForUpdate()
                        ->first();

                    if (!$stock) {
                        throw new \Exception('Product not found in warehouse stock');
                    }

                    if ($stock->stock <= 0) {
                        throw new \Exception('No stock available for selected product');
                    }

                    if ($stock->stock < $item['qty']) {
                        throw new \Exception('Return qty exceeds available stock');
                    }
                }

                // ============================
                // 🔥 PROCESS RETURN
                // ============================
                foreach ($validated['items'] as $item) {

                    $qty = $item['qty'];
                    $price = $item['price'];

                    // Save item
                    StockReturnItem::create([
                        'return_id'  => $return->id,
                        'product_id' => $item['product_id'],
                        'qty'        => $qty,
                        'price'      => $price,
                        'total'      => $qty * $price
                    ]);

                    // ✅ STOCK OUT (use existing supported type)
                    $stockService->moveStock([
                        'account_id'   => $accountId,
                        'warehouse_id' => $validated['warehouse_id'],
                        'product_id'   => $item['product_id'],
                        'type'         => 'adjustment_sub', // ✅ IMPORTANT FIX
                        'qty'          => $qty,
                        'reference_id' => $return->id,
                        'remarks'      => 'Stock Return #' . $returnNo
                    ]);
                }

                // ============================
                // 🔥 VENDOR BALANCE UPDATE
                // ============================

                $vendor = Vendor::lockForUpdate()->find($validated['vendor_id']);

                $oldBalance = $vendor->current_balance ?? 0;
                $newBalance = $oldBalance - $totalAmount;

                // Ledger Entry
                VendorLedger::create([
                    'account_id'  => $accountId,
                    'vendor_id'   => $vendor->id,
                    'type'        => 5, //
                    'reference_id'=> $return->id,
                    'debit'       => 0,
                    'credit'      => $totalAmount,
                    'balance'     => $newBalance,
                    'remarks'     => 'Stock Return #' . $returnNo
                ]);

                // Update Vendor
                $vendor->update([
                    'current_balance' => $newBalance
                ]);

            });

            return Settings::roleRedirect('stock_returns.index', 'Stock Return Created Successfully.');

        } catch (\Exception $e) {

            echo "<pre>";
            print_r($e->getMessage());
            echo "</pre>";
            exit;
            return Settings::roleRedirect('stock_returns.index', $e->getMessage(), 'error');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | VIEW
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        $id = Settings::getDecodeCode($id);

        $return = StockReturn::with(['items.product', 'vendor', 'warehouse'])
            ->where('account_id', auth()->user()->account_id)
            ->findOrFail($id);

        return view('backend.admin.stock_return.view', compact('return'));
    }

    public function getStock(Request $request)
    {
        $stock = \App\Models\ProductStock::where([
            'warehouse_id' => $request->warehouse_id,
            'product_id' => $request->product_id,
            'account_id' => auth()->user()->account_id
        ])->value('stock') ?? 0;

        return response()->json([
            'stock' => $stock
        ]);
    }

    public function viewAjax($id)
    {
        $id = Settings::getDecodeCode($id);

        $return = StockReturn::with(['vendor','warehouse','items.product'])
            ->where('account_id', auth()->user()->account_id)
            ->findOrFail($id);

        return view('backend.admin.stock_return._view', compact('return')); 
    }

    /*
|--------------------------------------------------------------------------
| CANCEL RETURN
|--------------------------------------------------------------------------
*/
    public function cancel(Request $request)
    {
        try {

            $id = Settings::getDecodeCode($request->id);

            DB::transaction(function () use ($id) {

                $accountId = auth()->user()->account_id;

                $return = StockReturn::with('items')
                    ->where('account_id', $accountId)
                    ->lockForUpdate()
                    ->findOrFail($id);

                // ❌ Already cancelled
                if ($return->status == 0) {
                    throw new \Exception('Return already cancelled');
                }

                $stockService = app(StockService::class);

                // ============================
                // 🔁 REVERSE STOCK
                // ============================
                foreach ($return->items as $item) {

                    $stockService->moveStock([
                        'account_id'   => $accountId,
                        'warehouse_id' => $return->warehouse_id,
                        'product_id'   => $item->product_id,
                        'type'         => 'adjustment_add', // reverse stock
                        'qty'          => $item->qty,
                        'reference_id' => $return->id,
                        'remarks'      => 'Cancel Stock Return #' . $return->return_no
                    ]);
                }

                // ============================
                // 🔁 REVERSE VENDOR BALANCE
                // ============================
                $vendor = Vendor::lockForUpdate()->find($return->vendor_id);

                $oldBalance = $vendor->current_balance ?? 0;
                $newBalance = $oldBalance + $return->total;

                // Ledger reverse entry
                VendorLedger::create([
                    'account_id'   => $accountId,
                    'vendor_id'    => $vendor->id,
                    'type'         => 6, // cancel return type (define constant if needed)
                    'reference_id' => $return->id,
                    'debit'        => $return->total,
                    'credit'       => 0,
                    'balance'      => $newBalance,
                    'remarks'      => 'Cancel Stock Return #' . $return->return_no
                ]);

                // Update vendor balance
                $vendor->update([
                    'current_balance' => $newBalance
                ]);

                // ============================
                // 🔁 UPDATE STATUS
                // ============================
                $return->update([
                    'status' => 0
                ]);

            });

            return response()->json([
                'success' => true,
                'message' => 'Stock Return Cancelled Successfully'
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}