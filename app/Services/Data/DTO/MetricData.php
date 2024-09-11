<?php

namespace App\Services\Data\DTO;

readonly class MetricData
{
    public function __construct(
        public ?float $data,
    )
    {
    }

}
