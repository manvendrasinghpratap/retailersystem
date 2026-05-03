<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductStock extends Model
{
    protected $fillable = [
        'account_id',
        'warehouse_id',
        'product_id',
        'stock',
        'low_stock_alert'
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}