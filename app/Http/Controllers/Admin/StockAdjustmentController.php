<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockAdjustment;
use Illuminate\Http\Request;
use App\Helpers\Settings;
use Illuminate\Support\Facades\Config;
use App\Models\Inventory;
class StockAdjustmentController extends Controller
{
    public function store(Request $request)
    {
        try{
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'type'       => 'required|in:add,deduct,sale,return,damage',
                'quantity'   => 'required|integer|min:1',
                'note'       => 'nullable|string',
            ]);
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
            if($request->route == 'Add'){
                return Settings::roleRedirect('barcode','Stock Adjusted Successfully.');
            }
            return Settings::roleRedirect('inventory','Stock Adjusted Successfully.');
        } catch (\Exception $e) {
            return Settings::roleRedirect('inventory','Something went wrong!','error');
        }
    }
}