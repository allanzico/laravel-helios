<?php

namespace Allanzico\LaravelHelios\Services;

use Allanzico\LaravelHelios\Enums\HealthStatus;
use Allanzico\LaravelHelios\HealthChecks\Checks\ApplicationHealthCheck;
use Allanzico\LaravelHelios\HealthChecks\Checks\CacheHealthCheck;
use Allanzico\LaravelHelios\HealthChecks\Checks\DatabaseHealthCheck;
use Allanzico\LaravelHelios\HealthChecks\Checks\DiskSpaceHealthCheck;
use Allanzico\LaravelHelios\HealthChecks\Checks\EnvironmentHealthCheck;
use Allanzico\LaravelHelios\HealthChecks\Checks\HttpHealthCheck;
use Allanzico\LaravelHelios\HealthChecks\Checks\QueueHealthCheck;
use Allanzico\LaravelHelios\HealthChecks\Checks\RedisHealthCheck;
use Allanzico\LaravelHelios\HealthChecks\Checks\SchedulerHealthCheck;
use Allanzico\LaravelHelios\HealthChecks\Checks\StorageHealthCheck;
use Allanzico\LaravelHelios\Models\HeliosHealthCheckSetting;

class HealthCheckService
{
    protected array $availableChecks = [
        // System & Server Checks
        ApplicationHealthCheck::class,
        HttpHealthCheck::class,
        SchedulerHealthCheck::class,
        
        // Infrastructure Checks
        DatabaseHealthCheck::class,
        RedisHealthCheck::class,
        CacheHealthCheck::class,
        StorageHealthCheck::class,
        
        // Resource Checks
        DiskSpaceHealthCheck::class,
        QueueHealthCheck::class,
        
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
                HttpHealthCheck::class,
                SchedulerHealthCheck::class,
            ],
            'Infrastructure' => [
                DatabaseHealthCheck::class,
                RedisHealthCheck::class,
                CacheHealthCheck::class,
                StorageHealthCheck::class,
            ],
            'Resources' => [
                DiskSpaceHealthCheck::class,
                QueueHealthCheck::class,
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
                $results[] = $result->toArray();
            } catch (\Throwable $e) {
                $results[] = [
                    'check' => class_basename($checkClass),
                    'label' => class_basename($checkClass),
                    'status' => HealthStatus::CRASHED->value,
                    'message' => $e->getMessage(),
                    'short_summary' => 'crashed',
                    'meta' => [],
                ];
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
}