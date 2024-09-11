<?php

namespace App\Contracts;

use App\DTO\CreatePosSaleDTO;
use App\Models\Sale;
use App\Models\User;

interface IPaymentStrategy
{
    public function pay(CreatePosSaleDTO $dto, Sale $order, User $user);
}
