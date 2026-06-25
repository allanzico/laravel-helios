<?php

namespace Allanzico\LaravelHelios\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class HeliosAction extends Model
{
    use HasUuids;

    protected $table = 'helios_actions';

    const UPDATED_AT = null;

    protected $fillable = [
        'id',
        'action',
        'target_type',
        'target_id',
        'status',
        'actor_id',
        'ip_address',
        'user_agent',
        'metadata',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }
}
