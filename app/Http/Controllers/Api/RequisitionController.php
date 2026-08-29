<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\Warehouse;
use App\Models\Store;
use App\Models\ProductStock;
use App\Models\PurchaseItemTracking;
use App\Services\StockService;
use App\Helpers\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RequisitionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        try {

            $accountId = auth()->user()->account_id;

            $query = Requisition::with([
                'fromWarehouse:id,name',
                'store:id,name',
                'creator:id,name',
                'items.masterItem:id,name',
            ])
                ->where('account_id', $accountId)
                ->latest();

            /*
            |--------------------------------------------------------------------------
            | Filters
            |--------------------------------------------------------------------------
            */

            if ($request->filled('requisition_no')) {
                $query->where(
                    'requisition_no',
                    'LIKE',
                    '%' . trim($request->requisition_no) . '%'
                );
            }

            if ($request->filled('from_warehouse_id')) {
                $query->where(
                    'from_warehouse_id',
                    $request->from_warehouse_id
                );
            }

            if ($request->filled('for_store_id')) {
                $query->where(
                    'for_store_id',
                    $request->for_store_id
                );
            }

            if ($request->filled('status')) {
                $query->where(
                    'status',
                    $request->status
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Date Filter
            |--------------------------------------------------------------------------
            */

            if ($request->filled('from_date')) {
                $query->whereDate(
                    'date',
                    '>=',
                    Settings::formatDate(
                        $request->from_date,
                        'Y-m-d'
                    )
                );
            }

            if ($request->filled('to_date')) {
                $query->whereDate(
                    'date',
                    '<=',
                    Settings::formatDate(
                        $request->to_date,
                        'Y-m-d'
                    )
                );
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

            $requisitions = $query->paginate($perPage);

            /*
            |--------------------------------------------------------------------------
            | Response Transformation
            |--------------------------------------------------------------------------
            */

            $requisitions->getCollection()->transform(
                function ($requisition) {

                    $requisition->product_names = $requisition->items
                        ->pluck('masterItem.name')
                        ->filter()
                        ->unique()
                        ->implode(', ');

                    $requisition->status_label = match (
                        (int) $requisition->status
                    ) {
                        3 => 'Moved to Store',
                        2 => 'Partial to Store',
                        1 => 'Active',
                        default => 'Cancelled',
                    };

                    return $requisition;
                }
            );

            return response()->json([
                'success' => true,
                'message' => 'Requisitions fetched successfully.',
                'data' => $requisitions,
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    public function show($id)
    {
        try {

            $accountId = auth()->user()->account_id;

            /*
             * API can receive either encoded or normal ID.
             */
            try {
                $decodedId = Settings::getDecodeCode($id);
            } catch (\Exception $e) {
                $decodedId = $id;
            }

            $requisition = Requisition::with([
                'items.masterItem',
                'items.purchaseItemTracking',
                'fromWarehouse',
                'store',
                'creator',
            ])
                ->where('account_id', $accountId)
                ->findOrFail($decodedId);

            return response()->json([
                'success' => true,
                'message' => 'Requisition fetched successfully.',
                'data' => $requisition,
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
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

                'from_warehouse_id' => [
                    'required',
                    'exists:warehouses,id'
                ],

                'for_store_id' => [
                    'required',
                    'exists:stores,id'
                ],

                'date' => [
                    'required'
                ],

                'items' => [
                    'required',
                    'array',
                    'min:1'
                ],

                'items.*.master_item_id' => [
                    'required',
                    'exists:master_items,id'
                ],

                'items.*.qty' => [
                    'required',
                    'numeric',
                    'min:1'
                ],

                'items.*.tracking_ids' => [
                    'nullable',
                    'array'
                ],

                'items.*.tracking_ids.*' => [
                    'exists:purchase_item_trackings,id'
                ],

                'items.*.barcodes' => [
                    'nullable',
                    'array'
                ],

                'items.*.barcodes.*' => [
                    'string'
                ],
            ]);

        } catch (ValidationException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        }

        try {

            $requisition = DB::transaction(function () use ($validated) {

                $accountId = auth()->user()->account_id;

                /*
                |--------------------------------------------------------------------------
                | Generate Requisition Number
                |--------------------------------------------------------------------------
                */

                $requisitionNo =
                    'REQ-' .
                    date('Ymd') .
                    '-' .
                    rand(1000, 9999);

                /*
                |--------------------------------------------------------------------------
                | Total Quantity
                |--------------------------------------------------------------------------
                */

                $totalQty = collect($validated['items'])
                    ->sum(function ($item) {
                        return (float) $item['qty'];
                    });

                /*
                |--------------------------------------------------------------------------
                | Validate Tracking Records
                |--------------------------------------------------------------------------
                */

                foreach ($validated['items'] as $item) {

                    if (empty($item['tracking_ids'])) {
                        continue;
                    }

                    foreach ($item['tracking_ids'] as $trackingId) {

                        $tracking = PurchaseItemTracking::where(
                                'id',
                                $trackingId
                            )
                            ->where(
                                'warehouse_id',
                                $validated['from_warehouse_id']
                            )
                            ->lockForUpdate()
                            ->available()
                            ->first();

                        if (!$tracking) {

                            throw new \Exception(
                                'One or more scanned barcodes are no longer available.'
                            );
                        }
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Validate Warehouse Stock
                |--------------------------------------------------------------------------
                */

                foreach ($validated['items'] as $item) {

                    $stock = ProductStock::where([
                        'account_id' => $accountId,
                        'warehouse_id' =>
                            $validated['from_warehouse_id'],
                        'master_item_id' =>
                            $item['master_item_id'],
                    ])
                        ->lockForUpdate()
                        ->first();

                    if (!$stock) {
                        throw new \Exception(
                            'Item stock not found.'
                        );
                    }

                    if (
                        (float) $stock->stock <
                        (float) $item['qty']
                    ) {

                        throw new \Exception(
                            'Insufficient stock for selected item.'
                        );
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Create Requisition
                |--------------------------------------------------------------------------
                */

                $req = Requisition::create([

                    'account_id' => $accountId,

                    'from_warehouse_id' =>
                        $validated['from_warehouse_id'],

                    'for_store_id' =>
                        $validated['for_store_id'],

                    'requisition_no' =>
                        $requisitionNo,

                    'date' =>
                        Settings::formatDate(
                            $validated['date'],
                            'Y-m-d'
                        ),

                    'total_qty' =>
                        $totalQty,

                    'status' => 1,

                    'created_by' =>
                        auth()->id(),
                ]);

                $stockService = app(
                    StockService::class
                );

                /*
                |--------------------------------------------------------------------------
                | Process Items
                |--------------------------------------------------------------------------
                */

                foreach ($validated['items'] as $item) {

                    $qty = (float) $item['qty'];

                    $trackingIds =
                        $item['tracking_ids'] ?? [];

                    $barcodes =
                        $item['barcodes'] ?? [];

                    /*
                    |--------------------------------------------------------------------------
                    | Save Tracking Items
                    |--------------------------------------------------------------------------
                    */

                    foreach ($barcodes as $key => $barcode) {

                        if (!isset($trackingIds[$key])) {
                            throw new \Exception(
                                'Tracking ID missing for barcode.'
                            );
                        }

                        $trackingId =
                            $trackingIds[$key];

                        $reqItem =
                            RequisitionItem::create([

                                'requisition_id' =>
                                    $req->id,

                                'master_item_id' =>
                                    $item['master_item_id'],

                                'purchase_item_tracking_id' =>
                                    $trackingId,

                                'qty' => 1,

                                'barcode' =>
                                    $barcode,
                            ]);

                        /*
                        |--------------------------------------------------------------------------
                        | Reserve Tracking
                        |--------------------------------------------------------------------------
                        */

                        PurchaseItemTracking::where(
                            'id',
                            $trackingId
                        )->update([

                            'is_reserved' => 1,

                            'requisition_id' =>
                                $req->id,

                            'requisition_item_id' =>
                                $reqItem->id,
                        ]);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Save Non-Tracking Item
                    |--------------------------------------------------------------------------
                    */

                    if (empty($barcodes)) {

                        RequisitionItem::create([

                            'requisition_id' =>
                                $req->id,

                            'master_item_id' =>
                                $item['master_item_id'],

                            'qty' => $qty,
                        ]);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Move Stock
                    |--------------------------------------------------------------------------
                    */

                    $stockService->moveStock([

                        'account_id' =>
                            $accountId,

                        'warehouse_id' =>
                            $validated['from_warehouse_id'],

                        'master_item_id' =>
                            $item['master_item_id'],

                        'type' =>
                            'transfer_out',

                        'qty' =>
                            $qty,

                        'reference_id' =>
                            $req->id,

                        'remarks' =>
                            'Requisition OUT #' .
                            $requisitionNo,
                    ]);
                }

                return $req;
            });

            /*
            |--------------------------------------------------------------------------
            | Load Response
            |--------------------------------------------------------------------------
            */

            $requisition->load([
                'items.masterItem',
                'items.purchaseItemTracking',
                'fromWarehouse',
                'store',
                'creator',
            ]);

            return response()->json([
                'success' => true,
                'message' =>
                    'Requisition created successfully.',
                'data' => $requisition,
            ], 201);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | CANCEL REQUISITION
    |--------------------------------------------------------------------------
    */

    public function cancel(Request $request)
    {
        try {

            $id = $request->id;

            try {
                $id = Settings::getDecodeCode($id);
            } catch (\Exception $e) {
                // Keep original ID
            }

            $accountId =
                auth()->user()->account_id;

            DB::transaction(function () use (
                $id,
                $accountId
            ) {

                $req = Requisition::with('items')
                    ->where(
                        'account_id',
                        $accountId
                    )
                    ->lockForUpdate()
                    ->findOrFail($id);

                /*
                |--------------------------------------------------------------------------
                | Only Active Requisition Can Be Cancelled
                |--------------------------------------------------------------------------
                */

                if ((int) $req->status !== 1) {

                    $message = match (
                        (int) $req->status
                    ) {

                        0 =>
                            __('translation.requisition_already_cancelled'),

                        2 =>
                            __('translation.requisition_partially_completed'),

                        3 =>
                            __('translation.requisition_already_completed'),

                        default =>
                            __('translation.invalid_requisition_status'),
                    };

                    throw new \Exception($message);
                }

                $stockService =
                    app(StockService::class);

                /*
                |--------------------------------------------------------------------------
                | Reverse Stock & Release Tracking
                |--------------------------------------------------------------------------
                */

                foreach ($req->items as $item) {

                    $stockService->moveStock([

                        'account_id' =>
                            $accountId,

                        'warehouse_id' =>
                            $req->from_warehouse_id,

                        'master_item_id' =>
                            $item->master_item_id,

                        'type' =>
                            'transfer_in',

                        'qty' =>
                            (float) $item->qty,

                        'reference_id' =>
                            $req->id,

                        'remarks' =>
                            'Cancel Requisition #' .
                            $req->requisition_no,
                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | Release Tracking
                    |--------------------------------------------------------------------------
                    */

                    PurchaseItemTracking::where(
                        'requisition_item_id',
                        $item->id
                    )
                        ->lockForUpdate()
                        ->update([

                            'is_reserved' => 0,

                            'requisition_id' => null,

                            'requisition_item_id' => null,
                        ]);

                    /*
                    |--------------------------------------------------------------------------
                    | Cancel Item
                    |--------------------------------------------------------------------------
                    */

                    $item->update([
                        'status' => 0,
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Cancel Requisition
                |--------------------------------------------------------------------------
                */

                $req->update([

                    'status' => 0,

                    'cancelled_by' =>
                        auth()->id(),

                    'cancelled_at' =>
                        now(),
                ]);
            });

            return response()->json([
                'success' => true,
                'message' =>
                    __('translation.requisition_cancelled_successfully'),
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' =>
                    $e->getMessage(),
            ], 422);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | CANCEL ITEM
    |--------------------------------------------------------------------------
    */

    public function cancelItem(Request $request)
    {
        try {

            $itemId = $request->id;

            try {
                $itemId =
                    Settings::getDecodeCode($itemId);
            } catch (\Exception $e) {
                // Keep original ID
            }

            DB::transaction(function () use ($itemId) {

                $accountId =
                    auth()->user()->account_id;

                $item = RequisitionItem::with(
                    'requisition'
                )
                    ->whereHas(
                        'requisition',
                        function ($q) use ($accountId) {
                            $q->where(
                                'account_id',
                                $accountId
                            );
                        }
                    )
                    ->lockForUpdate()
                    ->findOrFail($itemId);

                $requisition =
                    $item->requisition;

                /*
                |--------------------------------------------------------------------------
                | Validation
                |--------------------------------------------------------------------------
                */

                if ((int) $item->status === 0) {

                    throw new \Exception(
                        'Item already cancelled.'
                    );
                }

                if (!empty($item->accepted_by)) {

                    throw new \Exception(
                        'Accepted item cannot be cancelled.'
                    );
                }

                if (
                    (int) $requisition->status === 0
                ) {

                    throw new \Exception(
                        'Requisition already cancelled.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Reverse Stock
                |--------------------------------------------------------------------------
                */

                $stockService =
                    app(StockService::class);

                $stockService->moveStock([

                    'account_id' =>
                        $accountId,

                    'warehouse_id' =>
                        $requisition->from_warehouse_id,

                    'master_item_id' =>
                        $item->master_item_id,

                    'type' =>
                        'transfer_in',

                    'qty' =>
                        (float) $item->qty,

                    'reference_id' =>
                        $requisition->id,

                    'remarks' =>
                        'Cancel Requisition Item #' .
                        $requisition->requisition_no,
                ]);

                /*
                |--------------------------------------------------------------------------
                | Release Tracking
                |--------------------------------------------------------------------------
                */

                PurchaseItemTracking::where(
                    'requisition_item_id',
                    $item->id
                )
                    ->lockForUpdate()
                    ->update([

                        'is_reserved' => 0,

                        'requisition_id' => null,

                        'requisition_item_id' => null,
                    ]);

                /*
                |--------------------------------------------------------------------------
                | Cancel Item
                |--------------------------------------------------------------------------
                */

                $item->update([

                    'status' => 0,

                    'cancelled_by' =>
                        auth()->id(),

                    'cancelled_at' =>
                        now(),
                ]);

                /*
                |--------------------------------------------------------------------------
                | Refresh Requisition Status
                |--------------------------------------------------------------------------
                */

                $requisition->refreshStatus();
            });

            return response()->json([
                'success' => true,
                'message' =>
                    'Item cancelled successfully.',
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' =>
                    $e->getMessage(),
            ], 422);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | PENDING POSTING
    |--------------------------------------------------------------------------
    */

    public function pendingPosting(Request $request)
    {
        try {

            $accountId =
                auth()->user()->account_id;

            $query = RequisitionItem::with([
                'requisition',
                'masterItem',
                'requisition.store',
                'requisition.fromWarehouse',
                'requisition.creator',
            ])
                ->whereNull('accepted_by')
                ->whereHas(
                    'requisition',
                    function ($q) use (
                        $request,
                        $accountId
                    ) {

                        $q->where(
                            'account_id',
                            $accountId
                        );

                        $q->whereIn(
                            'status',
                            [1, 2]
                        );

                        /*
                        |------------------------------------------------------
                        | Requisition Number
                        |------------------------------------------------------
                        */

                        $q->when(
                            $request->requisition_no,
                            function ($qq) use ($request) {

                                $qq->where(
                                    'requisition_no',
                                    'like',
                                    '%' .
                                    $request->requisition_no .
                                    '%'
                                );
                            }
                        );

                        /*
                        |------------------------------------------------------
                        | Warehouse
                        |------------------------------------------------------
                        */

                        $q->when(
                            $request->from_warehouse_id,
                            function ($qq) use ($request) {

                                $qq->where(
                                    'from_warehouse_id',
                                    $request->from_warehouse_id
                                );
                            }
                        );

                        /*
                        |------------------------------------------------------
                        | Store
                        |------------------------------------------------------
                        */

                        $q->when(
                            $request->for_store_id,
                            function ($qq) use ($request) {

                                $qq->where(
                                    'for_store_id',
                                    $request->for_store_id
                                );
                            }
                        );

                        /*
                        |------------------------------------------------------
                        | Status
                        |------------------------------------------------------
                        */

                        if ($request->filled('status')) {

                            $q->where(
                                'status',
                                $request->status
                            );
                        }

                        /*
                        |------------------------------------------------------
                        | Date
                        |------------------------------------------------------
                        */

                        if ($request->filled('from_date')) {

                            $q->whereDate(
                                'date',
                                '>=',
                                Settings::formatDate(
                                    $request->from_date,
                                    'Y-m-d'
                                )
                            );
                        }

                        if ($request->filled('to_date')) {

                            $q->whereDate(
                                'date',
                                '<=',
                                Settings::formatDate(
                                    $request->to_date,
                                    'Y-m-d'
                                )
                            );
                        }
                    }
                )
                ->orderBy(
                    'updated_at',
                    'desc'
                );

            $perPage = (int) $request->get(
                'per_page',
                account_setting('general.pagination')
            );

            $items = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' =>
                    'Pending requisition items fetched successfully.',
                'data' => $items,
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' =>
                    $e->getMessage(),
            ], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | PENDING POSTING HISTORY
    |--------------------------------------------------------------------------
    */

    public function pendingPostingHistory(
        Request $request
    ) {
        try {

            $accountId =
                auth()->user()->account_id;

            $query = RequisitionItem::with([
                'requisition',
                'masterItem',
                'requisition.store',
                'requisition.fromWarehouse',
                'requisition.creator',
                'acceptedBy',
            ])
                ->whereNotNull('accepted_by')
                ->whereHas(
                    'requisition',
                    function ($q) use (
                        $request,
                        $accountId
                    ) {

                        $q->where(
                            'account_id',
                            $accountId
                        );

                        $q->whereIn(
                            'status',
                            [2, 3]
                        );

                        /*
                        |------------------------------------------------------
                        | Requisition Number
                        |------------------------------------------------------
                        */

                        $q->when(
                            $request->requisition_no,
                            function ($qq) use ($request) {

                                $qq->where(
                                    'requisition_no',
                                    'like',
                                    '%' .
                                    $request->requisition_no .
                                    '%'
                                );
                            }
                        );

                        /*
                        |------------------------------------------------------
                        | Warehouse
                        |------------------------------------------------------
                        */

                        $q->when(
                            $request->from_warehouse_id,
                            function ($qq) use ($request) {

                                $qq->where(
                                    'from_warehouse_id',
                                    $request->from_warehouse_id
                                );
                            }
                        );

                        /*
                        |------------------------------------------------------
                        | Store
                        |------------------------------------------------------
                        */

                        $q->when(
                            $request->for_store_id,
                            function ($qq) use ($request) {

                                $qq->where(
                                    'for_store_id',
                                    $request->for_store_id
                                );
                            }
                        );

                        /*
                        |------------------------------------------------------
                        | Status
                        |------------------------------------------------------
                        */

                        if ($request->filled('status')) {

                            $q->where(
                                'status',
                                $request->status
                            );
                        }

                        /*
                        |------------------------------------------------------
                        | From Date
                        |------------------------------------------------------
                        */

                        if ($request->filled('from_date')) {

                            $q->whereDate(
                                'updated_at',
                                '>=',
                                Settings::formatDate(
                                    $request->from_date,
                                    'Y-m-d'
                                )
                            );
                        }

                        /*
                        |------------------------------------------------------
                        | To Date
                        |------------------------------------------------------
                        */

                        if ($request->filled('to_date')) {

                            $q->whereDate(
                                'date',
                                '<=',
                                Settings::formatDate(
                                    $request->to_date,
                                    'Y-m-d'
                                )
                            );
                        }
                    }
                )
                ->orderBy(
                    'updated_at',
                    'desc'
                );

            $perPage = (int) $request->get(
                'per_page',
                account_setting('general.pagination')
            );

            $items = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' =>
                    'Pending posting history fetched successfully.',
                'data' => $items,
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' =>
                    $e->getMessage(),
            ], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDATE REQUISITION BARCODE
    |--------------------------------------------------------------------------
    */

    public function validateRequisitionBarcode(
        Request $request
    ) {
        try {

            $validated = $request->validate([

                'barcode' => [
                    'required'
                ],

                'warehouse_id' => [
                    'required',
                    'exists:warehouses,id'
                ],

                'product_id' => [
                    'required',
                    'exists:master_items,id'
                ],
            ]);

            $barcode =
                PurchaseItemTracking::with([
                    'purchaseItem.purchase',
                    'purchaseItem.masterItem',
                ])
                    ->where(
                        'barcode',
                        trim($validated['barcode'])
                    )
                    ->available()
                    ->first();

            if (!$barcode) {

                return response()->json([
                    'success' => false,
                    'message' =>
                        'Barcode not found or unavailable.',
                ], 404);
            }

            /*
            |--------------------------------------------------------------------------
            | Product Validation
            |--------------------------------------------------------------------------
            */

            if (
                $barcode->purchaseItem->master_item_id !=
                $validated['product_id']
            ) {

                return response()->json([
                    'success' => false,
                    'message' =>
                        'Scanned barcode belongs to another product.',
                ], 422);
            }

            /*
            |--------------------------------------------------------------------------
            | Warehouse Validation
            |--------------------------------------------------------------------------
            */

            if (
                $barcode->warehouse_id !=
                $validated['warehouse_id']
            ) {

                return response()->json([
                    'success' => false,
                    'message' =>
                        'This barcode is not available in selected warehouse.',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' =>
                    'Barcode validated successfully.',
                'data' => [
                    'barcode' =>
                        $barcode->barcode,

                    'tracking_id' =>
                        $barcode->id,

                    'product_id' =>
                        $barcode->purchaseItem->master_item_id,

                    'product' =>
                        $barcode->purchaseItem
                            ->masterItem
                            ->name,
                ],
            ]);

        } catch (ValidationException $e) {

            return response()->json([
                'success' => false,
                'message' =>
                    'Validation failed.',
                'errors' =>
                    $e->errors(),
            ], 422);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' =>
                    $e->getMessage(),
            ], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | SEARCH BARCODE
    |--------------------------------------------------------------------------
    */

    public function searchBarcode(
        Request $request
    ) {
        try {

            $request->validate([

                'warehouse_id' => [
                    'required',
                    'exists:warehouses,id'
                ],

                'barcode' => [
                    'required'
                ],

                'scanned_ids' => [
                    'nullable',
                    'array'
                ],

                'scanned_ids.*' => [
                    'integer'
                ],
            ]);

            $query = PurchaseItemTracking::with(
                'purchaseItem.masterItem'
            )
                ->available()
                ->where(
                    'warehouse_id',
                    $request->warehouse_id
                )
                ->where(
                    'barcode',
                    trim($request->barcode)
                );

            /*
            |--------------------------------------------------------------------------
            | Exclude Already Scanned
            |--------------------------------------------------------------------------
            */

            if (!empty($request->scanned_ids)) {

                $query->whereNotIn(
                    'id',
                    $request->scanned_ids
                );
            }

            $tracking =
                $query
                    ->orderBy('id')
                    ->first();

            if (!$tracking) {

                return response()->json([
                    'success' => false,
                    'message' =>
                        'No available barcode found.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' =>
                    'Barcode found successfully.',
                'data' => [

                    'tracking_id' =>
                        $tracking->id,

                    'barcode' =>
                        $tracking->barcode,

                    'tracking_type' =>
                        $tracking->tracking_type,

                    'master_item_id' =>
                        $tracking->purchaseItem
                            ->master_item_id,

                    'master_item_name' =>
                        $tracking->purchaseItem
                            ->masterItem
                            ->name,
                ],
            ]);

        } catch (ValidationException $e) {

            return response()->json([
                'success' => false,
                'message' =>
                    'Validation failed.',
                'errors' =>
                    $e->errors(),
            ], 422);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' =>
                    $e->getMessage(),
            ], 500);
        }
    }
}