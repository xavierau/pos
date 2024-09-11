<?php

namespace Modules\Promotions\Enums;

use App\Enums\EnumsToArray;

enum PromotedItemType: string
{
    use EnumsToArray;

    case FreeProduct = 'free_product';
    case ProductPercentageDiscount = 'product_percentage_discount';
    case ProductFixedDiscount = 'product_fixed_discount';

    case CartPercentageDiscount = 'cart_percentage_discount';
    case CartFixedDiscount = 'cart_fixed_discount';

}
