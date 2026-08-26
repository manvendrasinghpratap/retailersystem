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
use App\Models\PurchaseItemTracking;

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
            'title' => __('translation.purchase_returns'),
            'breadcrumb' => [
                ['route' => 'admin.dashboard', 'title' => __('translation.dashboard')],
                ['route' => 'admin.stock_returns.index', 'title' => __('translation.purchase_returns')],
                ['route' => 'admin.stock_returns.create', 'title' => __('translation.add_purchase_return')],
            ],
            'route1' => 'admin.stock_returns.create',
            'route1Title' => __('translation.add_purchase_return'),
            'route2' => 'admin.stock_returns.index',
            'route2Title' => __('translation.purchase_return_list'),
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
        $vendors = Vendor::ofAccount()->active()->orderBy('company_name', 'asc')->pluck('company_name', 'id');
        $warehouses = Warehouse::ofAccount()->active()->orderBy('name', 'asc')->pluck('name', 'id');
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
        $this->breadcrumb['route1Title'] = __('translation.add_purchase_return');

        $vendors = Vendor::ofAccount()->active()->orderBy('company_name', 'asc')->pluck('company_name', 'id');
        $products = Product::ofAccount()->active()->orderBy('name', 'asc')->pluck('name', 'id');
        $warehouses = Warehouse::ofAccount()->active()->orderBy('name', 'asc')->pluck('name', 'id');

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
    /*
|--------------------------------------------------------------------------
| STORE
|--------------------------------------------------------------------------
| @description: Store Purchase Return to Supplier
| @method: POST
| @return: Redirect to stock_returns.index
*/
    public function store(Request $request)
    {
        // dd($request->all());
        try {
            /*
            |--------------------------------------------------------------------------
            | VALIDATION
            |--------------------------------------------------------------------------
            */
            $validated = $request->validate([
                'vendor_id' => ['required','exists:vendors,id'],
                'warehouse_id' => ['required','exists:warehouses,id'],
                'items' => ['required','array','min:1'],
                'items.*.master_item_id' => ['required','exists:master_items,id'],
                'items.*.qty' => ['required','numeric','min:0.01'],
                'items.*.price' => ['required','numeric','min:0.01'],
                'items.*.reason' => ['nullable','string','max:500'],
            ]);
            
            $accountId = auth()->user()->account_id;
            $userId = auth()->id();
            
            DB::transaction(function () use ($validated,$accountId,$userId) {
                /*
                |--------------------------------------------------------------------------
                | VENDOR
                |--------------------------------------------------------------------------
                */
                $vendor = Vendor::query()->where('id', $validated['vendor_id'])->where('account_id', $accountId)->lockForUpdate()->first();
                if (!$vendor) {
                    throw new \Exception('Vendor not found.');
                }

                /*
                |--------------------------------------------------------------------------
                | WAREHOUSE
                |--------------------------------------------------------------------------
                */
                $warehouse = Warehouse::query()->where('id', $validated['warehouse_id'])->where('account_id', $accountId)->first();
                if (!$warehouse) {
                    throw new \Exception('Warehouse not found.');
                }

                /*
                |--------------------------------------------------------------------------
                | RETURN NUMBER
                |--------------------------------------------------------------------------
                */
                $returnNo = 'RET-' . now()->format('YmdHis') . '-' . strtoupper(\Illuminate\Support\Str::random(4));
                /*
                |--------------------------------------------------------------------------
                | STOCK SERVICE
                |--------------------------------------------------------------------------
                */
                $stockService = app(StockService::class);
                /*
                |--------------------------------------------------------------------------
                | CREATE RETURN HEADER
                |--------------------------------------------------------------------------
                */
                $return = StockReturn::create([
                    'account_id' => $accountId,
                    'vendor_id' => $vendor->id,
                    'warehouse_id' => $warehouse->id,
                    'return_no' => $returnNo,
                    'return_date' => now()->format('Y-m-d'),
                    'total' => 0,
                    'created_by' => $userId,
                ]);
                /*
                |--------------------------------------------------------------------------
                | TOTAL RETURN AMOUNT
                |--------------------------------------------------------------------------
                */
                $totalAmount = 0;
                /*
                |--------------------------------------------------------------------------
                | PROCESS EACH PRODUCT
                |--------------------------------------------------------------------------
                */

                foreach ($validated['items'] as $item) {
                    $masterItemId = (int) $item['master_item_id'];
                    $requestedQty = (float) $item['qty'];
                    /*
                    |--------------------------------------------------------------------------
                    | PRODUCT STOCK
                    |--------------------------------------------------------------------------
                    */
                    $stock = ProductStock::query()->where('account_id', $accountId)->where('warehouse_id', $warehouse->id)->where('master_item_id', $masterItemId)->lockForUpdate()->first();
                    if (!$stock) {
                        throw new \Exception('Item not found in warehouse stock.');
                    }
                    /*
                    |--------------------------------------------------------------------------
                    | CHECK WAREHOUSE STOCK
                    |--------------------------------------------------------------------------
                    */
                    $warehouseQty = (float) $stock->stock;
                    if ($warehouseQty <= 0) {
                        throw new \Exception('No stock available for this item.');
                    }
                    if ($requestedQty > $warehouseQty) {
                        throw new \Exception("Return quantity for item {$masterItemId} exceeds warehouse stock.");
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | FIND ELIGIBLE PURCHASE TRACKING RECORDS
                    |--------------------------------------------------------------------------
                    |
                    | Supplier return is allowed only when:
                    |
                    | requisition_id      IS NULL
                    | requisition_item_id IS NULL
                    | store_id            IS NULL
                    | is_reserved         = 0
                    | status              = 1
                    |
                    */
                    $trackings = PurchaseItemTracking::query()
                        ->where('warehouse_id', $warehouse->id)
                        // ->whereNull('requisition_id')
                        // ->whereNull('requisition_item_id')
                        ->whereNull('store_id')
                        ->where('is_reserved', 0)
                        ->whereIn('is_sold', [0, 2])
                        ->where('status', 1)
                        ->where('returned_quantity', 0)
                        ->whereHas('purchaseItem', function ($query) use (
                            $masterItemId,
                            $vendor
                        ) {
                            /*
                            |--------------------------------------------------------------------------
                            | SAME MASTER ITEM
                            |--------------------------------------------------------------------------
                            */

                            $query->where('master_item_id', $masterItemId);

                            /*
                            |--------------------------------------------------------------------------
                            | SAME VENDOR
                            |--------------------------------------------------------------------------
                            |
                            | This assumes purchase_items has purchase_id
                            | and purchases has vendor_id.
                            |
                            */

                            $query->whereHas('purchase', function ($purchaseQuery) use (
                                $vendor
                            ) {

                                $purchaseQuery->where(
                                    'vendor_id',
                                    $vendor->id
                                );
                            });
                        })

                        ->orderBy('id')

                        ->lockForUpdate()

                        ->get();

                    /*
                    |--------------------------------------------------------------------------
                    | CALCULATE ELIGIBLE TRACKING QUANTITY
                    |--------------------------------------------------------------------------
                    */

                    $eligibleQty = $trackings->sum(function ($tracking) {

                        return (float) $tracking->quantity;
                    });

                    /*
                    |--------------------------------------------------------------------------
                    | CHECK PURCHASE TRACKING STOCK
                    |--------------------------------------------------------------------------
                    */

                    if ($eligibleQty <= 0) {

                        throw new \Exception(
                            "No eligible purchase stock available "
                            . "for item {$masterItemId}."
                        );
                    }

                    if ($requestedQty > $eligibleQty) {

                        throw new \Exception(
                            "Return quantity for item {$masterItemId} "
                            . "exceeds eligible purchase quantity. "
                            . "Available: {$eligibleQty}, "
                            . "Requested: {$requestedQty}."
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | REMAINING QUANTITY TO RETURN
                    |--------------------------------------------------------------------------
                    */

                    $remainingQty = $requestedQty;

                    /*
                    |--------------------------------------------------------------------------
                    | ALLOCATE RETURN ACROSS TRACKING RECORDS
                    |--------------------------------------------------------------------------
                    */

                    foreach ($trackings as $tracking) {

                        if ($remainingQty <= 0) {
                            break;
                        }

                        $trackingQty = (float) $tracking->quantity;

                        if ($trackingQty <= 0) {
                            continue;
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | QUANTITY TO TAKE FROM THIS TRACKING
                        |--------------------------------------------------------------------------
                        */

                        $returnQty = min(
                            $remainingQty,
                            $trackingQty
                        );

                        /*
                        |--------------------------------------------------------------------------
                        | GET PURCHASE PRICE
                        |--------------------------------------------------------------------------
                        |
                        | Price is NOT stored in purchase_item_trackings.
                        | It comes from purchase_items.
                        |
                        */

                        $purchaseItem = $tracking->purchaseItem;

                        if (!$purchaseItem) {

                            throw new \Exception(
                                "Purchase item not found for tracking ID "
                                . $tracking->id
                            );
                        }

                        $price = (float) $purchaseItem->cost_price;

                        if ($price <= 0) {

                            throw new \Exception(
                                "Invalid purchase price for tracking ID "
                                . $tracking->id
                            );
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | LINE TOTAL
                        |--------------------------------------------------------------------------
                        */

                        $lineTotal = round(
                            $returnQty * $price,
                            2
                        );

                        /*
                        |--------------------------------------------------------------------------
                        | SAVE RETURN ITEM
                        |--------------------------------------------------------------------------
                        |
                        | ONE ROW PER PURCHASE TRACKING RECORD
                        |
                        */

                        StockReturnItem::create([

                            'return_id' =>
                                $return->id,

                            'master_item_id' =>
                                $masterItemId,

                            'purchase_item_tracking_id' =>
                                $tracking->id,

                            'qty' =>
                                $returnQty,

                            'price' =>
                                $price,

                            'total' =>
                                $lineTotal,

                            'reason' =>
                                $item['reason'] ?? null,
                        ]);

                        /*
                        |--------------------------------------------------------------------------
                        | REDUCE PURCHASE TRACKING QUANTITY
                        |--------------------------------------------------------------------------
                        */

                        $newTrackingQty = round(
                            $trackingQty - $returnQty,
                            2
                        );
 
                        $tracking->update([
                            'returned_quantity' => 1,
                            'is_sold' => 2,    // 2 return 
                        ]);

                        /*
                        |--------------------------------------------------------------------------
                        | UPDATE TRACKING STATUS
                        |--------------------------------------------------------------------------
                        |
                        | If all quantity from this tracking record has been
                        | returned, mark it as returned.
                        |
                        | is_sold:
                        | 0 = available
                        | 1 = sold
                        | 2 = return
                        | 3 = damage
                        |
                        */

                        // if ($newTrackingQty <= 0) {

                        //     $tracking->update([
                        //         'quantity' => 0,
                        //         'is_sold' => 2,
                        //     ]);
                        // }

                        /*
                        |--------------------------------------------------------------------------
                        | TOTAL
                        |--------------------------------------------------------------------------
                        */

                        $totalAmount += $lineTotal;

                        /*
                        |--------------------------------------------------------------------------
                        | REMAINING
                        |--------------------------------------------------------------------------
                        */

                        $remainingQty = round(
                            $remainingQty - $returnQty,
                            2
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | SAFETY CHECK
                    |--------------------------------------------------------------------------
                    */

                    if ($remainingQty > 0) {

                        throw new \Exception(
                            "Unable to allocate complete return quantity "
                            . "for item {$masterItemId}."
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | REDUCE WAREHOUSE STOCK
                    |--------------------------------------------------------------------------
                    */

                    $stockService->moveStock([

                        'account_id' =>
                            $accountId,

                        'warehouse_id' =>
                            $warehouse->id,

                        'master_item_id' =>
                            $masterItemId,

                        'type' =>
                            5, // Purchase Return

                        'qty' =>
                            -$requestedQty,

                        'reference_id' =>
                            $return->id,

                        'remarks' =>
                            'Purchase Return #' . $returnNo,
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | UPDATE RETURN TOTAL
                |--------------------------------------------------------------------------
                */

                $totalAmount = round(
                    $totalAmount,
                    2
                );

                $return->update([
                    'total' => $totalAmount,
                ]);

                /*
                |--------------------------------------------------------------------------
                | UPDATE VENDOR BALANCE
                |--------------------------------------------------------------------------
                */

                $oldBalance = (float) (
                    $vendor->current_balance ?? 0
                );

                $newBalance = round(
                    $oldBalance - $totalAmount,
                    2
                );

                /*
                |--------------------------------------------------------------------------
                | VENDOR LEDGER
                |--------------------------------------------------------------------------
                */

                VendorLedger::create([

                    'account_id' =>
                        $accountId,

                    'vendor_id' =>
                        $vendor->id,

                    'type' =>
                        5, // Purchase Return

                    'reference_id' =>
                        $return->id,

                    'debit' =>
                        0,

                    'credit' =>
                        $totalAmount,

                    'balance' =>
                        $newBalance,

                    'remarks' =>
                        'Purchase Return #' . $returnNo,
                ]);

                /*
                |--------------------------------------------------------------------------
                | UPDATE VENDOR
                |--------------------------------------------------------------------------
                */

                $vendor->update([
                    'current_balance' => $newBalance,
                ]);
            });

            /*
            |--------------------------------------------------------------------------
            | SUCCESS
            |--------------------------------------------------------------------------
            */

            return Settings::roleRedirect(
                'stock_returns.index',
                'Purchase Return Created Successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return Settings::roleRedirect(
                'stock_returns.index',
                $e->getMessage(),
                'error'
            );
        }
    }


    public function store_backup(Request $request)
    {
        try {
            $validated = $request->validate([

                'vendor_id' => 'required|exists:vendors,id',
                'warehouse_id' => 'required|exists:warehouses,id',
                'items' => 'required|array|min:1',
                'items.*.master_item_id' => 'required|exists:master_items,id',
                'items.*.purchase_item_tracking_id' => 'required|exists:purchase_item_trackings,id',
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
                        'purchase_item_tracking_id' => $item['purchase_item_tracking_id'],
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
                'Purchase Return Created Successfully.'
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
                $userId = auth()->id();

                /*
                |--------------------------------------------------------------------------
                | GET RETURN
                |--------------------------------------------------------------------------
                */

                $return = StockReturn::with('items')
                    ->where('account_id', $accountId)
                    ->lockForUpdate()
                    ->findOrFail($id);

                /*
                |--------------------------------------------------------------------------
                | ALREADY CANCELLED
                |--------------------------------------------------------------------------
                */

                if ((int) $return->status === 0) {

                    throw new \Exception(
                        'Stock Return already cancelled'
                    );
                }

                $stockService = app(\App\Services\StockService::class);

                /*
                |--------------------------------------------------------------------------
                | REVERSE RETURN ITEMS
                |--------------------------------------------------------------------------
                */

                foreach ($return->items as $item) {

                    /*
                    |--------------------------------------------------------------------------
                    | VALIDATE MASTER ITEM
                    |--------------------------------------------------------------------------
                    */

                    if (!$item->master_item_id) {

                        throw new \Exception(
                            'Invalid return item detected'
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | RESTORE PURCHASE TRACKING
                    |--------------------------------------------------------------------------
                    */

                    if (!$item->purchase_item_tracking_id) {

                        throw new \Exception(
                            'Purchase tracking record not found for return item.'
                        );
                    }

                    $tracking = PurchaseItemTracking::query()
                        ->lockForUpdate()
                        ->find($item->purchase_item_tracking_id);

                    if (!$tracking) {

                        throw new \Exception(
                            'Purchase tracking record not found. Tracking ID: '
                            . $item->purchase_item_tracking_id
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | VALIDATE RETURNED QUANTITY
                    |--------------------------------------------------------------------------
                    */

                    $returnedQty = (float) $item->qty;

                    $trackingReturnedQty =
                        (float) ($tracking->returned_quantity ?? 0);

                    /*
                    |--------------------------------------------------------------------------
                    | PREVENT INVALID RESTORATION
                    |--------------------------------------------------------------------------
                    */

                    if ($trackingReturnedQty < $returnedQty) {

                        throw new \Exception(
                            'Invalid returned quantity for tracking ID '
                            . $tracking->id
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | RESTORE TRACKING
                    |--------------------------------------------------------------------------
                    |
                    | Your tracking records represent one physical unit,
                    | therefore quantity remains 1.
                    |
                    | We only reverse returned_quantity and is_sold.
                    |
                    */

                    $newReturnedQty = round(
                        $trackingReturnedQty - $returnedQty,
                        2
                    );

                    $tracking->update([

                        'returned_quantity' =>
                            max(0, $newReturnedQty),

                        'is_sold' =>
                            $newReturnedQty <= 0
                                ? 0
                                : 2,
                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | RESTORE WAREHOUSE STOCK
                    |--------------------------------------------------------------------------
                    */

                    $stockService->moveStock([

                        'account_id' =>
                            $accountId,

                        'warehouse_id' =>
                            $return->warehouse_id,

                        'master_item_id' =>
                            $item->master_item_id,

                        'type' =>
                            'adjustment_add',

                        'qty' =>
                            $returnedQty,

                        'reference_id' =>
                            $return->id,

                        'remarks' =>
                            'Cancel Stock Return #'
                            . $return->return_no,
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | REVERSE VENDOR BALANCE
                |--------------------------------------------------------------------------
                */

                $vendor = Vendor::query()
                    ->where('account_id', $accountId)
                    ->lockForUpdate()
                    ->findOrFail($return->vendor_id);

                $oldBalance = (float) (
                    $vendor->current_balance ?? 0
                );

                $returnTotal = (float) $return->total;

                /*
                |--------------------------------------------------------------------------
                | RESTORE PAYABLE
                |--------------------------------------------------------------------------
                */

                $newBalance = round(
                    $oldBalance + $returnTotal,
                    2
                );

                /*
                |--------------------------------------------------------------------------
                | VENDOR LEDGER
                |--------------------------------------------------------------------------
                */

                VendorLedger::create([

                    'account_id' =>
                        $accountId,

                    'vendor_id' =>
                        $vendor->id,

                    'type' =>
                        6, // Cancel Purchase Return

                    'reference_id' =>
                        $return->id,

                    'debit' =>
                        $returnTotal,

                    'credit' =>
                        0,

                    'balance' =>
                        $newBalance,

                    'remarks' =>
                        'Cancel Stock Return #'
                        . $return->return_no,
                ]);

                /*
                |--------------------------------------------------------------------------
                | UPDATE VENDOR BALANCE
                |--------------------------------------------------------------------------
                */

                $vendor->update([

                    'current_balance' =>
                        $newBalance,
                ]);

                /*
                |--------------------------------------------------------------------------
                | CANCEL RETURN
                |--------------------------------------------------------------------------
                */

                $return->update([

                    'status' =>
                        0,

                    'cancelled_by' =>
                        $userId,

                    'cancelled_at' =>
                        now(),
                ]);
            });

            /*
            |--------------------------------------------------------------------------
            | SUCCESS
            |--------------------------------------------------------------------------
            */

            return response()->json([

                'success' =>
                    true,

                'message' =>
                    __('translation.stock_return_cancelled_successfully'),
            ]);

        } catch (\Throwable $e) {

            report($e);

            return response()->json([

                'success' =>
                    false,

                'message' =>
                    $e->getMessage(),

            ], 500);
        }
    }


    public function cancel_delete(Request $request)
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