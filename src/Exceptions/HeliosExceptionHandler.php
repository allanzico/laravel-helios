<?php

namespace Allanzico\LaravelHelios\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Allanzico\LaravelHelios\Services\ErrorHandler;
use Throwable;

class HeliosExceptionHandler extends ExceptionHandler
{
    /**
     * Report or log an exception.
     */
    public function report(Throwable $exception): void
    {
        // Call parent to maintain Laravel's default error handling
        parent::report($exception);

        // Track the error in Helios if it's reportable
        if ($this->shouldReport($exception) && config('helios.error_tracking.enabled', true)) {
            app(ErrorHandler::class)->report($exception);
        }
    }
}