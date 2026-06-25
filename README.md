# Laravel Helios

A lightweight, self-hosted monitoring tool for Laravel applications. Helios provides real-time monitoring of:

- Slow and failed requests
- Application Logs
- Queued Jobs
- Scheduled Tasks
- Slow Database Queries
- Health Checks
- Error Tracking

## Features

- **Real-time Monitoring**: Track your Laravel application's performance in real-time
- **Modern UI**: Beautiful React-based dashboard with TanStack Router and Query
- **Easy Installation**: Simple Composer installation with automatic service provider registration
- **Lightweight**: Minimal overhead on your application
- **Self-hosted**: All data stays in your database
- **Error Tracking**: Automatic error grouping and tracking with detailed stack traces
- **Queue Actions**: Retry or forget failed queue jobs from the dashboard
- **Scheduled Commands**: Run known scheduled tasks manually when enabled

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

### 3. Open Helios

**That's it!** Frontend assets are automatically inlined (similar to Laravel Horizon), so no asset publishing is required.

Helios will try to discover scheduled tasks from Laravel's schedule automatically. If your app only exposes scheduled tasks through a console kernel, you can still sync them manually:

```bash
php artisan helios:sync-tasks
```

> **Note:** If you're upgrading from an earlier version, clear your view cache: `php artisan view:clear`

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
- **Jobs**: Track execution history and retry or forget failed queue jobs
- **Queries**: View slow database queries with execution time and bindings
- **Scheduled Tasks**: Monitor cron jobs and run scheduled commands manually
- **Logs**: Browse and search application logs
- **Health Checks**: Configure and monitor application health checks
- **Errors**: Track and group application errors with detailed stack traces

## Local Playground

This repo includes a real Laravel playground app in `playground/` that installs Helios through a local Composer path repository.

```bash
bash scripts/playground.sh
```

Then open:

```text
http://127.0.0.1:8001
http://127.0.0.1:8001/helios
```

To generate sample requests, queries, logs, errors, and a failed queued job:

```bash
bash scripts/playground-demo.sh
```

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
    'enabled' => env('HELIOS_ENABLED', true),
    'path' => env('HELIOS_PATH', 'helios'),

    'middleware' => [
        'web',
        \Allanzico\LaravelHelios\Http\Middleware\Authorize::class,
    ],

    'allowed_environments' => ['local', 'testing'],
    'gate' => 'viewHelios',

    'log_path' => storage_path('logs'),

    'watchers' => [
        'requests' => [
            'enabled' => env('HELIOS_REQUESTS_ENABLED', true),
            'slow_ms' => (float) env('HELIOS_SLOW_REQUEST_MS', 1000),
            'sample_rate' => (float) env('HELIOS_REQUEST_SAMPLE_RATE', 0.05),
        ],
        'queries' => [
            'enabled' => env('HELIOS_QUERIES_ENABLED', true),
            'slow_ms' => (float) env('HELIOS_SLOW_QUERY_MS', 100),
            'sample_rate' => (float) env('HELIOS_QUERY_SAMPLE_RATE', 0.0),
        ],
    ],

    'retention_days' => (int) env('HELIOS_RETENTION_DAYS', 7),

    'error_tracking' => [
        'enabled' => env('HELIOS_ERROR_TRACKING_ENABLED', true),
    ],
];
```

### Production Access

In production, define a `viewHelios` gate in your application:

```php
use Illuminate\Support\Facades\Gate;

Gate::define('viewHelios', fn ($user = null) => $user?->email === 'you@example.com');
```

### Environment Variables

Add to your `.env` file:

```env
# Enable/disable all of Helios
HELIOS_ENABLED=true

# Move the dashboard path
HELIOS_PATH=helios

# Tune collection
HELIOS_SLOW_REQUEST_MS=1000
HELIOS_SLOW_QUERY_MS=100
HELIOS_REQUEST_SAMPLE_RATE=0.05
HELIOS_QUERY_SAMPLE_RATE=0

# Enable/disable error tracking
HELIOS_ERROR_TRACKING_ENABLED=true
```

## Error Tracking

Automatic error tracking is registered by the package service provider. You do not need to replace your application's exception handler.

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

Or use the built-in prune command:

```bash
php artisan helios:prune
php artisan helios:prune --days=14
```

## Troubleshooting

### "Unable to locate file in Vite manifest" Error

If you see an error like:
```
Illuminate\Foundation\ViteException - Internal Server Error
Unable to locate file in Vite manifest: packages/helios/helios/ui/src/main.tsx
```

This error occurs when Laravel's Vite helper conflicts with Helios's custom asset loading. **Helios uses inline assets (like Laravel Horizon) and does not use Laravel's Vite system.**

**Solution:**

1. **Clear Laravel caches:**
   ```bash
   php artisan view:clear
   php artisan config:clear
   php artisan cache:clear
   ```

2. **Clear browser cache:**
   - Hard refresh your browser (Ctrl+F5 or Cmd+Shift+R)
   - Or clear browser cache completely

3. **Verify the package is properly installed:**
   ```bash
   composer dump-autoload
   ```

4. **If using Laravel Herd or Valet, restart the service:**
   ```bash
   # For Herd
   herd restart

   # For Valet
   valet restart
   ```

5. **Check your application's `vite.config.js`:**
   Make sure your application's Vite configuration is not scanning the vendor directory. Your config should NOT include paths like `vendor/**/*.blade.php`.

### Assets Not Loading

If the Helios dashboard appears blank or unstyled:

1. Verify migrations have run:
   ```bash
   php artisan migrate
   ```

2. Clear view cache:
   ```bash
   php artisan view:clear
   ```

3. Check file permissions on the vendor directory:
   ```bash
   chmod -R 755 vendor/allanzico/laravel-helios
   ```

### Database Connection Errors

If you see database-related errors:

1. Ensure migrations have run:
   ```bash
   php artisan migrate
   ```

2. Verify your database connection is configured correctly in `.env`

3. Check that your database user has permissions to create tables

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
