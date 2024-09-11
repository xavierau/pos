<?php

namespace App\Services\Data\DTO;

use App\Enums\AggregateDateType;
use Carbon\Carbon;

readonly class DataQueryOption
{
    public function __construct(
        public AggregateDateType $aggregateType,
        public Carbon            $start_date,
        public Carbon            $end_date,
        public ?int              $warehouse_id = null,
        public ?int              $user_id = null
    )
    {
    }
}
