<?php

namespace App\Enums;

enum ProductType: string
{
    use EnumsToArray;

    case Single = "is_single";
    case Variant = "is_variant";
    case Service = "is_service";
}
