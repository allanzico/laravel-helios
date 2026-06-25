<?php

namespace Allanzico\LaravelHelios\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class HeliosScheduledTask extends Model
{
    use HasUuids;

    protected $table = 'helios_scheduled_tasks';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'command',
        'expression',
        'status',
        'output',
        'started_at',
        'finished_at',
        'runtime_ms',
        'triggered_by'
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'runtime_ms' => 'float',
        ];
    }
}
