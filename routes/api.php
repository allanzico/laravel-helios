<?php

use Illuminate\Support\Facades\Route;
use Allanzico\LaravelHelios\Http\Controllers\Api\DashboardController;
use Allanzico\LaravelHelios\Http\Controllers\Api\ErrorController;
use Allanzico\LaravelHelios\Http\Controllers\Api\JobController;
use Allanzico\LaravelHelios\Http\Controllers\Api\LogController;
use Allanzico\LaravelHelios\Http\Controllers\Api\PurgeController;
use Allanzico\LaravelHelios\Http\Controllers\Api\QueryController;
use Allanzico\LaravelHelios\Http\Controllers\Api\RequestController;
use Allanzico\LaravelHelios\Http\Controllers\Api\ScheduledTaskController;
use Allanzico\LaravelHelios\Http\Controllers\Api\HealthCheckController;

// Log routes
Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
Route::get('/logs/{file}', [LogController::class, 'show'])->name('logs.show');

// Job routes (add this line)
Route::get('/jobs', [JobController::class, 'index'])->name('jobs.index');

//Tasks routes
Route::get('/scheduled-tasks', [ScheduledTaskController::class, 'index'])->name('scheduled-tasks.index');
Route::get('/scheduled-tasks/run', [ScheduledTaskController::class, 'run'])->name('scheduled-tasks.run');

// Database Query routes
Route::get('/queries', [QueryController::class, 'index'])->name('queries.index');

// Request routes
Route::get('/requests', [RequestController::class, 'index'])->name('requests.index');


// Dashboard routes
Route::get('/dashboard-stats', [DashboardController::class, 'stats'])->name('dashboard.stats');
Route::get('/requests-per-minute', [DashboardController::class, 'requestsPerMinute'])->name('dashboard.requests-per-minute');

// Purge route
Route::post('/purge', PurgeController::class)->name('purge');

// Clear log file route
Route::delete('/logs/{file}', [LogController::class, 'destroy'])->name('logs.destroy');

// Health Check routes
Route::get('/health-checks', [HealthCheckController::class, 'index'])->name('health-checks.index');
Route::get('/health-checks/available', [HealthCheckController::class, 'available'])->name('health-checks.available');
Route::get('/health-checks/settings', [HealthCheckController::class, 'settings'])->name('health-checks.settings');
Route::post('/health-checks/settings', [HealthCheckController::class, 'updateSettings'])->name('health-checks.update-settings');

// Error Tracking routes
Route::get('/errors', [ErrorController::class, 'index'])->name('errors.index');
Route::get('/errors/stats', [ErrorController::class, 'stats'])->name('errors.stats');
Route::get('/errors/{id}', [ErrorController::class, 'show'])->name('errors.show');
Route::post('/errors/{id}/resolve', [ErrorController::class, 'resolve'])->name('errors.resolve');
Route::post('/errors/{id}/ignore', [ErrorController::class, 'ignore'])->name('errors.ignore');
Route::post('/errors/{id}/unresolve', [ErrorController::class, 'unresolve'])->name('errors.unresolve');
Route::delete('/errors/{id}', [ErrorController::class, 'destroy'])->name('errors.destroy');