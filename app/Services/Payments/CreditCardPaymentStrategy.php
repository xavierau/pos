<?php

namespace App\Services\Payments;

use App\Contracts\IPaymentStrategy;
use App\DTO\CreatePosSaleDTO;
use App\Models\Sale;
use App\Models\User;

class CreditCardPaymentStrategy implements IPaymentStrategy
{

    public function pay(CreatePosSaleDTO $dto, Sale $order, User $user)
    {
        // TODO: Implement pay() method.
        throw new \Exception('Not implemented yet');
    }
}
