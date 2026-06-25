<?php

namespace Allanzico\LaravelHelios\Support;

use Illuminate\Support\Str;

class Redactor
{
    public function redact(mixed $value, ?string $key = null): mixed
    {
        if ($key !== null && $this->isSensitiveKey($key)) {
            return $this->redactedValue();
        }

        if (is_array($value)) {
            $redacted = [];

            foreach ($value as $childKey => $childValue) {
                $redacted[$childKey] = $this->redact($childValue, is_string($childKey) ? $childKey : null);
            }

            return $redacted;
        }

        if (is_string($value)) {
            return $this->redactString($value);
        }

        return $value;
    }

    public function queryBindings(array $bindings): array
    {
        if (! config('helios.security.store_query_bindings', false)) {
            return [];
        }

        return $this->redact($bindings);
    }

    public function logContent(string $content): string
    {
        return $this->redactString($content);
    }

    public function redactedValue(): string
    {
        return config('helios.security.redacted_value', '[REDACTED]');
    }

    protected function isSensitiveKey(string $key): bool
    {
        $normalizedKey = Str::lower($key);

        foreach (config('helios.security.redact_keys', []) as $pattern) {
            if (Str::is(Str::lower($pattern), $normalizedKey)) {
                return true;
            }
        }

        return false;
    }

    protected function redactString(string $value): string
    {
        $redacted = $this->redactedValue();

        $patterns = [
            '/(authorization:\s*bearer\s+)[^\s]+/i',
            '/((?:password|token|secret|api_key|apikey|client_secret)=)([^&\s]+)/i',
            '/("?(password|token|secret|api_key|apikey|client_secret)"?\s*[:=]\s*)"[^"]+"/i',
        ];

        foreach ($patterns as $pattern) {
            $replacement = str_contains($pattern, '"[^"]+"') ? '$1"'.$redacted.'"' : '$1'.$redacted;
            $value = preg_replace($pattern, $replacement, $value) ?? $value;
        }

        return $value;
    }
}
