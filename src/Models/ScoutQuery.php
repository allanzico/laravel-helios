<?php

namespace Allanzico\LaravelHelios\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ScoutQuery extends Model
{
    use HasUuids;

    protected $table = 'scout_queries';

    const UPDATED_AT = null;

    protected $fillable = [
        'id',
        'connection_name',
        'sql',
        'bindings',
        'time_ms',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'bindings' => 'json',
            'time_ms' => 'float',
        ];
    }
}