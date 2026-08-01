<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductTracking extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'purchase_id',
        'purchase_item_id',
        'master_item_id',
        'warehouse_id',
        'barcode',
        'tracking_type',
        'batch_no',
        'serial_no',
        'expiry_date',
        'status'
    ];
}
