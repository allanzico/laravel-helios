<?php

namespace Allanzico\LaravelHelios\HealthChecks\Checks;

use Allanzico\LaravelHelios\HealthChecks\HealthCheck;
use Allanzico\LaravelHelios\HealthChecks\HealthCheckResult;
use Allanzico\LaravelHelios\Models\HeliosScheduledTask;
use Allanzico\LaravelHelios\Models\HeliosTaskDefinition;
use Cron\CronExpression;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SchedulerFreshnessHealthCheck extends HealthCheck
{
    public function run(): HealthCheckResult
    {
        if (! config('helios.watchers.schedule.enabled', true)) {
            $this->shortSummary = 'disabled';

            return $this->warning('Schedule monitoring is disabled');
        }

        try {
            if (! Schema::hasTable('helios_task_definitions') || ! Schema::hasTable('helios_scheduled_tasks')) {
                $this->shortSummary = 'not migrated';

                return $this->warning('Scheduler tables have not been migrated');
            }

            $definitions = HeliosTaskDefinition::query()->get();

            if ($definitions->isEmpty()) {
                $this->shortSummary = 'no tasks';

                return $this->warning('No scheduled tasks have been discovered');
            }

            $lookbackMinutes = (int) config('helios.health.scheduler.lookback_minutes', 1440);
            $graceMinutes = (int) config('helios.health.scheduler.grace_minutes', 5);
            $cutoff = now()->subMinutes($lookbackMinutes);
            $missed = [];
            $checked = 0;

            foreach ($definitions as $definition) {
                try {
                    $previousDueAt = Carbon::instance(
                        (new CronExpression($definition->expression))->getPreviousRunDate(now())
                    );
                } catch (Throwable) {
                    continue;
                }

                if ($previousDueAt->lt($cutoff)) {
                    continue;
                }

                $checked++;

                $latestRun = HeliosScheduledTask::query()
                    ->where('command', $definition->command)
                    ->where('triggered_by', 'scheduler')
                    ->whereIn('status', ['finished', 'failed'])
                    ->latest('finished_at')
                    ->first();

                if (! $latestRun || $latestRun->finished_at?->lt($previousDueAt->copy()->subMinutes($graceMinutes))) {
                    $missed[] = [
                        'command' => $definition->command,
                        'expected_after' => $previousDueAt->toIso8601String(),
                        'last_finished_at' => $latestRun?->finished_at?->toIso8601String(),
                    ];
                }
            }

            $this->shortSummary = count($missed) === 0
                ? "{$checked} checked"
                : count($missed).' missed';

            $this->meta = [
                'tasks_discovered' => $definitions->count(),
                'tasks_checked' => $checked,
                'lookback_minutes' => $lookbackMinutes,
                'grace_minutes' => $graceMinutes,
                'missed' => $missed,
            ];

            if ($checked === 0) {
                return $this->ok('No scheduled tasks were due during the freshness window');
            }

            if (count($missed) > 0) {
                return $this->failed('One or more scheduled tasks appear to be stale');
            }

            return $this->ok('Scheduled tasks are fresh');
        } catch (Throwable $e) {
            return $this->crashed('Could not check scheduler freshness', $e);
        }
    }
}
