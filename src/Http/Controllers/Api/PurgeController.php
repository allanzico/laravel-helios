<?php

namespace Allanzico\LaravelHelios\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PurgeController extends Controller
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'table' => ['required', 'string', Rule::in([
                'scout_jobs',
                'scout_requests',
                'scout_queries',
                'scout_scheduled_tasks',
            ])]
        ]);

        DB::table($validated['table'])->truncate();

        return response()->noContent();
    }
}