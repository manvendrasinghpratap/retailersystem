<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalePayment extends Model
{
    protected $table = 'sale_payments';
    protected $appends = ['formatted_date'];


    protected $fillable = [
        'sale_id',
        'method',
        'amount',
        'payment_received_by'
    ];

    /**
     * Relationships
     */

    // ✅ Payment belongs to a Sale
    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(
            PaymentMethod::class,
            'payment_method_id'
        );
    }
    public function paymentReceivedBy()
    {
        return $this->belongsTo(
            User::class,
            'payment_received_by'
        );
    }

    public function getFormattedDateAttribute()
    {
        return \App\Helpers\Settings::getFormattedDatetime(
            $this->created_at
        );
    }
}