<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Security;

use Avax\HTTP\Session\Contracts\Storage\Store;

/**
 * CsrfToken - CSRF Token Management
 *
 * OWASP ASVS 4.2.2 Compliant
 *
 * Session-bound CSRF token generation and verification.
 *
 * Features:
 * - Cryptographically secure tokens
 * - Session-bound (invalidated on logout)
 * - Constant-time comparison
 * - Automatic rotation
 *
 * @package Avax\HTTP\Session\Security
 */
final class CsrfToken
{
    private const TOKEN_KEY    = '_csrf_token';
    private const TOKEN_LENGTH = 32;  // 256 bits

    /**
     * CsrfToken Constructor.
     *
     * @param Store $store Session storage.
     */
    public function __construct(
        private Store $store
    ) {}

    /**
     * Generate a new CSRF token.
     *
     * Stores token in session for verification.
     *
     * @return string Hex-encoded token.
     */
    public function generate() : string
    {
        $token = bin2hex(random_bytes(self::TOKEN_LENGTH));
        $this->store->put(self::TOKEN_KEY, $token);

        return $token;
    }

    /**
     * Verify CSRF token.
     *
     * Uses constant-time comparison to prevent timing attacks.
     *
     * @param string $providedToken Token to verify.
     *
     * @return bool True if valid.
     */
    public function verify(string $providedToken) : bool
    {
        $storedToken = $this->store->get(self::TOKEN_KEY);

        if ($storedToken === null) {
            return false;
        }

        return hash_equals($storedToken, $providedToken);
    }

    /**
     * Verify token or throw exception.
     *
     * @param string $providedToken Token to verify.
     *
     * @return void
     *
     * @throws \RuntimeException If token invalid.
     */
    public function verifyOrFail(string $providedToken) : void
    {
        if (! $this->verify($providedToken)) {
            throw new \RuntimeException('CSRF token mismatch - possible CSRF attack');
        }
    }

    /**
     * Get current token (generate if missing).
     *
     * @return string Current token.
     */
    public function getToken() : string
    {
        $token = $this->store->get(self::TOKEN_KEY);

        if ($token === null) {
            return $this->generate();
        }

        return $token;
    }

    /**
     * Rotate CSRF token.
     *
     * Generates new token, invalidating the old one.
     *
     * @return string New token.
     */
    public function rotate() : string
    {
        return $this->generate();
    }

    /**
     * Clear CSRF token.
     *
     * Call on logout to invalidate token.
     *
     * @return void
     */
    public function clear() : void
    {
        $this->store->delete(self::TOKEN_KEY);
    }
}
