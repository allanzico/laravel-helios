<?php

namespace Allanzico\LaravelHelios\Services;

use DateTimeInterface;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Queue\Events\JobRetryRequested;
use RuntimeException;

class QueueActionService
{
    public function capabilities(): array
    {
        return [
            'failed_driver' => config('queue.failed.driver'),
            'failed_provider' => $this->providerName(),
            'uses_helios_job_ids' => $this->usesHeliosJobIds(),
            'retry_supported' => $this->supportsActions(),
            'forget_supported' => $this->supportsActions(),
        ];
    }

    public function supportsActions(): bool
    {
        return app()->bound('queue.failer')
            && $this->usesHeliosJobIds()
            && method_exists($this->failer(), 'find')
            && method_exists($this->failer(), 'forget');
    }

    public function retry(string $id): array
    {
        $this->ensureSupported();

        $job = $this->findFailedJob($id);
        $queue = app('queue')->connection($job->connection);

        if (class_exists(JobRetryRequested::class)) {
            app('events')->dispatch(new JobRetryRequested($job));
        }

        $queue->pushRaw(
            $this->preparePayload($job->payload),
            $job->queue,
            $this->getQueueableOptions($queue, $job)
        );

        $this->failer()->forget($id);

        return [
            'id' => $id,
            'connection' => $job->connection,
            'queue' => $job->queue,
            'provider' => $this->providerName(),
        ];
    }

    public function forget(string $id): array
    {
        $this->ensureSupported();

        $job = $this->findFailedJob($id);

        if (! $this->failer()->forget($id)) {
            throw new RuntimeException("No failed job matches ID [{$id}].");
        }

        return [
            'id' => $id,
            'connection' => $job->connection,
            'queue' => $job->queue,
            'provider' => $this->providerName(),
        ];
    }

    protected function findFailedJob(string $id): object
    {
        $job = $this->failer()->find($id);

        if (! $job) {
            throw new RuntimeException("No failed job matches Helios job ID [{$id}].");
        }

        foreach (['connection', 'queue', 'payload'] as $property) {
            if (! isset($job->{$property})) {
                throw new RuntimeException("Failed job [{$id}] is missing required [{$property}] data.");
            }
        }

        return $job;
    }

    protected function ensureSupported(): void
    {
        if (! app()->bound('queue.failer')) {
            throw new RuntimeException('Laravel failed job provider is not configured.');
        }

        if (! $this->usesHeliosJobIds()) {
            throw new RuntimeException(sprintf(
                'Helios queue actions require a UUID-based failed job provider. Current failed driver [%s] uses IDs that Helios cannot safely map.',
                config('queue.failed.driver') ?: 'unknown'
            ));
        }

        if (! method_exists($this->failer(), 'find') || ! method_exists($this->failer(), 'forget')) {
            throw new RuntimeException(sprintf(
                'Failed job provider [%s] does not support find/forget actions.',
                $this->providerName()
            ));
        }
    }

    protected function usesHeliosJobIds(): bool
    {
        return in_array(config('queue.failed.driver'), ['database-uuids', 'file', 'dynamodb'], true);
    }

    protected function preparePayload(string $payload): string
    {
        return $this->refreshRetryUntil($this->resetAttempts($payload));
    }

    protected function resetAttempts(string $payload): string
    {
        $decoded = $this->decodePayload($payload);

        if (array_key_exists('attempts', $decoded)) {
            $decoded['attempts'] = 0;
        }

        return json_encode($decoded);
    }

    protected function refreshRetryUntil(string $payload): string
    {
        $decoded = $this->decodePayload($payload);

        if (! isset($decoded['data']['command'])) {
            return json_encode($decoded);
        }

        $instance = $this->getInstanceFromPayload($decoded);

        if (is_object($instance) && ! $instance instanceof \__PHP_Incomplete_Class && method_exists($instance, 'retryUntil')) {
            $retryUntil = $instance->retryUntil();

            $decoded['retryUntil'] = $retryUntil instanceof DateTimeInterface
                ? $retryUntil->getTimestamp()
                : $retryUntil;
        }

        return json_encode($decoded);
    }

    protected function getQueueableOptions(mixed $queue, object $job): array
    {
        if (! method_exists($queue, 'getQueueableOptions')) {
            return [];
        }

        $payload = $this->decodePayload($job->payload);

        if (! isset($payload['data']['command'])) {
            return [];
        }

        return $queue->getQueueableOptions($this->getInstanceFromPayload($payload), $job->queue, $job->payload);
    }

    protected function getInstanceFromPayload(array $payload): mixed
    {
        $command = $payload['data']['command'] ?? null;

        if (! is_string($command)) {
            throw new RuntimeException('Unable to extract job command from payload.');
        }

        if (str_starts_with($command, 'O:')) {
            return unserialize($command);
        }

        if (app()->bound(Encrypter::class)) {
            return unserialize(app(Encrypter::class)->decrypt($command));
        }

        throw new RuntimeException('Unable to decrypt job payload.');
    }

    protected function decodePayload(string $payload): array
    {
        $decoded = json_decode($payload, true);

        if (! is_array($decoded)) {
            throw new RuntimeException('Failed job payload is not valid JSON.');
        }

        return $decoded;
    }

    protected function failer(): mixed
    {
        return app('queue.failer');
    }

    protected function providerName(): ?string
    {
        if (! app()->bound('queue.failer')) {
            return null;
        }

        return class_basename($this->failer());
    }
}
