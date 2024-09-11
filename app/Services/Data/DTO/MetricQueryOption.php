<?php

namespace App\Services\Data\DTO;

use Carbon\Carbon;

readonly class MetricQueryOption
{
    public function __construct(
        public ?Carbon $start_date = null,
        public ?Carbon $end_date = null,
        public ?int    $warehouse_id = null,
        public ?int    $user_id = null
    )
    {
    }
}
