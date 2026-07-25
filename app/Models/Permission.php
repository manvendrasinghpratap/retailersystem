<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'module_id',
        'menu_id',
        'name',
        'slug',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Permission belongs to a module.
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'module_id');
    }

    /**
     * Permission belongs to a menu.
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    /**
     * Permission has many designation assignments.
     */
    public function designationPermissions(): HasMany
    {
        return $this->hasMany(DesignationPermission::class);
    }

    /**
     * Permission belongs to many designations.
     */
    public function designations(): BelongsToMany
    {
        return $this->belongsToMany(
            Designation::class,
            'designation_permissions'
        )->withTimestamps();
    }

    /**
     * Scope active permissions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}