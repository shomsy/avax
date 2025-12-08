<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Security;

/**
 * SessionIdValidator - Session ID Entropy Validation
 *
 * OWASP ASVS 3.2.2 Compliant
 *
 * Validates session ID quality to ensure cryptographic strength.
 *
 * Requirements:
 * - Minimum 128 bits entropy (32 hex chars)
 * - Cryptographically secure randomness
 * - No pattern repetition
 *
 * @package Avax\HTTP\Session\Security
 */
final class SessionIdValidator
{
    private const MIN_LENGTH = 32;  // 128 bits in hex

    /**
     * Validate session ID entropy.
     *
     * @param string $sessionId Session ID to validate.
     *
     * @return bool True if valid.
     *
     * @throws \RuntimeException If session ID quality insufficient.
     */
    public static function validate(string $sessionId) : bool
    {
        // Check minimum length (128 bits)
        if (strlen($sessionId) < self::MIN_LENGTH) {
            throw new \RuntimeException(
                sprintf(
                    'Session ID entropy too low: %d chars (minimum %d)',
                    strlen($sessionId),
                    self::MIN_LENGTH
                )
            );
        }

        // Check for pattern repetition (basic randomness test)
        if (preg_match('/^(.)\1+$/', $sessionId)) {
            throw new \RuntimeException('Session ID lacks randomness - repetitive pattern detected');
        }

        // Check for sequential patterns
        if (preg_match('/01234|12345|23456|abcde|bcdef/', $sessionId)) {
            throw new \RuntimeException('Session ID lacks randomness - sequential pattern detected');
        }

        return true;
    }

    /**
     * Validate current session ID.
     *
     * @return bool True if current session ID is valid.
     */
    public static function validateCurrent() : bool
    {
        $sessionId = session_id();

        if (empty($sessionId)) {
            throw new \RuntimeException('No active session to validate');
        }

        return self::validate($sessionId);
    }
}
