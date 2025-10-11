# Laravel Helios

A lightweight, self-hosted monitoring tool for Laravel applications. Helios provides real-time monitoring of:

- Request Performance
- Application Logs
- Queued Jobs
- Scheduled Tasks
- Database Queries
- Health Checks

## Features

- **Real-time Monitoring**: Track your Laravel application's performance in real-time
- **Modern UI**: Beautiful React-based dashboard with TanStack Router and Query
- **Easy Installation**: Simple Composer installation with automatic service provider registration
- **Lightweight**: Minimal overhead on your application
- **Self-hosted**: All data stays in your database

## Requirements

- PHP 8.2 or higher
- Laravel 11.0 or higher
- MySQL/PostgreSQL database

## Installation

### 1. Install via Composer

```bash
composer require allanzico/laravel-helios
```

The service provider will be automatically registered.

### 2. Run Migrations

```bash
php artisan migrate
```

### 3. Publish Assets

```bash
php artisan vendor:publish --tag=helios-assets --force
```

### 4. Sync Scheduled Tasks

After installation, sync your scheduled tasks:

```bash
php artisan helios:sync-tasks
```

## Usage

Once installed, access the Helios dashboard at:

```
http://localhost/helios
```

## Configuration

Optionally publish the configuration file:

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