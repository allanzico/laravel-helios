<?php

namespace Allanzico\LaravelHelios\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Console\Scheduling\Schedule;
use Allanzico\LaravelHelios\Models\HeliosTaskDefinition;
use ReflectionMethod;

class SyncTasks extends Command
{
    protected $signature = 'helios:sync-tasks';
    protected $description = 'Sync the scheduled tasks from the Kernel to the database for Helios.';

    public function handle(Kernel $kernel, Schedule $schedule): void
    {
        $this->info('Syncing scheduled tasks with Helios...');

        if (empty($schedule->events()) && method_exists($kernel, 'schedule')) {
            $scheduleMethod = new ReflectionMethod($kernel, 'schedule');
            $scheduleMethod->invoke($kernel, $schedule);
        }

        $definedTasks = collect($schedule->events())->map(function ($event) {
            if (empty($event->command)) return null;
            return [
                'command' => $event->command,
                'expression' => $event->expression,
                'description' => $event->description,
            ];
        })->filter()->keyBy('command');

        // Remove tasks from DB that are no longer in the Kernel
        HeliosTaskDefinition::query()
            ->whereNotIn('command', $definedTasks->keys())
            ->delete();

        // Update or create tasks from the Kernel
        foreach ($definedTasks as $task) {
            HeliosTaskDefinition::updateOrCreate(
                ['command' => $task['command']],
                $task
            );
        }

        $this->info('Scheduled tasks synced successfully.');
    }
}
