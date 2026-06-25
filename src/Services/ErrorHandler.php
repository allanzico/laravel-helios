<?php

namespace Allanzico\LaravelHelios\Services;

use Illuminate\Support\Facades\Auth;
use Allanzico\LaravelHelios\Models\HeliosError;
use Throwable;

class ErrorHandler
{
    public function report(Throwable $exception, ?array $context = null): void
    {
        if (! config('helios.watchers.errors.enabled', config('helios.error_tracking.enabled', true))) {
            return;
        }

        try {
            // Generate a unique hash for grouping similar errors
            $hash = $this->generateErrorHash($exception);

            // Check if this error already exists
            $existingError = HeliosError::where('hash', $hash)->first();

            if ($existingError) {
                // Update existing error
                $existingError->increment('occurrences');
                $existingError->update([
                    'last_seen_at' => now(),
                    'request_data' => $this->getRequestData(),
                ]);
            } else {
                // Create new error record
                HeliosError::create([
                    'hash' => $hash,
                    'type' => get_class($exception),
                    'message' => $exception->getMessage(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'trace' => $this->formatStackTrace($exception),
                    'level' => $this->determineErrorLevel($exception),
                    'environment' => app()->environment(),
                    'url' => $this->request()?->fullUrl(),
                    'method' => $this->request()?->method(),
                    'request_data' => $this->getRequestData(),
                    'user_id' => Auth::id(),
                    'ip_address' => $this->request()?->ip(),
                    'user_agent' => $this->request()?->userAgent(),
                    'first_seen_at' => now(),
                    'last_seen_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            // Don't let error tracking cause issues
            // Silently fail or log to Laravel's default logger
            logger()->error('Helios error tracking failed: ' . $e->getMessage());
        }
    }

    protected function generateErrorHash(Throwable $exception): string
    {
        // Create a hash based on exception type, message, file, and line
        // This groups similar errors together
        $key = sprintf(
            '%s:%s:%s:%d',
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        );

        return hash('sha256', $key);
    }

    protected function formatStackTrace(Throwable $exception): string
    {
        $trace = collect($exception->getTrace())
            ->map(function ($frame, $index) {
                return sprintf(
                    "#%d %s(%d): %s%s%s()",
                    $index,
                    $frame['file'] ?? '[internal function]',
                    $frame['line'] ?? 0,
                    $frame['class'] ?? '',
                    $frame['type'] ?? '',
                    $frame['function'] ?? ''
                );
            })
            ->take(20) // Limit to top 20 frames
            ->implode("\n");

        return $trace;
    }

    protected function determineErrorLevel(Throwable $exception): string
    {
        // You can customize this based on exception types
        $criticalExceptions = [
            \Illuminate\Database\QueryException::class,
            \Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException::class,
        ];

        if (in_array(get_class($exception), $criticalExceptions)) {
            return 'critical';
        }

        return 'error';
    }

    protected function getRequestData(): string
    {
        $request = $this->request();

        if (! $request) {
            return json_encode([]);
        }

        $data = [
            'query' => $request->query(),
            'body' => $request->except([
                'password',
                'password_confirmation',
                'current_password',
                'token',
                'api_token',
                'access_token',
                'refresh_token',
                'secret',
            ]),
            'headers' => $this->sanitizeHeaders($request->headers->all()),
        ];

        return json_encode($data);
    }

    protected function sanitizeHeaders(array $headers): array
    {
        $sensitiveHeaders = ['authorization', 'cookie', 'x-csrf-token'];

        foreach ($sensitiveHeaders as $header) {
            if (isset($headers[$header])) {
                $headers[$header] = ['***REDACTED***'];
            }
        }

        return $headers;
    }

    protected function request(): ?\Illuminate\Http\Request
    {
        return app()->bound('request') ? request() : null;
    }
}
