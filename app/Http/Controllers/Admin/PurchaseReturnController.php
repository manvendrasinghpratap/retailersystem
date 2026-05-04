<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\ProductStock;
use App\Models\Vendor;
use App\Models\VendorLedger;
use App\Services\StockService;
use App\Helpers\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseReturnController extends Controller
{
    public function create($id)
    {
        $purchaseId = Settings::getDecodeCode($id);

        $purchase = Purchase::with(['items.product', 'vendor', 'warehouse'])
            ->where('account_id', auth()->user()->account_id)
            ->findOrFail($purchaseId);

        return view('backend.admin.purchase_return.form', compact('purchase'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'purchase_id' => 'required|exists:purchases,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'nullable|numeric|min:0',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        try {

            DB::transaction(function () use ($request) {

                $accountId = auth()->user()->account_id;

                $purchase = Purchase::with('items')
                    ->where('account_id', $accountId)
                    ->findOrFail($request->purchase_id);

                $returnNo = 'RET-' . date('Ymd') . '-' . rand(1000, 9999);

                $totalReturn = 0;

                $stockService = app(StockService::class);

                foreach ($request->items as $item) {

                    if (empty($item['qty']) || $item['qty'] <= 0) continue;

                    $purchaseItem = $purchase->items
                        ->where('product_id', $item['product_id'])
                        ->first();

                    if (!$purchaseItem) {
                        throw new \Exception('Invalid product selected');
                    }

                    // ❌ Cannot return more than purchased
                    if ($item['qty'] > $purchaseItem->quantity) {
                        throw new \Exception('Return qty exceeds purchased qty');
                    }

                    // ❌ Cannot return more than stock
                    $stock = ProductStock::where([
                        'account_id' => $accountId,
                        'warehouse_id' => $purchase->warehouse_id,
                        'product_id' => $item['product_id']
                    ])->value('stock') ?? 0;

                    if ($item['qty'] > $stock) {
                        throw new \Exception('Return qty exceeds available stock');
                    }

                    $lineTotal = $item['qty'] * $item['price'];
                    $totalReturn += $lineTotal;

                    // ✅ STOCK OUT
                    $stockService->moveStock([
                        'account_id' => $accountId,
                        'warehouse_id' => $purchase->warehouse_id,
                        'product_id' => $item['product_id'],
                        'type' => 'purchase_return',
                        'qty' => $item['qty'],
                        'reference_id' => $purchase->id,
                        'remarks' => 'Return #' . $returnNo
                    ]);
                }

                if ($totalReturn <= 0) {
                    throw new \Exception('No return quantity entered');
                }

                // ✅ Vendor Update
                $vendor = Vendor::lockForUpdate()->find($purchase->vendor_id);

                $newBalance = $vendor->current_balance - $totalReturn;

                VendorLedger::create([
                    'account_id' => $accountId,
                    'vendor_id' => $vendor->id,
                    'type' => 'purchase_return',
                    'reference_id' => $purchase->id,
                    'debit' => 0,
                    'credit' => $totalReturn,
                    'balance' => $newBalance,
                    'remarks' => 'Return #' . $returnNo
                ]);

                $vendor->update([
                    'current_balance' => $newBalance
                ]);
            });

            return redirect()->route('admin.purchases.index')
                ->with('success', 'Purchase return completed successfully');

        } catch (\Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }
}