<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'account_id',
        'staff_id',
        'warehouse_code',
        'name',
        'manager_name',
        'phone',
        'email',
        'address',
        'status',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'status' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Boot
    |--------------------------------------------------------------------------
    */

    protected static function booted()
    {
        static::creating(function ($warehouse) {

            if (auth()->check()) {
                $warehouse->account_id = auth()->user()->account_id;
                $warehouse->created_by = auth()->id();
            }
        });

        static::updating(function ($warehouse) {

            if (auth()->check()) {
                $warehouse->updated_by = auth()->id();
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors & Mutators
    |--------------------------------------------------------------------------
    */

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? ucwords($value) : null,
            set: fn ($value) => $value ? strtolower(trim($value)) : null
        );
    }

    protected function managerName(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? ucwords($value) : null,
            set: fn ($value) => $value ? strtolower(trim($value)) : null
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeAccount(Builder $query): Builder
    {
        if (auth()->check()) {
            return $query->where('account_id', auth()->user()->account_id);
        }

        return $query;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 1);
    }

    /*
    |--------------------------------------------------------------------------
    | Dropdown Helper
    |--------------------------------------------------------------------------
    */

    public static function dropdown()
    {
        return self::account()
            ->active()
            ->orderBy('name', 'asc')
            ->pluck('name', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // Warehouse → Product Stocks
    public function stocks()
    {
        return $this->hasMany(ProductStock::class);
    }

    // Warehouse → Stock Movements
    public function movements()
    {
        return $this->hasMany(StockMovement::class);
    }

    // Transfers FROM this warehouse
    public function transfersFrom()
    {
        return $this->hasMany(StockTransfer::class, 'from_warehouse_id');
    }

    // Transfers TO this warehouse
    public function transfersTo()
    {
        return $this->hasMany(StockTransfer::class, 'to_warehouse_id');
    }

    // Assigned Staff
    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
    public function scopeOfAccount($query)
    {
        return $query->where(
            'account_id',
            auth()->user()->account_id
        );
    }
}