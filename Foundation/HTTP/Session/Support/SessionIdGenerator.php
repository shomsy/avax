<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Support;

/**
 * SessionIdGenerator
 *
 * Cryptographically secure session ID generator.
 *
 * This utility generates random, unpredictable session identifiers
 * using cryptographically secure random number generation.
 *
 * Enterprise Rules:
 * - Security: Uses CSPRNG (Cryptographically Secure Pseudo-Random Number Generator).
 * - Uniqueness: Extremely low collision probability.
 * - Standards: Follows OWASP session management guidelines.
 *
 * Usage:
 *   $generator = new SessionIdGenerator();
 *   $sessionId = $generator->generate();
 *
 * @package Avax\HTTP\Session\Support
 */
final readonly class SessionIdGenerator
{
    /**
     * Default session ID length in bytes.
     */
    private const DEFAULT_LENGTH = 32;

    /**
     * SessionIdGenerator Constructor.
     *
     * @param int $length The length of the session ID in bytes.
     */
    public function __construct(
        private int $length = self::DEFAULT_LENGTH
    ) {
        // Guard: Validate length is positive.
        if ($this->length <= 0) {
            throw new \InvalidArgumentException(
                message: "Session ID length must be positive, got: {$this->length}"
            );
        }

        // Guard: Validate length is reasonable (not too short).
        if ($this->length < 16) {
            throw new \InvalidArgumentException(
                message: "Session ID length must be at least 16 bytes for security, got: {$this->length}"
            );
        }
    }

    /**
     * Generate a cryptographically secure session ID.
     *
     * This method uses random_bytes() which is a CSPRNG on all
     * supported platforms.
     *
     * @return string The generated session ID (hexadecimal).
     */
    public function generate(): string
    {
        try {
            // Generate cryptographically secure random bytes.
            $randomBytes = random_bytes($this->length);

            // Convert to hexadecimal string.
            $sessionId = bin2hex($randomBytes);

            // Log generation (without the actual ID for security).
            logger()?->debug(
                message: 'Session ID generated',
                context: [
                    'length_bytes' => $this->length,
                    'length_hex' => strlen($sessionId),
                    'action' => 'SessionIdGenerator',
                ]
            );

            return $sessionId;
        } catch (\Exception $e) {
            // Log failure.
            logger()?->critical(
                message: 'Failed to generate session ID',
                context: [
                    'error' => $e->getMessage(),
                    'action' => 'SessionIdGenerator',
                ]
            );

            // Re-throw as RuntimeException.
            throw new \RuntimeException(
                message: 'Failed to generate secure session ID: ' . $e->getMessage(),
                previous: $e
            );
        }
    }

    /**
     * Validate a session ID format.
     *
     * Checks if the provided string is a valid hexadecimal session ID
     * of the expected length.
     *
     * @param string $sessionId The session ID to validate.
     *
     * @return bool True if valid, false otherwise.
     */
    public function validate(string $sessionId): bool
    {
        // Expected length in hexadecimal (2 chars per byte).
        $expectedLength = $this->length * 2;

        // Check length.
        if (strlen($sessionId) !== $expectedLength) {
            return false;
        }

        // Check if hexadecimal.
        if (!ctype_xdigit($sessionId)) {
            return false;
        }

        return true;
    }
}
