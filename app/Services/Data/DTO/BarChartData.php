<?php

namespace App\Services\Data\DTO;

readonly class BarChartData
{
    public function __construct(
        public array $data,
        public array $days
    )
    {
    }

}
