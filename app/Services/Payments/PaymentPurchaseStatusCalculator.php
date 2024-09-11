<?php

namespace App\Services\Payments;

use App\Enums\PaymentStatus;

class PaymentPurchaseStatusCalculator
{
    public function execute($due, $grand_total): PaymentStatus
    {
        if ($due === 0.0 || $due < 0.0) {
            return PaymentStatus::Paid;
        } elseif ($due !== $grand_total) {
            return PaymentStatus::Partial;
        }
        return PaymentStatus::Unpaid;
    }
}
