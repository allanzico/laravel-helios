<?php

namespace Allanzico\LaravelHelios\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Allanzico\LaravelHelios\Models\ScoutError;

class ErrorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ScoutError::query();

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by level
        if ($request->has('level')) {
            $query->where('level', $request->level);
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', 'like', "%{$request->type}%");
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('message', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%")
                  ->orWhere('file', 'like', "%{$search}%");
            });
        }

        $errors = $query->orderBy('last_seen_at', 'desc')
            ->paginate(20);

        return response()->json($errors);
    }

    public function show(string $id): JsonResponse
    {
        $error = ScoutError::findOrFail($id);

        return response()->json($error);
    }

    public function resolve(Request $request, string $id): JsonResponse
    {
        $error = ScoutError::findOrFail($id);
        $error->markAsResolved($request->user()?->id);

        return response()->json([
            'message' => 'Error marked as resolved',
            'error' => $error->fresh(),
        ]);
    }

    public function ignore(string $id): JsonResponse
    {
        $error = ScoutError::findOrFail($id);
        $error->markAsIgnored();

        return response()->json([
            'message' => 'Error marked as ignored',
            'error' => $error->fresh(),
        ]);
    }

    public function unresolve(string $id): JsonResponse
    {
        $error = ScoutError::findOrFail($id);
        $error->markAsUnresolved();

        return response()->json([
            'message' => 'Error marked as unresolved',
            'error' => $error->fresh(),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $error = ScoutError::findOrFail($id);
        $error->delete();

        return response()->json([
            'message' => 'Error deleted successfully',
        ]);
    }

    public function stats(): JsonResponse
    {
        $stats = [
            'total_errors' => ScoutError::count(),
            'unresolved' => ScoutError::where('status', 'unresolved')->count(),
            'resolved' => ScoutError::where('status', 'resolved')->count(),
            'ignored' => ScoutError::where('status', 'ignored')->count(),
            'last_24h' => ScoutError::where('last_seen_at', '>=', now()->subDay())->count(),
            'critical' => ScoutError::where('level', 'critical')->where('status', 'unresolved')->count(),
        ];

        return response()->json($stats);
    }
}