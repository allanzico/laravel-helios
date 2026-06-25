<?php

namespace Allanzico\LaravelHelios\Listeners;

use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskStarting;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Events\Dispatcher;
use Allanzico\LaravelHelios\Models\HeliosScheduledTask;
use Throwable;

class ScheduledTaskEventListener
{
    public function handleTaskStarting(ScheduledTaskStarting $event): void
    {
        try {
            HeliosScheduledTask::create([
                'command' => $event->task->command,
                'expression' => $event->task->expression,
                'status' => 'starting',
                'started_at' => now(),
                'triggered_by' => 'scheduler',
            ]);
        } catch (Throwable) {
        }
    }

    public function handleTaskFinished(ScheduledTaskFinished $event): void
    {
        try {
            $this->findTask($event->task)?->update([
                'status' => 'finished',
                'finished_at' => now(),
                'runtime_ms' => $event->runtime,
            ]);
        } catch (Throwable) {
        }
    }

    public function handleTaskFailed(ScheduledTaskFailed $event): void
    {
        try {
            $this->findTask($event->task)?->update([
                'status' => 'failed',
                'finished_at' => now(),
                'output' => (string) $event->exception,
            ]);
        } catch (Throwable) {
        }
    }

    /**
     * Find the helios task for the given event.
     */
    private function findTask(Event $task): ?HeliosScheduledTask
    {
        return HeliosScheduledTask::query()
            ->where('command', $task->command)
            ->where('status', 'starting')
            ->latest('started_at')
            ->first();
    }

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(ScheduledTaskStarting::class, [self::class, 'handleTaskStarting']);
        $events->listen(ScheduledTaskFinished::class, [self::class, 'handleTaskFinished']);
        $events->listen(ScheduledTaskFailed::class, [self::class, 'handleTaskFailed']);
    }
}
