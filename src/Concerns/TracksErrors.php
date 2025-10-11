<?php

namespace Allanzico\LaravelHelios\Concerns;

use Allanzico\LaravelHelios\Services\ErrorHandler;
use Throwable;

trait TracksErrors
{
    /**
     * Track errors in Helios before reporting them.
     */
    protected function trackInHelios(Throwable $exception): void
    {
        if ($this->shouldReport($exception) && config('helios.error_tracking.enabled', true)) {
            try {
                app(ErrorHandler::class)->report($exception);
            } catch (\Throwable $e) {
                // Silently fail - don't let error tracking break the app
                logger()->error('Helios error tracking failed: ' . $e->getMessage());
            }
        }
    }

    /**
     * Override the report method to include Helios tracking.
     * Call this from your Exception Handler's report method.
     */
    public function reportWithHelios(Throwable $exception): void
    {
        $this->trackInHelios($exception);
        parent::report($exception);
    }
}