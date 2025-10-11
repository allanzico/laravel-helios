<?php

namespace Allanzico\LaravelHelios\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Allanzico\LaravelHelios\Models\ScoutHealthCheckSetting;
use Allanzico\LaravelHelios\Services\HealthCheckService;

class HealthCheckController extends Controller
{
    public function __construct(
        protected HealthCheckService $healthCheckService
    ) {}

    public function index(): JsonResponse
    {
        $results = $this->healthCheckService->runAllChecks();

        return response()->json([
            'checks' => $results,
            'overall_status' => $this->healthCheckService->getOverallStatus($results),
        ]);
    }

public function available(): JsonResponse
{
    $checks = $this->healthCheckService->getAvailableChecks();

    // Ensure checks is returned as an array, not an object
    return response()->json([
        'checks' => array_values($checks) 
    ]);
}

public function settings(): JsonResponse
{
    $settings = ScoutHealthCheckSetting::all()->toArray();

    return response()->json([
        'settings' => array_values($settings) 
    ]);
}

    public function updateSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'enabled_checks' => 'array', 
            'enabled_checks.*' => 'string',
        ]);

        // Clear all settings first
        ScoutHealthCheckSetting::query()->delete();

        // Insert new settings only if there are enabled checks
        if (!empty($validated['enabled_checks'])) {
            foreach ($validated['enabled_checks'] as $checkClass) {
                ScoutHealthCheckSetting::create([
                    'check_class' => $checkClass,
                    'enabled' => true,
                ]);
            }
        }

        return response()->json(['message' => 'Settings updated successfully']);
    }
}