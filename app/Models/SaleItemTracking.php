<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItemTracking extends Model
{
    protected $fillable = [
        'sale_item_id',
        'purchase_item_tracking_id',
    ];

    // public function saleItem()
    // {
    //     return $this->belongsTo(SaleItem::class);
    // }
    public function sale()
    {
        return $this->belongsTo(
            SaleItem::class,
            'sale_item_id'
        );
    }

    public function saleItem()
    {
        return $this->belongsTo(
            SaleItem::class,
            'sale_item_id'
        );
    }


    public function tracking()
    {
        return $this->belongsTo(
            PurchaseItemTracking::class,
            'purchase_item_tracking_id'
        );
    }
    public function purchaseItemTracking()
    {
        return $this->belongsTo(
            PurchaseItemTracking::class,
            'purchase_item_tracking_id'
        );
    }
}