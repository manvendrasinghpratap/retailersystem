<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleReturnTracking extends Model
{
    protected $fillable = [
        'sale_return_item_id',
        'purchase_item_tracking_id',
        'barcode',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    public function saleReturnItem()
    {
        return $this->belongsTo(
            SaleReturnItem::class,
            'sale_return_item_id'
        );
    }

    public function purchaseTracking()
    {
        return $this->belongsTo(
            PurchaseItemTracking::class,
            'purchase_item_tracking_id'
        );
    }
}