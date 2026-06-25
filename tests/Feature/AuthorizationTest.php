<?php

namespace Allanzico\LaravelHelios\Tests\Feature;

use Allanzico\LaravelHelios\Tests\TestCase;
use Illuminate\Support\Facades\Gate;

class AuthorizationTest extends TestCase
{
    public function test_dashboard_is_blocked_when_environment_is_not_allowed_and_gate_is_missing(): void
    {
        config()->set('helios.allowed_environments', []);

        $this->get('/helios')->assertForbidden();
    }

    public function test_dashboard_allows_explicit_view_gate(): void
    {
        config()->set('helios.allowed_environments', []);

        Gate::define('viewHelios', fn () => true);

        $this->get('/helios')->assertOk();
    }
}
