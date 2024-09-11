<?php

namespace App\Enums;

enum SaleStatus: string
{
    use EnumsToArray;

    case Completed = 'completed';
}
