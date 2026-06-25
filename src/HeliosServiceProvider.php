<?php

namespace Allanzico\LaravelHelios;

use Illuminate\Foundation\Http\Kernel;
use Illuminate\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Allanzico\LaravelHelios\Console\Prune;
use Allanzico\LaravelHelios\Console\SyncTasks;
use Allanzico\LaravelHelios\Http\Middleware\Authorize;
use Allanzico\LaravelHelios\Http\Middleware\TrackRequestPerformance;
use Allanzico\LaravelHelios\Providers\EventServiceProvider;
use Allanzico\LaravelHelios\Services\ErrorHandler;
use Throwable;

class HeliosServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(Kernel $kernel): void
    {
        if (! config('helios.enabled', true)) {
            return;
        }

        // Register Blade directive for Helios assets
        $this->registerBladeDirectives();

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

        // Publish views (optional, for customization)
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/helios'),
        ], 'helios-views');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'helios');

        if (config('helios.watchers.requests.enabled', true)) {
            $kernel->appendMiddlewareToGroup('web', TrackRequestPerformance::class);
        }
        
        // Load the routes
        $this->registerRoutes();
    }

    /**
     * Register the package's routes.
     */
    protected function registerRoutes(): void
    {
        $path = trim(config('helios.path', 'helios'), '/');
        $middleware = config('helios.middleware', ['web', Authorize::class]);

        Route::domain(config('helios.domain'))
             ->middleware($middleware)
             ->prefix("{$path}/api")
             ->as('helios.api.')
             ->group(function () {
                 $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
             });

        Route::domain(config('helios.domain'))
             ->middleware($middleware)
             ->prefix($path)
             ->as('helios.')
             ->group(function () {
                 $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
             });
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/helios.php', 'helios'
        );

        if (config('helios.enabled', true)) {
            $this->app->register(EventServiceProvider::class);
        }
        
        // Register middleware
        $this->app->singleton(TrackRequestPerformance::class);
        $this->app->singleton(Authorize::class);
        
        // Register services
        $this->registerServices();
        $this->registerExceptionTracking();
        
        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                Prune::class,
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

    protected function registerExceptionTracking(): void
    {
        if (! config('helios.watchers.errors.enabled', config('helios.error_tracking.enabled', true))) {
            return;
        }

        $this->app->afterResolving(ExceptionHandlerContract::class, function ($handler) {
            if (! method_exists($handler, 'reportable')) {
                return;
            }

            $handler->reportable(function (Throwable $exception): void {
                app(ErrorHandler::class)->report($exception);
            });
        });
    }

    /**
     * Register Blade directives.
     */
    protected function registerBladeDirectives(): void
    {
        Blade::directive('heliosAssets', function () {
            return "<?php echo \Allanzico\LaravelHelios\Support\Vite::assets(); ?>";
        });
    }
}
