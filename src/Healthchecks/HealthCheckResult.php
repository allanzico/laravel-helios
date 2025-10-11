<?php

namespace Allanzico\LaravelHelios\HealthChecks;

use Allanzico\LaravelHelios\Enums\HealthStatus;

class HealthCheckResult
{
    public function __construct(
        public string $check,
        public string $label,
        public HealthStatus $status,
        public string $message = '',
        public ?string $shortSummary = null,
        public array $meta = []
    ) {}

    public function toArray(): array
    {
        return [
            'check' => $this->check,
            'label' => $this->label,
            'status' => $this->status->value,
            'message' => $this->message,
            'short_summary' => $this->shortSummary,
            'meta' => $this->meta,
        ];
    }
}