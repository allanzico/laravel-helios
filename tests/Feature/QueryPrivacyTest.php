<?php

namespace Allanzico\LaravelHelios\Tests\Feature;

use Allanzico\LaravelHelios\Models\HeliosQuery;
use Allanzico\LaravelHelios\Tests\TestCase;
use Illuminate\Support\Facades\DB;

class QueryPrivacyTest extends TestCase
{
    public function test_query_bindings_are_not_stored_by_default(): void
    {
        config()->set('helios.watchers.queries.enabled', true);
        config()->set('helios.watchers.queries.slow_ms', 0);
        config()->set('helios.watchers.queries.sample_rate', 1);
        config()->set('helios.security.store_query_bindings', false);

        DB::select('select ? as secret_value', ['super-secret-token']);

        $query = HeliosQuery::query()->latest('created_at')->first();

        $this->assertNotNull($query);
        $this->assertSame([], $query->bindings);
    }
}
