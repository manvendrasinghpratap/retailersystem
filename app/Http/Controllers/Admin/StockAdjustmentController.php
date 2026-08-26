<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockAdjustment;
use Illuminate\Http\Request;
use App\Helpers\Settings;
use Illuminate\Support\Facades\Config;
use App\Models\Inventory;
use App\Models\RequisitionItem;
use App\Models\Requisition;
use Illuminate\Support\Facades\DB;
use App\Services\StockService;
use App\Models\Product;
use App\Models\PurchaseItemTracking;
class StockAdjustmentController extends Controller
{

    public function customerReturn(Request $request)
    {
        $stockService = app(StockService::class);

        DB::beginTransaction();

        try {

            $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
                'barcode' => 'nullable|string',
                'reference_id' => 'nullable|integer',
                'note' => 'nullable|string',
            ]);

            $accountId = auth()->user()->account_id;
            $storeId = auth()->user()->store_id;

            if (!$storeId) {
                throw new \Exception('Store not found.');
            }

            $product = Product::findOrFail($request->product_id);

            /*
            |--------------------------------------------------------------------------
            | Barcode Return
            |--------------------------------------------------------------------------
            */

            if ($request->filled('barcode')) {

                $tracking = PurchaseItemTracking::where('barcode', $request->barcode)
                    ->where('is_sold', 1)
                    ->lockForUpdate()
                    ->first();

                if (!$tracking) {
                    throw new \Exception(
                        'This barcode is not currently marked as sold.'
                    );
                }

                $tracking->update([
                    'is_sold' => 0,
                    'sold_at' => null,
                    'is_reserved' => 0,
                ]);
            }



            /*
            |--------------------------------------------------------------------------
            | Add Stock Back To Store
            |--------------------------------------------------------------------------
            */

