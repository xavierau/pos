<?php

namespace App\Services\products;

use App\DTO\GetProductsByWarehouseDTO;
use App\Enums\ProductType;
use App\Models\ProductWarehouse;
use Modules\Promotions\DTO\PosProduct;

class GetProductsByWarehouse
{
    public function execute(GetProductsByWarehouseDTO $dto): array
    {
        $product_warehouse_data = ProductWarehouse::with('warehouse',
            'product.activeDiscountedPrices',
            'productVariant.activeDiscountedPrices')
            ->where(fn($query) => $query->where('warehouse_id', $dto->warehouse_id)
                ->when($dto->is_sale, fn($q) => $q->whereHas('product', fn($query) => $query->where('not_selling', 0)))
                ->when($dto->included_empty_stock === false, fn($q) => $q->where('qty', '>', 0))
                ->where(function ($query) use ($dto) {
                    if ($dto->product_service == '0') {
                        return $query->Where('manage_stock', true);
                    }elseif ($dto->stock == '0') {
                        return $query->Where('manage_stock', false);
                    }
                })
            )
            ->get();

        return $product_warehouse_data->reduce(function ($carry, $pw) use ($product_warehouse_data) {

            if ($pw->productVariant) {
                $carry[] = new PosProduct(
                    product_id: $pw->product_id,
                    name: $pw->product->name,
                    code: $pw->productVariant->code,
                    unit_price: $pw->productVariant->price,
                    sale_unit: $pw->product->unitSale->short_name,
                    sale_unit_id: $pw->product->unitSale->id,
                    available_qty: $pw->qty,
                    image: $pw->product->image,
                    has_imei: $pw->product->is_imei,
                    discounted_price: $pw->productVariant->activeDiscountedPrices->first()?->discouted_price,
                    product_variant_id: $pw->product_variant_id,
                );
                return $carry;

            } elseif ($pw->product->type === ProductType::Service) {
                $carry[] = new PosProduct(
                    product_id: $pw->product_id,
                    name: $pw->product->name,
                    code: $pw->product->code,
                    unit_price: $pw->product->price,
                    sale_unit: $pw->product->unitSale->short_name,
                    sale_unit_id: $pw->product->unitSale->id,
                    available_qty: 1,
                    image: $pw->product->image,
                    discounted_price: $pw->product->activeDiscountedPrices->first()?->discouted_price,
                );
            } else {
                $carry[] = new PosProduct(
                    product_id: $pw->product_id,
                    name: $pw->product->name,
                    code: $pw->product->code,
                    unit_price: $pw->product->price,
                    sale_unit: $pw->product->unitSale->short_name,
                    sale_unit_id: $pw->product->unitSale->id,
                    available_qty: $pw->qty,
                    image: $pw->product->image,
                    has_imei: $pw->product->is_imei,
                    discounted_price: $pw->product->activeDiscountedPrices->first()?->discouted_price,
                );
            }

            return $carry;

        }, []);
    }
}
