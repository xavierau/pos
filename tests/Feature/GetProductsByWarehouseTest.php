<?php

namespace Tests\Feature;

use App\DTO\GetProductsByWarehouseDTO;
use App\Models\Warehouse;
use App\Services\products\GetProductsByWarehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetProductsByWarehouseTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function productsAreRetrievedCorrectly()
    {
        // create a warehouse
        // create a product
        Warehouse::factory()->create();


        $dto = new GetProductsByWarehouseDTO(
            warehouse_id: 1,
            is_sale: true,
            stock: true,
            product_service: true
        );

        $service = new GetProductsByWarehouse();

        $products = $service->execute($dto);

        $this->assertIsArray($products);
        $this->assertNotEmpty($products);
    }

    /**
     * @test
     */
    public function productsAreEmptyWhenNoMatch()
    {
        $dto = new GetProductsByWarehouseDTO(
            warehouse_id: 999,
            is_sale: true,
            stock: true,
            product_service: true
        );
        $service = new GetProductsByWarehouse();

        $products = $service->execute($dto);

        $this->assertIsArray($products);
        $this->assertEmpty($products);
    }

    /**
     * @test
     */
    public function productsAreRetrievedCorrectlyWhenNotOnSale()
    {
        $dto = new GetProductsByWarehouseDTO(
            warehouse_id: 1,
            is_sale: false,
            stock: true,
            product_service: true
        );

        $service = new GetProductsByWarehouse();

        $products = $service->execute($dto);

        $this->assertIsArray($products);
        $this->assertNotEmpty($products);
    }

    /**
     * @test
     */
    public function productsAreRetrievedCorrectlyWhenStockIsZero()
    {
        $dto = new GetProductsByWarehouseDTO(
            warehouse_id: 1,
            is_sale: true,
            stock: false,
            product_service: true
        );

        $service = new GetProductsByWarehouse();

        $products = $service->execute($dto);

        $this->assertIsArray($products);
        $this->assertNotEmpty($products);
    }
}
