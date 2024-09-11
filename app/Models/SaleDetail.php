<?php

namespace App\Models;

use Database\Factories\SaleDetailFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'date', 'sale_id', 'sale_unit_id', 'quantity', 'product_id', 'product_variant_id',
        'price', 'tax_net', 'discount', 'discount_method', 'tax_method'
    ];

    protected $casts = [
        'id' => 'integer',
        'quantity' => 'double',
        'sale_id' => 'integer',
        'sale_unit_id' => 'integer',
        'product_id' => 'integer',
        'product_variant_id' => 'integer',
        'price' => 'double',
        'tex_net' => 'double',
        'discount' => 'double',
    ];

    public function sale()
    {
        return $this->belongsTo('App\Models\Sale');
    }

    public function unitSale()
    {
        return $this->belongsTo('App\Models\Unit', 'sale_unit_id');
    }

    public function product()
    {
        return $this->belongsTo('App\Models\Product');
    }

    public function productVariant()
    {
        return $this->belongsTo('App\Models\ProductVariant');
    }

    public static function newFactory()
    {
        return SaleDetailFactory::new();
    }

    // region Accessors

    public function getTotalAttribute(): float
    {
        return $this->quantity * $this->price;
    }

    public function getSubTotalAttribute()
    {
        return $this->net_price * $this->quantity + $this->tax_price * $this->quantity;
    }

    public function getDiscountNetAttribute()
    {
        return ($this->discount_method === '1') ?
            $this->price * $this->discount / 100 :
            $this->discount;
    }

    public function getTaxPriceAttribute()
    {
        return ($this->tax_method === '1') ?
            $this->tax_net * (($this->price - $this->discount_net) / 100) :
            $this->price - $this->net_price - $this->discount_net;
    }

    public function getNetPriceAttribute()
    {
        return ($this->tax_method === '1') ?
            $this->price - $this->discount_net :
            ($this->price - $this->discount_net) / (($this->tax_net / 100) + 1);

    }

    // endregion

}