            if ($request->reason_id) {
                $product = Product::find($request->product_id);

                $stockService->moveStock([
                    'account_id' => $request->to_account_id,
                    'warehouse_id' => $request->warehouse_id,
                    'master_item_id' => $product->master_item_id,
                    'type' => 'transfer_in',
                    'qty' => $request->quantity,
                    'reference_id' => $request->reference_id,
                    'remarks' => 'Added To Store: '
                        . ($request->reason_id ?? '')
                        . ' | '
                        . ($request->note ?? ''),

                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Stock Adjustment
            |--------------------------------------------------------------------------
            */

            StockAdjustment::create([
                'account_id' => $accountId,
                'product_id' => $request->product_id,
                'type' => 'return',
                'quantity' => $request->quantity,
                'reference_id' => $request->reference_id,
                'note' => 'Customer Return'
                    . ($request->note ? ' | ' . $request->note : ''),
                'created_by' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product returned to store successfully.',
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
    /*
    @
    */
    public function store_dont_delete(Request $request)
    {
        $this->pr($request->all());
        $stockService = app(StockService::class);
        DB::beginTransaction();
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
                'from_account_id' => 'nullable|exists:accounts,id',
                'to_account_id' => 'nullable|exists:accounts,id',
                'store_id' => 'nullable|exists:stores,id',
                'warehouse_id' => 'nullable|exists:warehouses,id',
                'reason_id' => 'nullable|string|max:255',
                'note' => 'nullable|string',

            ]);

            if ($request->route == 'Add') {
                $id = Settings::getDecodeCode($request->requisition_item_id);

                $requisitionItem = RequisitionItem::with('masterItem')
                    ->lockForUpdate()
                    ->find($id);

                if (!$requisitionItem) {
                    throw new \Exception('Invalid requisition item');
                }

                if (!empty($requisitionItem->accepted_by)) {

                    throw new \Exception(
                        'This requisition item is already accepted by another user.'
                    );
                }

                $requisition = Requisition::find($requisitionItem->requisition_id);
                $requisition->updateStatusByItems();

                $requisitionItem->update(['accepted_by' => auth()->id()]);
            }

            if ($request->from_account_id && in_array($request->type, Config::get('constants.subtractfrominventory'))) {
                $storeInventory = Inventory::where(['account_id' => $request->from_account_id, 'product_id' => $request->product_id])->lockForUpdate()->first();
                if (!$storeInventory || $storeInventory->stock < $request->quantity) {
                    DB::rollBack();
                    return back()->withInput()->with('error', 'Not enough stock in store');
                }
            }
            $note = 'Returned To Warehouse : ';
            if ($request->route == 'Damage') {
                $note = 'Damaged : ';
            }
            StockAdjustment::create([
                'product_id' => $request->product_id,
                'type' => $request->type,
                'quantity' => $request->quantity,
                'reference_id' => $request->reference_id,
                'note' => $note
                    . ($request->reason_id ?? '')
                    . ' | '
                    . ($request->note ?? ''),
                'created_by' => auth()->id(),
            ]);

            // ===================================================
            // ADD STOCK TO WAREHOUSE
            // ===================================================
 
            if ($request->reason_id) {
                $product = Product::find($request->product_id);
                $stockService->moveStock([
                    'account_id' => $request->to_account_id,
                    'warehouse_id' => $request->warehouse_id,
                    'master_item_id' => $product->master_item_id,
                    'type' => 'transfer_in',
                    'qty' => $request->quantity,
                    'reference_id' => $request->reference_id,
                    'remarks' => 'Received From Store : '
                        . ($request->reason_id ?? '')
                        . ' | '
                        . ($request->note ?? ''),
                ]);
            }

            if ($request->filled('barcode')) {

                $tracking = PurchaseItemTracking::where('barcode', $request->barcode)
                    ->where('is_sold', 0)
                    ->where('is_reserved', 1)
                    ->where('requisition_item_id', $request->requisition_item_id)
                    ->lockForUpdate()
                    ->first();

                if (!$tracking) {
                    throw new \Exception(
                        'This barcode is not available for this requisition item.'
                    );
                }

                $tracking->update([
                    'is_sold' => 3,
                    'sold_at' => Carbon::now(),
                    'is_reserved' => 1,
                ]);
            }

            DB::commit();

            return Settings::roleRedirect('inventory', 'Stock Adjusted Successfully.');

        } catch (\Exception $e) {

            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        // $this->pr($request->all());
        $stockService = app(StockService::class);
        
        DB::beginTransaction();
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
                'from_account_id' => 'nullable|exists:accounts,id',
                'to_account_id' => 'nullable|exists:accounts,id',
                'store_id' => 'nullable|exists:stores,id',
                'warehouse_id' => 'nullable|exists:warehouses,id',
                'reason_id' => 'nullable|string|max:255',
                'note' => 'nullable|string',
            ]);

            $requisitionItemId = $request->filled('requisition_item_id') 
                ? Settings::getDecodeCode($request->requisition_item_id) 
                : null;

            if ($request->route == 'Add') {
                $requisitionItem = RequisitionItem::with('masterItem')->lockForUpdate()->find($requisitionItemId);

                if (!$requisitionItem) {
                    throw new \Exception('Invalid requisition item');
                }

                if (!empty($requisitionItem->accepted_by)) {
                    throw new \Exception('This requisition item is already accepted by another user.');
                }

                $requisition = Requisition::find($requisitionItem->requisition_id);
                $requisition?->updateStatusByItems();

                $requisitionItem->update(['accepted_by' => auth()->id()]);
            }

            if ($request->from_account_id && in_array($request->type, Config::get('constants.subtractfrominventory', []))) {
                $storeInventory = Inventory::where(['account_id' => $request->from_account_id, 'product_id' => $request->product_id])->lockForUpdate()->first();

                if (!$storeInventory || $storeInventory->stock < $request->quantity) {
                    DB::rollBack();
                    return back()->withInput()->with('error', 'Not enough stock in store');
                }
            }

            $note = ($request->route == 'Damage') ? 'Damaged : ' : 'Returned To Warehouse : ';
            
            StockAdjustment::create([
                'product_id' => $request->product_id,
                'type' => $request->type,
                'quantity' => $request->quantity,
                'reference_id' => $request->reference_id,
                'note' => $note . ($request->reason_id ?? '') . ' | ' . ($request->note ?? ''),
                'created_by' => auth()->id(),
            ]);

            if ($request->reason_id) {
                $product = Product::findOrFail($request->product_id);
                $stockService->moveStock([
                    'account_id' => $request->to_account_id,
                    'warehouse_id' => $request->warehouse_id,
                    'master_item_id' => $product->master_item_id,
                    'type' => 'transfer_in',
                    'qty' => $request->quantity,
                    'reference_id' => $request->reference_id,
                    'remarks' => 'Received From Store : ' . ($request->reason_id ?? '') . ' | ' . ($request->note ?? ''),
                ]);
            }

            if ($request->filled('barcode')) {
                $tracking = PurchaseItemTracking::where('barcode', $request->barcode)
                    ->where('is_sold', 0)
                    ->where('is_reserved', 1)
                    ->where('requisition_item_id', $requisitionItemId)
                    ->lockForUpdate()
                    ->first();

                if (!$tracking) {
                    DB::rollBack();
                    return back()->withInput()->with('error', 'This barcode is not available for this requisition item.');
                } 
                if($request->route == 'Deduct'){
                    $tracking->update(['is_sold' => 2,'sold_at' => now(),'is_reserved' => 0,]);
                }else if($request->route == 'Damage'){
                    $tracking->update(['is_sold' => 3,'sold_at' => now(),]);
                }else if($request->route == 'Add'){
                    $tracking->update(['is_sold' => 0,'sold_at' => null,]);
                }   
                
            }

            DB::commit();

            return Settings::roleRedirect('inventory', 'Stock Adjusted Successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

}