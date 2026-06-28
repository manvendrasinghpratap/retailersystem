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

    public function scopeOfAccount($query)
    {
        return $query->where(
            'account_id',
            auth()->user()->account_id
        );
    }

    /**
     * Get module settings
     */
    public static function module(string $module)
    {
        return static::ofAccount()
            ->where('module', $module)
            ->first();
    }

    /**
     * Get single setting
     */
    public static function getSetting(
        string $module,
        string $key,
        $default = null
    ) {
        $setting = static::module($module);

        return data_get(
            $setting?->settings,
            $key,
            $default
        );
    }
}