<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Warehouse;
use App\Models\Vendor;
use App\Models\VendorLedger;
use App\Services\StockService;
use App\Helpers\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\PurchaseItemTracking;


class PurchaseController extends Controller
{
    protected $breadcrumb;
    protected $vendors;
    protected $warehouses;

    public function __construct()
    {
        $this->middleware('auth');

        $this->breadcrumb = [
            'title' => __('translation.purchases'),
            'breadcrumb' => [
                ['route' => 'admin.dashboard', 'title' => __('translation.dashboard')],
                ['route' => 'admin.purchases.index', 'title' => __('translation.purchases')],
                ['route' => 'admin.purchases.create', 'title' => __('translation.add_new_purchase')],
                ['route' => 'admin.purchases.purchase-barcodes', 'title' => __('translation.barcodePrint')],
            ],
            'route1' => 'admin.purchases.create',
            'route1Title' => __('translation.add_new_purchase'),
            'route2' => 'admin.purchases.index',
            'route2Title' => __('translation.purchase_list'),
        ];

    }

    /*
    |--------------------------------------------------------------------------
    | LISTING
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $date = date('Y-m-d');
        $breadcrumb = $this->breadcrumb;
        $vendors = Vendor::ofAccount()->active()->orderBy('company_name', 'asc')->pluck('company_name', 'id');
        $warehouses = Warehouse::ofAccount()->active()->orderBy('name', 'asc')->pluck('name', 'id');

        $query = Purchase::with(['vendor', 'warehouse'])->ofAccount()->active();

        if ($request->purchase_no) {
            $query->where('purchase_no', 'LIKE', '%' . trim($request->purchase_no) . '%');
        }
        if ($request->warehouse_id) {
            $query->where('warehouse_id', $request->warehouse_id);
        }
        if ($request->vendor_id) {
            $query->where('vendor_id', $request->vendor_id);
        }
        $query = Settings::applyDateRange($query, $request, 'created_at', true);

        // PDF Export
        if ($request->has('pdf')) {
            $data = $query->get();
            $pdf = Pdf::loadView('backend.pdf.purchases.purchaseList', compact('data', 'breadcrumb'));
            return Settings::downloadpdf($pdf)->stream('purchase-list.pdf');
        }

        // CSV Export
        if ($request->has('csv')) {
            $data = $query->get();

            $rows[] = ['#', 'Purchase No', 'Vendor', 'Warehouse', 'Total', 'Date'];

            foreach ($data as $i => $row) {
                $rows[] = [
                    $i + 1,
                    $row->purchase_no,
                    $row->vendor->name ?? '',
                    $row->warehouse->name ?? '',
                    $row->total,
                    $row->created_at
                ];
            }

            return Settings::downloadcsvfile($rows, 'purchase-list.csv');
        }

        $purchases = $query->latest()->paginate(account_setting('general.pagination'));

        return view('backend.admin.purchase.index', compact('purchases', 'breadcrumb', 'vendors', 'warehouses', 'date'));
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        $breadcrumb = $this->breadcrumb;
        $vendors = Vendor::ofAccount()->active()->orderBy('name', 'asc')->pluck('name', 'id');
        $warehouses = Warehouse::ofAccount()->active()->orderBy('name', 'asc')->pluck('name', 'id');
        return view('backend.admin.purchase.form', compact('vendors', 'warehouses', 'breadcrumb'));
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        // echo '<pre>';
        // print_r($request->all());
        // echo '</pre>';
        // exit;
        try {

            // ============================
            // ✅ VALIDATION
            // ============================
            $validated = $request->validate([
                'vendor_id' => 'required|exists:vendors,id',
                'warehouse_id' => 'required|exists:warehouses,id',

                'items' => 'required|array|min:1',

                'items.*.product_id' => 'required|exists:master_items,id',
                'items.*.qty' => 'required|numeric|min:1',
                'items.*.price' => 'required|numeric|min:0',

                // NEW
                'items.*.tracking_type' => 'nullable|in:none,batch,individual',
                'items.*.trackings' => 'nullable|array',
                'items.*.trackings.*.barcode' => 'required_with:items.*.trackings',
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {

            return Settings::roleRedirect('purchases.index', $e->getMessage(), 'error');
        }

        try {

            DB::transaction(function () use ($validated) {

                $accountId = auth()->user()->account_id;

                // ============================
                // ✅ GENERATE PURCHASE NO
                // ============================
                $purchaseNo = 'PUR-' . date('Ymd') . '-' . rand(1000, 9999);

                // ============================
                // ✅ CALCULATE TOTAL
                // ============================
                $totalAmount = 0;

                foreach ($validated['items'] as $item) {
                    $totalAmount += ((float) $item['qty'] * (float) $item['price']);
                }

                // ============================
                // ✅ CREATE PURCHASE
                // ============================
                $purchase = Purchase::create([
                    'account_id' => $accountId,
                    'vendor_id' => $validated['vendor_id'],
                    'warehouse_id' => $validated['warehouse_id'],
                    'purchase_no' => $purchaseNo,
                    'total' => $totalAmount,
                    'status' => 1,
                    'created_by' => auth()->id()
                ]);

                $stockService = app(StockService::class);

                // ============================
                // ✅ SAVE ITEMS + STOCK IN
                // ============================
                foreach ($validated['items'] as $item) {

                    $masterItemId = $item['product_id'];
                    $qty = (float) $item['qty'];
                    $price = (float) $item['price'];

                    $purchaseItem = PurchaseItem::create([
                        'purchase_id' => $purchase->id,
                        'master_item_id' => $masterItemId, // ✅ IMPORTANT CHANGE
                        'quantity' => $qty,
                        'cost_price' => $price,
                        'total' => $qty * $price,
                        'tracking_type' => $item['tracking_type'] ?? 'none',
                    ]);

                    // ===========================
                    // SAVE TRACKING
                    // ===========================
                    $this->savePurchaseTracking($purchaseItem, $item, $qty);

                    // if (!empty($item['trackings']) && is_array($item['trackings'])) {

                    //     foreach ($item['trackings'] as $tracking) {

                    //         PurchaseItemTracking::create([

                    //             'purchase_item_id' => $purchaseItem->id,

                    //             'barcode' => $tracking['barcode'],

                    //             'batch_no' => $tracking['batch_no'] ?? null,

                    //             'serial_no' => $tracking['serial_no'] ?? null,

                    //             'expiry_date' => $tracking['expiry_date'] ?? null,

                    //         ]);
                    //         PurchaseItem::where('id', $purchaseItem->id)->update(['tracking_type' => $item['tracking_type'] ?? 'none']);
                    //     }

                    // }

                    // ✅ STOCK IN
                    $stockService->moveStock([
                        'account_id' => $accountId,
                        'warehouse_id' => $validated['warehouse_id'],
                        'product_id' => $masterItemId, // (can rename later internally)
                        'type' => 2, // Purchase IN
                        'qty' => $qty,
                        'reference_id' => $purchase->id,
                        'remarks' => 'Purchase Entry #' . $purchaseNo
                    ]);
                }

                // ============================
                // ✅ VENDOR BALANCE UPDATE
                // ============================
                $vendor = Vendor::lockForUpdate()->findOrFail($validated['vendor_id']);

                $oldBalance = (float) ($vendor->current_balance ?? 0);
                $newBalance = $oldBalance + $totalAmount;

                // ✅ Ledger Entry
                VendorLedger::create([
                    'account_id' => $accountId,
                    'vendor_id' => $vendor->id,
                    'type' => 2,
                    'reference_id' => $purchase->id,
                    'debit' => $totalAmount,
                    'credit' => 0,
                    'balance' => $newBalance,
                    'remarks' => 'Purchase #' . $purchaseNo
                ]);

                // ✅ Update Vendor
                $vendor->update([
                    'current_balance' => $newBalance
                ]);
            });

            return Settings::roleRedirect('purchases.index', 'Purchase Created Successfully.');

        } catch (\Exception $e) {

            return Settings::roleRedirect('purchases.index', $e->getMessage(), 'error');
        }
    }



    /**
     * Save Purchase Item Tracking
     */
    private function savePurchaseTracking(PurchaseItem $purchaseItem, array $item, int $qty): void
    {
        $trackingType = $item['tracking_type'] ?? 'none';

        switch ($trackingType) {
            /*
            |--------------------------------------------------------------------------
            | Individual
            |--------------------------------------------------------------------------
            */
            case 'individual':

                foreach ($item['trackings'] ?? [] as $tracking) {

                    PurchaseItemTracking::create([
                        'purchase_item_id' => $purchaseItem->id,
                        'barcode' => $tracking['barcode'],
                        'tracking_type' => 'individual',
                        'batch_no' => null,
                        'serial_no' => null,
                        'expiry_date' => null,
                    ]);
                }

                break;

            /*
            |--------------------------------------------------------------------------
            | Batch
            |--------------------------------------------------------------------------
            */
            case 'batch':

                $barcode = $item['trackings'][0]['barcode'] ?? Settings::generateEan13();

                for ($i = 0; $i < $qty; $i++) {

                    PurchaseItemTracking::create([
                        'purchase_item_id' => $purchaseItem->id,
                        'barcode' => $barcode,
                        'tracking_type' => 'batch',
                        'batch_no' => null,
                        'serial_no' => null,
                        'expiry_date' => null,
                    ]);
                }

                break;

            /*
            |--------------------------------------------------------------------------
            | None
            |--------------------------------------------------------------------------
            */
            default:

                for ($i = 0; $i < $qty; $i++) {
                    do {
                        $barcode = Settings::generateEan13();
                    } while (PurchaseItemTracking::where('barcode', $barcode)->exists());

                    PurchaseItemTracking::create([
                        'purchase_item_id' => $purchaseItem->id,
                        'barcode' => $barcode,
                        'tracking_type' => 'none',
                        'batch_no' => null,
                        'serial_no' => null,
                        'expiry_date' => null,
                    ]);
                }

                break;
        }
    }
    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        $id = Settings::getDecodeCode($id);

