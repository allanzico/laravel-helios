<?php

use App\Jobs\DemoFailingJob;
use Allanzico\LaravelHelios\Services\ActionRecorder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return <<<'HTML'
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Helios Playground</title>
    <style>
        body { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; margin: 40px; line-height: 1.6; max-width: 880px; }
        a { color: #111827; font-weight: 700; }
        li { margin-bottom: 10px; }
        code { background: #f3f4f6; padding: 2px 5px; border-radius: 4px; }
    </style>
</head>
<body>
    <h1>Helios Playground</h1>
    <p>This app installs the local package through Composer's path repository. Use these links to create data, then open <a href="/helios">/helios</a>.</p>
    <ul>
        <li><a href="/demo/request">Create a normal request, query, and log entry</a></li>
        <li><a href="/demo/slow-request">Create a slow request</a></li>
        <li><a href="/demo/error">Report a handled exception</a></li>
        <li><a href="/demo/jobs/fail">Dispatch and process a failing queued job</a></li>
        <li><a href="/demo/action">Create a harmless audit action</a></li>
    </ul>
    <p>Scheduled task demo: open <code>/helios/tasks</code> to see <code>demo:heartbeat</code>. Manual runs are disabled by default; opt in with <code>HELIOS_ALLOW_MANUAL_SCHEDULE_RUNS=true</code> and an allowlist when testing that flow.</p>
</body>
</html>
HTML;
});

Route::get('/demo/request', function () {
    DB::select('select 1 as helios_playground');
    Log::info('Helios playground request endpoint was visited.');

    return 'Created a request, query, and log entry. Open /helios.';
});

Route::get('/demo/slow-request', function () {
    usleep(250_000);
    DB::select('select 2 as helios_slow_playground');

    return 'Created a slow request. Open /helios/requests.';
});

Route::get('/demo/error', function () {
    try {
        throw new RuntimeException('Helios playground handled exception.');
    } catch (Throwable $exception) {
        report($exception);
    }

    return response('Reported a handled exception. Open /helios/errors.', 500);
});

Route::get('/demo/jobs/fail', function () {
    DemoFailingJob::dispatch();

    Artisan::call('queue:work', [
        '--once' => true,
        '--tries' => 1,
        '--timeout' => 5,
    ]);

    return response()->json([
        'message' => 'Dispatched and processed a failing job. Open /helios/jobs.',
        'queue_output' => trim(Artisan::output()),
    ]);
});

Route::get('/demo/action', function () {
    app(ActionRecorder::class)->record('demo_action', 'playground', 'demo', 'finished', [
        'source' => 'playground',
    ]);

    return 'Created a harmless audit action. Open /helios.';
});
