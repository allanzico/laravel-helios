<?php

namespace Allanzico\LaravelHelios\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class HeliosJob extends Model
{
    use HasUuids;

    protected $table = 'helios_jobs';

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

    protected $appends = [
        'can_retry',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'json',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function getCanRetryAttribute(): bool
    {
        return $this->status === 'failed';
    }
}
