<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Logging;

use Avax\Components\Security\Redaction\System\PublicSurface\Redaction;
use PHPUnit\Framework\TestCase;

final class LoggingCapabilitiesTest extends TestCase
{
    // --- Security/Redaction key-based redaction ---

    public function test_it_redacts_password_key_value() : void
    {
        $result = Redaction::redactLog(
            logData      : ['password' => 'my-secret-pass'],
            sensitiveKeys: ['password', 'secret', 'token'],
        );
        $this->assertSame('***', $result['password']);
    }

    public function test_it_redacts_api_key_value() : void
    {
        $result = Redaction::redactLog(
            logData      : ['api_key' => 'sk-live-abcdef123456'],
            sensitiveKeys: ['api_key', 'secret', 'token'],
        );
        $this->assertSame('***', $result['api_key']);
    }

    public function test_it_preserves_non_sensitive_keys() : void
    {
        $result = Redaction::redactLog(
            logData      : ['username' => 'john', 'email' => 'john@example.com'],
            sensitiveKeys: ['password', 'secret', 'token'],
        );
        $this->assertSame('john', $result['username']);
        $this->assertSame('john@example.com', $result['email']);
    }

    public function test_it_redacts_nested_sensitive_keys() : void
    {
        $result = Redaction::redactLog(
            logData      : [
                               'config' => [
                                   'database' => [
                                       'db_password' => 'root123',
                                       'host'        => 'localhost',
                                   ],
                               ],
                           ],
            sensitiveKeys: ['password', 'db_password', 'secret'],
        );
        $this->assertSame('***', $result['config']['database']['db_password']);
        $this->assertSame('localhost', $result['config']['database']['host']);
    }

    public function test_it_applies_policy_with_pattern_detection() : void
    {
        $result = Redaction::applyPolicy(data: [
                                                   'password' => 'secret123',
                                                   'token'    => 'abc-token-xyz',
                                                   'safe'     => 'hello',
                                               ]);
        $this->assertTrue($result['was_redacted']);
        $this->assertSame('***', $result['redacted']['password']);
        $this->assertSame('***', $result['redacted']['token']);
        $this->assertSame('hello', $result['redacted']['safe']);
    }

    public function test_it_classifies_sensitive_data() : void
    {
        $classifications = Redaction::classify(data: 'test@example.com');
        $this->assertNotEmpty($classifications);
        $this->assertSame('email', $classifications[0]['type']);
    }

    public function test_it_detects_sensitive_strings() : void
    {
        $this->assertTrue(Redaction::isSensitive(data: 'test@example.com'));
        $this->assertTrue(Redaction::isSensitive(data: '123-45-6789'));
        $this->assertFalse(Redaction::isSensitive(data: 'hello world'));
    }

    public function test_it_accepts_empty_sensitive_keys() : void
    {
        $result = Redaction::redactLog(
            logData      : ['username' => 'john', 'password' => 'secret'],
            sensitiveKeys: [],
        );
        // Without keys, only policy-level patterns apply
        $this->assertSame('john', $result['username']);
    }
}
