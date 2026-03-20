<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'account_id',
        'category_id',
        'name',
        'slug',
        'sku',
        'barcode',
        'price',
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

    public function category()
    {
        return $this->belongsTo(Category::class);
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
}