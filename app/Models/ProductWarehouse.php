<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ProductWarehouse extends Model
{
    protected $table = 'product_warehouse';

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'qte',
        'manage_stock'
    ];

    protected $casts = [
        'product_id' => 'integer',
        'warehouse_id' => 'integer',
        'manage_stock' => 'integer',
        'qte' => 'double',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    // region Scopes

    public function scopeGetProductAlert(Builder $query, $warehouse_id, $array_warehouses_id)
    {

        return $query->join('products', 'product_warehouse.product_id', '=', 'products.id')
            ->where('manage_stock', true)
            ->whereRaw('qte <= stock_alert')
            ->where('product_warehouse.deleted_at', null)
            ->where(fn($query) => ($warehouse_id !== 0) ?
                $query->where('product_warehouse.warehouse_id', $warehouse_id) :
                $query->whereIn('product_warehouse.warehouse_id', $array_warehouses_id))
            ->take('5');


    }

    // endregion

    // region Helpers

    public static function getStockAlert($warehouse_id, $array_warehouses_id, $limit = 5)
    {
        $product_warehouse_data = static::with('warehouse', 'product','productVariant')
            ->getProductAlert($warehouse_id, $array_warehouses_id)
            ->take('5')
            ->get();

        $stock_alert = [];
        if ($product_warehouse_data->isNotEmpty()) {

            foreach ($product_warehouse_data as $product_warehouse) {
                if ($product_warehouse->qte <= $product_warehouse['product']->stock_alert) {
                    $item['code'] = ($product_warehouse->product_variant_id !== null) ?
                        $product_warehouse['productVariant']->name . '-' . $product_warehouse['product']->code :
                        $product_warehouse['product']->code;

                    $item['quantity'] = $product_warehouse->qte;
                    $item['name'] = $product_warehouse['product']->name;
                    $item['warehouse'] = $product_warehouse['warehouse']->name;
                    $item['stock_alert'] = $product_warehouse['product']->stock_alert;
                    $stock_alert[] = $item;
                }
            }

        }

        return $stock_alert;

    }

    // endregion

}
