<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductModifier extends Model
{
    protected $fillable = [
        'account_id',
        'product_id',
        'name',
        'is_required',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->account_id = auth()->user()->account_id;
            }
        });
    }

    /* Relationships */

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function options()
    {
        return $this->hasMany(ModifierOption::class, 'modifier_id');
    }
}