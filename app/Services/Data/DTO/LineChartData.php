<?php

namespace App\Services\Data\DTO;

readonly class LineChartData
{
    public function __construct(
        public array $data,
        public array $days
    )
    {
    }

}
