<?php

namespace Modules\Promotions\Enums;

use App\Enums\EnumsToArray;

enum PromotionType: string
{
    use EnumsToArray;

    case BuyXGetY = 'buy_x_get_y';
    case FreeXIfOverYAmount = 'free_x_if_over_y_amount';
    case DiscountXPercentageIfOverYAmount = 'discount_x_percentage_if_over_y_amount';
}
