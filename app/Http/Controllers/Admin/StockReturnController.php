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
use PDF;
use App\Models\PurchaseItem;

class StockReturnController extends Controller
{
    protected $breadcrumb;
    protected $breadcrumbListing;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:stock_return.view')->only(['index', 'viewAjax', 'viewAjaxPdf', 'getStock']);
        $this->middleware('permission:stock_return.create')->only(['create', 'store']);
        $this->middleware('permission:stock_return.cancel')->only(['cancel']);
        $this->middleware('permission:stock_return.export')->only(['exportPdf', 'exportCsv']);
        $this->breadcrumb = [
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
    @description: List Stock Return
    @method: GET
    @return: view('backend.admin.stock_return.index')
    */
    public function index(Request $request)
    {
        $breadcrumb = $this->breadcrumb;
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
        $returns = Settings::applyDateRange($returns, $request, 'created_at', true);
        if ($request->has('pdf')) {
            $returns = $returns->get();
            $pdfHeaderdata = \Config::get('constants.stockreturnpdf');
            $pdf = PDF::loadView('backend.pdf.stockreturn.stockreturnpdf', compact('returns', 'pdfHeaderdata', 'breadcrumb'));
            $pdf = Settings::downloadpdf($pdf);
            $fileName = $pdfHeaderdata['filename'] . '-' . date('Y-m-d') . '.pdf';
            return $pdf->stream($fileName);
        } elseif ($request->has('csv')) {
            $returns = $returns->get();
            $csvHeaderdata = \Config::get('constants.stockreturnpdf');
            $fileName = $csvHeaderdata['filename'] . '-' . date('Y-m-d') . '.csv';
            $data = [];
            $ii = $i = 0;
            // ✅ Header Row
            $data[$ii] = [
                '#',
                __('translation.return_no'),
                __('translation.vendor'),
                __('translation.warehouse'),
                __('translation.currency') . __('translation.total'),
                __('translation.status'),
                __('translation.createdat'),
            ];

            foreach ($returns as $return) {
                $data[++$ii] = [
                    $ii,
                    $return->return_no,
                    $return->vendor->name,
                    $return->warehouse->name,
                    $return->total,
                    $return->status == 1 ? __('translation.active') : __('translation.inactive'),
                    !empty($return->created_at) ? "\t" . Settings::getFormattedDatetime($return->created_at) : '-',
                ];
            }
            return Settings::downloadcsvfile($data, $fileName);
        }
        $returns = $returns->paginate(account_setting('general.pagination'));

        return view('backend.admin.stock_return.index', compact('returns', 'breadcrumb', 'vendors', 'warehouses', 'date'));
    }

    public function exportPdf(Request $request)
    {
        $request->merge(['pdf' => 1]);
        return $this->index($request);
    }
    public function exportCsv(Request $request)
    {
        $request->merge(['csv' => 1]);
        return $this->index($request);
    }
    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    @description: Create Stock Return
    @method: GET
    @return: view('backend.admin.stock_return.form')
    */
    public function create()
    {
        $this->breadcrumb['route1Title'] = 'Create Stock Return';

        $vendors = Vendor::ofAccount()->active()->pluck('name', 'id')->sort();
        $products = Product::ofAccount()->active()->pluck('name', 'id');
        $warehouses = Warehouse::ofAccount()->active()->pluck('name', 'id')->sort();

        return view('backend.admin.stock_return.form', [
            'breadcrumb' => $this->breadcrumb,
            'vendors' => $vendors,
            'products' => $products,
            'warehouses' => $warehouses
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    @description: Store Stock Return
    @method: POST
    @return: view('backend.admin.stock_return.form')
    */
    public function store(Request $request)
    {
        try {

            $validated = $request->validate([

                'vendor_id' => 'required|exists:vendors,id',

                'warehouse_id' => 'required|exists:warehouses,id',

                'items' => 'required|array|min:1',

                'items.*.master_item_id' => 'required|exists:master_items,id',

                'items.*.qty' => 'required|numeric|min:0.01',

                'items.*.price' => 'required|numeric|min:0.01',
                'items.*.reason' => 'nullable|string|max:500',
            ]);

            DB::transaction(function () use ($validated) {

                $accountId = auth()->user()->account_id;

                // =========================
                // RETURN NUMBER
                // =========================
                $returnNo = 'RET-' . now()->format('YmdHis');

                // =========================
                // TOTAL AMOUNT
                // =========================
                $totalAmount = collect($validated['items'])->sum(function ($item) {

                    return $item['qty'] * $item['price'];
                });

                // =========================
                // CREATE RETURN
                // =========================
                $return = StockReturn::create([

                    'account_id' => $accountId,

                    'vendor_id' => $validated['vendor_id'],

                    'warehouse_id' => $validated['warehouse_id'],

                    'return_no' => $returnNo,

                    'return_date' => now()->format('Y-m-d'),

                    'total' => $totalAmount,

                    'created_by' => auth()->id(),
                ]);

                $stockService = app(StockService::class);

                // =========================
                // VALIDATE STOCK
                // =========================
                foreach ($validated['items'] as $item) {

                    $stock = ProductStock::query()

                        ->where('account_id', $accountId)

                        ->where('warehouse_id', $validated['warehouse_id'])

                        ->where('master_item_id', $item['master_item_id'])

                        ->lockForUpdate()

                        ->first();

                    if (!$stock) {

                        throw new \Exception('Item not found in warehouse stock');
                    }

                    if ($stock->stock <= 0) {

                        throw new \Exception('No stock available');
                    }

                    if ($stock->stock < $item['qty']) {

                        throw new \Exception('Return qty exceeds available stock');
                    }
                }

                // =========================
                // PROCESS ITEMS
                // =========================
                foreach ($validated['items'] as $item) {
                    $qty = $item['qty'];
                    $price = $item['price'];
                    // =====================
                    // SAVE RETURN ITEM
                    // =====================
                    StockReturnItem::create([
                        'return_id' => $return->id,
                        'master_item_id' => $item['master_item_id'],
                        'qty' => $qty,
                        'price' => $price,
                        'total' => $qty * $price,
                        'reason' => $item['reason']
                    ]);

                    // =====================
                    // STOCK OUT
                    // =====================
                    $stockService->moveStock([
                        'account_id' => $accountId,
                        'warehouse_id' => $validated['warehouse_id'],
                        'master_item_id' => $item['master_item_id'],
                        'type' => 5, // stock return

                        // IMPORTANT
                        'qty' => -$qty,
                        'reference_id' => $return->id,
                        'remarks' => 'Stock Return #' . $returnNo
                    ]);
                }

                // =========================
                // VENDOR UPDATE
                // =========================
                $vendor = Vendor::lockForUpdate()
                    ->findOrFail($validated['vendor_id']);

                $oldBalance = (float) ($vendor->current_balance ?? 0);

                $newBalance = $oldBalance - $totalAmount;

                // =========================
                // LEDGER ENTRY
                // =========================
                VendorLedger::create([

                    'account_id' => $accountId,

                    'vendor_id' => $vendor->id,

                    'type' => 5,

                    'reference_id' => $return->id,

                    'debit' => 0,

                    'credit' => $totalAmount,

                    'balance' => $newBalance,

                    'remarks' => 'Stock Return #' . $returnNo
                ]);

                // =========================
                // UPDATE VENDOR BALANCE
                // =========================
                $vendor->update([

                    'current_balance' => $newBalance
                ]);
            });

            return Settings::roleRedirect(
                'stock_returns.index',
                'Stock Return Created Successfully.'
            );

        } catch (\Exception $e) {

            return Settings::roleRedirect(
                'stock_returns.index',
                $e->getMessage(),
                'error'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | VIEW
    |--------------------------------------------------------------------------
    @description: View Stock Return
    @method: GET
    @return: view('backend.admin.stock_return.view')
    */
    public function show($id)
    {
        $id = Settings::getDecodeCode($id);

        $return = StockReturn::with(['items.product', 'vendor', 'warehouse'])
            ->where('account_id', auth()->user()->account_id)
            ->findOrFail($id);

        return view('backend.admin.stock_return.view', compact('return'));
    }

    /*
    |--------------------------------------------------------------------------
    | GET STOCK
    |--------------------------------------------------------------------------
    @description: Get Stock
    @method: GET
    @return: json
    */
    public function getStock_delete(Request $request)
    {
        $stock = \App\Models\ProductStock::where([
            'warehouse_id' => $request->warehouse_id,
            'master_item_id' => $request->master_item_id,
            'account_id' => auth()->user()->account_id
        ])->value('stock') ?? 0;

        return response()->json([
            'stock' => $stock
        ]);
    }



    public function getStock(Request $request)
    {
        $stock = ProductStock::where([
            'warehouse_id' => $request->warehouse_id,
            'master_item_id' => $request->master_item_id,
            'account_id' => auth()->user()->account_id,
        ])->value('stock') ?? 0;

        $trackingType = PurchaseItem::where('master_item_id', $request->master_item_id)
            ->whereHas('purchase', function ($q) use ($request) {
                $q->where('warehouse_id', $request->warehouse_id)
                    ->where('account_id', auth()->user()->account_id)
                    ->where('status', 1);
            })
            ->latest()
            ->value('tracking_type') ?? 'none';

        return response()->json([
            'stock' => $stock,
            'tracking_type' => $trackingType,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | VIEW AJAX
    |--------------------------------------------------------------------------
    @description: View Stock Return
    @method: GET
    @return: view('backend.admin.stock_return.view')
    */
    public function viewAjax($id, $type = '')
    {
        $id = Settings::getDecodeCode($id);
        $return = StockReturn::with(['vendor', 'warehouse', 'items.masterItem'])->ofAccount()->findOrFail($id);
        if ($type == 'pdf') {
            $pdfHeaderdata = \Config::get('constants.viewStockReturnListItemPdf');
            $pdf = PDF::loadView('backend.pdf.stockreturn.viewStockReturnListItemPdf', compact('return', 'pdfHeaderdata'));
            $pdf = Settings::downloadpdf($pdf);
            $fileName = $pdfHeaderdata['filename'] . '-' . date('Y-m-d') . '.pdf';
            return $pdf->stream($fileName);
        }
        return view('backend.admin.stock_return._view', compact('return'));
    }
    public function viewAjaxPdf($id)
    {
        return $this->viewAjax($id, 'pdf');
    }

    /*
    |--------------------------------------------------------------------------
    | CANCEL RETURN
    |--------------------------------------------------------------------------
    @description: Cancel Stock Return
    @method: POST
    @return: view('backend.admin.stock_return.view')
    */

    public function cancel(Request $request)
    {
        try {

            $id = \App\Helpers\Settings::getDecodeCode($request->id);

            DB::transaction(function () use ($id) {

                $accountId = auth()->user()->account_id;

                $return = StockReturn::with('items')
                    ->where('account_id', $accountId)
                    ->lockForUpdate()
                    ->findOrFail($id);

                // =========================
                // ❌ ALREADY CANCELLED
                // =========================
                if ((int) $return->status === 0) {
                    throw new \Exception('Stock Return already cancelled');
                }

                $stockService = app(\App\Services\StockService::class);

                // =========================
                // 🔁 REVERSE STOCK
                // =========================
                foreach ($return->items as $item) {

                    // Validate item
                    if (!$item->master_item_id) {
                        throw new \Exception('Invalid return item detected');
                    }

                    $stockService->moveStock([
                        'account_id' => $accountId,
                        'warehouse_id' => $return->warehouse_id,

                        // ✅ master item
                        'master_item_id' => $item->master_item_id,

                        // ✅ reverse stock add
                        'type' => 'adjustment_add',

                        // ✅ positive qty
                        'qty' => (float) $item->qty,

                        'reference_id' => $return->id,
                        'remarks' => 'Cancel Stock Return #' . $return->return_no
                    ]);
                }

                // =========================
                // 🔁 REVERSE VENDOR BALANCE
                // =========================
                $vendor = Vendor::where('account_id', $accountId)
                    ->lockForUpdate()
                    ->findOrFail($return->vendor_id);

                $oldBalance = (float) ($vendor->current_balance ?? 0);

                $returnTotal = (float) $return->total;

                // Vendor payable restored
                $newBalance = $oldBalance + $returnTotal;

                // =========================
                // 🧾 LEDGER ENTRY
                // =========================
                VendorLedger::create([
                    'account_id' => $accountId,
                    'vendor_id' => $vendor->id,

                    // cancel stock return type
                    'type' => 6,

                    'reference_id' => $return->id,

                    // payable increased again
                    'debit' => $returnTotal,
                    'credit' => 0,

                    'balance' => $newBalance,

                    'remarks' => 'Cancel Stock Return #' . $return->return_no
                ]);

                // =========================
                // 💰 UPDATE VENDOR
                // =========================
                $vendor->update([
                    'current_balance' => $newBalance
                ]);

                // =========================
                // 🔁 UPDATE RETURN STATUS
                // =========================
                $return->update([
                    'status' => 0,
                    'cancelled_by' => auth()->id(),
                    'cancelled_at' => now(),
                ]);

            });

            return response()->json([
                'success' => true,
                'message' => __('translation.stock_return_cancelled_successfully')
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}