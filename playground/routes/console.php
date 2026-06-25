<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('demo:heartbeat', function () {
    $this->info('Helios playground heartbeat at '.now()->toDateTimeString());
})->purpose('Emit a small scheduled-command demo message');

Schedule::command('demo:heartbeat')
    ->everyMinute()
    ->description('Helios playground heartbeat');
