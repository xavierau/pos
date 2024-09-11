<?php

namespace Modules\Promotions\Providers;

use App\Events\CartUpdatedEvent;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        CartUpdatedEvent::class => [],
    ];
}
