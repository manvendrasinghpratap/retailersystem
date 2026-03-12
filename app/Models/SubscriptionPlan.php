<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class SubscriptionPlan extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $table = 'subscription_plans';
    protected $fillable = [
        'name',
        'description',
        'price',
        'duration',
        'status',
        'created_at',
        'updated_at',
    ];

}
