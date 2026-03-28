<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Product extends Model
{
    protected $fillable = [
        'account_id',
        'category_id',
        'name',
        'slug',
        'sku',
        'barcode',
        'selling_price',
        'cost_price',
        'status',
        'description',
        'deleted_at',
        'is_deleted',
        'deleted_by',
    ];

    protected static function booted()
    {
        // Auto set values when creating
        static::creating(function ($product) {

            // Multi-tenant safety
            if (auth()->check()) {
                $product->account_id = auth()->user()->account_id;
            }

            // Auto slug
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }

            // Default status
            if (is_null($product->status)) {
                $product->status = 1;
            }
        });

        static::updating(function ($product) {
            if ($product->isDirty('name')) {
                $product->slug = \Str::slug($product->name);
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    protected function name(): Attribute
    {
        return Attribute::make(

            // ✅ Accessor (GET)
            get: fn($value) => ucwords($value),

            // ✅ Mutator (SET)
            set: fn($value) => strtolower(trim($value))
        );
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function stock()
    {
        return $this->hasOne(Inventory::class);
    }

    public function stockAdjustments()
    {
        return $this->hasMany(StockAdjustment::class);
    }

    public function scopeNotDeleted($query)
    {
        return $query->where('is_deleted', 0);
    }
    public function scopeOfAccount($query)
    {
        return $query->where('account_id', auth()->user()->account_id);
    }
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
    public function scopeGetProducts($query)
    {
        return $query->with('category')
            ->where('account_id', auth()->user()->account_id)->notDeleted()
            ->latest();
    }

    public static function getProductPluck()
    {
        return Product::ofAccount()->notDeleted()->active()->pluck('name', 'id');
    }
}