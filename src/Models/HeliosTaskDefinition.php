<?php

namespace Allanzico\LaravelHelios\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class HeliosTaskDefinition extends Model
{
    use HasUuids;
    protected $table = 'helios_task_definitions';
    protected $guarded = [];
}