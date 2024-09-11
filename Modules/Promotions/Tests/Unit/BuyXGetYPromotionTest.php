<?php


namespace Modules\Promotions\Tests\Unit;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Promotions\Entities\Promotion;
use Modules\Promotions\Entities\PromotionRule;
use Modules\Promotions\Enums\PromotionType;
use Modules\Promotions\Services\Promotions\BuyXGetYPromotion;
use Tests\TestCase;

class BuyXGetYPromotionTest extends TestCase
{
    use RefreshDatabase;

    protected PromotionRule $rule;
    protected PromotionRule $multipleRule;
    protected PromotionRule $maxUsageRule;

    protected function setUp(): void
    {
        parent::setUp();

        $products = Product::factory()->count(3)->create();
        // Create a promotion rule for Buy 2 Get 1 free
        $this->rule = PromotionRule::factory()->create([
            'x' => 2,
            'y' => 1,
            'type' => PromotionType::BuyXGetY->value,
            'promotion_id' => Promotion::factory()->create()->id, // assuming a promotion ID
            'product_id' => $products[0]->id,
            'y_product_id' => $products[1]->id// assuming product ID 1 is eligible for the promotion
        ]);
        // Create a promotion rule for Buy 2 Get 1 free
        $this->multipleRule = PromotionRule::factory()->create([
            'x' => 2,
            'y' => 1,
            'type' => PromotionType::BuyXGetY->value,
            'promotion_id' => Promotion::factory()->create([
                'max_applications_per_sale' => 3
            ])->id, // assuming a promotion ID
            'product_id' => $products[0]->id,
            'y_product_id' => $products[1]->id// assuming product ID 1 is eligible for the promotion
        ]);
        // Create a promotion rule for Buy 2 Get 1 free
        $this->maxUsageRule = PromotionRule::factory()->create([
            'x' => 2,
            'y' => 1,
            'type' => PromotionType::BuyXGetY->value,
            'promotion_id' => Promotion::factory()->create([
                'max_usage' => 2,
                'max_applications_per_sale' => 3
            ])->id, // assuming a promotion ID
            'product_id' => $products[0]->id,
            'y_product_id' => $products[1]->id// assuming product ID 1 is eligible for the promotion
        ]);
    }

    public function test_apply_promotion()
    {
        // Create a sale with 2 items of product_id 1
        $sale = Sale::factory()->create();
        SaleDetail::factory()->count(2)->create([
            'sale_id' => $sale->id,
            'product_id' => 1,
            'quantity' => 1,
        ]);


        $sale->load(['details.product.category', 'details.productVariant.product.category']);


        $promotion = new BuyXGetYPromotion($this->rule);
        $promotion->apply($sale);


        // Check if the promotion is applied (1 free item added)
        $this->assertCount(3, $sale->details);
        $this->assertDatabaseHas('sale_details', [
            'sale_id' => $sale->id,
            'product_id' => $this->rule->y,
            'quantity' => 1,
            'price' => 0,
            'discount_method' => 'buy_x_get_y',
        ]);
    }

    public function test_remove_promotion()
    {
        // Create a sale with 2 items of product_id 1
        $sale = Sale::factory()->create();
        SaleDetail::factory()->count(2)->create([
            'sale_id' => $sale->id,
            'product_id' => 1,
            'quantity' => 1,
        ]);

        $sale->load(['details.product.category', 'details.productVariant.product.category']);

        $promotion = new BuyXGetYPromotion($this->rule);
        $promotion->apply($sale);


        // Remove one item
        $sale->details()->first()->delete();

        // Reapply the promotion logic
        $promotion->apply($sale);


        // Check if the promotion is removed (no free items)
        $this->assertCount(1, $sale->details);
        $this->assertDatabaseMissing('sale_details', [
            'sale_id' => $sale->id,
            'product_id' => $this->rule->y,
            'discount_method' => 'buy_x_get_y',
        ]);
    }

    public function test_promotion_not_applied_when_ineligible()
    {
        // Create a sale with 1 item of product_id 1
        $sale = Sale::factory()->create();
        SaleDetail::factory()->create([
            'sale_id' => $sale->id,
            'product_id' => 1,
            'quantity' => 1,
        ]);

        $sale->load(['details.product.category', 'details.productVariant.product.category']);

        $promotion = new BuyXGetYPromotion($this->rule);
        $promotion->apply($sale);

        // Check that the promotion is not applied (no free items)
        $this->assertCount(1, $sale->details);
        $this->assertDatabaseMissing('sale_details', [
            'sale_id' => $sale->id,
            'discount_method' => 'buy_x_get_y',
        ]);
    }

