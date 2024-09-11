<?php

namespace Tests\Feature;

use App\DTO\ADTO;
use App\DTO\Attributes\ArrayOf;
use App\DTO\Attributes\IDTOAttribute;
use App\DTO\Attributes\ProductCaster;
use App\Models\Product;
use Attribute;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Tests\TestCase;


#[Attribute(Attribute::TARGET_PROPERTY)]
class MyCarbonCaster implements IDTOAttribute
{

    /**
     * @throws \Exception
     */
    public function process($value, bool $isOptional, $defaultValue): Carbon
    {
        if (is_null($value) and $isOptional) {
            return $defaultValue;
        }

        return Carbon::parse($value);
    }
}

class MyProductDTO extends ADTO
{
    public function __construct(
        #[ProductCaster]
        public readonly ?Product $product
    )
    {
    }
}

class MyTestingDTO extends ADTO
{
    public function __construct(
        #[ArrayOf(MyProductDTO::class)]
        public readonly array $products
    )
    {

    }
}


class ExampleTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {

        $request = new Request([
            'products' => [
                [
                    'product' => 1
                ],
                [
                    'product' => null
                ],
                [
                    'product' => 3
                ],
            ]
        ]);

        $dto = MyTestingDTO::fromRequest($request);

        dd($dto);

    }
}
