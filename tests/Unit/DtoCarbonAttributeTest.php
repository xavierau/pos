<?php

namespace Tests\Unit;

use App\DTO\ADTO;
use App\DTO\Attributes\CarbonType;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;

class SimpleDateDTO extends ADTO
{
    public function __construct(
        #[CarbonType]
        public readonly Carbon $date,
    )
    {
    }
}

class DtoCarbonAttributeTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_example(): void
    {
        $data = [
            'date'=>"2025-05-12"
        ];

        $request = new Request($data);

        $dto = SimpleDateDTO::fromRequest($request);


        $this->assertTrue(true);
    }
}
