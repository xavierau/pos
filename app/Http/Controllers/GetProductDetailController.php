<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductWarehouse;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GetProductDetailController extends BaseController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Product $product)
    {
        $product->load('variants', 'unit', 'warehouses');

        $this->authorizeForUser($request->user('api'), 'view', Product::class);

        //get warehouses assigned to user
        $user_auth = auth()->user();
        if ($user_auth->is_all_warehouses) {
            $warehouses = Warehouse::get(['id', 'name']);
        } else {
            $warehouses = Warehouse::whereIn(
                'id',
                fn($q) => $q->select('warehouse_id')
                    ->from('user_warehouse')
                    ->where('user_id', $user_auth->id))
                ->get(['id', 'name']);
        }

        $item['id'] = $product->id;
        $item['type'] = $product->type;
        $item['code'] = $product->code;
        $item['type_barcode'] = $product->type_barcode;
        $item['name'] = $product->name;
        $item['note'] = $product->note;
        $item['category'] = $product['category']->name;
        $item['brand'] = $product['brand'] ? $product['brand']->name : 'N/D';
        $item['price'] = $product->price;
        $item['cost'] = $product->cost;
        $item['stock_alert'] = $product->stock_alert;
        $item['tax'] = $product->tax_net;
        $item['tax_method'] = $product->tax_method == '1' ? 'Exclusive' : 'Inclusive';

       $this->updateWithProductType($item, $product);

        if ($product->is_variant) {
            $productsVariants = $product->variants;
            foreach ($productsVariants as $variant) {
                $ProductVariant['code'] = $variant->code;
                $ProductVariant['name'] = $variant->name;
                $ProductVariant['cost'] = number_format($variant->cost, 2, '.', ',');
                $ProductVariant['price'] = number_format($variant->price, 2, '.', ',');
                $item['products_variants_data'][] = $ProductVariant;
                foreach ($warehouses as $warehouse) {
                    $product_warehouse = ProductWarehouse::where('product_id', $product->id)
                        ->where('warehouse_id', $warehouse->id)
                        ->where('product_variant_id', $variant->id)
                        ->select(DB::raw('SUM(product_warehouse.qte) AS sum'))
                        ->first();

                    $war_var['mag'] = $warehouse->name;
                    $war_var['variant'] = $variant->name;
                    $war_var['qte'] = $product_warehouse->sum;
                    $item['CountQTY_variants'][] = $war_var;
                }
            }
        }

        foreach ($warehouses as $warehouse) {
            $product_warehouse_data = ProductWarehouse::where('product_id', $product->id)
                ->where('warehouse_id', $warehouse->id)
                ->select(DB::raw('SUM(product_warehouse.qte) AS sum'))
                ->first();

            $war['mag'] = $warehouse->name;
            $war['qte'] = $product_warehouse_data->sum;
            $item['CountQTY'][] = $war;
        }

        if ($product->image != '') {
            foreach (explode(',', $product->image) as $img) {
                $item['images'][] = $img;
            }
        }

        $data[] = $item;

        return response()->json($data[0]);
    }

    private function updateWithProductType(&$item, Product $product){
        if ($product->type == 'is_single') {
            $item['is_variant'] = 'no';
            $item['type_name'] = 'Single';
            $item['unit'] = $product['unit']->short_name;
        } elseif ($product->type == 'is_variant') {
            $item['is_variant'] = 'yes';
            $item['type_name'] = 'Variable';
            $item['unit'] = $product['unit']->short_name;
            $item['CountQTY_variants'] = [];
        } else {
            $item['is_variant'] = 'no';
            $item['type_name'] = 'Service';
            $item['unit'] = '----';
        }
    }
}
