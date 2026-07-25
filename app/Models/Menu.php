<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'module_id',
        'name',
        'slug',
        'route_name',
        'icon',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Menu belongs to a module.
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * Menu has many permissions.
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class, 'menu_id');
    }

    /**
     * Scope active menus.
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}