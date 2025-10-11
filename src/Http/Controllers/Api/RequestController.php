<?php

namespace Allanzico\LaravelHelios\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Allanzico\LaravelHelios\Models\HeliosRequest;

class RequestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $requests = HeliosRequest::query()
            ->orderByDesc('created_at')
            ->paginate($request->input('per_page', 25));
        return response()->json(['requests' => $requests]);
    }
}
