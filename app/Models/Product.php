<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{

    protected $dates = [
        'deleted_at',
        'promotional_start_date' => 'date:Y-m-d',
        'promotional_end_date' => 'date:Y-m-d',
    ];

    protected $fillable = [
        'code', 'type_barcode', 'name', 'cost', 'price', 'unit_id', 'unit_sale_id', 'unit_purchase_id',
        'stock_alert', 'category_id', 'sub_category_id', 'is_variant', 'is_imei',
        'tax_method', 'image', 'brand_id', 'is_active', 'note', 'type', 'promotional_price',
        'promotional_start_date',
        'promotional_end_date',
    ];

    protected $casts = [
        'category_id' => 'integer',
        'sub_category_id' => 'integer',
        'unit_id' => 'integer',
        'unit_sale_id' => 'integer',
        'unit_purchase_id' => 'integer',
        'is_variant' => 'integer',
        'is_imei' => 'integer',
        'brand_id' => 'integer',
        'is_active' => 'integer',
        'cost' => 'double',
        'price' => 'double',
        'stock_alert' => 'double',
        'TaxNet' => 'double',
        'promotional_price' => 'double',

    ];


    protected $appends = ['active_price'];

    public function ProductVariant()
    {
        return $this->belongsTo('App\Models\ProductVariant');
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
        return $this->belongsTo('App\Models\Unit', 'unit_id');
    }

    public function unitPurchase()
    {
        return $this->belongsTo(Unit::class, 'unit_purchase_id');
    }

    public function unitSale()
    {
        return $this->belongsTo('App\Models\Unit', 'unit_sale_id');
    }

    public function brand()
    {
        return $this->belongsTo('App\Models\Brand');
    }

    public function getActivePrice()
    {

        // if promotional price is not null and start date and end date is not null and within the range
        if ($this->promotional_price != null && $this->promotional_start_date != null && $this->promotional_end_date != null) {
            $today = date('Y-m-d');
            if ($today >= $this->promotional_start_date && $today <= $this->promotional_end_date) {
                return $this->promotional_price;
            }
        }

        return $this->price;
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

    // region scopes

    public function scopeSearch(Builder $query, ?string $search)
    {
        return $search ?
            $query->where('products.name', 'LIKE', "%{$search}%")
            ->orWhere('products.code', 'LIKE', "%{$search}%")
            ->orWhere(fn($query) => $query->whereHas('category', fn($q) => $q->where('name', 'LIKE', "%{$search}%")))
            ->orWhere(fn($query) => $query->whereHas('brand', fn($q) => $q->where('name', 'LIKE', "%{$search}%"))) :
            $query;
    }

    // endregion

    // region helpers

    public function getUnitPurchaseCost()
    {
        if (!$this->unitPurchase) {
            return 0;
        }

        return $this->unitPurchase->operator == '/' ?
            $this->cost / $this->unitPurchase->operator_value :
            $this->cost * $this->unitPurchase->operator_value;
    }

    public function getUnitPrice()
    {
        if (!$this->unitSale) {
            return $this->price;
        }

        return $this->unitSale->operator == '/' ?
            $this->price / $this->unitSale->operator_value :
            $this->price * $this->unitSale->operator_value;
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
