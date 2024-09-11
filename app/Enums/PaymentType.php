<?php

namespace App\Enums;

enum PaymentType: string
{
    use EnumsToArray;

    case CreditCard = 'credit card';
    case Cash = 'cash';
}
