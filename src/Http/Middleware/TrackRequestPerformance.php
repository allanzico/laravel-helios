<?php

namespace Allanzico\LaravelHelios\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Allanzico\LaravelHelios\Models\ScoutRequest;
use Symfony\Component\HttpFoundation\Response;

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
        // Don't log our own API requests
        if ($request->is('scout/*')) {
            return;
        }
        
        $duration = (microtime(true) - $this->startTime) * 1000;
         $memoryUsage = (memory_get_peak_usage() - $this->startMemory) / 1024 / 1024;


        $route = $request->route();

        ScoutRequest::create([
            'method' => $request->getMethod(),
            'uri' => ltrim($request->path(), '/'),
            'controller_action' => $route?->getActionName(),
            'status_code' => $response->getStatusCode(),
            'duration_ms' => $duration,
            'memory_mb' => $memoryUsage,
            'created_at' => now(),
        ]);
    }
}