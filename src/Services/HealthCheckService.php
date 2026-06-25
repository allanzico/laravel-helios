<?php

namespace Allanzico\LaravelHelios\Services;

use Allanzico\LaravelHelios\Enums\HealthStatus;
use Allanzico\LaravelHelios\HealthChecks\Checks\ApplicationHealthCheck;
use Allanzico\LaravelHelios\HealthChecks\Checks\CacheHealthCheck;
use Allanzico\LaravelHelios\HealthChecks\Checks\DatabaseHealthCheck;
use Allanzico\LaravelHelios\HealthChecks\Checks\DiskSpaceHealthCheck;
use Allanzico\LaravelHelios\HealthChecks\Checks\EnvironmentHealthCheck;
use Allanzico\LaravelHelios\HealthChecks\Checks\QueueHealthCheck;
use Allanzico\LaravelHelios\HealthChecks\Checks\RedisHealthCheck;
use Allanzico\LaravelHelios\HealthChecks\Checks\SchedulerFreshnessHealthCheck;
use Allanzico\LaravelHelios\HealthChecks\Checks\StorageWritabilityHealthCheck;
use Allanzico\LaravelHelios\Models\HeliosHealthCheckSetting;
use Allanzico\LaravelHelios\Support\Redactor;

class HealthCheckService
{
    protected array $availableChecks = [
        // System & Server Checks
        ApplicationHealthCheck::class,
        
        // Infrastructure Checks
        DatabaseHealthCheck::class,
        CacheHealthCheck::class,
        RedisHealthCheck::class,
        
        // Resource Checks
        DiskSpaceHealthCheck::class,
        StorageWritabilityHealthCheck::class,
        QueueHealthCheck::class,
        SchedulerFreshnessHealthCheck::class,
        
        // Configuration Checks
        EnvironmentHealthCheck::class,
    ];

    public function getAvailableChecks(): array
    {
        return collect($this->availableChecks)->map(function ($checkClass) {
            try {
                $instance = new $checkClass();
                return [
                    'class' => $checkClass,
                    'name' => $instance->getName(),
                    'label' => $instance->getLabel(),
                    'category' => $this->getCategoryForCheck($checkClass),
                ];
            } catch (\Throwable $e) {
                return null;
            }
        })->filter()->toArray();
    }

    protected function getCategoryForCheck(string $checkClass): string
    {
        $categories = [
            'System & Server' => [
                ApplicationHealthCheck::class,
            ],
            'Infrastructure' => [
                DatabaseHealthCheck::class,
                CacheHealthCheck::class,
                RedisHealthCheck::class,
            ],
            'Resources' => [
                DiskSpaceHealthCheck::class,
                StorageWritabilityHealthCheck::class,
                QueueHealthCheck::class,
                SchedulerFreshnessHealthCheck::class,
            ],
            'Configuration' => [
                EnvironmentHealthCheck::class,
            ],
        ];

        foreach ($categories as $category => $checks) {
            if (in_array($checkClass, $checks)) {
                return $category;
            }
        }

        return 'Other';
    }

    public function runAllChecks(): array
    {
        $enabledChecks = HeliosHealthCheckSetting::where('enabled', true)
            ->pluck('check_class')
            ->toArray();

        // If no settings exist, run all checks by default
        if (empty($enabledChecks)) {
            $enabledChecks = $this->availableChecks;
        }

        $results = [];

        foreach ($enabledChecks as $checkClass) {
            if (!class_exists($checkClass)) {
                continue;
            }

            try {
                $check = new $checkClass();
                $result = $check->run();
                $results[] = $this->sanitizeResult($result->toArray());
            } catch (\Throwable $e) {
                $results[] = $this->sanitizeResult([
                    'check' => class_basename($checkClass),
                    'label' => class_basename($checkClass),
                    'status' => HealthStatus::CRASHED->value,
                    'message' => $e->getMessage(),
                    'short_summary' => 'crashed',
                    'meta' => [],
                ]);
            }
        }

        return $results;
    }

    public function getOverallStatus(array $results): string
    {
        $statuses = collect($results)->pluck('status');

        if ($statuses->contains(HealthStatus::CRASHED->value) || $statuses->contains(HealthStatus::FAILED->value)) {
            return 'failed';
        }

        if ($statuses->contains(HealthStatus::WARNING->value)) {
            return 'warning';
        }

        return 'ok';
    }

    protected function sanitizeResult(array $result): array
    {
        $redactor = app(Redactor::class);

        $result['message'] = $redactor->redact($result['message'] ?? '');
        $result['short_summary'] = $redactor->redact($result['short_summary'] ?? '');

        if (! config('helios.security.show_health_meta', false)) {
            $result['meta'] = [];

            return $result;
        }

        $result['meta'] = $redactor->redact($result['meta'] ?? []);

        return $result;
    }
}
