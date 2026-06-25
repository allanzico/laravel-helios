<?php

namespace Allanzico\LaravelHelios\Tests\Feature;

use Allanzico\LaravelHelios\Models\HeliosTaskDefinition;
use Allanzico\LaravelHelios\Tests\TestCase;

class ScheduledTaskSecurityTest extends TestCase
{
    public function test_manual_scheduled_run_requires_allowlist(): void
    {
        config()->set('helios.actions.run_scheduled_tasks', true);
        config()->set('helios.watchers.schedule.allow_manual_runs', true);
        config()->set('helios.watchers.schedule.manual_allowlist', []);

        HeliosTaskDefinition::create([
            'command' => "'php' 'artisan' reports:daily",
            'expression' => '* * * * *',
            'description' => 'Daily reports',
        ]);

        $this->getJson('/helios/api/scheduled-tasks')
            ->assertOk()
            ->assertJsonPath('tasks.0.can_run', false);

        $this->postJson('/helios/api/scheduled-tasks/run', ['signature' => 'reports:daily'])
            ->assertForbidden();
    }

    public function test_allowlisted_scheduled_task_reports_as_runnable(): void
    {
        config()->set('helios.actions.run_scheduled_tasks', true);
        config()->set('helios.watchers.schedule.allow_manual_runs', true);
        config()->set('helios.watchers.schedule.manual_allowlist', ['reports:*']);

        HeliosTaskDefinition::create([
            'command' => "'php' 'artisan' reports:daily",
            'expression' => '* * * * *',
            'description' => 'Daily reports',
        ]);

        $this->getJson('/helios/api/scheduled-tasks')
            ->assertOk()
            ->assertJsonPath('tasks.0.can_run', true);
    }
}
