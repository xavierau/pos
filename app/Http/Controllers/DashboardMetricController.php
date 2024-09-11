<?php

namespace App\Http\Controllers;

use App\Services\Data\DTO\MetricQueryOption;
use App\Services\Data\Metric\GetTodayPurchasesMetric;
use App\Services\Data\Metric\GetTodaySalesMetric;
use Illuminate\Http\Request;

class DashboardMetricController extends Controller
{
    public function getTodaySalesData(
        Request             $request,
        GetTodaySalesMetric $service
    )
    {
        $option = new MetricQueryOption(
            warehouse_id: $request->get('warehouse_id', null),
            user_id: $request->user('api')->roles()->first()->inRole('record_view') ?
                $request->user('api')->id :
                null
        );

        $metric = $service->execute($option);

        return response()->json($metric);


    }

    public function getTodayPurchasesData(
        Request                 $request,
        GetTodayPurchasesMetric $service
    )
    {
        $option = new MetricQueryOption(
            warehouse_id: $request->get('warehouse_id', null),
            user_id: $request->user('api')->roles()->first()->inRole('record_view') ?
                $request->user('api')->id :
                null
        );

        $metric = $service->execute($option);

        return response()->json($metric);
    }
}
