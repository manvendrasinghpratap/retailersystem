<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\Settings;   

class VendorPayment extends Model
{
    protected $table = 'vendor_payments';

    protected $fillable = [
        'account_id',
        'vendor_id',
        'payment_date',
        'amount',
        'payment_method',
        'reference_no',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2'
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
    public function setPaymentDateAttribute($value)
    {
        $this->attributes['payment_date'] = $value
            ? Settings::formatDate($value, config('constants.dateformat.datepicker'))
            : null;
    }
}