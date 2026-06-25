<?php

namespace Allanzico\LaravelHelios\Services;

use Illuminate\Support\Facades\Auth;
use Allanzico\LaravelHelios\Models\HeliosError;
use Allanzico\LaravelHelios\Support\Redactor;
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
                    'message' => $this->redactor()->redact($exception->getMessage()),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'trace' => $this->formatStackTrace($exception),
                    'level' => $this->determineErrorLevel($exception),
                    'environment' => app()->environment(),
                    'url' => $this->request()?->url(),
                    'method' => $this->request()?->method(),
                    'request_data' => $this->getRequestData(),
                    'user_id' => config('helios.security.store_user_id', false) ? Auth::id() : null,
                    'ip_address' => config('helios.security.store_ip_address', false) ? $this->request()?->ip() : null,
                    'user_agent' => config('helios.security.store_user_agent', false) ? $this->request()?->userAgent() : null,
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
        $parts = [
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
        ];

        if (config('helios.error_tracking.group_by_line', false)) {
            $parts[] = $exception->getLine();
        }

        return hash('sha256', implode(':', $parts));
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
            'query' => $this->redactor()->redact($request->query()),
        ];

        if (config('helios.security.store_request_body', false)) {
            $data['body'] = $this->redactor()->redact($request->all());
        }

        if (config('helios.security.store_request_headers', false)) {
            $data['headers'] = $this->redactor()->redact($request->headers->all());
        }

        return json_encode($data);
    }

    protected function request(): ?\Illuminate\Http\Request
    {
        return app()->bound('request') ? request() : null;
    }

    protected function redactor(): Redactor
    {
        return app(Redactor::class);
    }
}
