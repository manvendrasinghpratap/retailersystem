<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorLedger extends Model
{
    protected $table = 'vendor_ledgers';

    protected $fillable = [
        'account_id',
        'vendor_id',
        'type',
        'reference_id',
        'debit',
        'credit',
        'balance',
        'remarks'
    ];

    protected $casts = [
        'debit'   => 'decimal:2',
        'credit'  => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    public function vendor_purchase()
    {
        return $this->belongsTo(VendorPurchase::class, 'reference_id');
    }

    public function vendorPayment()
    {
        return $this->belongsTo(VendorPayment::class, 'reference_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}