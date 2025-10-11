<?php

namespace Allanzico\LaravelHelios\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Events\Dispatcher;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Allanzico\LaravelHelios\Models\ScoutJob;

class JobEventListener
{
    /**
     * Handle a job starting to process.
     */
    public function handleJobProcessing(JobProcessing $event): void
    {
        ScoutJob::create([
            'id' => $event->job->uuid(),
            'name' => $event->job->resolveName(),
            'status' => 'running',
            'payload' => $event->job->getRawBody(),
            'started_at' => now(),
        ]);
    }

    /**
     * Handle a job that has been successfully processed.
     */
    public function handleJobProcessed(JobProcessed $event): void
    {
        $this->updateJobStatus($event->job->uuid(), 'processed');
    }

    /**
     * Handle a job that has failed.
     */
    public function handleJobFailed(JobFailed $event): void
    {
        $this->updateJobStatus($event->job->uuid(), 'failed', $event->exception);
    }
    
    /**
     * Helper method to update the job's status.
     */
    private function updateJobStatus(string $uuid, string $status, ?\Throwable $exception = null): void
    {
        $scoutJob = ScoutJob::find($uuid);
        
        if ($scoutJob) {
            $scoutJob->update([
                'status' => $status,
                'exception' => $exception?->getMessage(),
                'finished_at' => now(),
            ]);
        }
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe(Dispatcher $events): void
    {
        $events->listen(JobProcessing::class, [self::class, 'handleJobProcessing']);
        $events->listen(JobProcessed::class, [self::class, 'handleJobProcessed']);
        $events->listen(JobFailed::class, [self::class, 'handleJobFailed']);
    }
}