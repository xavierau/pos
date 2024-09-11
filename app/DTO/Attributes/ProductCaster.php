<?php

namespace App\DTO\Attributes;

use App\Models\Product;
use Attribute;
use Illuminate\Foundation\Testing\DatabaseMigrations;


#[Attribute(Attribute::TARGET_PROPERTY)]
class ProductCaster implements IDTOAttribute
{
    use DatabaseMigrations;

    private array $possibleKeys = [
        'id',
        'product_id'
    ];

    /**
     * @throws \Exception
     */
    public function process($value, bool $isOptional, $defaultValue): Product
    {
        if (is_null($value) and $isOptional) {
            return $defaultValue;
        }

        if (is_int($value)) {
            return Product::findOrFail($value);
        } elseif (is_array($value)) {
            foreach ($this->possibleKeys as $key) {
                if (in_array($key, array_keys($value))) {
                    return Product::findOrFail($value[$key] ?? null);
                }
            }
        }

        throw new \Exception("No id found in value");
    }
}
