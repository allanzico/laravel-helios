<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Helios
    |--------------------------------------------------------------------------
    */

    'enabled' => env('HELIOS_ENABLED', true),

    'path' => env('HELIOS_PATH', 'helios'),

    'domain' => env('HELIOS_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | Route Protection
    |--------------------------------------------------------------------------
    |
    | By default Helios is available in local/testing environments. In other
    | environments define a "viewHelios" gate in your application.
    |
    */

    'middleware' => [
        'web',
        \Allanzico\LaravelHelios\Http\Middleware\Authorize::class,
    ],

    'allowed_environments' => ['local', 'testing'],

    'gate' => 'viewHelios',

    /*
    |--------------------------------------------------------------------------
    | Log Viewer Path
    |--------------------------------------------------------------------------
    |
    | This value is the path to the directory where your application's
    | log files are stored. By default, this is the storage_path,
    | but you can change it to any directory you prefer.
    |
    */
    'log_path' => storage_path('logs'),

    /*
    |--------------------------------------------------------------------------
    | Watchers
    |--------------------------------------------------------------------------
    |
    | Keep the defaults useful but quiet. Helios stores slow/error requests and
    | slow queries by default, with low-volume sampling for normal activity.
    |
    */

    'watchers' => [
        'requests' => [
            'enabled' => env('HELIOS_REQUESTS_ENABLED', true),
            'slow_ms' => (float) env('HELIOS_SLOW_REQUEST_MS', 1000),
            'sample_rate' => (float) env('HELIOS_REQUEST_SAMPLE_RATE', 0.05),
            'ignore_paths' => [
                'helios*',
                'telescope*',
                'horizon*',
                'pulse*',
                '_debugbar*',
            ],
        ],

        'queries' => [
            'enabled' => env('HELIOS_QUERIES_ENABLED', true),
            'slow_ms' => (float) env('HELIOS_SLOW_QUERY_MS', 100),
            'sample_rate' => (float) env('HELIOS_QUERY_SAMPLE_RATE', 0.0),
        ],

        'jobs' => [
            'enabled' => env('HELIOS_JOBS_ENABLED', true),
        ],

        'schedule' => [
            'enabled' => env('HELIOS_SCHEDULE_ENABLED', true),
            'allow_manual_runs' => env('HELIOS_ALLOW_MANUAL_SCHEDULE_RUNS', true),
        ],

        'errors' => [
            'enabled' => env('HELIOS_ERRORS_ENABLED', true),
        ],

        'health' => [
            'enabled' => env('HELIOS_HEALTH_ENABLED', true),
        ],
    ],

    'retention_days' => (int) env('HELIOS_RETENTION_DAYS', 7),

    'error_tracking' => [
        'enabled' => env('HELIOS_ERROR_TRACKING_ENABLED', env('HELIOS_ERRORS_ENABLED', true)),
    ],
];
