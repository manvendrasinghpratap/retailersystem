<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Purchase extends Model
{
    protected $table = 'purchases';

    protected $fillable = [
        'account_id',
        'vendor_id',
        'warehouse_id',
        'purchase_no',
        'total',
        'status',
        'created_by'
    ];

    protected $casts = [
        'total' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeOfAccount($query)
    {
        return $query->where('account_id', auth()->user()->account_id);
    }

    public function scopeOfActiveStatus($query)
    {
        return $query->where('status', 1);
    }

    public function scopeActive($query)
    {
        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function items()
    {
        return $this->hasMany(PurchaseItem::class, 'purchase_id', 'id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id', 'id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    protected function quantity(): Attribute
    {
        return Attribute::make(
            get: fn($value) => (int) $value,
            set: fn($value) => (int) $value,
        );
    }
}