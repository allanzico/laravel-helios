<?php

namespace Allanzico\LaravelHelios\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Allanzico\LaravelHelios\Models\HeliosJob;
use Throwable;

class JobController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $jobs = HeliosJob::query()
            ->orderByDesc('started_at')
            ->paginate($request->input('per_page', 15));

        return response()->json([
            'jobs' => $jobs,
            'summary' => [
                'pending_jobs' => Schema::hasTable('jobs') ? DB::table('jobs')->count() : 0,
                'failed_jobs' => Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : 0,
            ],
        ]);
    }

    public function retry(string $id): JsonResponse
    {
        try {
            Artisan::call('queue:retry', ['id' => [$id]]);

            HeliosJob::query()
                ->whereKey($id)
                ->where('status', 'failed')
                ->update(['status' => 'retried']);

            return response()->json([
                'message' => 'Job retry requested.',
                'output' => trim(Artisan::output()),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Unable to retry job.',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function forget(string $id): JsonResponse
    {
        try {
            Artisan::call('queue:forget', ['id' => $id]);

            HeliosJob::query()
                ->whereKey($id)
                ->where('status', 'failed')
                ->delete();

            return response()->json([
                'message' => 'Failed job forgotten.',
                'output' => trim(Artisan::output()),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Unable to forget job.',
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}
