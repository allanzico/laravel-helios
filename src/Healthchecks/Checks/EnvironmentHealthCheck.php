<?php

namespace Allanzico\LaravelHelios\HealthChecks\Checks;

use Allanzico\LaravelHelios\HealthChecks\HealthCheck;
use Allanzico\LaravelHelios\HealthChecks\HealthCheckResult;

class EnvironmentHealthCheck extends HealthCheck
{
    protected string $expectedEnvironment = 'production';

    public function expectedEnvironment(string $environment): static
    {
        $this->expectedEnvironment = $environment;
        return $this;
    }

    public function run(): HealthCheckResult
    {
        $currentEnv = app()->environment();
        $expectedEnvironment = config('helios.health.environment.expected');

        $this->shortSummary = $currentEnv;
        $this->meta = [
            'current_environment' => $currentEnv,
            'expected_environment' => $expectedEnvironment,
            'debug_mode' => config('app.debug'),
        ];

        if ($expectedEnvironment && $currentEnv !== $expectedEnvironment) {
            return $this->warning("Environment mismatch. Expected '{$expectedEnvironment}', got '{$currentEnv}'");
        }

        if (config('app.debug') && $currentEnv === 'production') {
            return $this->warning("Debug mode is enabled in production");
        }

        return $this->ok("Environment is correctly configured");
    }
}
