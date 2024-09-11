<?php

namespace App\DTO;

use App\DTO\Attributes\ArrayOf;

class  CreatePosSaleDTO extends ADTO
{

    /**
     * @param int|null $client_id
     * @param int $warehouse_id
     * @param float $tax_rate
     * @param float $tax_net
     * @param float $discount
     * @param float $shipping
     * @param float $grand_total
     * @param float $amount
     * @param float $change
     * @param \App\DTO\PaymentDTO $payment
     * @param array $details
     * @param string|null $notes
     * @param string|null $ref
     */
    public function __construct(
        public readonly ?int       $client_id,
        public readonly int        $warehouse_id,
        public readonly ?float     $tax_rate,
        public readonly ?float     $tax_net,
        public readonly float      $discount,
        public readonly float      $shipping,
        public readonly float      $grand_total,
        public readonly float      $amount,
        public readonly float      $change,
        public readonly PaymentDTO $payment,
        #[ArrayOf(SaleDetailDTO::class)]
        public readonly array      $details,
        public readonly ?string    $notes = null,
        public readonly ?string    $ref = null,
    )
    {
    }

}
