<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleReturn extends Model
{
    protected $fillable = [
        'account_id',
        'store_id',
        'sale_id',
        'customer_id',
        'return_no',
        'total_amount',
        'refund_amount',
        'refund_type',
        'payment_method',
        'status',
        'reason',
        'note',
        'created_by',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function items()
    {
        return $this->hasMany(SaleReturnItem::class);
    }

    public function payments()
    {
        return $this->hasMany(SaleReturnPayment::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}