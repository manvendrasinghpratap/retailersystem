<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleReturnItem extends Model
{
    protected $fillable = [
        'sale_return_id',
        'sale_item_id',
        'product_id',
        'purchase_item_tracking_id',
        'quantity',
        'price',
        'total',
        'reason',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function saleReturn()
    {
        return $this->belongsTo(SaleReturn::class);
    }

    public function saleItem()
    {
        return $this->belongsTo(SaleItem::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function trackings()
    {
        return $this->hasMany(
            SaleReturnTracking::class,
            'sale_return_item_id'
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