<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $table = 'sales';

    protected $fillable = [
        'invoice_no',
        'account_id',
        'customer_id',
        'subtotal',
        'tax',
        'discount',
        'total',
        'paid_amount',
        'change_amount',
        'payment_type',
        'payment_method',
        'status',
        'user_id',
        'payable_amount',
        'balance_amount',
        'interest_amount',
        'interest_rate',
        'due_date',
        'credit_duration_id',
        'payment_status'
    ];

    /**
     * Relationships
     */

    // ✅ One Sale has many items
    public function items()
    {
        return $this->hasMany(SaleItem::class, 'sale_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }
    // ✅ One Sale has many payments (NEW 🔥)
    public function payments()
    {
        return $this->hasMany(SalePayment::class, 'sale_id');
    }

    // ✅ Sale belongs to customer
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    // ✅ Sale belongs to user (cashier)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getPaymentMethodsAttribute()
    {
        return $this->payments
            ->map(function ($payment) {
                return ucwords($payment->method) . ' (' . __('translation.b_ngn') . ' ' . $payment->amount . ')';
            })
            ->implode(', ');
    }

    public function scopeVisibleToUser($query)
    {
        if (auth()->check() && auth()->user()->designation_id != 2) {
            $query->where('user_id', auth()->id());
        }

        return $query;
    }

    public function getCashAmountAttribute()
    {
        return $this->payments->where('method', 'cash')->sum('amount');
    }

    public function getCardAmountAttribute()
    {
        return $this->payments->where('method', 'card')->sum('amount');
    }

    public function getTransferAmountAttribute()
    {
        return $this->payments->where('method', 'transfer')->sum('amount');
    }
    public function creditDuration()
    {
        return $this->belongsTo(
            CreditDuration::class,
            'credit_duration_id'
        );
    }
    public function customerPaymentType()
    {
        return $this->belongsTo(
            PaymentType::class,
            'payment_type',
            'short_name'
        );
    }
}