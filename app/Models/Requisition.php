<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
class Requisition extends Model
{
    protected $table = 'requisitions';

    /*
    |--------------------------------------------------------------------------
    | STATUS CONSTANTS
    |--------------------------------------------------------------------------
    */

    const STATUS_CANCELLED = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_PARTIAL = 2;
    const STATUS_COMPLETED = 3;

    /*
    |--------------------------------------------------------------------------
    | FILLABLE
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'account_id',
        'from_warehouse_id',
        'for_store_id',
        'requisition_no',
        'date',
        'total_qty',
        'status',
        'created_by',
    ];

    /*
    |--------------------------------------------------------------------------
    | CASTS
    |--------------------------------------------------------------------------
    */

    protected $casts = [
        'date' => 'date',
        'total_qty' => 'decimal:2',
        'status' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function items()
    {
        return $this->hasMany(RequisitionItem::class, 'requisition_id');
    }

    public function fromWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'for_store_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeOfAccount($query)
    {
        return $query->where(
            'account_id',
            auth()->user()->account_id
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    public function getStatusLabelAttribute()
    {
        return match ($this->status) {

            self::STATUS_ACTIVE =>
            'Active',

            self::STATUS_PARTIAL =>
            'Partial To Store',

            self::STATUS_COMPLETED =>
            'Moved To Store',

            self::STATUS_CANCELLED =>
            'Cancelled',

            default =>
            'Unknown',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    public function updateStatusByItems()
    {
        // skip if cancelled
        if ($this->status == self::STATUS_CANCELLED) {
            return $this;
        }

        $pendingItems = $this->items()
            ->whereNull('accepted_by')
            ->count();

        $this->update([
            'status' => $pendingItems > 0
                ? self::STATUS_PARTIAL
                : self::STATUS_COMPLETED
        ]);

        return $this;
    }

    public function refreshStatus()
    {
        $totalItems = $this->items()->count();

        // =========================
        // CANCELLED ITEMS
        // =========================
        $cancelledItems = $this->items()
            ->where('status', 0)
            ->count();

        // =========================
        // PENDING ITEMS
        // status = 1
        // accepted_by = null
        // =========================
        $pendingItems = $this->items()
            ->where('status', 1)
            ->whereNull('accepted_by')
            ->count();

        // =========================
        // FULLY CANCELLED
        // =========================
        if ($cancelledItems == $totalItems) {

            $this->update([
                'status' => 0
            ]);

            return;
        }

        // =========================
        // PARTIAL / PENDING
        // =========================
        if ($pendingItems > 0) {

            $this->update([
                'status' => 2
            ]);

            return;
        }

        // =========================
        // COMPLETED
        // =========================
        $this->update([
            'status' => 3
        ]);
    }

    protected function totalQty(): Attribute
    {
        return Attribute::make(
            get: fn($value) => (int) $value,
            set: fn($value) => (int) $value,
        );
    }
}