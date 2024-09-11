<?php


namespace Modules\Promotions\Tests\Unit;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Promotions\Entities\Promotion;
use Modules\Promotions\Entities\PromotionRule;
use Modules\Promotions\Enums\PromotionType;
use Modules\Promotions\Services\Promotions\BuyXGetYPromotion;
use Modules\Promotions\Services\Promotions\DiscountXPercentageIfOverYAmountPromotion;
use Tests\TestCase;

class DiscountXPercentageIfOverYAmountTest extends TestCase
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
            'x' => 2999,
            'y' => 10,
            'type' => PromotionType::DiscountXPercentageIfOverYAmount,
            'promotion_id' => Promotion::factory()->create()->id, // assuming a promotion ID
        ]);
    }


    public function test_promotion_applied_when_grand_total_over_thredhold()
    {

        $sale = Sale::factory()->create([
            'grand_total' => 3000,
        ]);

        $promotion = new DiscountXPercentageIfOverYAmountPromotion($this->rule);
        $promotion->apply($sale);

        $this->assertEquals(300, $sale->discount);
        $this->assertDatabaseHas('promotion_sale', [
            'sale_id' => $sale->id,
            'promotion_id' => $this->rule->promotion_id,
        ]);

    }


}
