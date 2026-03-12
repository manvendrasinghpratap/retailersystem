<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAccountSubscription extends Model
{
    use HasFactory;

    protected $table = 'user_account_subscriptions';

    protected $fillable = [
        'user_id',
        'account_subscription_id',
        'status'
    ];

    /**
     * Status Constants
     */
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;

    /**
     * Relationship with User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship with Subscription Plan
     */
    public function subscription()
    {
        return $this->belongsTo(AccountSubscription::class, 'account_subscription_id');
    }

    /**
     * Scope for Active Subscriptions
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }
}