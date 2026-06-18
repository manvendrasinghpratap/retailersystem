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
class StockAdjustmentController extends Controller
{

    public function storew(Request $request)
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'type' => 'required|in:add,deduct,sale,return,damage',
                'quantity' => 'required|integer|min:1',
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

            // if (in_array($request->type, Config::get('constants.subtractfrominventory'))) {
            //     $inventory = Inventory::where('product_id', $request->product_id)->first();

            //     if ($inventory && $inventory->stock < $request->quantity) {
            //         return back()->withInput()->with('error', 'Not enough stock');
            //     }
            // }

            if (in_array($request->type, Config::get('constants.subtractfrominventory'))) {
                $storeInventory = Inventory::where(['account_id' => $request->from_account_id, 'product_id' => $request->product_id])->lockForUpdate()->first();
                if (!$storeInventory || $storeInventory->stock < $request->quantity) {
                    return back()->withInput()->with('error', 'Not enough stock in store');
                }
            }
            // ===================================================
            // STORE STOCK ADJUSTMENT LOG
            // ===================================================

            StockAdjustment::create([
                'product_id' => $request->product_id,
                'type' => $request->type,
                'quantity' => $request->quantity,
                'reference_id' => $request->reference_id,
                'note' => 'Returned To Warehouse : '
                    . ($request->reason_id ?? '')
                    . ' | '
                    . ($request->note ?? ''),
                'created_by' => auth()->id(),
            ]);


            if ($request->route == 'Add') {
                return redirect()->route('admin.requisitions.pending.posting')->with('success', __('translation.product_added_successfully'));
            }
            return Settings::roleRedirect('inventory', 'Stock Adjusted Successfully.');
        } catch (\Exception $e) {
            return Settings::roleRedirect('inventory', 'Something went wrong!', 'error');
        }
    }

    public function store(Request $request)
    {
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

            StockAdjustment::create([
                'product_id' => $request->product_id,
                'type' => $request->type,
                'quantity' => $request->quantity,
                'reference_id' => $request->reference_id,
                'note' => 'Returned To Warehouse : '
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

            DB::commit();

            return Settings::roleRedirect(
                'inventory',
                'Stock Adjusted Successfully.'
            );

        } catch (\Exception $e) {

            DB::rollBack();

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }
    public function storeworking(Request $request)
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'type' => 'required|in:add,deduct,sale,return,damage',
                'quantity' => 'required|integer|min:1',
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

            if (in_array($request->type, Config::get('constants.subtractfrominventory'))) {
                $inventory = Inventory::where('product_id', $request->product_id)->first();

                if ($inventory && $inventory->stock < $request->quantity) {
                    return back()->withInput()->with('error', 'Not enough stock');
                }
            }
            StockAdjustment::create([
                'product_id' => $request->product_id,
                'type' => $request->type,
                'quantity' => $request->quantity,
                'reference_id' => $request->reference_id,
                'note' => $request->note,
            ]);
            if ($request->route == 'Add') {
                return redirect()->route('admin.requisitions.pending.posting')->with('success', __('translation.product_added_successfully'));
            }
            return Settings::roleRedirect('inventory', 'Stock Adjusted Successfully.');
        } catch (\Exception $e) {
            return Settings::roleRedirect('inventory', 'Something went wrong!', 'error');
        }
    }
}