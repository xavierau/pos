<?php

namespace App\Models;

use App\Services\products\GetUnitCostAndPrice;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\DiscountedPrice\Traits\HasDiscountedPrice;

class ProductVariant extends Model
{
    use HasDiscountedPrice, HasFactory, SoftDeletes;

    protected $table = 'product_variants';

    protected $fillable = [
        'product_id', 'name', 'qty', 'cost', 'price', 'code', 'image'
    ];

    protected $casts = [
        'product_id' => 'integer',
        'qty' => 'double',
        'cost' => 'double',
        'price' => 'double',
    ];

    // region relationship

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // endregion

    // region helpers

    public function getUnitPurchaseCost()
    {
        return GetUnitCostAndPrice::getUnitAmount($this->cost, $this->product->unitPurchase);
    }

    public function getUnitPrice()
    {
        return GetUnitCostAndPrice::getUnitAmount($this->price, $this->product->unitSale, $this->price);
    }

    // endregion

}
