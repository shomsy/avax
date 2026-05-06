<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Logging\System\Capabilities\Redaction;

/**
 * Secret redactor that detects and redacts sensitive data from error contexts.
 *
 * Detects and redacts:
 * - Passwords (keys matching password patterns)
 * - Tokens (Bearer tokens, API keys, JWT tokens)
 * - Credit card numbers (via Luhn-check compatible patterns)
 * - Social Security Numbers (SSNs)
 * - Email addresses (optional)
 * - Custom configurable patterns
 */
final readonly class SecretRedactor
{
    /**
     * Keys that indicate sensitive values and should have their values redacted.
     */
    private const array SENSITIVE_KEYS
        = [
            'password',
            'passwd',
            'pass',
            'pwd',
            'secret',
            'secret_key',
            'api_key',
            'apikey',
            'api-key',
            'access_token',
            'access-token',
            'refresh_token',
            'refresh-token',
            'auth_token',
            'auth-token',
            'authorization',
            'token',
            'bearer',
            'jwt',
            'private_key',
            'private-key',
            'credit_card',
            'creditcard',
            'card_number',
            'cardnumber',
            'cvv',
            'cvc',
            'card_cvv',
            'ssn',
            'social_security',
            'social-security-number',
            'database_url',
            'database-password',
            'db_password',
            'db-pass',
            'encryption_key',
            'encryption-key',
        ];

    /**
     * The replacement string for redacted values.
     */
    private const string REDACTED = '***REDACTED***';

    /**
     * Additional sensitive key patterns (case-insensitive partial matches).
     */
    private const array SENSITIVE_KEY_PATTERNS
        = [
            'password',
            'secret',
            'token',
            'api_key',
            'private_key',
            'auth',
            'credential',
        ];

    /**
     * Regex patterns for detecting secrets in string values.
     *
     * @var array<string, string> Pattern name => regex
     */
    private const array STRING_PATTERNS
        = [
            'bearer_token' => '/Bearer\s+[A-Za-z0-9\-_\.]+\s*/i',
            'api_key_header' => '/(?:x-api-key|api-key)\s*[:=]\s*[A-Za-z0-9\-_\.]+\s*/i',
            'authorization_header' => '/Authorization\s*[:=]\s*[A-Za-z0-9\-_\.]+\s*/i',
            'jwt_token' => '/eyJ[A-Za-z0-9\-_]+\.eyJ[A-Za-z0-9\-_]+\.[A-Za-z0-9\-_]+/',
            'aws_access_key' => '/AKIA[0-9A-Z]{16}/',
            'aws_secret_key' => '/(?<![A-Za-z0-9\/+])[A-Za-z0-9\/+=]{40}(?![A-Za-z0-9\/+=])/',
            'credit_card' => '/\b(?:\d{4}[\s\-]?){3}\d{4}\b/',
            'ssn' => '/\b\d{3}-\d{2}-\d{4}\b/',
            'private_key_block' => '/-----BEGIN (?:RSA |EC |DSA )?PRIVATE KEY-----/',
            'generic_api_key' => '/(?:api[_-]?key|apikey)\s*[:=]\s*["\']?[A-Za-z0-9\-_\.]{16,}["\']?/i',
        ];

    /**
     * @param  string  $redactionMask  The replacement string for redacted values
     * @param  bool  $redactEmails  Whether to redact email addresses
     * @param  list<string>  $additionalSensitiveKeys  Additional sensitive keys to redact
     */
    public function __construct(
        private string $redactionMask = self::REDACTED,
        private bool $redactEmails = false,
        private array $additionalSensitiveKeys = [],
    ) {
    }

    /**
     * Recursively redact sensitive data from an array.
     *
     * Redacts values for sensitive keys and scans string values for patterns.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function redactArray(array $data): array
    {
        return $this->redactRecursive($data);
    }

    /**
     * Recursively process and redact an array.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function redactRecursive(array $data): array
    {
        $redacted = [];

        foreach ($data as $key => $value) {
            $normalizedKey = $this->normalizeKey((string) $key);

            if ($this->isSensitiveKey($normalizedKey)) {
                $redacted[$key] = $this->createRedactedPlaceholder((string) $key);
            } elseif (is_array($value)) {
                $redacted[$key] = $this->redactRecursive($value);
            } elseif (is_string($value)) {
                $redacted[$key] = $this->redactString($value);
            } else {
                $redacted[$key] = $value;
            }
        }

        return $redacted;
    }

    /**
     * Normalize a key for comparison (lowercase, replace separators).
     */
    private function normalizeKey(string $key): string
    {
        $normalized = strtolower($key);
        $normalized = (string) preg_replace('/[\s\-_]+/', '_', $normalized);

        return trim($normalized, '_');
    }

    /**
     * Check if a key indicates a sensitive value.
     */
    private function isSensitiveKey(string $normalizedKey): bool
    {
        // Check exact matches
        if (in_array($normalizedKey, self::SENSITIVE_KEYS, true)) {
            return true;
        }

        // Check additional custom keys
        $additionalKeys = array_map($this->normalizeKey(...), $this->additionalSensitiveKeys);

        if (in_array($normalizedKey, $additionalKeys, true)) {
            return true;
        }

        return array_any(self::SENSITIVE_KEY_PATTERNS, fn ($pattern): bool => str_contains($normalizedKey, (string) $pattern));
    }

    /**
     * Redact sensitive data from a string value.
     *
     * Scans for patterns like credit cards, SSNs, tokens, etc.
     */
    public function redactString(string $value): string
    {
        foreach (self::STRING_PATTERNS as $patternName => $pattern) {
            $value = (string) preg_replace_callback(
                $pattern,
                fn (array $matches): string => $this->createRedactedPlaceholder($patternName),
                $value,
            );
        }

        if ($this->redactEmails) {
            return (string) preg_replace(
                '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/',
                $this->createRedactedPlaceholder('email'),
                $value,
            );
        }

        return $value;
    }

    /**
     * Create a redacted placeholder that indicates what was redacted.
     */
    private function createRedactedPlaceholder(string $source): string
    {
        return sprintf('[%s: %s]', strtoupper($source), $this->redactionMask);
    }
}
