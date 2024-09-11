<?php

namespace App\Enums;

enum TaxMethod: string
{
    use EnumsToArray;

    case Exclusive = '1';
    case Inclusive = '2';
}
