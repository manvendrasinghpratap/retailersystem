<?php

namespace App\Models;

use App\Helpers\Settings;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class PaymentType extends Model
{
    use HasFactory;

    protected $table = 'payment_types';

    protected $fillable = [
        'account_id',
        'short_name',
        'name',
        'status',
    ];

    protected $casts = [
        'account_id' => 'integer',
        'status' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 1,
    ];

    /**
     * Automatically assign account_id.
     */
    protected static function booted()
    {
        static::creating(function ($model) {

            if (auth()->check()) {

                $model->account_id ??= auth()->user()->account_id;
            }
        });
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn($value) => ucwords(strtolower($value)),
            set: fn($value) => ucwords(strtolower(trim($value))),
        );
    }
    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope records by logged-in account.
     */
    public function scopeOfAccount($query)
    {
        if (auth()->check()) {
            $query->where('account_id', auth()->user()->account_id);
        }

        return $query;
    }

    /**
     * Scope active records.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Default ordering.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('id');
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    /**
     * Active / Inactive text.
     */
    public function getStatusTextAttribute()
    {
        return $this->status
            ? __('translation.active')
            : __('translation.inactive');
    }

    /**
     * Display Name
     * Example:
     * Cash (cash)
     */
    public function getDisplayNameAttribute()
    {
        return sprintf(
            '%s (%s)',
            $this->name,
            $this->short_name
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
     * Dropdown options.
     *
     * Returns:
     * [
     *     'cash' => 'Cash',
     *     'card' => 'Card'
     * ]
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
            ->pluck('name', 'short_name')
            ->toArray();
    }

    /**
     * Dropdown with display names.
     *
     * Returns:
     * [
     *     'cash' => 'Cash (cash)',
     *     'card' => 'Card (card)'
     * ]
     */
    public static function getSelectableDisplay($activeOnly = true)
    {
        $query = self::query()
            ->ofAccount()
            ->ordered();

        if ($activeOnly) {
            $query->active();
        }

        return $query
            ->get()
            ->pluck('display_name', 'short_name')
            ->toArray();
    }

    /**
     * Return full collection.
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