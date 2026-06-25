<?php

namespace Allanzico\LaravelHelios\Console;

use Illuminate\Console\Command;
use Allanzico\LaravelHelios\Models\HeliosError;
use Allanzico\LaravelHelios\Models\HeliosAction;
use Allanzico\LaravelHelios\Models\HeliosJob;
use Allanzico\LaravelHelios\Models\HeliosQuery;
use Allanzico\LaravelHelios\Models\HeliosRequest;
use Allanzico\LaravelHelios\Models\HeliosScheduledTask;

class Prune extends Command
{
    protected $signature = 'helios:prune {--days= : Override the configured retention window}';

    protected $description = 'Delete old Helios monitoring records.';

    public function handle(): int
    {
        $days = (int) ($this->option('days') ?: config('helios.retention_days', 7));

        if ($days < 1) {
            $this->error('Retention days must be at least 1.');

            return self::FAILURE;
        }

        $cutoff = now()->subDays($days);

        $deleted = [
            'requests' => HeliosRequest::where('created_at', '<', $cutoff)->delete(),
            'queries' => HeliosQuery::where('created_at', '<', $cutoff)->delete(),
            'jobs' => HeliosJob::where('started_at', '<', $cutoff)->delete(),
            'scheduled_tasks' => HeliosScheduledTask::where('started_at', '<', $cutoff)->delete(),
            'errors' => HeliosError::where('last_seen_at', '<', $cutoff)->delete(),
            'actions' => HeliosAction::where('created_at', '<', $cutoff)->delete(),
        ];

        foreach ($deleted as $name => $count) {
            $this->line("Deleted {$count} {$name} records.");
        }

        return self::SUCCESS;
    }
}
