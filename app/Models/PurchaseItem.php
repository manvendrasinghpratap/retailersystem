<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    protected $fillable = [
        'purchase_id',
        'master_item_id',
        'quantity',
        'cost_price',
        'total'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function masterItem()
{
    return $this->belongsTo(MasterItem::class, 'master_item_id');
}

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

}