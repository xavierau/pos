<?php

namespace App\Services\Data\DTO;

readonly class XYChartData
{
    public function __construct(
        public array $x,
        public array $y
    )
    {
    }

}
