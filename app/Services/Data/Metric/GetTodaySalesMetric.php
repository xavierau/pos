<?php

namespace App\Services\Data\Metric;

use App\Models\Sale;
use App\Services\Data\DTO\MetricData;
use App\Services\Data\DTO\MetricQueryOption;

class GetTodaySalesMetric
{

    public function execute(MetricQueryOption $option): MetricData
    {

        $sum = Sale::whereDate('date', \Carbon\Carbon::today())
            ->when($option->user_id, fn($q) => $q->where('user_id', $option->user_id))
            ->when($option->warehouse_id, fn($q) => $q->whereIn('warehouse_id', is_array($option->warehouse_id) ? $option->warehouse_id : [$option->warehouse_id]))
            ->sum('grand_total');
        
        return new MetricData($sum);

    }

}
