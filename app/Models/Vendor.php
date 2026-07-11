<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Models\VendorLedger;
use App\Models\VendorPayment;

class Vendor extends Model
{
    protected $table = 'vendors';

    protected $fillable = [
        'account_id',
        'vendor_code',
        'name',
        'company_name',
        'phone',
        'email',
        'address',
        'lga_id',
        'state_id',
        'country_id',
        'opening_balance',
        'current_balance',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'status' => 'integer',
    ];

    public function scopeOfAccount($query)
    {
        return $query->where('account_id', auth()->user()->account_id);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn($value) => ucwords($value),
            set: fn($value) => strtolower(trim($value))
        );
    }

    protected function companyName(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? ucwords($value) : null,
            set: fn($value) => $value ? strtolower(trim($value)) : null
        );
    }

    public function ledgers()
    {
        return $this->hasMany(VendorLedger::class);
    }

    public function payments()
    {
        return $this->hasMany(VendorPayment::class);
    }

    public function increaseBalance($amount)
    {
        $this->increment('current_balance', $amount);
    }

    public function decreaseBalance($amount)
    {
        $this->decrement('current_balance', $amount);
    }
}