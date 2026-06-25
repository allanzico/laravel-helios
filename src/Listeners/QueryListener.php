<?php

namespace Allanzico\LaravelHelios\Listeners;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Schema;
use Allanzico\LaravelHelios\Models\HeliosQuery;
use Allanzico\LaravelHelios\Support\Redactor;
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
        if (self::$disabled || ! config('helios.watchers.queries.enabled', true)) {
            return;
        }

        if (! $this->shouldRecord($event)) {
            return;
        }

        try {
            self::$disabled = true;

            if (!Schema::hasTable('helios_queries')) {
                return;
            }

            HeliosQuery::create([
                'connection_name' => $event->connectionName,
                'sql' => $event->sql,
                'bindings' => app(Redactor::class)->queryBindings($event->bindings),
                'time_ms' => $event->time,
                'created_at' => now(),
            ]);
        } catch (Throwable $e) {
            // Do nothing to avoid breaking the application
        } finally {
            self::$disabled = false;
        }
    }

    protected function shouldRecord(QueryExecuted $event): bool
    {
        $sql = strtolower($event->sql);

        if (str_contains($sql, 'helios_')) {
            return false;
        }

        if (str_contains($sql, 'from "jobs"') || str_contains($sql, 'from `jobs`')) {
            return false;
        }

        if (str_contains($sql, 'from "cache"') || str_contains($sql, 'from `cache`')) {
            return false;
        }

        if ($event->time >= (float) config('helios.watchers.queries.slow_ms', 100)) {
            return true;
        }

        return $this->sample((float) config('helios.watchers.queries.sample_rate', 0.0));
    }

    protected function sample(float $rate): bool
    {
        if ($rate <= 0) {
            return false;
        }

        if ($rate >= 1) {
            return true;
        }

        return mt_rand() / mt_getrandmax() <= $rate;
    }
}
