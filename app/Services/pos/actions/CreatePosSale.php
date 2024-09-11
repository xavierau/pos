<?php

namespace App\Services\pos\actions;

use App\Contracts\IPaymentStrategy;
use App\DTO\CreatePosSaleDTO;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Enums\SaleStatus;
use App\Models\ProductWarehouse;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\User;
use App\Services\Payments\CashPaymentStrategy;
use App\Services\Payments\CreditCardPaymentStrategy;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CreatePosSale
{
    public function execute(CreatePosSaleDTO $dto, User $user)
    {
        $order = DB::transaction(function () use ($dto, $user) {
            $order = $this->createSaleOrder($dto, $user);
            $this->createSaleDetails($dto, $order);
            $this->handlePayment($dto, $order, $user);
            $this->decrementStock($dto);
            $this->promotionSale($dto, $order);

            return $order;
        }, 3);

        return $order->id;
    }

    private function createSaleOrder(CreatePosSaleDTO $dto, User $user)
    {
        return Sale::create([
            'is_pos' => 1,
            'date' => Carbon::now(),
            'client_id' => $dto->client_id,
            'warehouse_id' => $dto->warehouse_id,
            'tax_rate' => $dto->tax_rate ?? 0,
            'tax_net' => $dto->tax_net ?? 0,
            'discount' => $dto->discount,
            'shipping' => $dto->shipping,
            'grand_total' => $dto->grand_total,
            'notes' => $dto->notes,
            'status' => SaleStatus::Completed,
            'payment_status' => PaymentStatus::Unpaid,
            'user_id' => $user->id,
        ]);
    }

    private function createSaleDetails(CreatePosSaleDTO $dto, Sale $order): void
    {
        $orderDetails = collect($dto->details)
            ->map(fn($detail) => $this->createSaleDetail($detail, $order));
        SaleDetail::insert($orderDetails->toArray());
    }

    private function createSaleDetail($detail, Sale $order): array
    {
        return [
            'date' => Carbon::now(),
            'sale_id' => $order->id,
            'sale_unit_id' => $detail->sale_unit_id,
            'quantity' => $detail->quantity,
            'product_id' => $detail->product_id,
            'product_variant_id' => $detail->product_variant_id,
            'price' => $detail->unit_price,
            'tax_net' => $detail->tax_percent ?? 0,
            'tax_method' => $detail->tax_method ?? "0",
            'discount' => $detail->discount,
            'discount_method' => $detail->discount_method ?? "0",
            'imei_number' => $detail->imei_number,
        ];
    }

    private function getPaymentStrategy(PaymentType $type): IPaymentStrategy
    {
        return match ($type) {
            PaymentType::CreditCard => new CreditCardPaymentStrategy(),
            PaymentType::Cash => new CashPaymentStrategy(),
        };
    }

    private function handlePayment(CreatePosSaleDTO $dto, Sale $order, User $user): void
    {
        $this->getPaymentStrategy($dto->payment->type)
            ->pay($dto, $order, $user);
    }

    private function decrementStock(CreatePosSaleDTO $dto)
    {
        collect($dto->details)->each(function ($detail) use ($dto) {
            ProductWarehouse::where('product_id', $detail->product_id)
                ->where('warehouse_id', $dto->warehouse_id)
                ->where('manage_stock', true)
                ->when($detail->product_variant_id, fn($q) => $q->where('product_variant_id', $detail->product_variant_id))
                ->decrement('qty', $detail->quantity);
        });
    }

    private function promotionSale(CreatePosSaleDTO $dto, Sale $order)
    {
        $promotionSales = collect($dto->details)
            ->filter(fn($detail) => $detail->promotion_id)
            ->map(fn($promotionItem) => [
                'product_id' => $promotionItem->product_id,
                'sale_id' => $order->id,
            ]);

        DB::table('promotion_sale')->insert($promotionSales->toArray());

    }
}
