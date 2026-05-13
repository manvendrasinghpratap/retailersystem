<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Store extends Model
{
    protected $fillable = [
        'account_id',
        'name',
        'code',
        'email',
        'phone',
        'alternate_phone',
        'gst_number',
        'address',
        'city',
        'state',
        'country',
        'pincode',
        'manager_id',
        'status',
        'created_by',
        'updated_by',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    // Multi-tenant scope
    public function scopeOfAccount(Builder $query)
    {
        if (auth()->check()) {
            $query->where('account_id', auth()->user()->account_id);
        }
    }

    // Active stores only
    public function scopeActive(Builder $query)
    {
        return $query->where('status', 1);
    }
}