<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountSubscription extends Model
{
    use HasFactory;

    protected $table = 'account_subscription';

    protected $fillable = [
        'account_id',
        'subscription_id',
        'subscription_name',
        'start_date',
        'end_date',
        'amount_paid',
        'subscription_price',
        'discount',
        'is_expired',
        'status',
        'created_by',
        'is_deleted',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }


    public function activeSubscription()
    {
        return $this->hasOne(AccountSubscription::class, 'account_id')
            ->where('status', 1)
            ->where('is_deleted', 0)
            ->where('is_expired', 0);
    }
}