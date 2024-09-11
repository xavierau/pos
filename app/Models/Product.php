<?php

namespace App\Models;

use App\Services\products\GetUnitCostAndPrice;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\DiscountedPrice\Traits\HasDiscountedPrice;

class Product extends Model
{
    use HasFactory, HasDiscountedPrice, SoftDeletes;

    protected $dates = [
        'deleted_at',
    ];

    protected $fillable = [
        'code', 'type_barcode', 'name', 'cost', 'price', 'unit_id', 'unit_sale_id', 'unit_purchase_id',
        'stock_alert', 'category_id', 'is_imei',
        'tax_method', 'image', 'brand_id', 'is_active', 'note', 'type',
        'tax_net',
    ];

    protected $casts = [
        'category_id' => 'integer',
        'unit_id' => 'integer',
        'unit_sale_id' => 'integer',
        'unit_purchase_id' => 'integer',
        'brand_id' => 'integer',
        'is_active' => 'integer',
        'cost' => 'double',
        'price' => 'double',
        'stock_alert' => 'double',
        'tax_net' => 'double',
        'is_imei' => 'boolean',
        'promotional_start_date' => 'date:Y-m-d',
        'promotional_end_date' => 'date:Y-m-d',
    ];

    public function ProductVariant()
    {
        return $this->belongsTo('App\Models\ProductVariant');
    }

    public function variants(): HasMany
    {
        return $this->hasMany('App\Models\ProductVariant');
    }

    public function warehouses(): HasMany
    {
        return $this->hasMany(ProductWarehouse::class);
    }

    public function PurchaseDetail()
    {
        return $this->belongsTo('App\Models\PurchaseDetail');
    }

    public function SaleDetail()
    {
        return $this->belongsTo('App\Models\SaleDetail');
    }

    public function QuotationDetail()
    {
        return $this->belongsTo('App\Models\QuotationDetail');
    }

    public function category()
    {
        return $this->belongsTo('App\Models\Category');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function unitPurchase()
    {
        return $this->belongsTo(Unit::class, 'unit_purchase_id');
    }

    public function unitSale()
    {
        return $this->belongsTo(Unit::class, 'unit_sale_id');
    }

    public function brand()
    {
        return $this->belongsTo('App\Models\Brand');
    }

    public function check_code_exist($code)
    {
        $check_code = Product::where('code', $code)->whereNull('deleted_at')->first();
        if ($check_code) {
            $this->generate_random_code($code);
        } else {
            return $code;
        }
    }

    public function generate_random_code($value_code)
    {
        if ($value_code == '') {
            $gen_code = substr(number_format(time() * mt_rand(), 0, '', ''), 0, 8);
            $this->check_code_exist($gen_code);
        } else {
            $this->check_code_exist($value_code);
        }
    }

    public static function newFactory()
    {
        return ProductFactory::new();
    }

    // region Accessors

    public function getIsVariantAttribute()
    {
        return $this->type === "is_variant";
    }

    // endregion

    // region scopes

    public function scopeSearch(Builder $query, ?string $search)
    {
        return $search ?
            $query->where('products.name', 'LIKE', "%{$search}%")
                ->orWhere('products.code', 'LIKE', "%{$search}%")
                ->orWhere(fn($query) => $query->whereHas('category', fn($q) => $q->where('categories.name', 'LIKE', "%{$search}%")))
                ->orWhere(fn($query) => $query->whereHas('brand', fn($q) => $q->where('brands.name', 'LIKE', "%{$search}%")))
                ->orWhere(fn($query) => $query->whereHas('variants', fn($q) => $q->where('product_variants.name', 'LIKE', "%{$search}%")
                    ->orWhere('product_variants.code', 'LIKE', "%{$search}%"))) :
            $query;
    }
    public function scopeActive(Builder $query){
        return $query->where('is_active', true);
    }

    // endregion

    // region helpers

    public function getUnitPurchaseCost()
    {
        return GetUnitCostAndPrice::getUnitAmount($this->cost, $this->unitPurchase);
    }

    public function getUnitPrice()
    {
        return GetUnitCostAndPrice::getUnitAmount($this->price, $this->unitSale, $this->price);
    }

    public function removeImages(): void
    {
        foreach (explode(',', $this->image) as $img) {
            $pathIMG = public_path() . '/images/products/' . $img;
            if (file_exists($pathIMG)) {
                if ($img != 'no-image.png') {
                    @unlink($pathIMG);
                }
            }
        }
    }

    // endregion


}
