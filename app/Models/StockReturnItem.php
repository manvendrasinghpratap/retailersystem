<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockReturnItem extends Model
{
    protected $table = 'stock_return_items';

    protected $fillable = [
        'return_id',
        'master_item_id',
        'qty',
        'price',
        'total',
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
        return $this->belongsTo(Product::class);
    }

    public function masterItem()
    {
        return $this->belongsTo(MasterItem::class, 'master_item_id');
    }
}