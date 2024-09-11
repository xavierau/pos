<?php

namespace App\Services\pos;

use App\DTO\PosGetProductsDTO;
use App\Enums\ProductType;
use App\Models\ProductVariant;
use App\Models\ProductWarehouse;
use Modules\Promotions\DTO\PosProduct;

class GetProducts
{
    public function execute(PosGetProductsDTO $dto): array
    {
        $offSet = ($dto->page * $dto->perPage) - $dto->perPage;
//        $data = [];

        $query = ProductWarehouse::where('warehouse_id', $dto->warehouse_id)
            ->with('product.activeDiscountedPrices',
                'product.unitSale',
                'productVariant.activeDiscountedPrices')
            ->where($this->getStockCondition($dto))
            ->where($this->getCategoryCondition($dto))
            ->where($this->getBrandCondition($dto));

        $totalRows = $query->count();

        $product_warehouse_data = $query->offset($offSet)->take($dto->perPage)->get();

        $data = $product_warehouse_data->reduce(function ($carry, $pw) use ($product_warehouse_data) {

            if ($pw->productVariant) {
                $carry[] = new PosProduct(
                    product_id: $pw->product_id,
                    name: $pw->product->name . ' - ' . $pw->productVariant->name,
                    code: $pw->productVariant->code,
                    unit_price: $pw->productVariant->price,
                    sale_unit: $pw->product->unitSale?->short_name,
                    sale_unit_id: $pw->product->unitSale?->id,
                    available_qty: $pw->qty,
                    image: explode(',', $pw->product->image)[0],
                    has_imei: $pw->product->is_imei,
                    not_selling: $pw->product->not_selling,
                    discounted_price: $pw->productVariant->activeDiscountedPrices->first()?->discouted_price,
                    product_variant_id: $pw->product_variant_id,
                );
                return $carry;

            } elseif ($pw->product->type === ProductType::Service) {
                $carry[] = new PosProduct(
                    product_id: $pw->product_id,
                    name: $pw->product->name,
                    code: $pw->prodcut->code,
                    unit_price: $pw->product->price,
                    sale_unit: $pw->product->unitSale?->short_name,
                    sale_unit_id: $pw->product->unitSale?->id,
                    available_qty: 1,
                    image: explode(',', $pw->product->image)[0],
                    is_service: true,
                    not_selling: $pw->product->not_selling,
                    discounted_price: $pw->product->activeDiscountedPrices->first()?->discouted_price
                );
            } else {
                $carry[] = new PosProduct(
                    product_id: $pw->product_id,
                    name: $pw->product->name,
                    code: $pw->product->code ?? '',
                    unit_price: $pw->product->price,
                    sale_unit: $pw->product->unitSale?->short_name,
                    sale_unit_id: $pw->product->unitSale?->id,
                    available_qty: $pw->qty,
                    image: explode(',', $pw->product->image)[0],
                    has_imei: $pw->product->is_imei,
                    not_selling: $pw->product->not_selling,
                    discounted_price: $pw->product->activeDiscountedPrices->first()?->discouted_price,
                );
            }

            return $carry;

        }, []);

//        foreach ($product_warehouse_data as $product_warehouse) {
//            $data[] = $this->getItemData($product_warehouse);
//        }

        return [$data, $totalRows];
    }

    private function getStockCondition(PosGetProductsDTO $dto)
    {
        return function ($query) use ($dto) {
            if ($dto->stock && $dto->product_service) {
                return $query->where('qty', '>', 0)->orWhere('manage_stock', false);
            }
            if ($dto->stock === false && $dto->product_service === false) {
                return $query->where('qty', '>', 0)->orWhere('manage_stock', true);
            }
            return $query->where('manage_stock', true);
        };
    }

    private function getCategoryCondition(PosGetProductsDTO $dto)
    {
        return fn($query) => $query->when($dto->category_id,
            fn($query) => $query->whereHas('product',
                fn($q) => $q->where('category_id', '=', $dto->category_id)));
    }

    private function getBrandCondition(PosGetProductsDTO $dto)
    {
        return fn($query) => $query->when($dto->brand_id,
            fn($query) => $query->whereHas('product',
                fn($q) => $q->where('brand_id', $dto->brand_id)));
    }

    private function getItemData(ProductWarehouse $product_warehouse)
    {
        if ($product_warehouse->product_variant_id) {
            $productsVariants = ProductVariant::with('activeDiscountedPrices')
                ->where('product_id', $product_warehouse->product_id)
                ->where('id', $product_warehouse->product_variant_id)
                ->whereNull('deleted_at')
                ->first();

            $item = $this->getVariantItemData($product_warehouse, $productsVariants);
        } else {
            $item = $this->getNonVariantItemData($product_warehouse);
        }

        $item['id'] = $product_warehouse->product_id;
        $item['image'] = explode(',', $product_warehouse->product->image)[0];
        $item['qte_sale'] = $this->getQteSale($product_warehouse);
        $item['unit_sale'] = $product_warehouse->product->unitSale ? $product_warehouse->product->unitSale->short_name : '';
        $item['qte'] = $product_warehouse->product->type != 'is_service' ? $product_warehouse->qty : '-- - ';
        $item['product_type'] = $product_warehouse->product->type;
        $item['net_price'] = $this->getNetPrice($product_warehouse, $item['price'], $item['discounted_price']);

        return $item;
    }

    private function getVariantItemData(ProductWarehouse $product_warehouse, ProductVariant $productsVariants)
    {
        $productName = $product_warehouse->product->name . ' - ' . $productsVariants->name;
        return [
            'product_variant_id' => $product_warehouse->product_variant_id,
            'variant' => $productName,
            'name' => $productName,
            'code' => $productsVariants->code,
            'barcode' => $productsVariants->code,
            'price' => $product_warehouse['productVariant']->price,
            'discounted_price' => $product_warehouse['productVariant']->activeDiscountedPrices->first()?->discounted_price,
        ];
    }

    private function getNonVariantItemData(ProductWarehouse $product_warehouse)
    {
        return [
            'product_variant_id' => null,
            'Variant' => null,
            'code' => $product_warehouse->product->code,
            'name' => $product_warehouse->product->name,
            'barcode' => $product_warehouse->product->code,
            'price' => $product_warehouse->product->price,
            'discounted_price' => $product_warehouse->product->activeDiscountedPrices->first()?->discounted_price,
        ];
    }

    private function getQteSale(ProductWarehouse $product_warehouse)
    {
        if (!$product_warehouse->product?->unitSale) {
            return $product_warehouse->product->type != 'is_service' ? $product_warehouse->qty : '-- --';
        }

        if ($product_warehouse->product?->unitSale?->operator === ' / ') {
            return $product_warehouse->qty * $product_warehouse->product?->unitSale?->operator_value;
        }

        return $product_warehouse->qty / $product_warehouse->product?->unitSale?->operator_value;
    }

    private function getNetPrice(ProductWarehouse $product_warehouse, $price, ?int $discounted_price)
    {
        $calculated_price = $discounted_price ?? $price;
        if ($product_warehouse->product->tax_net === 0.0 || $product_warehouse->product->tax_method != '1') {
            return $calculated_price;
        }

        $tax_price = $calculated_price * $product_warehouse->product->tax_net / 100;
        return $calculated_price + $tax_price;
    }
}
