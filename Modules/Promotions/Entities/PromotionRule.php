<?php
// Modules/Promotions/Entities/PromotionRule.php

namespace Modules\Promotions\Entities;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Promotions\Database\factories\PromotionRuleFactory;
use Modules\Promotions\Enums\PromotionType;

class PromotionRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'promotion_id', 'type', 'x', 'y', 'amount',
        'product_id', 'product_variant_id', 'category_id',
        'y_product_id', 'y_product_variant_id', 'y_category_id'
    ];

    protected static function newFactory()
    {
        return PromotionRuleFactory::new();
    }

    protected $casts = [
        'type' => PromotionType::class,
        'amount' => 'float',
    ];

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function yProduct()
    {
        return $this->belongsTo(Product::class, 'y_product_id');
    }

    public function yProductVariant()
    {
        return $this->belongsTo(ProductVariant::class, 'y_product_variant_id');
    }

    public function yCategory()
    {
        return $this->belongsTo(Category::class, 'y_category_id');
    }
}
