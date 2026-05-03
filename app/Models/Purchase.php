<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}