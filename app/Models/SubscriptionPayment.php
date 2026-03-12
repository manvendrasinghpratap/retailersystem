<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPayment extends Model
{
    use HasFactory;

    protected $table = 'subscription_payment';

    protected $fillable = [
        'account_id',
        'account_subscription_id',
        'payment_method',
        'amount',
        'created_by'
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }
    
    public function getPaymentMethodNameAttribute()
    {
        return match($this->payment_method) {
            1 => 'POS',
            2 => 'Transfer',
            default => '-',
        };
    }
}