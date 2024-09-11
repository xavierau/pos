<?php

namespace App\Services\Data;

use App\Models\Purchase;
use App\Services\Data\DTO\DataQueryOption;
use App\Services\Data\DTO\XYChartData;
use Illuminate\Support\Facades\DB;

class GetPurchaseDataService extends AXYChartDataQueryService
{
    public function execute(DataQueryOption $queryOption): XYChartData
    {
        $dates = $this->getDates($queryOption);

        $purchases = Purchase::whereDate('date', '>=', $queryOption->start_date->startOfDay())
            ->whereDate('date', '<=', $queryOption->end_date->endOfDay())
            ->when($queryOption->user_id, fn($q) => $q->where('user_id', '=', $queryOption->user_id))
            ->when($queryOption->warehouse_id, fn($q) => $q->whereIn('warehouse_id', is_array($queryOption->warehouse_id) ? $queryOption->warehouse_id : [$queryOption->warehouse_id]))
            ->groupBy(DB::raw("DATE_FORMAT(date,'%Y-%m-%d')"))
            ->orderBy('date', 'asc')
            ->get([
                DB::raw(DB::raw("DATE_FORMAT(date,'%Y-%m-%d') as date")),
                DB::raw('SUM(grand_total) AS count'),
            ]);

        return $this->mergerData($dates, $purchases, 'date');
    }

}
