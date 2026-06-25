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

    'gates' => [
        'view' => env('HELIOS_VIEW_GATE', 'viewHelios'),
        'run_task' => env('HELIOS_RUN_TASK_GATE', 'runHeliosTask'),
        'retry_job' => env('HELIOS_RETRY_JOB_GATE', 'retryHeliosJob'),
        'forget_job' => env('HELIOS_FORGET_JOB_GATE', 'forgetHeliosJob'),
        'clear_log' => env('HELIOS_CLEAR_LOG_GATE', 'clearHeliosLog'),
        'purge_data' => env('HELIOS_PURGE_DATA_GATE', 'purgeHeliosData'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Production Guardrails
    |--------------------------------------------------------------------------
    |
    | Helios is an administrative surface. Outside the allowed environments it
    | must be protected by either the built-in Authorize middleware or custom
    | middleware configured above.
    |
    */

    'strict_authorization' => env('HELIOS_STRICT_AUTHORIZATION', true),

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
            'allow_manual_runs' => env('HELIOS_ALLOW_MANUAL_SCHEDULE_RUNS', app()->environment(['local', 'testing'])),
            'manual_allowlist' => env('HELIOS_SCHEDULE_MANUAL_ALLOWLIST')
                ? array_filter(array_map('trim', explode(',', env('HELIOS_SCHEDULE_MANUAL_ALLOWLIST', ''))))
                : (app()->environment(['local', 'testing']) ? ['*'] : []),
        ],

        'errors' => [
            'enabled' => env('HELIOS_ERRORS_ENABLED', true),
        ],

        'health' => [
            'enabled' => env('HELIOS_HEALTH_ENABLED', true),
        ],
    ],

    'retention_days' => (int) env('HELIOS_RETENTION_DAYS', 7),

    'actions' => [
        'run_scheduled_tasks' => env('HELIOS_ALLOW_MANUAL_SCHEDULE_RUNS', app()->environment(['local', 'testing'])),
        'retry_jobs' => env('HELIOS_ALLOW_JOB_RETRY', app()->environment(['local', 'testing'])),
        'forget_jobs' => env('HELIOS_ALLOW_JOB_FORGET', false),
        'clear_logs' => env('HELIOS_ALLOW_LOG_CLEAR', false),
        'purge_data' => env('HELIOS_ALLOW_PURGE_DATA', app()->environment(['local', 'testing'])),
    ],

    'security' => [
        'store_query_bindings' => env('HELIOS_STORE_QUERY_BINDINGS', false),
        'store_request_body' => env('HELIOS_STORE_REQUEST_BODY', false),
        'store_request_headers' => env('HELIOS_STORE_REQUEST_HEADERS', false),
        'store_user_id' => env('HELIOS_STORE_USER_ID', false),
        'store_ip_address' => env('HELIOS_STORE_IP_ADDRESS', false),
        'store_user_agent' => env('HELIOS_STORE_USER_AGENT', false),
        'show_health_meta' => env('HELIOS_SHOW_HEALTH_META', app()->environment(['local', 'testing'])),
        'redacted_value' => '[REDACTED]',
        'redact_keys' => [
            'password',
            'password_confirmation',
            'current_password',
            'token',
            'api_token',
            'access_token',
            'refresh_token',
            'secret',
            'client_secret',
            'authorization',
            'cookie',
            'x-csrf-token',
            'x-xsrf-token',
            'set-cookie',
            '*_token',
            '*_secret',
            '*password*',
        ],
    ],

    'health' => [
        'scheduler' => [
            'lookback_minutes' => (int) env('HELIOS_SCHEDULER_HEALTH_LOOKBACK_MINUTES', 1440),
            'grace_minutes' => (int) env('HELIOS_SCHEDULER_HEALTH_GRACE_MINUTES', 5),
        ],

        'redis' => [
            'enabled' => env('HELIOS_HEALTH_REDIS_ENABLED'),
        ],

        'environment' => [
            'expected' => env('HELIOS_HEALTH_EXPECTED_ENV'),
        ],

        'storage' => [
            'paths' => [
                storage_path('framework/cache'),
                storage_path('logs'),
            ],
        ],
    ],

    'error_tracking' => [
        'enabled' => env('HELIOS_ERROR_TRACKING_ENABLED', env('HELIOS_ERRORS_ENABLED', true)),
        'group_by_line' => env('HELIOS_ERROR_GROUP_BY_LINE', false),
    ],
];
