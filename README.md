# Laravel Helios

A lightweight, self-hosted monitoring tool for Laravel applications. Helios provides real-time monitoring of:

- Request Performance
- Application Logs
- Queued Jobs
- Scheduled Tasks
- Database Queries
- Health Checks
- Error Tracking

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

**That's it!** Frontend assets are automatically inlined (similar to Laravel Horizon), so no asset publishing is required.

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

### Publishing Resources (Optional)

You can publish configuration and other resources if needed for customization:

```bash
# Publish config file
php artisan vendor:publish --tag=helios-config

# Publish views (for customization)
php artisan vendor:publish --tag=helios-views

# Publish migrations (if you want to modify them)
php artisan vendor:publish --tag=helios-migrations
```

**Note:** Asset publishing is not required. All CSS and JavaScript are automatically inlined in the HTML, similar to how Laravel Horizon works.

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

## Technical Architecture

Helios uses a modern tech stack:

### Backend
- **Laravel Package**: Middleware for request tracking, event listeners for job monitoring
- **Database**: All monitoring data stored in your application's database
- **Service Provider**: Auto-discovery with automatic registration

### Frontend
- **React 18**: Modern React with hooks
- **TanStack Router**: File-based routing with type safety
- **TanStack Query**: Efficient data fetching and caching
- **Tailwind CSS v3**: Utility-first styling
- **Recharts**: Data visualization
- **Vite**: Fast build tool and dev server

### Asset Management
The package uses **inline asset loading** (inspired by Laravel Horizon). The `@heliosAssets` Blade directive reads the built CSS and JavaScript files from the package directory and embeds them directly into the HTML as `<style>` and `<script>` tags. This approach:

- **Eliminates the need for asset publishing** - No `vendor:publish` required
- **Simplifies updates** - `composer update` automatically updates frontend code
- **Reduces HTTP requests** - Assets are served with the initial HTML response
- **Works in any environment** - No public directory configuration needed
- **Provides better caching** - The entire page can be cached as one unit

## Building from Source

If you want to modify the frontend or contribute to development:

### Prerequisites
- Node.js 18+ and npm
- PHP 8.2+
- Composer

### Setup

```bash
# Clone the repository
git clone https://github.com/allanzico/laravel-helios.git
cd laravel-helios

# Install PHP dependencies
composer install

# Install frontend dependencies
cd ui
npm install

# Build the frontend
npm run build

# The built assets will be in the public/ directory
```

### Development

For frontend development with hot module replacement:

```bash
# Start the Vite dev server (from the ui directory)
cd ui
npm run dev

# In another terminal, start your Laravel app
php artisan serve
```

The Vite dev server runs on `http://localhost:5173` and proxies API requests to `http://localhost:8000`.

### Building for Production

```bash
cd ui
npm run build
```

This creates optimized production assets in the `public/` directory with:
- Minified JavaScript and CSS
- Content hashing for cache busting
- Vite manifest for dynamic asset loading