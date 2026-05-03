<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes; // ✅ REQUIRED

class Warehouse extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'account_id',
        'warehouse_code',
        'name',
        'manager_name',
        'phone',
        'email',
        'address',
        'status',
        'created_by',
        'updated_by'
    ];

    /*
    |--------------------------------------------------------------------------
    | Accessor & Mutator: Name
    |--------------------------------------------------------------------------
    */

    protected function name(): Attribute
    {
        return Attribute::make(
            // Get → when displaying
            get: fn ($value) => $value ? ucwords($value) : null,

            // Set → when saving
            set: fn ($value) => $value ? strtolower(trim($value)) : null
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Accessor & Mutator: Manager Name
    |--------------------------------------------------------------------------
    */

    protected function managerName(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? ucwords($value) : null,
            set: fn ($value) => $value ? strtolower(trim($value)) : null
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // Warehouse → Product Stocks
    public function stocks()
    {
        return $this->hasMany(ProductStock::class);
    }

    // Warehouse → Stock Movements
    public function movements()
    {
        return $this->hasMany(StockMovement::class);
    }

    // Transfers FROM this warehouse
    public function transfersFrom()
    {
        return $this->hasMany(StockTransfer::class, 'from_warehouse_id');
    }

    // Transfers TO this warehouse
    public function transfersTo()
    {
        return $this->hasMany(StockTransfer::class, 'to_warehouse_id');
    }
    
    public function scopeOfAccount($query)
    {
        return $query->where('account_id', auth()->user()->account_id);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}