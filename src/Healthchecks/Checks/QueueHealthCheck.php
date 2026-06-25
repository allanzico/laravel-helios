<?php

namespace Allanzico\LaravelHelios\HealthChecks\Checks;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Allanzico\LaravelHelios\HealthChecks\HealthCheck;
use Allanzico\LaravelHelios\HealthChecks\HealthCheckResult;

class QueueHealthCheck extends HealthCheck
{
    protected int $maxPendingJobs = 1000;
    protected int $maxFailedJobs = 50;

    public function maxPendingJobs(int $max): static
    {
        $this->maxPendingJobs = $max;
        return $this;
    }

    public function maxFailedJobs(int $max): static
    {
        $this->maxFailedJobs = $max;
        return $this;
    }

    public function run(): HealthCheckResult
    {
        try {
            $pendingJobs = Schema::hasTable('jobs') ? DB::table('jobs')->count() : 0;
            $failedJobs = Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : 0;

            $this->shortSummary = "{$pendingJobs} pending, {$failedJobs} failed";
            $this->meta = [
                'pending_jobs' => $pendingJobs,
                'failed_jobs' => $failedJobs,
                'max_pending_threshold' => $this->maxPendingJobs,
                'max_failed_threshold' => $this->maxFailedJobs,
            ];

            if ($failedJobs > $this->maxFailedJobs) {
                return $this->failed("Too many failed jobs: {$failedJobs}");
            }

            if ($pendingJobs > $this->maxPendingJobs) {
                return $this->warning("Queue backlog is high: {$pendingJobs} pending jobs");
            }

            return $this->ok("Queue is healthy");
        } catch (\Throwable $e) {
            return $this->crashed("Could not check queue status", $e);
        }
    }
}
