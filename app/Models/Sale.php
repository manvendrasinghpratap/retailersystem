<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $table = 'sales';

    protected $fillable = [
        'invoice_no',
        'customer_id',
        'subtotal',
        'tax',
        'discount',
        'total',
        'paid_amount',
        'change_amount',
        'payment_method',
        'status',
        'user_id',
    ];

    /**
     * Relationships
     */

    // One Sale has many items
    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    // Sale belongs to customer
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // Sale belongs to user (cashier)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}