<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\PurchaseItem;
// use App\Models\Vendor;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Vendor;
use App\Models\VendorLedger;
use App\Services\StockService;
use App\Helpers\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

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
        $vendors = Vendor::ofAccount()->active()->pluck('name', 'id');
        $warehouses = Warehouse::ofAccount()->active()->pluck('name', 'id');

        $query = Purchase::with(['vendor', 'warehouse'])->ofAccount()->active();

        if ($request->purchase_no) {
            $query->where('purchase_no', 'LIKE', '%' . trim($request->purchase_no) . '%');
        }

        if ($request->vendor_id) {
            $query->where('vendor_id', $request->vendor_id);
        }
        $query = Settings::applyDateRange($query,$request, 'created_at', true); 

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

        $purchases = $query->latest()->paginate(config('constants.pagination'));

        return view('backend.admin.purchase.index', compact('purchases', 'breadcrumb', 'vendors', 'warehouses', 'date'));
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        $vendors = Vendor::ofAccount()->active()->pluck('name', 'id');
        $warehouses = Warehouse::ofAccount()->active()->pluck('name', 'id');
        
        $products = Product::ofAccount()->active()->pluck('name', 'id');

        return view('backend.admin.purchase.form', [
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
    */

    public function store(Request $request)
    {
        try {

            $validated = $request->validate([
                'vendor_id' => 'required|exists:vendors,id',
                'warehouse_id' => 'required|exists:warehouses,id',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.qty' => 'required|integer|min:1',
                'items.*.price' => 'required|numeric|min:0',
                ]);

            } catch (\Illuminate\Validation\ValidationException $e) {
                return Settings::roleRedirect('purchases.index', $e->getMessage(), 'error');
            }

            try {

                DB::transaction(function () use ($validated) {

                    $accountId = auth()->user()->account_id;

                    // ✅ Generate Purchase No
                    $purchaseNo = 'PUR-' . date('Ymd') . '-' . rand(1000, 9999);

                    // ✅ Calculate total from backend (IMPORTANT)
                    $totalAmount = 0;
                    foreach ($validated['items'] as $item) {
                        $totalAmount += ($item['qty'] * $item['price']);
                    }

                    // ✅ Create Purchase
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

                    // ✅ Insert Items + Stock
                    foreach ($validated['items'] as $item) {

                        $qty = $item['qty'];
                        $price = $item['price'];

                        PurchaseItem::create([
                            'purchase_id' => $purchase->id,
                            'product_id' => $item['product_id'],
                            'quantity' => $qty,
                            'cost_price' => $price,
                            'total' => $qty * $price
                        ]);

                        // ✅ STOCK IN
                        $stockService->moveStock([
                            'account_id' => $accountId,
                            'warehouse_id' => $validated['warehouse_id'],
                            'product_id' => $item['product_id'],
                            'type' => 2, /// purchase coming from types table 
                            'qty' => $qty,
                            'reference_id' => $purchase->id,
                            'remarks' => 'Purchase Entry #' . $purchaseNo
                        ]);
                    }

                    // ============================
                    // ✅ VENDOR BALANCE LOGIC
                    // ============================

                    $vendor = Vendor::lockForUpdate()->find($validated['vendor_id']);

                    $oldBalance = $vendor->current_balance ?? 0;

                    // Purchase = Debit (you owe vendor more)
                    $newBalance = $oldBalance + $totalAmount;

                    // ✅ Ledger Entry
                    VendorLedger::create([
                        'account_id' => $accountId,
                        'vendor_id' => $vendor->id,
                        'type' => 2, /// purchase coming from types table 
                        'reference_id' => $purchase->id,
                        'debit' => $totalAmount,
                        'credit' => 0,
                        'balance' => $newBalance,
                        'remarks' => 'Purchase #' . $purchaseNo
                    ]);

                    // ✅ Update Vendor Current Balance
                    $vendor->update([
                        'current_balance' => $newBalance
                    ]);
                });

                return Settings::roleRedirect('purchases.index', 'Purchase Created Successfully.');

            } catch (\Exception $e) {

                return Settings::roleRedirect('purchases.index', $e->getMessage(), 'error');
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

            $purchase = Purchase::with(['items.product', 'vendor', 'warehouse'])
                ->where('account_id', auth()->user()->account_id)
                ->findOrFail($id);

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

                if ($purchase->status == 0) {
                    throw new \Exception('Purchase already cancelled');
                }

                $stockService = app(StockService::class);

                // =========================
                // 1. REVERSE STOCK
                // =========================
                foreach ($purchase->items as $item) {
                    $stockService->moveStock([
                        'account_id' => $purchase->account_id,
                        'warehouse_id' => $purchase->warehouse_id,
                        'product_id' => $item->product_id,
                        'type' => 3,
                        'qty' => -$item->quantity, // 🔴 reverse
                        'reference_id' => $purchase->id,
                        'remarks' => 'Cancel Purchase #' . $purchase->purchase_no
                    ]);
                }

                // =========================
                // 2. UPDATE VENDOR BALANCE
                // =========================
                $vendor = Vendor::lockForUpdate()->findOrFail($purchase->vendor_id);

                $oldBalance = $vendor->current_balance ?? 0;
                $newBalance = $oldBalance - $purchase->total;

                $vendor->update([
                    'current_balance' => $newBalance
                ]);

                // =========================
                // 3. LEDGER ENTRY (REVERSAL)
                // =========================
                VendorLedger::create([
                    'account_id' => $purchase->account_id,
                    'vendor_id' => $vendor->id,
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
}