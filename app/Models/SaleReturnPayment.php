<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleReturnPayment extends Model
{
    protected $fillable = [
        'sale_return_id',
        'method',
        'amount',
        'payment_received_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function saleReturn()
    {
        return $this->belongsTo(SaleReturn::class);
    }
}