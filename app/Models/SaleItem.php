<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class SaleItem extends Model
{
    protected $table = 'sale_items';

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
    | Boot Method
    |--------------------------------------------------------------------------
    */

    protected static function booted()
    {
        static::creating(function ($item) {

            // Auto assign account_id
            if (auth()->check()) {
                $item->account_id = auth()->user()->account_id;
            }

            // Auto calculate total
            $item->total = $item->quantity * $item->price;
        });

        static::created(function ($item) {

            // 🔥 Automatically deduct stock using StockAdjustment
            StockAdjustment::create([
                'account_id'   => $item->account_id,
                'product_id'   => $item->product_id,
                'type'         => 'sale',
                'quantity'     => $item->quantity,
                'reference_id' => $item->sale_id,
                'note'         => 'POS Sale Invoice #' . $item->sale->invoice_no,
                'created_by'   => auth()->id(),
            ]);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // Item belongs to sale
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    // Item belongs to product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    // Scope by account
    public function scopeAccount(Builder $query)
    {
        return $query->where('account_id', auth()->user()->account_id);
    }
}