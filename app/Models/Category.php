<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'image',
        'status',
        'is_deleted',
        'created_by',
        'description',
    ];

    // Additional model methods and relationships can be defined here
    protected function name(): Attribute
    {
        return Attribute::make(
            // Accessor (get)
            get: fn ($value) => ucwords($value),

            // Mutator (set)
            set: fn ($value) => ucwords(trim($value))
        );
    }

    /* ---------- SLUG ---------- */
    protected function slug(): Attribute
    {
        return Attribute::make(
            set: fn ($value, $attributes) =>
                $value ?: Str::slug($attributes['name'])
        );
    }
}

