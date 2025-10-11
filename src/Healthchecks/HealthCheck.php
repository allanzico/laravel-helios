<?php

namespace Allanzico\LaravelHelios\HealthChecks;

use Allanzico\LaravelHelios\Enums\HealthStatus;

abstract class HealthCheck
{
    protected string $name;
    protected string $label;
    protected ?string $message = null;
    protected ?string $shortSummary = null;
    protected array $meta = [];
    protected HealthStatus $status = HealthStatus::OK;

    abstract public function run(): HealthCheckResult;

    public function name(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function label(string $label): static
    {
        $this->label = $label;
        return $this;
    }

    public function getName(): string
    {
        return $this->name ?? class_basename($this);
    }

    public function getLabel(): string
    {
        return $this->label ?? $this->getName();
    }

    protected function ok(string $message = ''): HealthCheckResult
    {
        return new HealthCheckResult(
            check: $this->getName(),
            label: $this->getLabel(),
            status: HealthStatus::OK,
            message: $message,
            shortSummary: $this->shortSummary,
            meta: $this->meta
        );
    }

    protected function warning(string $message): HealthCheckResult
    {
        return new HealthCheckResult(
            check: $this->getName(),
            label: $this->getLabel(),
            status: HealthStatus::WARNING,
            message: $message,
            shortSummary: $this->shortSummary,
            meta: $this->meta
        );
    }

    protected function failed(string $message): HealthCheckResult
    {
        return new HealthCheckResult(
            check: $this->getName(),
            label: $this->getLabel(),
            status: HealthStatus::FAILED,
            message: $message,
            shortSummary: $this->shortSummary,
            meta: $this->meta
        );
    }

    protected function crashed(string $message, \Throwable $exception): HealthCheckResult
    {
        return new HealthCheckResult(
            check: $this->getName(),
            label: $this->getLabel(),
            status: HealthStatus::CRASHED,
            message: $message,
            shortSummary: $this->shortSummary,
            meta: array_merge($this->meta, [
                'exception' => get_class($exception),
                'exception_message' => $exception->getMessage(),
            ])
        );
    }
}