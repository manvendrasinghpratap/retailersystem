<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $table = 'payment_methods';

    protected $fillable = [
        'account_id',
        'name',
        'short_name',
        'status',
        'created_by',
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeOfAccount($query)
    {
        return $query->where(
            'account_id',
            auth()->user()->account_id
        );
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    public static function getSelectable()
    {
        return self::ofAccount()
            ->active()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }
}