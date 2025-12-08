<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Security;

use Avax\HTTP\Session\Contracts\Storage\Store;
use RuntimeException;

/**
 * SessionNonce - Replay Attack Prevention
 *
 * OWASP ASVS 3.3.4 Compliant
 *
 * Generates and validates single-use tokens (nonces) to prevent
 * replay attacks on critical state-changing operations.
 *
 * Nonces are cryptographically secure random values.
 *
 * @package Avax\HTTP\Session\Security
 */
final class SessionNonce
{
    private const string NONCE_KEY    = '_nonce';
    private const int    NONCE_LENGTH = 16; // 128 bits

    /**
     * SessionNonce Constructor.
     *
     * @param Store $store Session storage.
     */
    public function __construct(
        private Store $store
    ) {}

    /**
     * Generate a new nonce.
     *
     * Stores it in session for later verification.
     *
     * @return string Hex-encoded nonce.
     */
    public function generate() : string
    {
        $nonce = bin2hex(random_bytes(self::NONCE_LENGTH));
        $this->store->put(key: self::NONCE_KEY, value: $nonce);

        return $nonce;
    }

    /**
     * Verify nonce or throw exception.
     *
     * @param string $providedNonce Nonce to verify.
     *
     * @return void
     *
     * @throws \RuntimeException If nonce invalid.
     */
    public function verifyOrFail(string $providedNonce) : void
    {
        if (! $this->verify($providedNonce)) {
            throw new RuntimeException(
                'Invalid or missing nonce - potential replay attack detected'
            );
        }
    }

    /**
     * Verify and consume a nonce.
     *
     * Nonce is deleted after verification (single-use).
     *
     * @param string $providedNonce Nonce to verify.
     *
     * @return bool True if nonce is valid.
     */
    public function verify(string $providedNonce) : bool
    {
        $storedNonce = $this->store->get(key: self::NONCE_KEY);

        if ($storedNonce === null) {
            return false; // No nonce stored
        }

        // Consume nonce (delete it)
        $this->store->delete(key: self::NONCE_KEY);

        // Constant-time comparison
        return hash_equals($storedNonce, $providedNonce);
    }

    /**
     * Check if a nonce exists in session.
     *
     * @return bool True if nonce present.
     */
    public function exists() : bool
    {
        return $this->store->has(key: self::NONCE_KEY);
    }

    // ========================================
    // PER-REQUEST NONCE (REPLAY ATTACK PREVENTION)
    // ========================================

    /**
     * Generate a per-request nonce.
     *
     * Used for critical operations that should only execute once.
     * Each request gets a unique nonce that expires after use.
     *
     * @param string $action Action identifier (e.g., 'delete_account', 'transfer_funds').
     *
     * @return string Hex-encoded nonce.
     */
    public function generateForRequest(string $action) : string
    {
        $nonce = bin2hex(random_bytes(self::NONCE_LENGTH));
        $key   = self::NONCE_KEY . ".{$action}";

        $this->store->put(key: $key, value: [
            'nonce'      => $nonce,
            'created_at' => time(),
            'action'     => $action,
        ]);

        return $nonce;
    }

    /**
     * Verify per-request nonce or throw exception.
     *
     * @param string $action        Action identifier.
     * @param string $providedNonce Nonce to verify.
     * @param int    $maxAge        Maximum age in seconds.
     *
     * @return void
     *
     * @throws \RuntimeException If nonce invalid or expired.
     */
    public function verifyForRequestOrFail(string $action, string $providedNonce, int $maxAge = 300) : void
    {
        if (! $this->verifyForRequest($action, $providedNonce, $maxAge)) {
            throw new RuntimeException(
                "Invalid or expired nonce for action '{$action}' - potential replay attack detected"
            );
        }
    }

    /**
     * Verify and consume a per-request nonce.
     *
     * @param string $action        Action identifier.
     * @param string $providedNonce Nonce to verify.
     * @param int    $maxAge        Maximum age in seconds (default: 300 = 5 minutes).
     *
     * @return bool True if valid.
     */
    public function verifyForRequest(string $action, string $providedNonce, int $maxAge = 300) : bool
    {
        $key    = self::NONCE_KEY . ".{$action}";
        $stored = $this->store->get(key: $key);

        if ($stored === null) {
            return false; // No nonce for this action
        }

        // Check expiration
        if (time() - $stored['created_at'] > $maxAge) {
            $this->store->delete(key: $key);

            return false; // Expired
        }

        // Consume nonce (delete it)
        $this->store->delete(key: $key);

        // Constant-time comparison
        return hash_equals($stored['nonce'], $providedNonce);
    }

    /**
     * Clear all per-request nonces.
     *
     * @return void
     */
    public function clearAllRequests() : void
    {
        $all = $this->store->all();

        foreach (array_keys($all) as $key) {
            if (str_starts_with($key, self::NONCE_KEY . '.')) {
                $this->store->delete(key: $key);
            }
        }
    }

    /**
     * Get all active per-request nonces.
     *
     * Useful for debugging.
     *
     * @return array<string, array> Action => nonce data.
     */
    public function getActiveRequests() : array
    {
        $all    = $this->store->all();
        $nonces = [];

        foreach ($all as $key => $value) {
            if (str_starts_with($key, self::NONCE_KEY . '.') && is_array($value)) {
                $action          = substr($key, strlen(self::NONCE_KEY) + 1);
                $nonces[$action] = $value;
            }
        }

        return $nonces;
    }
}
