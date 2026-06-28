<?php

namespace App\Models;

use App\Helpers\Settings;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditDuration extends Model
{
    use HasFactory;

    protected $table = 'credit_durations';

    protected $fillable = [
        'account_id',
        'name',
        'duration_days',
        'interest',
        'status',
        'created_by',
    ];

    protected $casts = [
        'account_id' => 'integer',
        'duration_days' => 'integer',
        'interest' => 'decimal:2',
        'status' => 'boolean',
        'created_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 1,
    ];

    /**
     * Automatically assign account_id and created_by
     */
    protected static function booted()
    {
        static::creating(function ($model) {

            if (auth()->check()) {

                $model->account_id ??= auth()->user()->account_id;
                $model->created_by ??= auth()->id();
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Filter by logged-in account.
     */
    public function scopeOfAccount($query)
    {
        if (auth()->check()) {
            $query->where('account_id', auth()->user()->account_id);
        }

        return $query;
    }

    /**
     * Active records only.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Order by duration.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('duration_days');
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    /**
     * Active / Inactive
     */
    public function getStatusTextAttribute()
    {
        return $this->status
            ? __('translation.active')
            : __('translation.inactive');
    }

    /**
     * Example:
     * 30 Days (5.00%)
     */
    public function getDisplayNameAttribute()
    {
        return sprintf(
            '%s (%.2f%%)',
            $this->name,
            $this->interest
        );
    }

    /**
     * Formatted created date.
     */
    public function getCreatedDateAttribute()
    {
        return Settings::getFormattedDatetime($this->created_at);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Get dropdown options.
     *
     * @param bool $activeOnly
     * @return array
     */
    public static function getSelectable($activeOnly = true)
    {
        $query = self::query()
            ->ofAccount()
            ->ordered();

        if ($activeOnly) {
            $query->active();
        }

        return $query
            ->get()
            ->pluck('display_name', 'id')
            ->toArray();
    }

    /**
     * Get collection with all data.
     *
     * @param bool $activeOnly
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getSelectableWithData($activeOnly = true)
    {
        $query = self::query()
            ->ofAccount()
            ->ordered();

        if ($activeOnly) {
            $query->active();
        }

        return $query->get();
    }
}