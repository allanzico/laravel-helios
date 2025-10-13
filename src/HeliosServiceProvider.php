<?php

namespace Allanzico\LaravelHelios;

use Illuminate\Foundation\Http\Kernel;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Allanzico\LaravelHelios\Console\SyncTasks;
use Allanzico\LaravelHelios\Http\Middleware\TrackRequestPerformance;
use Allanzico\LaravelHelios\Providers\EventServiceProvider;

class HeliosServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(Kernel $kernel): void
    {
        // Load migrations automatically
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Allow users to publish the migrations for customization
        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations')
        ], 'helios-migrations');

        // Publish the config file
        $this->publishes([
            __DIR__.'/../config/helios.php' => config_path('helios.php'),
        ], 'helios-config');

        // Publish public assets (built frontend files)
        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/helios'),
        ], 'helios-assets');

        // Publish views
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/helios'),
        ], 'helios-views');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'helios');

        // Load public assets route (serve directly from package)
        $this->loadPackageAssets();

        $kernel->appendMiddlewareToGroup('web', TrackRequestPerformance::class);
        
        // Load the routes
        $this->registerRoutes();
    }

    /**
     * Register the package's routes.
     */
    protected function registerRoutes(): void
    {
        Route::middleware('api')
             ->prefix('helios/api')
             ->as('helios.api.')
             ->group(function () {
                 $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
             });

        Route::middleware('web')
             ->prefix('helios')
             ->as('helios.')
             ->group(function () {
                 $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
             });
    }

    /**
     * Load package assets - serve directly from package directory.
     */
    protected function loadPackageAssets(): void
    {
        Route::get('vendor/helios/assets/{file}', function ($file) {
            $path = __DIR__.'/../public/assets/'.$file;

            if (!file_exists($path)) {
                abort(404);
            }

            $mimeType = match (pathinfo($file, PATHINFO_EXTENSION)) {
                'js' => 'application/javascript',
                'css' => 'text/css',
                'json' => 'application/json',
                default => 'application/octet-stream',
            };

            return response()->file($path, [
                'Content-Type' => $mimeType,
                'Cache-Control' => 'public, max-age=31536000',
            ]);
        })->where('file', '.*')->name('helios.assets');
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/helios.php', 'helios'
        );

        $this->app->register(EventServiceProvider::class);
        
        // Register middleware
        $this->app->singleton(TrackRequestPerformance::class);
        
        // Register services
        $this->registerServices();
        
        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                SyncTasks::class,
            ]);
        }
    }

    /**
     * Register package services.
     */
    protected function registerServices(): void
    {
        // Register HealthCheckService as singleton
        $this->app->singleton(\Allanzico\LaravelHelios\Services\HealthCheckService::class, function ($app) {
            return new \Allanzico\LaravelHelios\Services\HealthCheckService();
        });

         // Register ErrorHandler as singleton
        $this->app->singleton(\Allanzico\LaravelHelios\Services\ErrorHandler::class, function ($app) {
            return new \Allanzico\LaravelHelios\Services\ErrorHandler();
        });
    }
}