<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseItemTracking extends Model
{
    protected $fillable = [
        'purchase_item_id',
        'warehouse_id',
        'store_id',
        'requisition_id',
        'requisition_item_id',
        'barcode',
        'tracking_type',
        'serial_no',
        'batch_no',
        'expiry_date',
        'quantity',
        'status',
        'is_reserved',
        'is_sold',
        'sold_at'
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'sold_at' => 'datetime',
        'is_sold' => 'boolean',
    ];

    /**
     * Barcode belongs to Purchase Item
     */
    public function purchaseItem()
    {
        return $this->belongsTo(PurchaseItem::class);
    }
    public function purchase()
    {
        return $this->purchaseItem?->purchase();
    }

    public function masterItem()
    {
        return $this->belongsTo(MasterItem::class, 'master_item_id');
    }

    public function trackings()
    {
        return $this->hasMany(PurchaseItemTracking::class, 'purchase_item_id')->status()->notSold();
    }
    /**
     * Only active records
     */
    public function scopeStatus($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Only barcodes that are not sold
     */
    public function scopeNotSold($query)
    {
        return $query->where('is_sold', 0);
    }
    public function requisition()
    {
        return $this->belongsTo(Requisition::class);
    }

    public function requisitionItem()
    {
        return $this->belongsTo(RequisitionItem::class);
    }
    public function scopeAvailable($query)
    {
        return $query
            ->where('status', 1)
            ->where('is_sold', 0)
            ->where('is_reserved', 0);
    }

}