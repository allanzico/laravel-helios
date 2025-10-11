<?php

namespace Allanzico\LaravelHelios\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Allanzico\LaravelHelios\Models\ScoutJob;

class JobController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $jobs = ScoutJob::query()
            ->orderByDesc('started_at')
            ->paginate($request->input('per_page', 15)); ;

        return response()->json(['jobs' => $jobs]);
    }
}