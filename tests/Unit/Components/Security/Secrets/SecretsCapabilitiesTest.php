<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Security\Secrets;

use Avax\Components\Security\Secrets\System\Capabilities\Stores\InMemorySecretStore;
use Avax\Components\Security\Secrets\System\Flows\RedactSecret\RedactSecret;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class SecretsCapabilitiesTest extends TestCase
{
    // --- InMemorySecretStore ---

    public function test_it_stores_and_retrieves_secret() : void
    {
        $store = new InMemorySecretStore();
        $store->set('db_password', 'my-secret');
        $this->assertSame('my-secret', $store->get('db_password'));
    }

    public function test_it_throws_when_secret_not_found() : void
    {
        $store = new InMemorySecretStore();
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('missing_key');
        $store->get('missing_key');
    }

    public function test_it_checks_secret_existence() : void
    {
        $store = new InMemorySecretStore();
        $this->assertFalse($store->has('key'));
        $store->set('key', 'value');
        $this->assertTrue($store->has('key'));
    }

    public function test_it_forgets_secret() : void
    {
        $store = new InMemorySecretStore();
        $store->set('key', 'value');
        $store->forget('key');
        $this->assertFalse($store->has('key'));
    }

    public function test_it_overwrites_secret_value() : void
    {
        $store = new InMemorySecretStore();
        $store->set('key', 'old');
        $store->set('key', 'new');
        $this->assertSame('new', $store->get('key'));
    }

    // --- RedactSecret ---

    public function test_it_fully_redacts_short_values() : void
    {
        $redact = new RedactSecret();
        $this->assertSame('****', $redact->redact('abc'));
        $this->assertSame('****', $redact->redact('abcd'));
    }

    public function test_it_partially_redacts_long_values() : void
    {
        $redact = new RedactSecret();
        $result = $redact->redact('my-secret-key');
        $this->assertStringStartsWith('my', $result);
        $this->assertStringEndsWith('ey', $result);
        $this->assertStringContainsString('*', $result);
    }

    public function test_it_preserves_first_and_last_two_characters() : void
    {
        $redact = new RedactSecret();
        $result = $redact->redact('abcdefgh');
        $this->assertSame('ab****gh', $result);
    }
}
