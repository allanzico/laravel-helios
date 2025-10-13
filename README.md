# Laravel Helios

A lightweight, self-hosted monitoring tool for Laravel applications. Helios provides real-time monitoring of:

- 📊 Request Performance
- 📝 Application Logs
- ⚙️ Queued Jobs
- ⏰ Scheduled Tasks
- 🗄️ Database Queries
- 🏥 Health Checks
- 🐛 Error Tracking

## Features

- **Real-time Monitoring**: Track your Laravel application's performance in real-time
- **Modern UI**: Beautiful React-based dashboard with TanStack Router and Query
- **Easy Installation**: Simple Composer installation with automatic service provider registration
- **Lightweight**: Minimal overhead on your application
- **Self-hosted**: All data stays in your database
- **Error Tracking**: Automatic error grouping and tracking with detailed stack traces

## Requirements

- PHP 8.2 or higher
- Laravel 11.0 or higher (Laravel 12 supported)
- MySQL/PostgreSQL database

## Installation

### 1. Install via Composer

```bash
composer require allanzico/laravel-helios
```

The service provider will be automatically registered via Laravel's package auto-discovery.

### 2. Run Migrations

```bash
php artisan migrate
```

This will create the following tables:
- `helios_jobs` - Track queued jobs
- `helios_queries` - Database query logs
- `helios_requests` - HTTP request tracking
- `helios_scheduled_tasks` - Scheduled task execution logs
- `helios_task_definitions` - Scheduled task definitions
- `helios_health_check_settings` - Health check configuration
- `helios_errors` - Error tracking and grouping

### 3. Sync Scheduled Tasks

After installation, sync your scheduled tasks to start monitoring them:

```bash
php artisan helios:sync-tasks
```

Run this command whenever you add or modify scheduled tasks in your application.

That's it! The frontend assets are served directly from the package, so no publishing step is required.

## Usage

Once installed, access the Helios dashboard at:

```
http://your-app.com/helios
```

For local development:
```
http://localhost:8000/helios
```

### Dashboard Features

- **Overview**: Quick stats on failed jobs, errors, average response time, and slow queries
- **Requests**: Monitor all HTTP requests with duration, status codes, and memory usage
- **Jobs**: Track queued job execution, failures, and performance
- **Queries**: View all database queries with execution time and bindings
- **Scheduled Tasks**: Monitor cron jobs and scheduled tasks
- **Logs**: Browse and search application logs
- **Health Checks**: Configure and monitor application health checks
- **Errors**: Track and group application errors with detailed stack traces

## Configuration

### Publishing Assets (Optional)

The package serves assets directly from the vendor directory, so publishing is not required. However, if you want to customize the frontend or host assets from your public directory:

```bash
# Publish frontend assets to public/vendor/helios
php artisan vendor:publish --tag=helios-assets --force

# Publish views to resources/views/vendor/helios
php artisan vendor:publish --tag=helios-views

# Publish config file
php artisan vendor:publish --tag=helios-config

# Publish migrations (if you want to modify them)
php artisan vendor:publish --tag=helios-migrations
```

### Configuration File

Optionally publish and customize the configuration file:

```bash
php artisan vendor:publish --tag=helios-config
```

This creates `config/helios.php`:

```php
return [
    'log_path' => storage_path('logs'),
    'error_tracking' => [
        'enabled' => env('HELIOS_ERROR_TRACKING_ENABLED', true),
    ],
];
```

### Environment Variables

Add to your `.env` file:

```env
# Enable/disable error tracking
HELIOS_ERROR_TRACKING_ENABLED=true
```

## Error Tracking

To enable automatic error tracking, extend the Helios exception handler in your `app/Exceptions/Handler.php`:

```php
<?php

namespace App\Exceptions;

use Allanzico\LaravelHelios\Exceptions\HeliosExceptionHandler;

class Handler extends HeliosExceptionHandler
{
    // Your existing exception handler code...
}
```

This will automatically track all exceptions in the Helios dashboard with grouping by error hash.

## Purging Old Data

You can purge old monitoring data directly from the dashboard using the "Purge" buttons on each page, or programmatically:

```php
use Allanzico\LaravelHelios\Models\HeliosRequest;
use Allanzico\LaravelHelios\Models\HeliosQuery;
use Allanzico\LaravelHelios\Models\HeliosJob;

// Delete old data
HeliosRequest::where('created_at', '<', now()->subDays(7))->delete();
HeliosQuery::where('created_at', '<', now()->subDays(7))->delete();
HeliosJob::where('started_at', '<', now()->subDays(7))->delete();
```