<?php

namespace Allanzico\LaravelHelios\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Events\Dispatcher;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Allanzico\LaravelHelios\Models\HeliosJob;
use Allanzico\LaravelHelios\Support\Redactor;
use Throwable;

class JobEventListener
{
    /**
     * Handle a job starting to process.
     */
    public function handleJobProcessing(JobProcessing $event): void
    {
        try {
            $payload = json_decode($event->job->getRawBody(), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $payload = $event->job->getRawBody();
            }

            HeliosJob::updateOrCreate(
                ['id' => $event->job->uuid()],
                [
                    'name' => $event->job->resolveName(),
                    'status' => 'running',
                    'payload' => app(Redactor::class)->redact($payload),
                    'exception' => null,
                    'started_at' => now(),
                    'finished_at' => null,
                ]
            );
        } catch (Throwable) {
        }
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
    private function updateJobStatus(string $uuid, string $status, ?Throwable $exception = null): void
    {
        try {
            $heliosJob = HeliosJob::find($uuid);

            if ($heliosJob) {
                $heliosJob->update([
                    'status' => $status,
                    'exception' => $exception ? app(Redactor::class)->redact($exception->getMessage()) : null,
                    'finished_at' => now(),
                ]);
            }
        } catch (Throwable) {
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
