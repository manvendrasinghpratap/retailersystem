<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockReturn extends Model
{
    protected $table = 'stock_returns';

    protected $fillable = [
    'account_id',
    'vendor_id',
    'warehouse_id',
    'return_no',
    'return_date',
    'total',
    'status',
    'created_by',
    'cancelled_at',
    'cancelled_by'
];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    // Vendor
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    // Warehouse
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    // Items
    public function items()
    {
        return $this->hasMany(StockReturnItem::class, 'return_id');
    }

    // Creator
    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeOfAccount($query)
    {
        return $query->where('account_id', auth()->user()->account_id);
    }
	
    // ✅ ADD HERE 👇
    public function getTotalItemsAttribute()
    {
        return $this->items->sum('qty');
    }
    public function getStatusTextAttribute()
    {
        return $this->status == 1 ? 'Active' : 'Cancelled';
    }
    public function getReturnDateAttribute($value)
    {
        return $value ? \Carbon\Carbon::parse($value)->format('d-m-Y') : null;
    }
    public function getTotalAttribute($value)
    {
        return \App\Helpers\Settings::getcustomnumberformat($value);
    }
    // -------------------------
    // CANCEL LOGIC
    // -------------------------
    public function cancel()
    {
        if ($this->status == 0) return false;
        foreach ($this->items as $item){  
            $product = \App\Models\Product::find($item->product_id);
            $product->stock += $item->qty;
            $product->save();
        }

        // Update status
        $this->status = 0;
        $this->cancelled_at = now();
        $this->cancelled_by = auth()->id();
        $this->save();

        return true;
    }
}