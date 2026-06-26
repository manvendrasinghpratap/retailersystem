<?php

namespace App\Models;

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
     * Auto assign account and creator
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {

            if (auth()->check()) {

                if (empty($model->account_id)) {
                    $model->account_id = auth()->user()->account_id;
                }

                if (empty($model->created_by)) {
                    $model->created_by = auth()->id();
                }
            }
        });
    }

    /**
     * Account Scope
     */
    public function scopeOfAccount($query)
    {
        if (auth()->check()) {
            return $query->where(
                'account_id',
                auth()->user()->account_id
            );
        }

        return $query;
    }

    /**
     * Active Scope
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Creator Relationship
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
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
            ->orderBy('duration_days')
            ->pluck('name', 'id')
            ->toArray();
    }

    /**
     * Status Text
     */
    public function getStatusTextAttribute()
    {
        return $this->status ? 'Active' : 'Inactive';
    }

    /**
     * Example:
     * 7 Days (0%)
     * 30 Days (5%)
     */
    public function getDisplayNameAttribute()
    {
        return "{$this->name} ({$this->interest}%)";
    }
}