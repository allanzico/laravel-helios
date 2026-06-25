<?php

namespace Allanzico\LaravelHelios\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Allanzico\LaravelHelios\Models\HeliosJob;
use Allanzico\LaravelHelios\Services\ActionRecorder;
use Allanzico\LaravelHelios\Services\QueueActionService;
use Allanzico\LaravelHelios\Support\ActionAuthorizer;
use Throwable;

class JobController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $queueActions = app(QueueActionService::class);

        $jobs = HeliosJob::query()
            ->orderByDesc('started_at')
            ->paginate($request->input('per_page', 15));

        return response()->json([
            'jobs' => $jobs,
            'summary' => [
                'pending_jobs' => Schema::hasTable('jobs') ? DB::table('jobs')->count() : 0,
                'failed_jobs' => Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : 0,
                'queue_actions' => $queueActions->capabilities(),
            ],
        ]);
    }

    public function retry(string $id): JsonResponse
    {
        app(ActionAuthorizer::class)->authorize('retry_job', 'retry_jobs', 'Queue retry actions are disabled.');

        try {
            $result = app(QueueActionService::class)->retry($id);

            HeliosJob::query()
                ->whereKey($id)
                ->where('status', 'failed')
                ->update(['status' => 'retried']);

            app(ActionRecorder::class)->record('retry_job', 'job', $id, 'finished', $result);

            return response()->json([
                'message' => 'Job retry requested.',
                'job' => $result,
            ]);
        } catch (Throwable $e) {
            app(ActionRecorder::class)->record('retry_job', 'job', $id, 'failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Unable to retry job.',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function forget(string $id): JsonResponse
    {
        app(ActionAuthorizer::class)->authorize('forget_job', 'forget_jobs', 'Queue forget actions are disabled.');

        try {
            $result = app(QueueActionService::class)->forget($id);

            HeliosJob::query()
                ->whereKey($id)
                ->where('status', 'failed')
                ->delete();

            app(ActionRecorder::class)->record('forget_job', 'job', $id, 'finished', $result);

            return response()->json([
                'message' => 'Failed job forgotten.',
                'job' => $result,
            ]);
        } catch (Throwable $e) {
            app(ActionRecorder::class)->record('forget_job', 'job', $id, 'failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Unable to forget job.',
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}
