<?php

namespace App\Services\Data;

use App\Services\Data\DTO\DataQueryOption;

abstract class AbstractDataQueryService
{

    abstract function execute(DataQueryOption $queryOption);

    protected function getDates(DataQueryOption $queryOption)
    {

        $dates = [];
        $pointer = $queryOption->start_date->clone();
        while ($queryOption->end_date->gte($pointer)) {
            $dates[] = $pointer->clone()->format('Y-m-d');
            $pointer->addDay();
        }

        return collect($dates);
    }

    abstract protected function mergerData($raw_x, $raw_y, $key);


}
