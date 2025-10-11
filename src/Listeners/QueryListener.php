<?php

namespace Allanzico\LaravelHelios\Listeners;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Schema;
use Allanzico\LaravelHelios\Models\HeliosQuery;
use Throwable;

class QueryListener
{
    /**
     * A flag to prevent the listener from recursively triggering itself.
     */
    private static bool $disabled = false;

    /**
     * Handle the event.
     */
    public function handle(QueryExecuted $event): void
    {
        if (self::$disabled) {
            return;
        }

        try {
            self::$disabled = true;

            if (!Schema::hasTable('helios_queries')) {
                return;
            }

            // Ignore queries from the queue worker polling for jobs or restart signals.
            if (str_contains($event->sql, 'from "jobs"') || str_contains($event->sql, 'from "cache" where "key" in (?)')) {
                return;
            }

            // We don't want to log queries that read from our own tables
            if (str_contains($event->sql, '`helios_')) {
                return;
            }

            HeliosQuery::create([
                'connection_name' => $event->connectionName,
                'sql' => $event->sql,
                'bindings' => $event->bindings,
                'time_ms' => $event->time,
                'created_at' => now(),
            ]);
        } catch (Throwable $e) {
            // Do nothing to avoid breaking the application
        } finally {
            self::$disabled = false;
        }
    }
}
