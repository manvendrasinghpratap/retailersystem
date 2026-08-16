<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\SaleItemTracking;

class SaleItem extends Model
{
    protected $table = 'sale_items';


    /*
    |--------------------------------------------------------------------------
    | MASS ASSIGNMENT
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'account_id',
        'sale_id',
        'product_id',
        'quantity',
        'price',
        'total',
    ];


    /*
    |--------------------------------------------------------------------------
    | CASTS
    |--------------------------------------------------------------------------
    */

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'total' => 'decimal:2',
    ];


    /*
    |--------------------------------------------------------------------------
    | BOOT
    |--------------------------------------------------------------------------
    */

    protected static function booted()
    {
        /*
        |--------------------------------------------------------------------------
        | Creating
        |--------------------------------------------------------------------------
        */

        static::creating(function ($item) {

            // Automatically assign account
            // from currently logged-in user.
            if (auth()->check()) {
                $item->account_id = auth()->user()->account_id;
            }

            // Automatically calculate item total.
            $item->total =
                (float) $item->quantity *
                (float) $item->price;
        });


        /*
        |--------------------------------------------------------------------------
        | Created
        |--------------------------------------------------------------------------
        */

        static::created(function ($item) {

            /*
            |--------------------------------------------------------------------------
            | Automatically deduct stock
            |--------------------------------------------------------------------------
            |
            | StockAdjustment handles the actual stock movement.
            |
            */

            StockAdjustment::create([
                'account_id' => $item->account_id,
                'product_id' => $item->product_id,
                'type' => 'sale',
                'quantity' => $item->quantity,
                'reference_id' => $item->sale_id,
                'note' => 'POS Sale Invoice #'
                    . optional($item->sale)->invoice_no,
                'created_by' => auth()->id(),
            ]);
        });
    }


    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Sale item belongs to Sale.
     */
    public function sale()
    {
        return $this->belongsTo(
            Sale::class,
            'sale_id'
        );
    }


    /**
     * Sale item belongs to Product.
     */
    public function product()
    {
        return $this->belongsTo(
            Product::class,
            'product_id'
        );
    }


    /**
     * Sale item has many tracking records.
     */
    public function trackingRecords()
    {
        return $this->hasMany(
            SaleItemTracking::class,
            'sale_item_id'
        );
    }


    /**
     * Sale item has many tracking records.
     *
     * Alias for trackingRecords().
     */
    public function trackings()
    {
        return $this->hasMany(
            SaleItemTracking::class,
            'sale_item_id'
        );
    }


    /**
     * Sale item has many returned items.
     */
    public function returnedItems()
    {
        return $this->hasMany(
            SaleReturnItem::class,
            'sale_item_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Restrict sale items to current account.
     */
    public function scopeAccount(Builder $query)
    {
        if (
            auth()->check() &&
            auth()->user()->account_id
        ) {
            $query->where(
                'account_id',
                auth()->user()->account_id
            );
        }

        return $query;
    }


    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    /**
     * Total quantity already returned.
     */
    public function getReturnedQtyAttribute()
    {
        return $this->returnedItems()->sum('quantity');
    }


    /**
     * Quantity still available for return.
     */
    public function getReturnableQtyAttribute()
    {
        return max(
            0,
            (float) $this->quantity -
            (float) $this->returned_qty
        );
    }
}