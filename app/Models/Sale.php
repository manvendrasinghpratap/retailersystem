<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $table = 'sales';

    /*
    |--------------------------------------------------------------------------
    | MASS ASSIGNMENT
    |--------------------------------------------------------------------------
    */

    protected $fillable = [

        /*
        |--------------------------------------------------------------------------
        | BASIC SALE
        |--------------------------------------------------------------------------
        */

        'invoice_no',
        'account_id',
        'store_id',
        'customer_id',

        /*
        |--------------------------------------------------------------------------
        | SALE AMOUNTS
        |--------------------------------------------------------------------------
        |
        | subtotal + tax - discount = total
        |
        */

        'subtotal',
        'tax',
        'discount',
        'total',

        /*
        |--------------------------------------------------------------------------
        | PAYMENT
        |--------------------------------------------------------------------------
        */

        'paid_amount',
        'change_amount',
        'balance_amount',

        'payment_type',
        'payment_method',
        'payment_status',

        /*
        |--------------------------------------------------------------------------
        | FINAL AMOUNT
        |--------------------------------------------------------------------------
        */

        'final_amount',

        /*
        |--------------------------------------------------------------------------
        | CREDIT
        |--------------------------------------------------------------------------
        */

        'payable_amount',
        'interest_amount',
        'interest_rate',
        'due_date',
        'credit_duration_id',

        /*
        |--------------------------------------------------------------------------
        | STATUS
        |--------------------------------------------------------------------------
        */

        'status',

        /*
        |--------------------------------------------------------------------------
        | USER / CASHIER
        |--------------------------------------------------------------------------
        */

        'user_id',

        /*
        |--------------------------------------------------------------------------
        | DELIVERY
        |--------------------------------------------------------------------------
        */

        'delivery_type',
        'delivery_address',
        'delivery_charge',
        'delivery_notes',

        /*
        |--------------------------------------------------------------------------
        | PAYMENT APPROVAL
        |--------------------------------------------------------------------------
        */

        'payment_approval_status',
        'payment_approved_by',
        'payment_approved_at',
        'payment_approval_note',
    ];


    /*
    |--------------------------------------------------------------------------
    | CASTS
    |--------------------------------------------------------------------------
    */

    protected $casts = [

        /*
        |--------------------------------------------------------------------------
        | SALE AMOUNTS
        |--------------------------------------------------------------------------
        */

        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',

        /*
        |--------------------------------------------------------------------------
        | PAYMENT AMOUNTS
        |--------------------------------------------------------------------------
        */

        'paid_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',

        /*
        |--------------------------------------------------------------------------
        | FINAL AMOUNT
        |--------------------------------------------------------------------------
        */

        'final_amount' => 'decimal:2',

        /*
        |--------------------------------------------------------------------------
        | CREDIT AMOUNTS
        |--------------------------------------------------------------------------
        */

        'payable_amount' => 'decimal:2',
        'interest_amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',

        /*
        |--------------------------------------------------------------------------
        | DELIVERY
        |--------------------------------------------------------------------------
        */

        'delivery_charge' => 'decimal:2',

        /*
        |--------------------------------------------------------------------------
        | CREDIT DUE DATE
        |--------------------------------------------------------------------------
        */

        'due_date' => 'date',

        /*
        |--------------------------------------------------------------------------
        | PAYMENT APPROVAL
        |--------------------------------------------------------------------------
        */

        'payment_approved_at' => 'datetime',
    ];


    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * Sale has many sale items.
     */
    public function items()
    {
        return $this->hasMany(
            SaleItem::class,
            'sale_id'
        );
    }


    /**
     * Sale belongs to Account.
     */
    public function account()
    {
        return $this->belongsTo(
            Account::class,
            'account_id'
        );
    }


    /**
     * Sale has many payments.
     */
    public function payments()
    {
        return $this->hasMany(
            SalePayment::class,
            'sale_id'
        );
    }


    /**
     * Sale belongs to Customer.
     */
    public function customer()
    {
        return $this->belongsTo(
            Customer::class,
            'customer_id'
        );
    }


    /**
     * Sale belongs to User / Cashier.
     */
    public function user()
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }


    /**
     * Sale belongs to Store.
     */
    public function store()
    {
        return $this->belongsTo(
            Store::class,
            'store_id'
        );
    }


    /**
     * Sale belongs to Credit Duration.
     */
    public function creditDuration()
    {
        return $this->belongsTo(
            CreditDuration::class,
            'credit_duration_id'
        );
    }


    /**
     * Sale belongs to Payment Type.
     *
     * sales.payment_type
     *      -> payment_types.short_name
     */
    public function customerPaymentType()
    {
        return $this->belongsTo(
            PaymentType::class,
            'payment_type',
            'short_name'
        );
    }


    /**
     * Sale has many returns.
     */
    public function returns()
    {
        return $this->hasMany(
            SaleReturn::class,
            'sale_id'
        );
    }


    /**
     * User who approved the payment.
     */
    public function paymentApprovedBy()
    {
        return $this->belongsTo(
            User::class,
            'payment_approved_by'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    /**
     * Display all payment methods.
     *
     * Example:
     *
     * Cash (₦ 10,000.00), Card (₦ 5,000.00)
     */
    public function getPaymentMethodsAttribute()
    {
        return $this->payments
            ->map(function ($payment) {

                $method = ucwords(
                    str_replace(
                        '_',
                        ' ',
                        $payment->method ?? ''
                    )
                );

                return $method
                    . ' ('
                    . __('translation.b_ngn')
                    . ' '
                    . number_format(
                        (float) $payment->amount,
                        2
                    )
                    . ')';
            })
            ->implode(', ');
    }


    /**
     * Total cash received.
     */
    public function getCashAmountAttribute()
    {
        return $this->payments
            ->where('method', 'cash')
            ->sum('amount');
    }


    /**
     * Total card payment received.
     */
    public function getCardAmountAttribute()
    {
        return $this->payments
            ->whereIn('method', [
                'card',
                'credit_card',
                'credit card',
                'credit-card',
            ])
            ->sum('amount');
    }


    /**
     * Total transfer payment received.
     */
    public function getTransferAmountAttribute()
    {
        return $this->payments
            ->where('method', 'transfer')
            ->sum('amount');
    }


    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Restrict sales to logged-in user's account.
     */
    public function scopeOfAccount($query)
    {
        if (
            auth()->check() &&
            auth()->user()->account_id
        ) {
            $query->where(
                'account_id',
                auth()->user()->account_id
            );
        }

        return $query;
    }


    /**
     * Restrict sales based on logged-in user's visibility.
     *
     * Designation 2 can see all sales.
     * Other users can see their own sales.
     */
    public function scopeVisibleToUser($query)
    {
        if (
            auth()->check() &&
            auth()->user()->designation_id != 2
        ) {
            $query->where(
                'user_id',
                auth()->id()
            );
        }

        return $query;
    }


    /*
    |--------------------------------------------------------------------------
    | PAYMENT APPROVAL HELPERS
    |--------------------------------------------------------------------------
    */

    /**
     * Check whether payment is waiting for approval.
     */
    public function requiresPaymentApproval(): bool
    {
        return $this->payment_approval_status === 'pending';
    }


    /**
     * Check whether payment has been approved.
     */
    public function isPaymentApproved(): bool
    {
        return $this->payment_approval_status === 'approved';
    }


    /**
     * Check whether payment has been rejected.
     */
    public function isPaymentRejected(): bool
    {
        return $this->payment_approval_status === 'rejected';
    }


    /**
     * Check whether payment approval is not required.
     */
    public function isPaymentApprovalNotRequired(): bool
    {
        return $this->payment_approval_status === 'not_required'
            || is_null($this->payment_approval_status);
    }


    /*
    |--------------------------------------------------------------------------
    | DELIVERY HELPERS
    |--------------------------------------------------------------------------
    */

    /**
     * Check whether sale is home delivery.
     */
    public function isHomeDelivery(): bool
    {
        return $this->delivery_type === 'delivery';
    }


    /**
     * Check whether sale is customer pickup.
     */
    public function isCustomerPickup(): bool
    {
        return $this->delivery_type === 'pickup';
    }


    /*
    |--------------------------------------------------------------------------
    | PAYMENT HELPERS
    |--------------------------------------------------------------------------
    */

    /**
     * Check whether sale is credit.
     */
    public function isCredit(): bool
    {
        return $this->payment_type === 'credit';
    }


    /**
     * Check whether sale is full payment.
     */
    public function isFullPayment(): bool
    {
        return $this->payment_type === 'full';
    }


    /**
     * Check whether sale is partial/split payment.
     */
    public function isPartialPayment(): bool
    {
        return $this->payment_type === 'partial';
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

}