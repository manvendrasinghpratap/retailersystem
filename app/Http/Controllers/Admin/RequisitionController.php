<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\ProductStock;
use App\Services\StockService;
use App\Helpers\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RequisitionController extends Controller
{
    protected $breadcrumb;
    protected $breadcrumbListing;

    public function __construct()
    {
        $this->middleware('auth');

        $this->breadcrumb = [
            'title' => 'Requisitions',
            'breadcrumb' => [
                ['route' => 'admin.dashboard', 'title' => 'Dashboard'],
                ['route' => 'admin.requisitions.index', 'title' => 'Requisitions'],
                ['route' => 'admin.requisitions.create', 'title' => 'Create Requisition'],
            ],
            'route1' => 'admin.requisitions.create',
            'route1Title' => 'Add Requisition',
            'route2' => 'admin.requisitions.index',
            'route2Title' => 'Requisition List',
        ];

        $this->breadcrumbListing = [
            'title' => 'Requisitions',
            'breadcrumb' => [
                ['route' => 'admin.dashboard', 'title' => 'Dashboard'],
                ['route' => 'admin.requisitions.index', 'title' => 'Requisitions'],
            ],
            'route1' => 'admin.requisitions.create',
            'route1Title' => 'Add Requisition',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | LIST
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $breadcrumb = $this->breadcrumb;
        $warehouses = Warehouse::ofAccount()->active()->pluck('name', 'id');

        $requisitions = Requisition::with(['fromWarehouse','toWarehouse'])
            ->where('account_id', auth()->user()->account_id)
            ->latest();

        // 🔍 Filters
        if ($request->filled('requisition_no')) {
            $requisitions->where('requisition_no', 'LIKE', '%' . trim($request->requisition_no) . '%');
        }

        if ($request->filled('from_warehouse_id')) {
            $requisitions->where('from_warehouse_id', $request->from_warehouse_id);
        }

        if ($request->filled('to_warehouse_id')) {
            $requisitions->where('to_warehouse_id', $request->to_warehouse_id);
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $from = date('Y-m-d', strtotime($request->from_date));
            $to   = date('Y-m-d', strtotime($request->to_date));

            $requisitions->whereBetween('date', [$from, $to]);
        }

        $requisitions = $requisitions->paginate(config('constants.pagination'));

        return view('backend.admin.requisition.index', compact('requisitions', 'breadcrumb', 'warehouses'));
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        $warehouses = Warehouse::where('account_id', auth()->user()->account_id)
            ->pluck('name','id');

        return view('backend.admin.requisition.form', [
            'breadcrumb' => $this->breadcrumb,
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
        $request->validate([
            'from_warehouse_id' => 'required|different:to_warehouse_id',
            'to_warehouse_id'   => 'required',
            'items'             => 'required|array|min:1',
            'items.*.product_id'=> 'required',
            'items.*.qty'       => 'required|numeric|min:1',
        ]);

        try {

            DB::transaction(function () use ($request) {

                $accountId = auth()->user()->account_id;

                $requisitionNo = 'REQ-' . date('Ymd') . '-' . rand(1000,9999);

                $totalQty = collect($request->items)->sum('qty');

                // ✅ Create requisition
                $req = Requisition::create([
                    'account_id'         => $accountId,
                    'from_warehouse_id' => $request->from_warehouse_id,
                    'to_warehouse_id'   => $request->to_warehouse_id,
                    'requisition_no'    => $requisitionNo,
                    'date'              => now(),
                    'total_qty'         => $totalQty,
                    'created_by'        => auth()->id(),
                ]);

                $stockService = app(StockService::class);

                foreach ($request->items as $item) {

                    // 🔒 lock stock
                    $stock = ProductStock::where([
                        'account_id'   => $accountId,
                        'warehouse_id' => $request->from_warehouse_id,
                        'product_id'   => $item['product_id']
                    ])->lockForUpdate()->first();

                    if (!$stock || $stock->stock < $item['qty']) {
                        throw new \Exception('Insufficient stock');
                    }

                    // Save item
                    RequisitionItem::create([
                        'requisition_id' => $req->id,
                        'product_id'     => $item['product_id'],
                        'qty'            => $item['qty']
                    ]);

                    // ➖ Deduct from source warehouse
                    $stockService->moveStock([
                        'account_id'   => $accountId,
                        'warehouse_id' => $request->from_warehouse_id,
                        'product_id'   => $item['product_id'],
                        'type'         => 'transfer_out',
                        'qty'          => $item['qty'],
                        'reference_id' => $req->id,
                        'remarks'      => 'Requisition OUT #' . $requisitionNo
                    ]);

                    // ➕ Add to destination warehouse
                    $stockService->moveStock([
                        'account_id'   => $accountId,
                        'warehouse_id' => $request->to_warehouse_id,
                        'product_id'   => $item['product_id'],
                        'type'         => 'transfer_in',
                        'qty'          => $item['qty'],
                        'reference_id' => $req->id,
                        'remarks'      => 'Requisition IN #' . $requisitionNo
                    ]);
                }
            });

            return Settings::roleRedirect('requisitions.index', 'Requisition Created Successfully');

        } catch (\Exception $e) {
            return Settings::roleRedirect('requisitions.index', $e->getMessage(), 'error');
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

        $requisition = Requisition::with(['items.product','fromWarehouse','toWarehouse'])
            ->where('account_id', auth()->user()->account_id)
            ->findOrFail($id);

        return view('backend.admin.requisition.view', compact('requisition'));
    }

    /*
    |--------------------------------------------------------------------------
    | CANCEL
    |--------------------------------------------------------------------------
    */
    public function cancel(Request $request)
    {
        try {

            $id = Settings::getDecodeCode($request->id);

            DB::transaction(function () use ($id) {

                $accountId = auth()->user()->account_id;

                $req = Requisition::with('items')
                    ->where('account_id', $accountId)
                    ->lockForUpdate()
                    ->findOrFail($id);

                if ($req->status == 0) {
                    throw new \Exception('Already cancelled');
                }

                $stockService = app(StockService::class);

                foreach ($req->items as $item) {

                    // 🔁 reverse stock

                    // add back to source
                    $stockService->moveStock([
                        'account_id'   => $accountId,
                        'warehouse_id' => $req->from_warehouse_id,
                        'product_id'   => $item->product_id,
                        'type'         => 'transfer_in',
                        'qty'          => $item->qty,
                        'reference_id' => $req->id,
                        'remarks'      => 'Cancel Requisition'
                    ]);

                    // remove from destination
                    $stockService->moveStock([
                        'account_id'   => $accountId,
                        'warehouse_id' => $req->to_warehouse_id,
                        'product_id'   => $item->product_id,
                        'type'         => 'transfer_out',
                        'qty'          => $item->qty,
                        'reference_id' => $req->id,
                        'remarks'      => 'Cancel Requisition'
                    ]);
                }

                $req->update(['status' => 0]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Requisition cancelled'
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function viewAjax($id)
    {
        $id = Settings::getDecodeCode($id);

        $requisition = Requisition::with([
            'items.product',
            'fromWarehouse',
            'toWarehouse'
        ])
        ->where('account_id', auth()->user()->account_id)
        ->findOrFail($id);

        return view('backend.admin.requisition._view', compact('requisition'));
    }
}