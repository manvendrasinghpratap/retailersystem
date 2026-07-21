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
        'quantity' => 'decimal:2',
    ];

    public function scopeOfAccount($query)
    {
        return $query->where('account_id', auth()->user()->account_id);
    }
    public function scopeActive($query)
    {
        return $query;
        //return $query->where('status', 1);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
    /*
    @function quantity
    */
    protected function quantity(): Attribute
    {
        return Attribute::make(
            get: fn($value) => (int) $value,
            set: fn($value) => (int) $value,
        );
    }

}