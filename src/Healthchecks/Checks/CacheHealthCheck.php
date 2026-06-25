<?php

namespace Allanzico\LaravelHelios\HealthChecks\Checks;

use Allanzico\LaravelHelios\HealthChecks\HealthCheck;
use Allanzico\LaravelHelios\HealthChecks\HealthCheckResult;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Throwable;

class CacheHealthCheck extends HealthCheck
{
    public function run(): HealthCheckResult
    {
        try {
            $key = 'helios:health:cache:'.Str::random(16);
            $value = Str::random(32);
            $start = microtime(true);

            Cache::put($key, $value, now()->addMinute());
            $read = Cache::get($key);
            Cache::forget($key);

            $runtimeMs = round((microtime(true) - $start) * 1000, 2);

            $this->shortSummary = "{$runtimeMs}ms";
            $this->meta = [
                'store' => config('cache.default'),
                'runtime_ms' => $runtimeMs,
            ];

            if ($read !== $value) {
                return $this->failed('Cache read/write check returned an unexpected value');
            }

            return $this->ok('Cache read/write check passed');
        } catch (Throwable $e) {
            return $this->crashed('Could not read and write cache', $e);
        }
    }
}
