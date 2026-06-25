<?php

namespace Allanzico\LaravelHelios\Models;

use Allanzico\LaravelHelios\Services\QueueActionService;
use Allanzico\LaravelHelios\Support\ActionAuthorizer;
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
        'can_forget',
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
        return $this->status === 'failed'
            && app(ActionAuthorizer::class)->allowed('retry_job', 'retry_jobs')
            && app(QueueActionService::class)->supportsActions();
    }

    public function getCanForgetAttribute(): bool
    {
        return $this->status === 'failed'
            && app(ActionAuthorizer::class)->allowed('forget_job', 'forget_jobs')
            && app(QueueActionService::class)->supportsActions();
    }
}
