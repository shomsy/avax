<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Security\Secrets;

use Avax\Components\Security\Secrets\System\PublicSurface\Secrets;
use PHPUnit\Framework\TestCase;

final class SecretsSecurityTest extends TestCase
{
    protected function tearDown(): void
    {
        Secrets::reset();
    }

    public function test_secret_survives_within_single_request(): void
    {
        Secrets::set('api_key', 'sk-12345');

        $this->assertTrue(Secrets::has('api_key'));
        $this->assertSame('sk-12345', Secrets::get('api_key'));
    }

    public function test_secret_is_cleared_after_reset(): void
    {
        Secrets::set('api_key', 'sk-12345');

        Secrets::reset();

        $this->assertFalse(Secrets::has('api_key'));
    }

    public function test_secrets_do_not_leak_between_simulated_requests(): void
    {
        Secrets::set('session_token', 'req-A-token');

        Secrets::reset();

        Secrets::set('session_token', 'req-B-token');

        $this->assertSame('req-B-token', Secrets::get('session_token'));
    }

    public function test_reset_clears_all_secrets(): void
    {
        Secrets::set('key_a', 'value-a');
        Secrets::set('key_b', 'value-b');

        Secrets::reset();

        $this->assertFalse(Secrets::has('key_a'));
        $this->assertFalse(Secrets::has('key_b'));
    }

    public function test_multiple_resets_keep_store_operational(): void
    {
        Secrets::set('key', 'value1');
        Secrets::reset();
        Secrets::set('key', 'value2');
        Secrets::reset();
        Secrets::set('key', 'value3');

        $this->assertSame('value3', Secrets::get('key'));
    }
}
