<?php

return [
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
    'error_tracking' => [
        'enabled' => env('HELIOS_ERROR_TRACKING_ENABLED', true),
    ],
    
];