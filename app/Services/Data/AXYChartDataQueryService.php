<?php

namespace App\Services\Data;

use App\Enums\AggregateDateType;
use App\Services\Data\DTO\DataQueryOption;
use App\Services\Data\DTO\XYChartData;
use InvalidArgumentException;

abstract class AXYChartDataQueryService extends AbstractDataQueryService
{
    protected function getDates(DataQueryOption $queryOption)
    {
        return match ($queryOption->aggregateType) {
            AggregateDateType::Day => parent::getDates($queryOption),
            default => throw new InvalidArgumentException('Not supported date aggregation type')
        };
    }

    abstract function execute(DataQueryOption $queryOption): XYChartData;

    protected function mergerData($raw_x, $raw_y, $key)
    {

        $y_data = [];
        $x_data = [];
        foreach ($raw_x as $x) {
            $y_data[] = $raw_y->first(fn($i) => $i[$key] === $x)?->count ?? 0;
            $x_data[] = $x;
        }

        return new XYChartData(
            y: $y_data, x: $x_data
        );

    }

}
