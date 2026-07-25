<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'name',
        'slug',
        'icon',
        'sort_order',
        'status',
        'is_deleted',
    ];

    protected $casts = [
        'status' => 'boolean',
        'is_deleted' => 'boolean',
    ];

    /**
     * Module has many menus.
     */
    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class, 'module_id');
    }

    /**
     * Module has many permissions.
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class);
    }

    /**
     * Scope active modules.
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
    public function scopeNotDeleted($query)
    {
        return $query->where('is_deleted', false);
    }
    public function scopeOfAccount($query)
    {
        return $query->where('account_id', auth()->user()->account_id);
    }
}