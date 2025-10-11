<?php

namespace Allanzico\LaravelHelios\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class HeliosRequest extends Model
{
    use HasUuids;

    protected $table = 'helios_requests';

    const UPDATED_AT = null;

    protected $fillable = [
        'id',
        'method',
        'uri',
        'controller_action',
        'status_code',
        'duration_ms',
        'memory_mb',
        'created_at',
    ];
}