    public function test_promotion_apply_for_multiple_times()
    {
        // Create a sale with 4 items of product_id 1
        $sale = Sale::factory()->create();
        SaleDetail::factory()->count(4)->create([
            'sale_id' => $sale->id,
            'product_id' => 1,
            'quantity' => 1,
        ]);

        $sale->load(['details.product.category', 'details.productVariant.product.category']);

        $promotion = new BuyXGetYPromotion($this->multipleRule);

        $promotion->apply($sale);

        // Check if the promotion is applied (2 free items added)
        $this->assertCount(6, $sale->details);
        $this->assertEquals(2, DB::table('promotion_sale')
            ->where('sale_id', $sale->id)
            ->where('promotion_id', $this->multipleRule->promotion_id)
            ->count());

    }

    public function test_promotion_apply_for_multiple_times_if_over_single_sale()
    {
        // Create a sale with 4 items of product_id 1
        $sale = Sale::factory()->create();
        SaleDetail::factory()->count(8)->create([
            'sale_id' => $sale->id,
            'product_id' => 1,
            'quantity' => 1,
        ]);

        $sale->load(['details.product.category', 'details.productVariant.product.category']);

        $promotion = new BuyXGetYPromotion($this->multipleRule);

        $promotion->apply($sale);

        // Check if the promotion is applied (2 free items added)
        $this->assertCount(11, $sale->details);
        $this->assertEquals(3, DB::table('promotion_sale')
            ->where('sale_id', $sale->id)
            ->where('promotion_id', $this->multipleRule->promotion_id)
            ->count());

    }

    public function test_promotion_apply_for_multiple_times_if_over_max_usage()
    {
        // Create a sale with 4 items of product_id 1
        $sale = Sale::factory()->create();
        SaleDetail::factory()->count(8)->create([
            'sale_id' => $sale->id,
            'product_id' => 1,
            'quantity' => 1,
        ]);

        $sale->load(['details.product.category', 'details.productVariant.product.category']);

        $promotion = new BuyXGetYPromotion($this->maxUsageRule);

        $promotion->apply($sale);

        $this->assertCount(10, $sale->details);
        $this->assertEquals(2, DB::table('promotion_sale')
            ->where('sale_id', $sale->id)
            ->where('promotion_id', $this->maxUsageRule->promotion_id)
            ->count());
    }

    public function test_promotion_apply_for_multiple_times_if_over_max_usage_and_then_remove_item()
    {
        // Create a sale with 4 items of product_id 1
        $sale = Sale::factory()->create();
        SaleDetail::factory()->count(8)->create([
            'sale_id' => $sale->id,
            'product_id' => 1,
            'quantity' => 1,
        ]);

        $sale->refresh();

        $sale->details()->first()->delete();

        $sale->load(['details.product.category', 'details.productVariant.product.category']);

        $promotion = new BuyXGetYPromotion($this->maxUsageRule);

        $promotion->apply($sale);

        $this->assertCount(9, $sale->details);
        $this->assertEquals(2, DB::table('promotion_sale')
            ->where('sale_id', $sale->id)
            ->where('promotion_id', $this->maxUsageRule->promotion_id)
            ->count());
    }



    public function test_promotion_apply_for_multiple_times_if_over_max_usage_and_then_remove_item_2()
    {
        // Create a sale with 4 items of product_id 1
        $sale = Sale::factory()->create();
        SaleDetail::factory()->count(8)->create([
            'sale_id' => $sale->id,
            'product_id' => 1,
            'quantity' => 1,
        ]);

        $sale->refresh();

        $sale->details()->first()->delete();
        $sale->details()->first()->delete();
        $sale->details()->first()->delete();

        $sale->load(['details.product.category', 'details.productVariant.product.category']);

        $promotion = new BuyXGetYPromotion($this->maxUsageRule);

        $promotion->apply($sale);

        $this->assertCount(7, $sale->details);
        $this->assertEquals(2, DB::table('promotion_sale')
            ->where('sale_id', $sale->id)
            ->where('promotion_id', $this->maxUsageRule->promotion_id)
            ->count());
    }


}
