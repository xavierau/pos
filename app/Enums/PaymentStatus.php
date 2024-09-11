<?php

namespace App\Enums;

enum PaymentStatus: string
{
    use EnumsToArray;

    case Unpaid = 'unpaid';
    case Paid = 'paid';
    case Partial = 'partial';
}
