<?php

namespace Allanzico\LaravelHelios\Http\Controllers\Api;

use Allanzico\LaravelHelios\Services\ActionRecorder;
use Allanzico\LaravelHelios\Support\ActionAuthorizer;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PurgeController extends Controller
{
    public function __invoke(Request $request)
    {
        app(ActionAuthorizer::class)->authorize('purge_data', 'purge_data', 'Data purge actions are disabled.');

        $validated = $request->validate([
            'table' => ['required', 'string', Rule::in([
                'helios_jobs',
                'helios_requests',
                'helios_queries',
                'helios_scheduled_tasks',
                'helios_errors',
                'helios_actions',
            ])]
        ]);

        DB::table($validated['table'])->truncate();
        app(ActionRecorder::class)->record('purge_data', 'table', $validated['table'], 'finished');

        return response()->noContent();
    }
}
