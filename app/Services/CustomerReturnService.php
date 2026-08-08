<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Inventory;
use App\Models\PaymentMethod;
use App\Models\PurchaseItemTracking;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleItemTracking;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\SaleReturnPayment;
use Illuminate\Support\Facades\DB;

class CustomerReturnService
{
    public function process(array $data): SaleReturn
    {
        return DB::transaction(function () use ($data) {

            $user = auth()->user();

            $accountId = $user->account_id;
            $storeId = $user->store_id;

            if (!$storeId) {
                throw new \Exception(
                    'Store not found for current user.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Lock Sale
            |--------------------------------------------------------------------------
            */

            $sale = Sale::where('account_id', $accountId)
                ->where('store_id', $storeId)
                ->lockForUpdate()
                ->findOrFail($data['sale_id']);

            if ($sale->status !== 'completed') {
                throw new \Exception(
                    'Only completed sales can be returned.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Customer
            |--------------------------------------------------------------------------
            */

            $customerId = $sale->customer_id;

            /*
            |--------------------------------------------------------------------------
            | Validate Items
            |--------------------------------------------------------------------------
            */

            $returnItems = [];

            $returnTotal = 0;

            foreach ($data['items'] as $requestItem) {

                $saleItem = SaleItem::where('sale_id', $sale->id)
                    ->lockForUpdate()
                    ->findOrFail($requestItem['sale_item_id']);

                $requestedQty = (float) $requestItem['quantity'];

                if ($requestedQty <= 0) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Already Returned
                |--------------------------------------------------------------------------
                */

                $returnedQty = SaleReturnItem::where(
                    'sale_item_id',
                    $saleItem->id
                )->sum('quantity');

                $returnableQty =
                    (float) $saleItem->quantity -
                    (float) $returnedQty;

                if ($requestedQty > $returnableQty) {

                    throw new \Exception(
                        "Return quantity exceeds available return quantity for product."
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Price
                |--------------------------------------------------------------------------
                */

                $price = (float) $saleItem->price;

                $lineTotal = round(
                    $requestedQty * $price,
                    2
                );

                $returnItems[] = [
                    'sale_item' => $saleItem,
                    'quantity' => $requestedQty,
                    'price' => $price,
                    'total' => $lineTotal,
                    'tracking_ids' =>
                        $requestItem['tracking_ids'] ?? [],
                ];

                $returnTotal += $lineTotal;
            }

            if (empty($returnItems)) {
                throw new \Exception(
                    'Please select at least one item to return.'
                );
            }

            $returnTotal = round($returnTotal, 2);

            /*
            |--------------------------------------------------------------------------
            | Return Number
            |--------------------------------------------------------------------------
            */

            $returnNo = $this->generateReturnNumber(
                $accountId
            );

            /*
            |--------------------------------------------------------------------------
            | Determine Refund
            |--------------------------------------------------------------------------
            */

            $refundType = $data['refund_type'] ?? 'refund';

            $refundAmount = 0;

            if ($refundType === 'refund') {

                $refundAmount = $returnTotal;
            }

            /*
            |--------------------------------------------------------------------------
            | Credit Sale
            |--------------------------------------------------------------------------
            */

            if ($sale->payment_type === 'credit') {

                if ($refundType === 'credit_adjustment') {

                    $newBalance = max(
                        0,
                        (float) $sale->balance_amount -
                        $returnTotal
                    );

                    $sale->update([
                        'balance_amount' => $newBalance,
                        'payable_amount' => max(
                            0,
                            (float) $sale->payable_amount -
                            $returnTotal
                        ),
                    ]);

                    $refundAmount = 0;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Create Return
            |--------------------------------------------------------------------------
            */

            $saleReturn = SaleReturn::create([

                'account_id' => $accountId,

                'store_id' => $storeId,

                'sale_id' => $sale->id,

                'customer_id' => $customerId,

                'return_no' => $returnNo,

                'total_amount' => $returnTotal,

                'refund_amount' => $refundAmount,

                'refund_type' => $refundType,

                'payment_method' =>
                    $data['payment_method'] ?? null,

                'status' => 'completed',

                'reason' =>
                    $data['reason'] ?? null,

                'note' =>
                    $data['note'] ?? null,

                'created_by' => auth()->id(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Process Items
            |--------------------------------------------------------------------------
            */

            foreach ($returnItems as $returnItem) {

                $saleItem = $returnItem['sale_item'];

                $quantity = $returnItem['quantity'];

                /*
                |--------------------------------------------------------------------------
                | Create Return Item
                |--------------------------------------------------------------------------
                */

                SaleReturnItem::create([

                    'sale_return_id' =>
                        $saleReturn->id,

                    'sale_item_id' =>
                        $saleItem->id,

                    'product_id' =>
                        $saleItem->product_id,

                    'quantity' =>
                        $quantity,

                    'price' =>
                        $returnItem['price'],

                    'total' =>
                        $returnItem['total'],

                    'reason' =>
                        $returnItem['reason'] ?? null,
                ]);

                /*
                |--------------------------------------------------------------------------
                | Restore Store Inventory
                |--------------------------------------------------------------------------
                */

                $inventory = Inventory::where(
                    'account_id',
                    $accountId
                )
                    ->where(
                        'product_id',
                        $saleItem->product_id
                    )
                    ->lockForUpdate()
                    ->first();

                if (!$inventory) {

                    throw new \Exception(
                        'Inventory not found for returned product.'
                    );
                }

                $inventory->increment(
                    'stock',
                    $quantity
                );

                /*
                |--------------------------------------------------------------------------
                | Barcode / Tracking Return
                |--------------------------------------------------------------------------
                */

                $trackingIds =
                    $returnItem['tracking_ids'];

                if (!empty($trackingIds)) {

                    foreach ($trackingIds as $trackingId) {

                        $tracking =
                            PurchaseItemTracking::lockForUpdate()
                                ->findOrFail($trackingId);

                        /*
                        | Only sold item can be returned
                        */

                        if ((int) $tracking->is_sold !== 1) {

                            throw new \Exception(
                                "Barcode {$tracking->barcode} is not marked as sold."
                            );
                        }

                        /*
                        | Make barcode available again
                        */

                        $tracking->update([

                            'is_sold' => 0,

                            'is_reserved' => 0,

                            'store_id' => $storeId,

                            'sold_at' => null,

                            'status' => 1,
                        ]);
                    }
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Refund Payment
            |--------------------------------------------------------------------------
            */

            if (
                $refundAmount > 0 &&
                $refundType === 'refund'
            ) {

                $paymentMethod =
                    PaymentMethod::where(
                        'account_id',
                        $accountId
                    )
                        ->where('status', 1)
                        ->where(
                            'short_name',
                            $data['payment_method']
                        )
                        ->first();

                if (!$paymentMethod) {

                    throw new \Exception(
                        'Invalid refund payment method.'
                    );
                }

                SaleReturnPayment::create([

                    'sale_return_id' =>
                        $saleReturn->id,

                    'method' =>
                        $paymentMethod->short_name,

                    'amount' =>
                        $refundAmount,

                    'payment_received_by' =>
                        auth()->id(),
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Recalculate Sale
            |--------------------------------------------------------------------------
            */

            $totalReturned = SaleReturnItem::whereHas(
                'saleReturn',
                function ($query) use ($sale) {
                    $query->where('sale_id', $sale->id)
                        ->where('status', 'completed');
                }
            )->sum('total');

            /*
            |--------------------------------------------------------------------------
            | Fully Returned
            |--------------------------------------------------------------------------
            */

            if ($totalReturned >= (float) $sale->total) {

                $sale->update([
                    'status' => 'cancelled',
                ]);
            }

            return $saleReturn;
        });
    }

    private function generateReturnNumber(int $accountId): string
    {
        do {

            $number =
                'RET' .
                now()->format('YmdHis') .
                random_int(10, 99);

        } while (
            SaleReturn::where('account_id', $accountId)
                ->where('return_no', $number)
                ->exists()
        );

        return $number;
    }
}