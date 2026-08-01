<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequisitionItemTracking extends Model
{
    protected $fillable = [
        'requisition_item_id',
        'purchase_item_tracking_id',
        'barcode'
    ];

    public function requisitionItem()
    {
        return $this->belongsTo(RequisitionItem::class);
    }

    public function purchaseTracking()
    {
        return $this->belongsTo(
            PurchaseItemTracking::class,
            'purchase_item_tracking_id'
        );
    }

}