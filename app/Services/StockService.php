<?php

namespace App\Services;

use App\Models\ProductStock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class StockService
{
    // ============================
    // TYPES
    // ============================
    const TYPE_SALE = 'sale';
    const TYPE_PURCHASE = 'purchase';
    const TYPE_TRANSFER_IN = 'transfer_in';
    const TYPE_TRANSFER_OUT = 'transfer_out';
    const TYPE_ADJUSTMENT_ADD = 'adjustment_add';
    const TYPE_ADJUSTMENT_SUB = 'adjustment_sub';
    const TYPE_PURCHASE_CANCEL = 'purchase_cancel';

    public function moveStock(array $data)
    {
        /*
        Required:
        account_id
        warehouse_id
        master_item_id (NEW)
        type
        qty
        reference_id
        */

        return DB::transaction(function () use ($data) {

            // ============================
            // ✅ SUPPORT OLD + NEW KEY
            // ============================
            $itemId = $data['master_item_id'] ?? $data['product_id'] ?? null;

            if (!$itemId) {
                throw new \Exception('Item ID is required for stock movement');
            }

            // ============================
            // 🔒 LOCK STOCK ROW
            // ============================
            $stock = ProductStock::where([
                'account_id' => $data['account_id'],
                'warehouse_id' => $data['warehouse_id'],
                'master_item_id' => $itemId, // ✅ changed
            ])->lockForUpdate()->first();

            if (!$stock) {
                $stock = ProductStock::create([
                    'account_id' => $data['account_id'],
                    'warehouse_id' => $data['warehouse_id'],
                    'master_item_id' => $itemId, // ✅ changed
                    'stock' => 0,
                    'reserved_stock' => 0
                ]);
            }

            $qtyIn = 0;
            $qtyOut = 0;

            // ============================
            // ✅ NORMALIZE TYPE
            // ============================
            $type = $this->normalizeType($data['type']);

            switch ($type) {

                // =====================
                // INCREASE STOCK
                // =====================
                case self::TYPE_PURCHASE:
                case self::TYPE_TRANSFER_IN:
                    $qtyIn = (float) $data['qty'];
                    break;
                case self::TYPE_ADJUSTMENT_ADD:
                    $qtyIn = (float) $data['qty'];
                    break;

                // =====================
                // DECREASE STOCK
                // =====================
                case self::TYPE_SALE:
                case self::TYPE_TRANSFER_OUT:
                case self::TYPE_ADJUSTMENT_SUB:
                case self::TYPE_PURCHASE_CANCEL:
                    $qtyOut = (float) $data['qty'];

                    if ((float) $stock->stock < $qtyOut) {
                        throw new \Exception(
                            'Insufficient stock for Item ID: ' . $itemId
                        );
                    }
                    break;

                default:
                    throw new \Exception('Invalid stock movement type');
            }

            // ============================
            // ✅ UPDATE STOCK
            // ============================
            $stock->stock = (float) $stock->stock + $qtyIn - $qtyOut;
            $stock->save();

            // ============================
            // ✅ STOCK MOVEMENT LOG
            // ============================
            StockMovement::create([
                'account_id' => $data['account_id'],
                'warehouse_id' => $data['warehouse_id'],
                'master_item_id' => $itemId, // ✅ changed
                'type' => $type,
                'reference_id' => $data['reference_id'] ?? null,
                'qty_in' => $qtyIn,
                'qty_out' => $qtyOut,
                'balance' => $stock->stock,
                'remarks' => $data['remarks'] ?? null,
                'created_by' => auth()->id()
            ]);

            return $stock;
        });
    }

    // ============================
    // TYPE NORMALIZER
    // ============================
    private function normalizeType($type)
    {
        if (is_numeric($type)) {
            switch ((int) $type) {
                case 1:
                    return self::TYPE_SALE;
                case 2:
                    return self::TYPE_PURCHASE;
                case 3:
                    return self::TYPE_TRANSFER_IN;
                case 4:
                    return self::TYPE_TRANSFER_OUT;
                case 5:
                    return self::TYPE_ADJUSTMENT_ADD;
                case 6:
                    return self::TYPE_ADJUSTMENT_SUB;
                case 7:
                    return self::TYPE_PURCHASE_CANCEL;
            }
        }

        return $type;
    }
}