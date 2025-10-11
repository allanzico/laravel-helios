<?php

namespace Allanzico\LaravelHelios\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ScoutTaskDefinition extends Model
{
    use HasUuids;
    protected $table = 'scout_task_definitions';
    protected $guarded = [];
}