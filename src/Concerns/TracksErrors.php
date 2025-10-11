<?php

namespace Allanzico\LaravelHelios\Concerns;

use Allanzico\LaravelHelios\Services\ErrorHandler;
use Throwable;

trait TracksErrors
{
    /**
     * Track errors in Scout before reporting them.
     */
    protected function trackInScout(Throwable $exception): void
    {
        if ($this->shouldReport($exception) && config('scout.error_tracking.enabled', true)) {
            try {
                app(ErrorHandler::class)->report($exception);
            } catch (\Throwable $e) {
                // Silently fail - don't let error tracking break the app
                logger()->error('Scout error tracking failed: ' . $e->getMessage());
            }
        }
    }

    /**
     * Override the report method to include Scout tracking.
     * Call this from your Exception Handler's report method.
     */
    public function reportWithScout(Throwable $exception): void
    {
        $this->trackInScout($exception);
        parent::report($exception);
    }
}