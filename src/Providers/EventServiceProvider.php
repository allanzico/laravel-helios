<?php

namespace Allanzico\LaravelHelios\Providers;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
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
    protected $listen = [];

    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [];

    public function boot(): void
    {
        parent::boot();

        if (config('helios.watchers.queries.enabled', true)) {
            Event::listen(QueryExecuted::class, QueryListener::class);
        }

        if (config('helios.watchers.jobs.enabled', true)) {
            Event::subscribe(JobEventListener::class);
        }

        if (config('helios.watchers.schedule.enabled', true)) {
            Event::subscribe(ScheduledTaskEventListener::class);
        }
    }
}
