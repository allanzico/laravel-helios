<?php

namespace Allanzico\LaravelHelios\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ScoutJob extends Model
{
    use HasUuids;

    protected $table = 'scout_jobs';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'name',
        'status',
        'payload',
        'exception',
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'json',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }
}