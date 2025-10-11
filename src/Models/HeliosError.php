<?php

namespace Allanzico\LaravelHelios\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class HeliosError extends Model
{
    use HasUuids;

    protected $table = 'helios_errors';

    public $timestamps = false;

    protected $fillable = [
        'hash',
        'type',
        'message',
        'file',
        'line',
        'trace',
        'level',
        'environment',
        'url',
        'method',
        'request_data',
        'user_id',
        'ip_address',
        'user_agent',
        'occurrences',
        'first_seen_at',
        'last_seen_at',
        'status',
        'resolved_at',
        'resolved_by',
    ];

    protected function casts(): array
    {
        return [
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'resolved_at' => 'datetime',
            'occurrences' => 'integer',
        ];
    }

    public function markAsResolved(?string $resolvedBy = null): void
    {
        $this->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'resolved_by' => $resolvedBy,
        ]);
    }

    public function markAsIgnored(): void
    {
        $this->update(['status' => 'ignored']);
    }

    public function markAsUnresolved(): void
    {
        $this->update([
            'status' => 'unresolved',
            'resolved_at' => null,
            'resolved_by' => null,
        ]);
    }
}