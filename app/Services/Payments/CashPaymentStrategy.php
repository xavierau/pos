<?php

namespace App\Services\Payments;

use App\Contracts\IPaymentStrategy;
use App\DTO\CreatePosSaleDTO;
use App\Enums\PaymentStatus;
use App\Models\Account;
use App\Models\PaymentSale;
use App\Models\Sale;
use App\Models\User;
use Carbon\Carbon;
use Exception;

class CashPaymentStrategy implements IPaymentStrategy
{

    /**
     * @throws \Exception
     */
    public function pay(CreatePosSaleDTO $dto, Sale $order, User $user)
    {
        PaymentSale::create([
            'sale_id' => $order->id,
            'account_id' => $dto->payment->account_id ? $dto->payment->account_id : NULL,
            'date' => Carbon::now(),
            'type' => $dto->payment->type,
            'amount' => $dto->amount,
            'change' => $dto->change,
            'notes' => $dto->payment->notes,
            'user_id' => $user->id,
        ]);

        Account::where('id', $dto->payment->account_id)
            ->increment('balance', $dto->amount);

        list($total_paid, $payment_status) = $this->getPaymentStatus($order, $dto);

        $order->update([
            'paid_amount' => $total_paid,
            'payment_status' => $payment_status,
        ]);
    }

    /**
     * @param \App\Models\Sale $order
     * @param \App\DTO\CreatePosSaleDTO $dto
     * @return array
     * @throws \Exception
     */
    private function getPaymentStatus(Sale $order, CreatePosSaleDTO $dto): array
    {
        $total_paid = round($order->paid_amount + $dto->amount, 2);

        $due = round($order->grand_total - $total_paid, 2);

        if ($due === 0.0 || $due < 0.0) {
            $payment_status = PaymentStatus::Paid;
        } else if ($due != $order->grand_total) {
            $payment_status = PaymentStatus::Partial;
        } else if ($due == $order->grand_total) {
            $payment_status = PaymentStatus::Unpaid;
        } else {
            throw new Exception("Unexpected payment status");
        }
        return array($total_paid, $payment_status);
    }
}
