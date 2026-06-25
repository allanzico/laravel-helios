<?php

namespace Allanzico\LaravelHelios\HealthChecks\Checks;

use Illuminate\Support\Facades\Redis;
use Allanzico\LaravelHelios\HealthChecks\HealthCheck;
use Allanzico\LaravelHelios\HealthChecks\HealthCheckResult;

class RedisHealthCheck extends HealthCheck
{
    protected string $connectionName = 'default';

    public function connection(string $name): static
    {
        $this->connectionName = $name;
        return $this;
    }

    public function run(): HealthCheckResult
    {
        if (! $this->shouldRun()) {
            $this->shortSummary = 'not used';

            return $this->skipped('Redis is not used by the configured cache, queue, or session drivers');
        }

        try {
            $connection = Redis::connection($this->connectionName);
            
            $startTime = microtime(true);
            $connection->ping();
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);

            $this->shortSummary = "{$responseTime}ms";
            $this->meta = [
                'connection_name' => $this->connectionName,
                'response_time_ms' => $responseTime,
            ];

            return $this->ok("Redis connection successful");
        } catch (\Throwable $e) {
            return $this->crashed("Could not connect to Redis", $e);
        }
    }

    protected function shouldRun(): bool
    {
        $configured = config('helios.health.redis.enabled');

        if ($configured !== null) {
            return (bool) $configured;
        }

        $cacheStore = config('cache.default');
        $cacheDriver = config("cache.stores.{$cacheStore}.driver");
        $queueConnection = config('queue.default');
        $queueDriver = config("queue.connections.{$queueConnection}.driver");

        return $cacheDriver === 'redis'
            || $queueDriver === 'redis'
            || config('session.driver') === 'redis';
    }
}
