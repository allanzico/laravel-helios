<?php

namespace Allanzico\LaravelHelios\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Allanzico\LaravelHelios\Models\HeliosJob;
use Allanzico\LaravelHelios\Models\HeliosQuery;
use Allanzico\LaravelHelios\Models\HeliosRequest;
use Allanzico\LaravelHelios\Models\HeliosScheduledTask;

class DashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        $stats = [
            'failed_jobs_24h' => HeliosJob::query()
                ->where('status', 'failed')
                ->where('finished_at', '>=', now()->subDay())
                ->count(),
            'errors_24h' => HeliosRequest::query()
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
            ->select(
                DB::raw("to_char(created_at, 'HH24:MI') as time"),
                DB::raw('count(*) as count')
            )
            ->where('created_at', '>=', now()->subHour())
            ->groupBy('time')
            ->orderBy('time')
            ->get();

        return response()->json($data);
    }
}