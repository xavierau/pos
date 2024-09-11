<?php

namespace App\Services\Data;

use App\Models\PaymentSaleReturns;
use App\Services\Data\DTO\DataQueryOption;
use App\Services\Data\DTO\XYChartData;
use Illuminate\Support\Facades\DB;

class GetPaymentSalesReturnsDataService extends AXYChartDataQueryService
{
    public function execute(DataQueryOption $queryOption): XYChartData
    {
        $dates = $this->getDates($queryOption);

        $payment_sale_returns = PaymentSaleReturns::with('SaleReturn')
            ->whereDate('date', '>=', $queryOption->start_date->startOfDay())
            ->whereDate('date', '<=', $queryOption->end_date->endOfDay())
            ->when($queryOption->user_id, fn($q) => $q->where('user_id', '=', $queryOption->user_id))
            ->when($queryOption->warehouse_id, fn($query) => $query->whereHas('SaleReturn', fn($q) => $q->whereIn('warehouse_id', is_array($queryOption->warehouse_id) ? $queryOption->warehouse_id : [$queryOption->warehouse_id])))
            ->groupBy(DB::raw("DATE_FORMAT(date,'%Y-%m-%d')"))
            ->orderBy('date', 'asc')
            ->get([
                DB::raw(DB::raw("DATE_FORMAT(date,'%Y-%m-%d') as date")),
                DB::raw('SUM(amount) AS count'),
            ])
            ->pluck('count', 'date');


        return $this->mergerData($dates, $payment_sale_returns, 'date');
    }

}
