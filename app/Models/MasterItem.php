<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class MasterItem extends Model
{
    use SoftDeletes;

    protected $table = 'master_items';

    protected $fillable = [
        'category_id',
        'account_id',
        'name',
        'code',
        'description',
        'status',
        'image',
        'created_by',
    ];

    protected $casts = [
        'status' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Boot
    |--------------------------------------------------------------------------
    */

    protected static function booted()
    {
        static::creating(function ($item) {

            if (auth()->check()) {
                $item->account_id = auth()->user()->account_id;
                $item->created_by = auth()->id();
            }

            if (empty($item->code)) {
                $item->code = self::generateCode();
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors & Mutators
    |--------------------------------------------------------------------------
    */

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn($value) => ucwords($value),
            set: fn($value) => strtolower(trim($value))
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeAccount(Builder $query): Builder
    {
        if (auth()->check()) {
            return $query->where('account_id', auth()->user()->account_id);
        }

        return $query;
    }
    public function scopeOfAccount($query)
    {
        return $query->where(
            'account_id',
            auth()->user()->account_id
        );
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 1);
    }

    /*
    |--------------------------------------------------------------------------
    | Dropdown Helper
    |--------------------------------------------------------------------------
    */

    public static function dropdown()
    {
        return self::account()
            ->active()
            ->orderBy('name', 'asc')
            ->pluck('name', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | Code Generator
    |--------------------------------------------------------------------------
    */

    public static function generateCode(): string
    {
        $lastItem = self::account()
            ->orderByDesc('id')
            ->first();

        $nextNumber = 1001;

        if ($lastItem && preg_match('/ITM(\d+)/', $lastItem->code, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        }

        return 'ITM' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function stocks()
    {
        return $this->hasMany(ProductStock::class, 'master_item_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}