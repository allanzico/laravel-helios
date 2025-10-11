<?php

namespace Allanzico\LaravelHelios\Providers;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Allanzico\LaravelHelios\Listeners\JobEventListener;
use Allanzico\LaravelHelios\Listeners\QueryListener;
use Allanzico\LaravelHelios\Listeners\ScheduledTaskEventListener;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        QueryExecuted::class => [
            QueryListener::class,
        ],
    ];

    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [
        JobEventListener::class,
        ScheduledTaskEventListener::class,
    ];
}
