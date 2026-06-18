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
        'is_deleted',
    ];

    protected static function booted()
    {
        static::addGlobalScope('not_deleted', function (Builder $builder) {
            $builder->where('is_deleted', 0);
        });
    }

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

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeOfAccount(Builder $query)
    {
        if (auth()->check()) {
            $query->where(
                'account_id',
                auth()->user()->account_id
            );
        }

        return $query;
    }

    public function scopeActive(Builder $query)
    {
        return $query->where('status', 1)->where('is_deleted', 0);
    }

    public function scopeOfMyStore(Builder $query)
    {
        if (auth()->check() && auth()->user()->store_id) {
            $query->where(
                'id',
                auth()->user()->store_id
            );
        }

        return $query;
    }

    public function scopeDeleted(Builder $query)
    {
        return $query->withoutGlobalScope('not_deleted')
            ->where('is_deleted', 1);
    }
}