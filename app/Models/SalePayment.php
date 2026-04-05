<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalePayment extends Model
{
    protected $table = 'sale_payments';

    protected $fillable = [
        'sale_id',
        'method',
        'amount',
    ];

    /**
     * Relationships
     */

    // ✅ Payment belongs to a Sale
    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }
}