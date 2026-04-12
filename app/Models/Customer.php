<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'account_id',
        'name',
        'phone',
        'email',
        'wallet_balance',
        'status',
    ];

    protected $casts = [
        'wallet_balance' => 'decimal:2',
    ];

    protected function name(): Attribute
    {
        return Attribute::make(
            // 🔹 When getting from DB
            get: fn($value) => ucwords($value),

            // 🔹 When saving to DB
            set: fn($value) => strtolower($value)
        );
    }


}