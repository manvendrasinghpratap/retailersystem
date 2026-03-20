<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $table = 'inventory';

    protected $fillable = [
        'account_id',
        'product_id',
        'stock',
        'low_stock_alert',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->account_id = auth()->user()->account_id;
            }
        });
    }

    /* Relationships */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /* Helper Methods (VERY IMPORTANT) */

    public function increaseStock($qty)
    {
        $this->increment('stock', $qty);
    }

    public function decreaseStock($qty)
    {
        $this->decrement('stock', $qty);
    }

    public function isLowStock()
    {
        return $this->stock <= $this->low_stock_alert;
    }
}