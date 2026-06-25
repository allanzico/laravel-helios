<?php

namespace Allanzico\LaravelHelios\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Allanzico\LaravelHelios\Models\HeliosError;
use Allanzico\LaravelHelios\Models\HeliosJob;
use Allanzico\LaravelHelios\Models\HeliosQuery;
use Allanzico\LaravelHelios\Models\HeliosRequest;
use Allanzico\LaravelHelios\Models\HeliosScheduledTask;
use Allanzico\LaravelHelios\Services\HealthCheckService;

class DashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        $healthService = app(HealthCheckService::class);
        $healthChecks = collect($healthService->runAllChecks());
        $problemChecks = $healthChecks
            ->whereIn('status', ['failed', 'crashed', 'warning'])
            ->values();

        $stats = [
            'health' => [
                'overall_status' => $healthService->getOverallStatus($healthChecks->all()),
                'total_checks' => $healthChecks->count(),
                'problem_count' => $problemChecks->count(),
                'problems' => $problemChecks
                    ->take(5)
                    ->map(fn (array $check) => [
                        'check' => $check['check'],
                        'status' => $check['status'],
                        'message' => $check['message'],
                        'short_summary' => $check['short_summary'],
                    ])
                    ->values(),
            ],
            'failed_jobs_24h' => HeliosJob::query()
                ->where('status', 'failed')
                ->where('finished_at', '>=', now()->subDay())
                ->count(),
            'errors_24h' => HeliosError::query()
                ->where('last_seen_at', '>=', now()->subDay())
                ->sum('occurrences'),
            'http_errors_24h' => HeliosRequest::query()
                ->where('status_code', '>=', 400)
                ->where('created_at', '>=', now()->subDay())
                ->count(),
            'avg_duration_24h' => round(HeliosRequest::query()
                ->where('created_at', '>=', now()->subDay())
                ->avg('duration_ms') ?? 0),
            'avg_memory_24h' => round(HeliosRequest::query()
                ->where('created_at', '>=', now()->subDay())
                ->avg('memory_mb') ?? 0, 2),
            'latest_failed_tasks' => HeliosScheduledTask::query()
                ->where('status', 'failed')
                ->orderByDesc('finished_at')
                ->limit(5)
                ->get(),
            'latest_slow_queries' => HeliosQuery::query()
                ->orderByDesc('time_ms')
                ->limit(5)
                ->get(),
        ];

        return response()->json($stats);
    }

    public function requestsPerMinute(): JsonResponse
    {
        $data = HeliosRequest::query()
            ->where('created_at', '>=', now()->subHour())
            ->orderBy('created_at')
            ->get(['created_at'])
            ->groupBy(fn (HeliosRequest $request) => $request->created_at->format('H:i'))
            ->map(fn ($requests, string $time) => [
                'time' => $time,
                'count' => $requests->count(),
            ])
            ->values();

        return response()->json($data);
    }
}
