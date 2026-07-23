<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class RequisitionItem extends Model
{
    protected $table = 'requisition_items';

    protected $fillable = [
        'requisition_id',
        'master_item_id',
        'qty',

        // Status
        'status',

        // Acceptance
        'accepted_by',
        'accepted_at',

        // Cancellation
        'cancelled_by',
        'cancelled_at',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'status' => 'integer',

        'accepted_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function requisition()
    {
        return $this->belongsTo(Requisition::class);
    }

    public function masterItem()
    {
        return $this->belongsTo(MasterItem::class, 'master_item_id');
    }

    public function acceptedBy()
    {
        return $this->belongsTo(User::class, 'accepted_by');
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 0);
    }

    public function scopePending($query)
    {
        return $query
            ->where('status', 1)
            ->whereNull('accepted_by');
    }

    public function scopeAccepted($query)
    {
        return $query
            ->where('status', 1)
            ->whereNotNull('accepted_by');
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    public function getStatusLabelAttribute()
    {
        if ($this->status == 0) {
            return 'Cancelled';
        }

        if (!is_null($this->accepted_by)) {
            return 'Accepted';
        }

        return 'Pending';
    }

    protected function qty(): Attribute
    {
        return Attribute::make(
            get: fn($value) => (int) $value,
            set: fn($value) => (int) $value,
        );
    }

}