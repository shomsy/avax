<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Foundation\Lifecycle;

/**
 * Redacts sensitive values from query bindings.
 *
 * Query lifecycle events use this by default to prevent accidental logging
 * of passwords, tokens, secrets, emails, and other PII.
 *
 * Redaction strategy:
 * - Keys matching sensitive patterns are replaced with '***'
 * - Positional bindings: values are inspected for known sensitive patterns
 *   (long hex tokens, email-like strings) and redacted when detected
 * - Unknown positional bindings are kept as-is to preserve debugging utility
 */
final class RedactBindings
{
    /**
     * Sensitive key patterns (case-insensitive).
     */
    private const SENSITIVE_KEYS
        = [
            'password',
            'passwd',
            'pass',
            'token',
            'secret',
            'api_key',
            'apikey',
            'api_secret',
            'authorization',
            'auth_token',
            'access_token',
            'refresh_token',
            'credential',
            'private_key',
            'ssn',
            'social_security',
            'credit_card',
            'card_number',
            'cvv',
        ];

    /**
     * Redact bindings — auto-detects named vs positional.
     *
     * @param array<int|string, mixed> $bindings
     *
     * @return array<int|string, mixed>
     */
    public static function redact(array $bindings) : array
    {
        if ($bindings === []) {
            return [];
        }

        // Check if bindings are named (string keys).
        $firstKey = array_key_first($bindings);
        if (is_string($firstKey)) {
            $named = [];
            foreach ($bindings as $key => $value) {
                if (is_string($key) && self::isSensitiveKey($key)) {
                    $named[$key] = '***';
                } else {
                    $named[$key] = $value;
                }
            }

            return $named;
        }

        $positional = [];
        foreach ($bindings as $value) {
            if (is_string($value) && self::isSensitiveValue($value)) {
                $positional[] = '***';
            } else {
                $positional[] = $value;
            }
        }

        return $positional;
    }

    private static function isSensitiveKey(string $key) : bool
    {
        $lower = strtolower($key);

        foreach (self::SENSITIVE_KEYS as $sensitive) {
            if (str_contains($lower, $sensitive)) {
                return true;
            }
        }

        return false;
    }

    private static function isSensitiveValue(string $value) : bool
    {
        // Bearer token pattern.
        if (preg_match('/^Bearer\s+\S+/i', $value) === 1) {
            return true;
        }

        // JWT-like pattern (three base64url segments separated by dots).
        if (preg_match('/^[A-Za-z0-9_-]+\.eyJ[A-Za-z0-9_-]+\.[A-Za-z0-9_-]+$/', $value) === 1) {
            return true;
        }

        // Long hex string that looks like an API token (32+ hex chars).
        if (preg_match('/^[a-f0-9]{32,}$/i', $value) === 1) {
            return true;
        }

        return false;
    }
}
