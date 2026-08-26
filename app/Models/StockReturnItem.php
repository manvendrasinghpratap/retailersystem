<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
class StockReturnItem extends Model
{
    protected $table = 'stock_return_items';

    protected $fillable = [
        'return_id',
        'master_item_id',
        'purchase_item_tracking_id',
        'qty',
        'price',
        'total',
        'reason'
    ];

    protected $casts = [
        'qty' => 'integer',
        'price' => 'decimal:2',
        'total' => 'decimal:2',
    ];
    
    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    // Parent Return
    public function stockReturn()
    {
        return $this->belongsTo(StockReturn::class, 'return_id');
    }

    // Product
    public function product()
    {
        return $this->belongsTo(MasterItem::class);
    }

    public function masterItem()
    {
        return $this->belongsTo(MasterItem::class, 'master_item_id');
    }
    protected function qty(): Attribute
    {
        return Attribute::make(
            get: fn($value) => (int) $value,
            set: fn($value) => (int) $value,
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