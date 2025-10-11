<?php

namespace Allanzico\LaravelHelios\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ScoutHealthCheckSetting extends Model
{
    use HasUuids;

    protected $table = 'scout_health_check_settings';

    public $timestamps = false;

    protected $fillable = [
        'check_class',
        'enabled',
        'config',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'config' => 'json',
        ];
    }
}