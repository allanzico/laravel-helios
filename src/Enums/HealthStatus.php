<?php

namespace Allanzico\LaravelHelios\Enums;

enum HealthStatus: string
{
    case OK = 'ok';
    case WARNING = 'warning';
    case FAILED = 'failed';
    case CRASHED = 'crashed';
    case SKIPPED = 'skipped';
}