        $purchase = Purchase::with(['items.product', 'vendor', 'warehouse'])
            ->where('account_id', auth()->user()->account_id)
            ->findOrFail($id);

        return view('backend.admin.purchase.view', compact('purchase'));
    }
    /*
    |--------------------------------------------------------------------------
    | VIEW AJAX
    |--------------------------------------------------------------------------
    */

    public function viewAjax($id)
    {
        $id = Settings::getDecodeCode($id);

        $purchase = Purchase::with(['items.product', 'vendor', 'warehouse'])->ofAccount()->findOrFail($id);
        return view('backend.admin.purchase._view', compact('purchase'));
    }

    /*
    |--------------------------------------------------------------------------
    | SOFT DELETE
    |--------------------------------------------------------------------------
    */
    public function softdelete(Request $request)
    {
        try {

            $id = Settings::getDecodeCode($request->id);

            $deleted = Purchase::where('account_id', auth()->user()->account_id)
                ->where('id', $id)
                ->update([
                    'status' => 0,
                    'updated_by' => auth()->id()
                ]);

            return response()->json(['success' => $deleted ? true : false]);

        } catch (\Exception $e) {
            return response()->json(['success' => false], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | STATUS UPDATE
    |--------------------------------------------------------------------------
    */
    public function statusUpdate(Request $request)
    {
        try {

            $id = Settings::getDecodeCode($request->id);

            $updated = Purchase::where('account_id', auth()->user()->account_id)
                ->where('id', $id)
                ->update(['status' => $request->status]);

            return response()->json(['success' => $updated ? true : false]);

        } catch (\Exception $e) {
            return response()->json(['success' => false], 500);
        }
    }

    /**
     * Update Purchase Item Tracking Status
     *
     * @param int $purchaseId
     * @param int $status
     * @return void
     */
    private function updatePurchaseTrackingStatus(int $purchaseId, int $status): void
    {
        PurchaseItemTracking::whereHas('purchaseItem', function ($query) use ($purchaseId) {
            $query->where('purchase_id', $purchaseId);
        })->update(['status' => $status]);
    }
    /*
    |--------------------------------------------------------------------------
    | destroy and restore all the function
    |--------------------------------------------------------------------------
    */
    public function destroy(Request $request)
    {
        try {

            $id = Settings::getDecodeCode($request->id);

            DB::transaction(function () use ($id) {
                $purchase = Purchase::with('items')->findOrFail($id);

                // Already cancelled
                if ($purchase->status == 0) {
                    throw new \Exception(__('translation.purchase_already_cancelled'));
                }

                $stockService = app(StockService::class);

                /*
                |--------------------------------------------------------------------------
                | Reverse Stock
                |--------------------------------------------------------------------------
                */
                foreach ($purchase->items as $item) {

                    $stockService->moveStock([
                        'account_id' => $purchase->account_id,
                        'warehouse_id' => $purchase->warehouse_id,
                        'master_item_id' => $item->master_item_id,
                        'type' => 3,
                        'qty' => -$item->quantity,
                        'reference_id' => $purchase->id,
                        'remarks' => 'Cancel Purchase #' . $purchase->purchase_no,
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Update Vendor Balance
                |--------------------------------------------------------------------------
                */
                $vendor = Vendor::lockForUpdate()->findOrFail($purchase->vendor_id);

                $newBalance = (float) $vendor->current_balance - (float) $purchase->total;

                $vendor->update([
                    'current_balance' => $newBalance
                ]);

                /*
                |--------------------------------------------------------------------------
                | Vendor Ledger
                |--------------------------------------------------------------------------
                */
                VendorLedger::create([
                    'account_id' => $purchase->account_id,
                    'vendor_id' => $vendor->id,
                    'type' => 3,
                    'reference_id' => $purchase->id,
                    'debit' => 0,
                    'credit' => $purchase->total,
                    'balance' => $newBalance,
                    'remarks' => 'Cancel Purchase #' . $purchase->purchase_no,
                ]);

                /*
                |--------------------------------------------------------------------------
                | Cancel Purchase
                |--------------------------------------------------------------------------
                */
                $purchase->update([
                    'status' => 0
                ]);

                /*
                |--------------------------------------------------------------------------
                | Disable all Barcodes of this Purchase
                |--------------------------------------------------------------------------
                */
                $this->updatePurchaseTrackingStatus($purchase->id, 0);

            });

            return response()->json([
                'success' => true,
                'message' => __('translation.purchase_cancelled_successfully')
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function destroy_delete(Request $request)
    {
        try {
            $id = Settings::getDecodeCode($request->id);
            DB::transaction(function () use ($id) {
                $purchase = Purchase::with('items')->findOrFail($id);
                $this->updatePurchaseTrackingStatus($purchase->id, 0);
                // ❌ Already cancelled
                if ($purchase->status == 0) {
                    throw new \Exception('Purchase already cancelled');
                }
                $stockService = app(StockService::class);
                // =========================
                // 1. REVERSE STOCK (CORRECT)
                // =========================
                foreach ($purchase->items as $item) {
                    $stockService->moveStock([
                        'account_id' => $purchase->account_id,
                        'warehouse_id' => $purchase->warehouse_id,
                        'master_item_id' => $item->master_item_id,
                        'type' => 3,
                        'qty' => -$item->quantity,
                        'reference_id' => $purchase->id,
                        'remarks' => 'Cancel Purchase #' . $purchase->purchase_no
                    ]);
                }

                // =========================
                // 2. UPDATE VENDOR BALANCE
                // =========================
                $vendor = Vendor::lockForUpdate()->findOrFail($purchase->vendor_id);

                $oldBalance = (float) ($vendor->current_balance ?? 0);
                $newBalance = $oldBalance - (float) $purchase->total;

                $vendor->update([
                    'current_balance' => $newBalance
                ]);

                // =========================
                // 3. LEDGER ENTRY (REVERSAL)
                // =========================
                VendorLedger::create([
                    'account_id' => $purchase->account_id,
                    'vendor_id' => $vendor->id,

                    // ✅ correct type
                    'type' => 3, /// purchase cancel coming from types table purshase_cancel type id is 3

                    'reference_id' => $purchase->id,
                    'debit' => 0,
                    'credit' => $purchase->total,
                    'balance' => $newBalance,
                    'remarks' => 'Cancel Purchase #' . $purchase->purchase_no
                ]);

                // =========================
                // 4. MARK AS CANCELLED
                // =========================
                $purchase->update([
                    'status' => 0
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => __('translation.purchase_cancelled_successfully')
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function printBarcode($id)
    {
        $purchaseId = Settings::getDecodeCode($id);
        $breadcrumb = $this->breadcrumb;
        $purchase = Purchase::with([
            'vendor',
            'warehouse',
            'items.masterItem',
            'items.trackings' => function ($q) {
                $q->status()->notSold();
            }
        ])->findOrFail($purchaseId);
        return view('backend.admin.purchase.printBarcode', compact('purchase', 'breadcrumb'));
    }


    public function barcodePreview(Request $request, $purchase)
    {
        $purchaseId = Settings::getDecodeCode($purchase);
        $purchase = Purchase::with([
            'items.masterItem',
            'items.trackings' => function ($q) {
                $q->status()->notSold();
            }
        ])->findOrFail($purchaseId);
        $pdfHeaderdata = \Config::get('constants.barcodePdf');
        $pdf = PDF::loadView('backend.pdf.barcodes.preview', [
            'purchase' => $purchase,
            'selectedItems' => $request->items ?? [],
            'printQty' => $request->print_qty ?? [],
            'copies' => (int) ($request->copies ?? 1),
            'pdfHeaderdata' => $pdfHeaderdata,
        ]);
        $pdf = Settings::downloadpdf($pdf);
        $fileName = $pdfHeaderdata['filename'] . '-' . date('Y-m-d') . '.pdf';
        return $pdf->stream($fileName);
    }

    public function purchaseBarcodes(Request $request)
    {
        $today = date('Y-m-d');
        $breadcrumb = $this->breadcrumb;
        $breadcrumb['title'] = __('translation.barcodes');
        $breadcrumb['route2'] = 'admin.purchases.purchase-barcodes';
        $breadcrumb['backroute'] = 'admin.purchases.purchase-barcodes';
        $vendors = Vendor::ofAccount()->active()->orderBy('company_name')->pluck('company_name', 'id');
        $warehouses = Warehouse::ofAccount()->active()->orderBy('name')->pluck('name', 'id');
        $query = PurchaseItem::with([
            'purchase.vendor',
            'purchase.warehouse',
            'masterItem',
            'trackings'
        ])->whereHas('purchase', function ($q) use ($request, $today) {
            $q->ofAccount();
            if ($request->filled('vendor_id')) {
                $q->where('vendor_id', $request->vendor_id);
            }
            if ($request->filled('warehouse_id')) {
                $q->where('warehouse_id', $request->warehouse_id);
            }
            if ($request->filled('purchase_no')) {
                $q->where('purchase_no', 'like', '%' . $request->purchase_no . '%');
            }
            if ($request->filled('from_date')) {
                $q->whereDate('created_at', '>=', Settings::formatDate($request->from_date, 'Y-m-d'));
            } else {
                $q->whereDate('created_at', '>=', $today);
            }
            if ($request->filled('to_date')) {
                $q->whereDate('created_at', '<=', Settings::formatDate($request->to_date, 'Y-m-d'));
            } else {
                $q->whereDate('created_at', '<=', $today);
            }
            $q->ofAccount()->ofActiveStatus();
        });

        $items = $query->latest()->paginate(account_setting('general.pagination'));
        return view(
            'backend.admin.purchase.purchase_barcodes.index',
            compact('breadcrumb', 'items', 'vendors', 'warehouses', 'today')
        );
    }
}