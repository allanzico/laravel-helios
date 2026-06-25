<?php

namespace Allanzico\LaravelHelios\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;
use Allanzico\LaravelHelios\Services\ActionRecorder;
use Allanzico\LaravelHelios\Services\LogViewerService;
use Allanzico\LaravelHelios\Support\ActionAuthorizer;

class LogController extends Controller
{
    protected $logViewerService;

    public function __construct(LogViewerService $logViewerService)
    {
        $this->logViewerService = $logViewerService;
    }

    public function index(): JsonResponse
    {
        return response()->json([
            'logs' => $this->logViewerService->getAllLogs(),
        ]);
    }

    public function show(string $fileName): JsonResponse
    {
        $content = $this->logViewerService->getLogContent($fileName);

        if ($content === null) {
            return response()->json(['error' => 'Log file not found.'], 404);
        }

        return response()->json([
            'file' => $fileName,
            'content' => $content,
            'can_clear' => app(ActionAuthorizer::class)->allowed('clear_log', 'clear_logs'),
        ]);
    }

    public function destroy(string $fileName): JsonResponse
    {
        app(ActionAuthorizer::class)->authorize('clear_log', 'clear_logs', 'Log clearing is disabled.');

        // Basic security to prevent directory traversal
        if (str_contains($fileName, '..') || str_contains($fileName, '/') || str_contains($fileName, '\\')) {
            abort(400, 'Invalid filename.');
        }
        $filePath = config('helios.log_path') . '/' . $fileName;

        if (!File::exists($filePath)) {
            abort(404, 'Log file not found.');
        }
        File::put($filePath, '');
        app(ActionRecorder::class)->record('clear_log', 'log', $fileName, 'finished');

        return response()->json(['message' => 'Log file cleared.']);
    }
}
