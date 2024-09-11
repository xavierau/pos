<?php
// Modules/Promotions/Contracts/PromotionInterface.php
namespace Modules\Promotions\Contracts;

use App\Models\Sale;
use App\Models\SaleReturn;
use Modules\Promotions\DTO\CartDTO;
use Modules\Promotions\DTO\PosDetailDTO;
use Modules\Promotions\DTO\PromotedItemChange;

interface IPromotion
{
    /**
     * @param \App\Models\Sale $sale
     * @return mixed
     */
    public function apply(Sale $sale);

    /**
     * @param \App\Models\SaleReturn $saleReturn
     * @return mixed
     */
    public function adjustForReturn(SaleReturn $saleReturn);

    /**
     * @param \App\Models\Sale $sale
     * @return bool
     */
    public function isEligibleForPromotion(Sale $sale): bool;


    /**
     * Base on the cart items, calculate the promotion items should be added or removed
     * @param Array<PosDetailDTO> $details
     * @param array $rewards
     * @return \Modules\Promotions\DTO\PromotedItemChange
     */
    public function getPromotedItems(CartDTO $cart): PromotedItemChange;
}
