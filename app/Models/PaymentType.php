<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
     * Boot Method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (auth()->check() && empty($model->account_id)) {
                $model->account_id = auth()->user()->account_id;
            }
        });
    }

    /**
     * Scope Account Records
     */
    public function scopeOfAccount($query)
    {
        if (auth()->check()) {
            return $query->where('account_id', auth()->user()->account_id);
        }

        return $query;
    }

    /**
     * Scope Active Records
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Account Relationship
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Status Text Accessor
     */
    public function getStatusTextAttribute()
    {
        return $this->status ? 'Active' : 'Inactive';
    }

    /**
     * Dropdown Options
     */
    public static function getSelectable($activeOnly = true)
    {
        $query = self::ofAccount();

        if ($activeOnly) {
            $query->active();
        }

        return $query
            ->orderBy('id')
            ->pluck('name', 'short_name')
            ->toArray();
    }
}