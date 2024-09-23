<?php

namespace App\Services\products;

use App\Models\Product;
use App\Models\ProductVariant;

class GetProductDetail
{
    public function execute($productId, $variantId = null)
    {
        $product = Product::with('unit', 'unitPurchase', 'unitSale', 'activeDiscountedPrices')
            ->where('id', $productId)
            ->whereNull('deleted_at')
            ->first();

        $item = $this->populateItem($product);

        $p = ($product['type'] == 'is_variant') ?
            $this->handleVariantProduct($productId, $variantId, $item) :
            $product;

        $this->calculateCostsAndPrices($p, $item);

        return $item;
    }

    private function populateItem($product)
    {
        return [
            'id' => $product->id,
            'image' => $product->image,
            'product_type' => $product->type,
            'type_barcode' => $product->type_barcode,
            'unit_id' => $product->unit?->id ?? '',
            'unit' => $product->unit?->short_name ?? '',
            'purchase_unit_id' => $product->unitPurchase?->id ?? '',
            'unit_purchase' => $product->unitPurchase?->short_name ?? '',
            'sale_unit_id' => $product->unitSale?->id ?? '',
            'unit_sale' => $product->unitSale?->short_name ?? '',
            'tax_method' => $product->tax_method,
            'tax_percent' => $product->tax_net,
            'is_imei' => $product->is_imei,
            'not_selling' => $product->not_selling,
            'fix_price' => $product->price,
            'code' => $product->code,
            'name' => $product->name,
        ];
    }

    private function handleVariantProduct($productId, $variantId, &$item)
    {
        $productVariant = ProductVariant::with('activeDiscountedPrices', 'product.unitSale', 'product.unitPurchase')
            ->where('product_id', $productId)
            ->where('id', $variantId)
            ->firstOrFail();

        $item['fix_price'] = $productVariant->price;
        $item['code'] = $productVariant->code;
        $item['name'] = '[' . $productVariant->name . ']' . $productVariant->product->name;

        return $productVariant;
    }

    private function calculateCostsAndPrices(Product|ProductVariant $product, &$item)
    {

        $cost = $product->getUnitPurchaseCost();
        $price = $product->getUnitPrice();
        $discounted_price = $product->activeDiscountedPrices->first()?->discounted_price;

        $item['unit_cost'] = $cost;
        $item['unit_price'] = $price;
        $item['discounted_price'] = $discounted_price;

        $p = $product instanceof Product ? $product : $product->product;

        if ($product->tax_net !== 0.0) {
            $this->calculateTax($p, $item, $cost, $discounted_price ?? $price);
        } else {
            $this->calculateWithoutTax($item, $cost, $discounted_price ?? $price);
        }
    }

    private function calculateTax(Product $product, &$item, $cost, $price)
    {
        if ($product['tax_method'] == '1') {
            $tax_price = $price * $product['tax_net'] / 100;
            $tax_cost = $cost * $product['tax_net'] / 100;

            $item['total_cost'] = $cost + $tax_cost;
            $item['total_price'] = $price + $tax_price;
            $item['net_cost'] = $cost;
            $item['net_price'] = $price;
            $item['tax_price'] = $tax_price;
            $item['tax_cost'] = $tax_cost;
        } else {
            $item['total_cost'] = $cost;
            $item['total_price'] = $price;
            $item['net_cost'] = $cost / (($product['tax_net'] / 100) + 1);
            $item['net_price'] = $price / (($product['tax_net'] / 100) + 1);
            $item['tax_cost'] = $item['total_cost'] - $item['net_cost'];
            $item['tax_price'] = $item['total_price'] - $item['net_price'];
        }
    }

    private function calculateWithoutTax(&$item, $cost, $price)
    {
        $item['total_cost'] = $cost;
        $item['total_price'] = $price;
        $item['net_cost'] = $cost;
        $item['net_price'] = $price;
        $item['tax_price'] = 0;
        $item['tax_cost'] = 0;
    }
}
