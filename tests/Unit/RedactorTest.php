<?php

namespace Allanzico\LaravelHelios\Tests\Unit;

use Allanzico\LaravelHelios\Support\Redactor;
use Allanzico\LaravelHelios\Tests\TestCase;

class RedactorTest extends TestCase
{
    public function test_redacts_sensitive_keys_recursively(): void
    {
        $redacted = app(Redactor::class)->redact([
            'email' => 'person@example.com',
            'password' => 'secret',
            'nested' => [
                'api_token' => 'token-value',
                'safe' => 'visible',
            ],
        ]);

        $this->assertSame('person@example.com', $redacted['email']);
        $this->assertSame('[REDACTED]', $redacted['password']);
        $this->assertSame('[REDACTED]', $redacted['nested']['api_token']);
        $this->assertSame('visible', $redacted['nested']['safe']);
    }

    public function test_query_bindings_are_empty_until_explicitly_enabled(): void
    {
        config()->set('helios.security.store_query_bindings', false);

        $this->assertSame([], app(Redactor::class)->queryBindings(['secret']));

        config()->set('helios.security.store_query_bindings', true);

        $this->assertSame(['secret'], app(Redactor::class)->queryBindings(['secret']));
    }
}
