<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;


class Category extends Model
{
    protected $fillable = [
        'account_id',
        'name',
        'slug',
        'image',
        'status',
        'is_deleted',
        'created_by',
        'description',
    ];

    protected $casts = [
        'status' => 'boolean',
        'is_deleted' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Accessors & Mutators
    |--------------------------------------------------------------------------
    */

    // Format Name (Auto Capitalize)
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn($value) => ucwords($value),
            set: fn($value) => ucwords(trim($value))
        );
    }

    // Auto Generate Slug
    protected function slug(): Attribute
    {
        return Attribute::make(
            set: function ($value, $attributes) {
                return $value ?: Str::slug($attributes['name'] ?? '');
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes (VERY IMPORTANT FOR SAAS)
    |--------------------------------------------------------------------------
    */

    // Filter by account automatically
    public function scopeOfAccount($query)
    {
        return $query->where('account_id', auth()->user()->account_id);
    }

    // Active categories only
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    // Not deleted
    public function scopeNotDeleted($query)
    {
        return $query->where('is_deleted', 0);
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships (Future Use - Product Module)
    |--------------------------------------------------------------------------
    */

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public static function createCategory($request, $imagePath = null)
    {
        return self::create([
            'account_id' => auth()->user()->account_id,
            'name' => $request->name,
            'slug' => Str::slug($request->slug),
            'image' => $imagePath,
            'status' => $request->status ?? 1,
            'is_deleted' => 0,
            'created_by' => auth()->id(),
            'description' => $request->description ?? '',
        ]);
    }

    public static function updateCategory($category, $request, $imagePath = null)
    {
        return $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->slug),
            'image' => $imagePath,
            'status' => $request->status ?? 1,
            'description' => $request->description ?? '',
        ]);
    }


    public static function getCategoriesPluck()
    {
        return Category::ofAccount()->notDeleted()->active()->pluck('name', 'id');
    }


    public static function getCategories()
    {
        return Category::ofAccount()->notDeleted()->latest();
    }

}