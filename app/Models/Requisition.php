<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Requisition extends Model
{
    protected $table = 'requisitions';

    protected $fillable = [
        'account_id',
        'from_warehouse_id',
        'to_warehouse_id',
        'requisition_no',
        'date',
        'total_qty',
        'status',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'total_qty' => 'decimal:2',
        'status' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function items()
    {
        return $this->hasMany(RequisitionItem::class);
    }

    public function fromWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeOfAccount($query)
    {
        return $query->where('account_id', auth()->user()->account_id);
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    public function getStatusLabelAttribute()
    {
        return $this->status == 1 ? 'Active' : 'Cancelled';
    }
}