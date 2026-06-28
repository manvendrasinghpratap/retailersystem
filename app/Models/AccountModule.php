<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountModule extends Model
{
    protected $table = 'account_modules';

    protected $fillable = [
        'name',
    ];

    /**
     * Relationship
     */
    public function accountSettings()
    {
        return $this->hasMany(AccountSetting::class, 'module', 'name');
    }

    /**
     * Accessor
     */
    public function getNameAttribute($value)
    {
        return ucwords(str_replace('_', ' ', $value));
    }

    /**
     * Scope
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('name');
    }

    /**
     * Get Selectable List
     */
    public static function getSelectable()
    {
        return self::ordered()
            ->pluck('name', 'name')
            ->toArray();
    }
}