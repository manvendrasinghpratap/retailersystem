<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Designation extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'name',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
    public static function getSelectable()
    {
        return self::ofAccount()->where('status', 1)->orderBy('name', 'asc')
            ->pluck('name', 'id')
            ->toArray();
    }
    public static function getSelectableSpecific($accountId)
    {
        return self::where('account_id', $accountId)->where('status', 1)->orderBy('name', 'asc')
            ->pluck('name', 'id')
            ->toArray();
    }
    public static function getDesignationIdOfCashier()
    {
        return self::ofAccount()->where('name', 'Cashier')->first()->id;
    }

    /**
     * Scope active designations.
     */
    public function scopeOfActive($query)
    {
        return $query->where('is_deleted', 0);
    }

    /**
     * Scope account designations.
     */
    public function scopeOfAccount($query)
    {
        return $query->where('account_id', auth()->user()->account_id);
    }

    /**
     * Existing route permissions.
     */
    public function routes(): BelongsToMany
    {
        return $this->belongsToMany(
            Route::class,
            'designation_route',
            'designation_id',
            'route_id'
        )->withPivot('is_allowed');
    }

    /**
     * Direct permission assignments.
     */
    public function designationPermissions(): HasMany
    {
        return $this->hasMany(DesignationPermission::class, 'designation_id');
    }

    /**
     * Permissions assigned to this designation.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'designation_permissions',
            'designation_id',
            'permission_id'
        )->withPivot('account_id')
            ->withTimestamps();
    }
    public function getCreatedDateAttribute()
    {
        return $this->created_at?->format(config('constants.dateformat.slashdmy'));
    }
}
