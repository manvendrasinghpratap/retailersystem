<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'code',
        'type',
        'value',
        'min_amount',
        'max_discount',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'expires_at' => 'date',
        'is_active' => 'boolean',
        'value' => 'decimal:2',
        'min_amount' => 'decimal:2',
        'max_discount' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isValid($totalAmount)
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->isExpired()) {
            return false;
        }

        if ($this->min_amount && $totalAmount < $this->min_amount) {
            return false;
        }

        return true;
    }

    public function calculateDiscount($totalAmount)
    {
        if (!$this->isValid($totalAmount)) {
            return 0;
        }

        if ($this->type === 'flat') {
            return min($this->value, $totalAmount);
        }

        // percent type
        $discount = ($totalAmount * $this->value) / 100;

        if ($this->max_discount) {
            return min($discount, $this->max_discount);
        }

        return $discount;
    }
    public function getCreatedDateAttribute()
    {
        return $this->created_at?->format(config('constants.dateformat.slashdmyonly'));
    }
    public function getExpiredDateAttribute()
    {
        return $this->expires_at?->format(config('constants.dateformat.slashdmyonly'));
    }
    //     public function getDiscountTypeAttribute()
//     {
//         return $this->type === 'flat' ? 'Flat' : 'Percent';
//     }
//     public function getStatusAttribute()
//     {
//         return $this->is_active ? 'Active' : 'Inactive';
//     }
//     public function getDiscountValueAttribute()
//     {
//         return $this->type === 'flat' ? $this->value : $this->value . '%';
//     }
//     public function getMinAmountAttribute()
//     {
//         return $this->min_amount ? $this->min_amount : 'N/A';
//     }
//     public function getMaxDiscountAttribute()
//     {
//         return $this->max_discount ? $this->max_discount : 'N/A';
//     }
}