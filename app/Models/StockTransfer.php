<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransfer extends Model
{
    protected $fillable = [
        'account_id',
        'transfer_no',
        'from_warehouse_id',
        'to_warehouse_id',
        'date',
        'status',
        'remarks',
        'created_by',
        'updated_by'
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // Transfer Items
    public function items()
    {
        return $this->hasMany(StockTransferItem::class, 'transfer_id');
    }

    // From Warehouse
    public function fromWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    // To Warehouse
    public function toWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    // Creator
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}