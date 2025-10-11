<?php

namespace Allanzico\LaravelHelios\HealthChecks\Checks;

use Allanzico\LaravelHelios\HealthChecks\HealthCheck;
use Allanzico\LaravelHelios\HealthChecks\HealthCheckResult;

class ApplicationHealthCheck extends HealthCheck
{
    public function run(): HealthCheckResult
    {
        try {
            $startTime = microtime(true);
            
            // Check if Laravel is running
            $appRunning = app()->isBooted();
            $phpVersion = PHP_VERSION;
            $laravelVersion = app()->version();
            
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);

            $this->shortSummary = "Laravel {$laravelVersion}";
            $this->meta = [
                'php_version' => $phpVersion,
                'laravel_version' => $laravelVersion,
                'app_booted' => $appRunning,
                'response_time_ms' => $responseTime,
            ];

            if (!$appRunning) {
                return $this->failed("Application is not properly booted");
            }

            return $this->ok("Application is running");
        } catch (\Throwable $e) {
            return $this->crashed("Application health check failed", $e);
        }
    }
}