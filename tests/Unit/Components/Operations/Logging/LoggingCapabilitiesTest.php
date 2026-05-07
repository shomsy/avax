<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Logging;

use Avax\Components\Operations\Logging\System\Capabilities\Redaction\SecretRedactor;
use PHPUnit\Framework\TestCase;

final class LoggingCapabilitiesTest extends TestCase
{
    // --- SecretRedactor key-based redaction ---

    public function test_it_redacts_password_key_value() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactArray(['password' => 'my-secret-pass']);
        $this->assertStringContainsString('REDACTED', $result['password']);
        $this->assertStringNotContainsString('my-secret-pass', $result['password']);
    }

    public function test_it_redacts_api_key_value() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactArray(['api_key' => 'sk-live-abcdef123456']);
        $this->assertStringContainsString('REDACTED', $result['api_key']);
    }

    public function test_it_preserves_non_sensitive_keys() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactArray(['username' => 'john', 'email' => 'john@example.com']);
        $this->assertSame('john', $result['username']);
        $this->assertSame('john@example.com', $result['email']);
    }

    public function test_it_redacts_nested_sensitive_keys() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactArray([
                                               'config' => [
                                                   'database' => [
                                                       'db_password' => 'root123',
                                                       'host'        => 'localhost',
                                                   ],
                                               ],
                                           ]);
        $this->assertStringContainsString('REDACTED', $result['config']['database']['db_password']);
        $this->assertSame('localhost', $result['config']['database']['host']);
    }

    // --- SecretRedactor string pattern detection ---

    public function test_it_redacts_bearer_token_in_string() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactString('Authorization: Bearer eyJhbGciOiJIUzI1NiJ9.eyJzdWIiOiIxMjM0NTY3ODkwIn0.dozjgNryP4J3jVmNHl0w5N_XgL0n3I9PlFUP0THsR8U');
        $this->assertStringContainsString('REDACTED', $result);
        $this->assertStringNotContainsString('eyJhbGciOiJIUzI1NiJ9', $result);
    }

    public function test_it_redacts_ssn_pattern_in_string() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactString('SSN is 123-45-6789 for this user');
        $this->assertStringContainsString('REDACTED', $result);
        $this->assertStringNotContainsString('123-45-6789', $result);
    }

    public function test_it_does_not_redact_emails_by_default() : void
    {
        $redactor = new SecretRedactor();
        $result   = $redactor->redactString('Contact: john@example.com');
        $this->assertStringContainsString('john@example.com', $result);
    }

    public function test_it_redacts_emails_when_configured() : void
    {
        $redactor = new SecretRedactor(redactEmails: true);
        $result   = $redactor->redactString('Contact: john@example.com');
        $this->assertStringContainsString('REDACTED', $result);
        $this->assertStringNotContainsString('john@example.com', $result);
    }

    public function test_it_uses_custom_redaction_mask() : void
    {
        $redactor = new SecretRedactor(redactionMask: '***HIDDEN***');
        $result   = $redactor->redactArray(['password' => 'secret']);
        $this->assertStringContainsString('HIDDEN', $result['password']);
    }

    public function test_it_redacts_additional_custom_keys() : void
    {
        $redactor = new SecretRedactor(additionalSensitiveKeys: ['custom_field']);
        $result   = $redactor->redactArray(['custom_field' => 'sensitive-value']);
        $this->assertStringContainsString('REDACTED', $result['custom_field']);
    }
}
