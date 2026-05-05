<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequisitionItem extends Model
{
    protected $table = 'requisition_items';

    protected $fillable = [
        'requisition_id',
        'product_id',
        'qty',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function requisition()
    {
        return $this->belongsTo(Requisition::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}