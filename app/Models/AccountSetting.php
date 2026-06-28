<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountSetting extends Model
{
    protected $table = 'account_settings';

    protected $fillable = [
        'account_id',
        'module',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}