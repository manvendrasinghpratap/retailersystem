<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\Warehouse;
use App\Models\MasterItem;
use App\Models\ProductStock;
use App\Services\StockService;
use App\Helpers\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Store;
use PDF;
class RequisitionController extends Controller
{
    protected $breadcrumb;
    protected $breadcrumbPendingPosting;

    public function __construct()
    {
        $this->middleware('auth');

        $this->breadcrumb = [
            'title' => 'Requisitions',
            'breadcrumb' => [
                [
                    'route' => 'admin.dashboard',
                    'title' => 'Dashboard'
                ],
                [
                    'route' => 'admin.requisitions.index',
                    'title' => 'Requisitions'
                ],
                [
                    'route' => 'admin.requisitions.create',
                    'title' => trans('translation.create_requisition')
                ],
            ],
            'route1' => 'admin.requisitions.create',
            'route1Title' => trans('translation.add_requisition'),
            'route2' => 'admin.requisitions.index',
            'route2Title' => trans('translation.requisitions'),
        ];

        $this->breadcrumbPendingPosting = [
            'title' => trans('translation.pending_posting'),
            'breadcrumb' => [
                [
                    'route' => 'admin.dashboard',
                    'title' => trans('translation.dashboard')
                ],
                [
                    'route' => 'admin.requisitions.pending.posting',
                    'title' => trans('translation.pending_posting')
                ],
            ],

            'route1' => 'admin.requisitions.index',
            'route1Title' => trans('translation.requisition_list'),

            'route2' => 'admin.requisitions.pending.posting',
            'route2Title' => trans('translation.pending_posting'),
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
        $warehouses = Warehouse::ofAccount()->active()->orderBy('name', 'asc')->pluck('name', 'id');
        $stores = Store::ofAccount()->active()->orderBy('name', 'asc')->pluck('name', 'id');
        $requisitions = Requisition::with(['fromWarehouse', 'toWarehouse'])->where('account_id', auth()->user()->account_id)->latest();
        // =========================
        // FILTERS
        // =========================
        if ($request->filled('requisition_no')) {
            $requisitions->where('requisition_no', 'LIKE', '%' . trim($request->requisition_no) . '%');
        }
        if ($request->filled('from_warehouse_id')) {
            $requisitions->where('from_warehouse_id', $request->from_warehouse_id);
        }
        if ($request->filled('for_store_id')) {
            $requisitions->where('for_store_id', $request->for_store_id);
        }
        if ($request->filled('status')) {
            $requisitions->where('status', $request->status);
        }
        $requisitions = Settings::applyDateRange($requisitions, $request, 'created_at', true);
        if ($request->has('pdf')) {
            $requisitions = $requisitions->get();
            $pdfHeaderdata = \Config::get('constants.requisitionListpdf');
            $pdf = PDF::loadView('backend.pdf.requisitions.requisitionListpdf', compact('requisitions', 'pdfHeaderdata', 'breadcrumb'));
            $pdf = Settings::downloadpdf($pdf);
            $fileName = $pdfHeaderdata['filename'] . '-' . date('Y-m-d') . '.pdf';
            return $pdf->stream($fileName);
        }
        if ($request->has('csv')) {
            $requisitions = $requisitions->get();
            $csvHeaderdata = \Config::get('constants.requisitionListpdf');
            $fileName = $csvHeaderdata['filename'] . '-' . date('Y-m-d') . '.csv';
            $data = [];
            $ii = $i = 0;
            // ✅ Header Row
            $data[$ii] = [
                '#',
                __('translation.requisition_no'),
                __('translation.from_warehouse'),
                __('translation.for_store'),
                __('translation.total_qty'),
                __('translation.status'),
                __('translation.requester'),
                __('translation.createdat'),
            ];

            foreach ($requisitions as $requisition) {
                $data[++$ii] = [
                    $ii,
                    $requisition->requisition_no,
                    $requisition->fromWarehouse->name ?? '-',
                    $requisition->store->name ?? '-',
                    $requisition->total_qty,
                    match ($requisition->status) {
                        3 => __('translation.moved_to_store'),
                        2 => __('translation.partial_to_store'),
                        1 => __('translation.active'),
                        default => __('translation.cancelled'),
                    },
                    $requisition->creator->name ?? '-',
                    !empty($requisition->created_at) ? "\t" . Settings::getFormattedDatetime($requisition->created_at) : '-',
                ];
            }
            return Settings::downloadcsvfile($data, $fileName);
        }
        $requisitions = $requisitions->paginate(account_setting('general.pagination'))->withQueryString();
        return view('backend.admin.requisition.index', compact('requisitions', 'breadcrumb', 'warehouses', 'stores'));
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
    */

    public function create()
    {
        $warehouses = Warehouse::ofAccount()->active()->orderBy('name', 'asc')->pluck('name', 'id');
        $stores = Store::ofAccount()->active()->orderBy('name', 'asc')->pluck('name', 'id');
        return view('backend.admin.requisition.form', [
            'breadcrumb' => $this->breadcrumb,
            'warehouses' => $warehouses,
            'stores' => $stores,
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
                'from_warehouse_id' => 'required|exists:warehouses,id',
                'for_store_id' => 'required|exists:stores,id',
                'date' => 'required',
                'items' => 'required|array|min:1',
                'items.*.master_item_id' => 'required|exists:master_items,id',
                'items.*.qty' => 'required|numeric|min:1',
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {

            return Settings::roleRedirect(
                'requisitions.index',
                $e->validator->errors()->first(),
                'error'
            );
        }

        try {

            DB::transaction(function () use ($validated) {

                $accountId = auth()->user()->account_id;

                $requisitionNo = 'REQ-' . date('Ymd') . '-' . rand(1000, 9999);

                $totalQty = collect($validated['items'])->sum(function ($item) {
                    return (float) $item['qty'];
                });

                // =========================
                // CREATE REQUISITION
                // =========================

                $req = Requisition::create([
                    'account_id' => $accountId,
                    'from_warehouse_id' => $validated['from_warehouse_id'],
                    'for_store_id' => $validated['for_store_id'],
                    'requisition_no' => $requisitionNo,
                    'date' => Settings::formatDate(
                        $validated['date'],
                        'Y-m-d'
                    ),
                    'total_qty' => $totalQty,
                    'status' => 1,
                    'created_by' => auth()->id(),
                ]);

                $stockService = app(StockService::class);

                // =========================
                // VALIDATE STOCK
                // =========================

                foreach ($validated['items'] as $item) {

                    $stock = ProductStock::where([
                        'account_id' => $accountId,
                        'warehouse_id' => $validated['from_warehouse_id'],
                        'master_item_id' => $item['master_item_id']
                    ])
                        ->lockForUpdate()
                        ->first();

                    if (!$stock) {
                        throw new \Exception('Item stock not found');
                    }

                    if ((float) $stock->stock < (float) $item['qty']) {
                        throw new \Exception(
                            'Insufficient stock for selected item'
                        );
                    }
                }

                // =========================
                // PROCESS ITEMS
                // =========================

                foreach ($validated['items'] as $item) {

                    $qty = (float) $item['qty'];

                    // SAVE ITEM
                    RequisitionItem::create([
                        'requisition_id' => $req->id,
                        'master_item_id' => $item['master_item_id'],
                        'qty' => $qty
                    ]);

                    // =========================
                    // STOCK OUT FROM WAREHOUSE
                    // =========================

                    $stockService->moveStock([
                        'account_id' => $accountId,
                        'warehouse_id' => $validated['from_warehouse_id'],
                        'master_item_id' => $item['master_item_id'],
                        'type' => 'transfer_out',
                        'qty' => $qty,
                        'reference_id' => $req->id,
                        'remarks' => 'Requisition OUT #' . $requisitionNo
                    ]);
                }
            });

            return Settings::roleRedirect(
                'requisitions.index',
                'Requisition Created Successfully'
            );

        } catch (\Exception $e) {

            return Settings::roleRedirect(
                'requisitions.index',
                $e->getMessage(),
                'error'
            );
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

        $requisition = Requisition::with([
            'items.masterItem',
            'fromWarehouse',
            'toWarehouse'
        ])
            ->where('account_id', auth()->user()->account_id)
            ->findOrFail($id);

        return view(
            'backend.admin.requisition.view',
            compact('requisition')
        );
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

                // =========================
                // ALREADY CANCELLED
                // =========================

                if ((int) $req->status === 0) {
                    throw new \Exception('Requisition already cancelled');
                }

                $stockService = app(StockService::class);

                // =========================
                // REVERSE STOCK
                // =========================

                foreach ($req->items as $item) {

                    $stockService->moveStock([
                        'account_id' => $accountId,
                        'warehouse_id' => $req->from_warehouse_id,
                        'master_item_id' => $item->master_item_id,
                        'type' => 'transfer_in',
                        'qty' => (float) $item->qty,
                        'reference_id' => $req->id,
                        'remarks' => 'Cancel Requisition #' . $req->requisition_no
                    ]);
                }

                // =========================
                // UPDATE STATUS
                // =========================

                $req->update([
                    'status' => 0,
                    'cancelled_by' => auth()->id(),
                    'cancelled_at' => now(),
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Requisition cancelled successfully'
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    /*
    |--------------------------------------------------------------------------
    | AJAX VIEW
    |--------------------------------------------------------------------------
    */

    public function viewAjax($id, $type = '')
    {
        $id = Settings::getDecodeCode($id);
        $requisition = Requisition::with(['items.masterItem', 'fromWarehouse', 'store'])->ofAccount()->findOrFail($id);
        if ($type == 'pdf') {
            $pdfHeaderdata = \Config::get('constants.viewRequisitionListItemPdf');
            $pdf = PDF::loadView('backend.pdf.requisitions.viewRequisitionListItemPdf', compact('requisition', 'pdfHeaderdata'));
            $pdf = Settings::downloadpdf($pdf);
            $fileName = $pdfHeaderdata['filename'] . '-' . date('Y-m-d') . '.pdf';
            return $pdf->stream($fileName);
        }
        return view('backend.admin.requisition._view', compact('requisition'));
    }

    public function viewAjaxPdf($id)
    {
        return $this->viewAjax($id, 'pdf');
    }


    public function pendingPosting(Request $request)
    {
        $breadcrumb = $this->breadcrumbPendingPosting;
        $warehouses = Warehouse::ofAccount()->active()->orderBy('name', 'asc')->pluck('name', 'id');
        $stores = Store::ofAccount()->active()->orderBy('name', 'asc')->pluck('name', 'id');

        $items = RequisitionItem::with([
            'requisition',
            'masterItem',
            'requisition.store',
            'requisition.fromWarehouse',
            'requisition.creator'
        ])

            // =========================
            // ONLY PENDING ITEMS
            // =========================
            ->whereNull('accepted_by')

            // =========================
            // ONLY ACTIVE REQUISITIONS
            // =========================
            ->whereHas('requisition', function ($q) use ($request) {

                // default active requisition
                $q->whereIn('status', [1, 2]);

                // =========================
                // FILTER : REQUISITION NO
                // =========================
                $q->when($request->requisition_no, function ($qq) use ($request) {
                    $qq->where(
                        'requisition_no',
                        'like',
                        '%' . $request->requisition_no . '%'
                    );
                });

                // =========================
                // FILTER : WAREHOUSE
                // =========================
                $q->when($request->from_warehouse_id, function ($qq) use ($request) {
                    $qq->where(
                        'from_warehouse_id',
                        $request->from_warehouse_id
                    );
                });

                // =========================
                // FILTER : STORE
                // =========================
                $q->when($request->for_store_id, function ($qq) use ($request) {
                    $qq->where(
                        'for_store_id',
                        $request->for_store_id
                    );
                });

                // =========================
                // FILTER : STATUS
                // =========================
                if ($request->filled('status')) {

                    $q->where('status', $request->status);

                }

                // =========================
                // FILTER : FROM DATE
                // =========================
                $q->when($request->from_date, function ($qq) use ($request) {

                    $qq->whereDate(
                        'date',
                        '>=',
                        Settings::formatDate(
                            $request->from_date,
                            'Y-m-d'
                        )
                    );
                });

                // =========================
                // FILTER : TO DATE
                // =========================
                $q->when($request->to_date, function ($qq) use ($request) {

                    $qq->whereDate(
                        'date',
                        '<=',
                        Settings::formatDate(
                            $request->to_date,
                            'Y-m-d'
                        )
                    );
                });
            })
            ->latest()
            ->paginate(config('constants.pagination'));

        return view(
            'backend.admin.requisition.pending-posting',
            compact(
                'items',
                'warehouses',
                'stores',
                'breadcrumb'
            )
        );
    }

    public function cancelItem(Request $request)
    {
        try {

            $itemId = Settings::getDecodeCode($request->id);

            DB::transaction(function () use ($itemId) {

                $accountId = auth()->user()->account_id;

                // =========================
                // GET ITEM
                // =========================
                $item = RequisitionItem::with('requisition')
                    ->whereHas('requisition', function ($q) use ($accountId) {
                        $q->where('account_id', $accountId);
                    })
                    ->lockForUpdate()
                    ->findOrFail($itemId);

                $requisition = $item->requisition;

                // =========================
                // VALIDATIONS
                // =========================

                // Already cancelled
                if ((int) $item->status === 0) {
                    throw new \Exception('Item already cancelled');
                }

                // Already accepted
                if (!empty($item->accepted_by)) {
                    throw new \Exception('Accepted item cannot be cancelled');
                }

                // Requisition already cancelled
                if ((int) $requisition->status === 0) {
                    throw new \Exception('Requisition already cancelled');
                }

                // =========================
                // REVERSE STOCK
                // =========================
                $stockService = app(StockService::class);

                $stockService->moveStock([
                    'account_id' => $accountId,
                    'warehouse_id' => $requisition->from_warehouse_id,
                    'master_item_id' => $item->master_item_id,
                    'type' => 'transfer_in',
                    'qty' => (float) $item->qty,
                    'reference_id' => $requisition->id,
                    'remarks' => 'Cancel Requisition Item #' . $requisition->requisition_no
                ]);

                // =========================
                // CANCEL ITEM
                // =========================
                $item->update([
                    'status' => 0,
                    'cancelled_by' => auth()->id(),
                    'cancelled_at' => now(),
                ]);

                // =========================
                // REFRESH REQUISITION STATUS
                // =========================
                $requisition->refreshStatus();

            });

            return response()->json([
                'success' => true,
                'message' => 'Item cancelled successfully'
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}