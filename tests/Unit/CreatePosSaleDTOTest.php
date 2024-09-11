<?php

namespace Tests\Unit;

use App\DTO\CreatePosSaleDTO;
use App\Enums\PaymentType;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\TestCase;

class CreatePosSaleDTOTest extends TestCase
{
    use DatabaseTransactions;

    public function testFromRequest()
    {
        $requestData = [
            'client_id' => 1,
            'warehouse_id' => 2,
            'tax_rate' => 5.0,
            'tax_net' => 100.0,
            'discount' => 10.0,
            'shipping' => 15.0,
            'grand_total' => 200.0,
            'date' => '2023-05-31',
            'amount' => 200.0,
            'change' => 0.0,
            'payment' => [
                'type' => 'credit card',
                'notes' => 'Paid in full',
                'account_id' => 123,
            ],
            'details' => [
                [
                    'product_id' => 1,
                    'quantity' => 2,
                    "sale_unit_id" => 1,
                    "subtotal" => 90,
                    "unit_price" => 45,
                    "tax_percent" => 0,
                    "tax_method" => 0,
                    "discount" => 10,
                    "discount_method" => "2",
                ],
                [
                    'product_id' => 2,
                    'quantity' => 3,
                    "sale_unit_id" => 1,
                    "subtotal" => 150,
                    "unit_price" => 50,
                    "tax_percent" => 0,
                    "tax_method" => 0,
                    "discount" => 30,
                    "discount_method" => "2",
                ],
            ],
            'notes' => 'Some notes',
            'ref' => 'REF12345',
        ];

        $request = new Request($requestData);
        $dto = CreatePosSaleDTO::fromRequest($request);

        dd($dto);

        $this->assertEquals(1, $dto->client_id);
        $this->assertEquals(2, $dto->warehouse_id);
        $this->assertEquals(5.0, $dto->tax_rate);
        $this->assertEquals(100.0, $dto->tax_net);
        $this->assertEquals(10.0, $dto->discount);
        $this->assertEquals(15.0, $dto->shipping);
        $this->assertEquals(200.0, $dto->grand_total);
        $this->assertEquals('2023-05-31', $dto->date);
        $this->assertEquals(200.0, $dto->amount);
        $this->assertEquals(0.0, $dto->change);
        $this->assertEquals(PaymentType::CreditCard, $dto->payment->type);
        $this->assertEquals('Paid in full', $dto->payment->notes);
        $this->assertEquals(123, $dto->payment->account_id);
        $this->assertCount(2, $dto->details);
        $this->assertEquals(1, $dto->details[0]->product_id);
        $this->assertEquals(2, $dto->details[0]->quantity);
        $this->assertEquals(2, $dto->details[1]->product_id);
        $this->assertEquals(3, $dto->details[1]->quantity);
        $this->assertEquals('Some notes', $dto->notes);
        $this->assertEquals('REF12345', $dto->ref);
    }
}
