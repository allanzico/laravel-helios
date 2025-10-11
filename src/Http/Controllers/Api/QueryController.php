<?php

namespace Allanzico\LaravelHelios\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Allanzico\LaravelHelios\Models\HeliosQuery;

class QueryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $queries = HeliosQuery::query()
            ->orderByDesc('created_at')
            ->paginate($request->input('per_page', 25));

        return response()->json(['queries' => $queries]);
    }
}
