# Changelog

All notable changes to `laravel-helios` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 2025-10-11

### Added
- Initial release of Laravel Helios
- Real-time request performance monitoring
- Application logs tracking and viewing
- Queued jobs monitoring with status tracking
- Scheduled tasks monitoring
- Database query tracking and analysis
- Health checks system with multiple check types:
  - Database connectivity check
  - Redis connectivity check
  - Disk space check
  - Queue health check
  - Environment check
  - Application health check
- Error tracking and reporting with stack traces
- Modern React-based dashboard UI
- Automatic service provider registration for Laravel
- Database migrations for all monitoring tables
- Middleware for automatic request tracking
- API endpoints for all monitoring features
- Command for syncing scheduled tasks

### Features
- PSR-4 autoloading
- Laravel 11.x and 12.x support
- PHP 8.2+ requirement
- Self-hosted solution - all data stays in your database
- Configurable monitoring settings
- Publishable migrations and configuration

[Unreleased]: https://github.com/allanzico/laravel-helios/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/allanzico/laravel-helios/releases/tag/v1.0.0
