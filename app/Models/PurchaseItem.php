<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class PurchaseItem extends Model
{
    protected $fillable = [
        'purchase_id',
        'master_item_id',
        'quantity',
        'cost_price',
        'total',
        'tracking_type'
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'purchase_id', 'id');
    }

    public function masterItem()
    {
        return $this->belongsTo(MasterItem::class, 'master_item_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Purchase Item
     *      hasMany
     * Purchase Item Tracking
     */
    // public function trackings()
    // {
    //     return $this->hasMany(
    //         PurchaseItemTracking::class,
    //         'purchase_item_id',
    //         'id'
    //     );
    // }

    public function trackings()
    {
        return $this->hasMany(
            PurchaseItemTracking::class,
            'purchase_item_id',
            'id'
        )
            ->where('status', 1)
            ->where('is_sold', 0);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessor
    |--------------------------------------------------------------------------
    */

    protected function quantity(): Attribute
    {
        return Attribute::make(
            get: fn($value) => (int) $value,
            set: fn($value) => (int) $value,
        );
    }


    public function getAvailableQtyAttribute()
    {
        return $this->trackings
            ->where('status', 1)
            ->where('is_sold', 0)
            ->where('is_reserved', 0)
            ->sum('quantity');
    }
}