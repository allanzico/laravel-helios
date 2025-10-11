<?php

namespace Allanzico\LaravelHelios\HealthChecks\Checks;

use Illuminate\Support\Facades\DB;
use Allanzico\LaravelHelios\HealthChecks\HealthCheck;
use Allanzico\LaravelHelios\HealthChecks\HealthCheckResult;

class DatabaseHealthCheck extends HealthCheck
{
    protected string $connectionName = 'default';

    public function connection(string $name): static
    {
        $this->connectionName = $name;
        return $this;
    }

    public function run(): HealthCheckResult
    {
        try {
            $connection = $this->connectionName === 'default' 
                ? DB::connection() 
                : DB::connection($this->connectionName);
            
            $startTime = microtime(true);
            $connection->getPdo();
            $connection->getDatabaseName();
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);

            $this->shortSummary = "{$responseTime}ms";
            $this->meta = [
                'connection_name' => $connection->getName(),
                'database' => $connection->getDatabaseName(),
                'response_time_ms' => $responseTime,
            ];

            return $this->ok("Connected to database successfully");
        } catch (\Throwable $e) {
            return $this->crashed("Could not connect to database", $e);
        }
    }
}