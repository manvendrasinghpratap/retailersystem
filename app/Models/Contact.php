<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = [
        'account_id',
        'name',
        'email',
        'organization',
        'phone',
        'subject',
        'message',
        'ip_address',
        'user_agent',
        'status',
        'is_deleted',
    ];

    protected $casts = [
        'status' => 'integer',
        'is_deleted' => 'boolean',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}