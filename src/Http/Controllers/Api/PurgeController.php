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
                'helios_jobs',
                'helios_requests',
                'helios_queries',
                'helios_scheduled_tasks',
                'helios_errors',
            ])]
        ]);

        DB::table($validated['table'])->truncate();

        return response()->noContent();
    }
}
