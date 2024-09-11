<?php

namespace Tests\Unit;

use App\Enums\AggregateDateType;
use App\Services\Data\DTO\DataQueryOption;
use App\Services\Data\GetSaleDataService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;

class GetSaleDataServiceTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * A basic unit test example.
     */
    public function test_example(): void
    {
        $queryOption = new DataQueryOption(
            aggregateType: AggregateDateType::Day,
            start_date: Carbon::parse('2024-05-01'),
            end_date: Carbon::now(),
        );
        $service = new GetSaleDataService();

        $data = $service->execute($queryOption);

        dd($data);


    }
}
