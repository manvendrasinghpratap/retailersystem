<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'account_id',
        'product_id',
        'type',
        'quantity',
        'reference_id',
        'note',
        'created_by',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {

            if (auth()->check()) {
                $model->account_id = auth()->user()->account_id;
                $model->created_by = auth()->id();
            }

            // 🔥 AUTO INVENTORY UPDATE (CORE POS LOGIC)
            $inventory = Inventory::firstOrCreate(
                ['product_id' => $model->product_id],
                ['stock' => 0]
            );

            switch ($model->type) {
                case 'add':
                case 'return':
                    $inventory->increaseStock($model->quantity);
                    break;

                case 'deduct':
                case 'sale':
                case 'damage':
                    // Prevent negative stock
                    if ($inventory->stock < $model->quantity) {
                        throw new \Exception('Insufficient stock');
                    }
                    $inventory->decreaseStock($model->quantity);
                    break;
            }
        });
    }

    /* Relationship */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}