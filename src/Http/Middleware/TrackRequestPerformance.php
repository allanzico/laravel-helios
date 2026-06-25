<?php

namespace Allanzico\LaravelHelios\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Allanzico\LaravelHelios\Models\HeliosRequest;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class TrackRequestPerformance
{
    protected float $startTime;
    protected int $startMemory;

    public function handle(Request $request, Closure $next): Response
    {
        $this->startTime = microtime(true);
        $this->startMemory = memory_get_usage();

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        if (! config('helios.watchers.requests.enabled', true)) {
            return;
        }

        $duration = (microtime(true) - $this->startTime) * 1000;

        if (! $this->shouldRecord($request, $response, $duration)) {
            return;
        }

        try {
            if (! Schema::hasTable('helios_requests')) {
                return;
            }
        } catch (Throwable) {
            return;
        }

        $memoryUsage = (memory_get_peak_usage() - $this->startMemory) / 1024 / 1024;


        $route = $request->route();

        try {
            HeliosRequest::create([
                'method' => $request->getMethod(),
                'uri' => ltrim($request->path(), '/'),
                'controller_action' => $route?->getActionName(),
                'status_code' => $response->getStatusCode(),
                'duration_ms' => $duration,
                'memory_mb' => $memoryUsage,
                'created_at' => now(),
            ]);
        } catch (Throwable) {
        }
    }

    protected function shouldRecord(Request $request, Response $response, float $duration): bool
    {
        foreach (config('helios.watchers.requests.ignore_paths', []) as $path) {
            if ($request->is($path)) {
                return false;
            }
        }

        if ($response->getStatusCode() >= 400) {
            return true;
        }

        if ($duration >= (float) config('helios.watchers.requests.slow_ms', 1000)) {
            return true;
        }

        return $this->sample((float) config('helios.watchers.requests.sample_rate', 0.05));
    }

    protected function sample(float $rate): bool
    {
        if ($rate <= 0) {
            return false;
        }

        if ($rate >= 1) {
            return true;
        }

        return mt_rand() / mt_getrandmax() <= $rate;
    }
}
