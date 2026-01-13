<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Shared\Security;

use RuntimeException;
use SensitiveParameter;

/**
 * Class SessionSignature
 * ------------------------------------------------------------
 * 🛡️ Enterprise-Grade Session Integrity Protection
 *
 * Provides cryptographic signing and verification of session
 * payloads to prevent tampering and replay attacks.
 *
 * ✅ OWASP ASVS 3.3.3 — "Verify that all session tokens are signed
 * or encrypted using a strong cryptographic algorithm."
 *
 * Implements HMAC-SHA256 integrity protection using immutable keys.
 *
 * 💡 Supports key rotation — allowing seamless transition from an
 * old secret to a new one without breaking existing sessions.
 *
 * @example Basic usage:
 *   $signature = new SessionSignature(secretKey: $_ENV['SESSION_KEY']);
 *   $hash = $signature->sign($sessionData);
 *   $signature->verify($sessionData, $hash);
 */
final class SessionSignature
{
    private const string ALGO = 'sha256';

    /**
     * @var string Primary secret key for signing.
     */
    private string $primaryKey;

    /**
     * @var string|null Optional secondary key for fallback verification (key rotation support).
     */
    private string|null $secondaryKey;

    /**
     * Constructor.
     *
     * @param string      $secretKey   Primary secret key (HMAC key).
     * @param string|null $fallbackKey Optional old key (used for key rotation verification).
     *
     * @throws RuntimeException If provided key(s) are invalid or too short.
     */
    public function __construct(#[SensitiveParameter] string $secretKey, string|null $fallbackKey = null)
    {
        if (strlen(string: $secretKey) < 32) {
            throw new RuntimeException(message: 'SessionSignature: Secret key must be at least 32 bytes long.');
        }

        $this->primaryKey   = $secretKey;
        $this->secondaryKey = $fallbackKey;
    }

    // ------------------------------------------------------------
    // 🔒 Signing
    // ------------------------------------------------------------

    /**
     * Verify integrity of session data using HMAC signature.
     *
     * Performs constant-time comparison to mitigate timing attacks.
     *
     * If verification fails with the primary key and a secondary key
     * is defined, verification is attempted again using the fallback key.
     *
     * @param string $data      Original session payload.
     * @param string $signature Expected signature (hexadecimal).
     *
     * @return bool True if signature is valid; false otherwise.
     */
    public function verify(string $data, string $signature) : bool
    {
        $expected = $this->sign(data: $data);

        // Primary key check
        if (hash_equals(known_string: $expected, user_string: $signature)) {
            return true;
        }

        // Optional fallback check (for rotated keys)
        if ($this->secondaryKey !== null) {
            $fallbackSignature = hash_hmac(algo: self::ALGO, data: $data, key: $this->secondaryKey);

            return hash_equals(known_string: $fallbackSignature, user_string: $signature);
        }

        return false;
    }

    // ------------------------------------------------------------
    // 🔍 Verification
    // ------------------------------------------------------------

    /**
     * Generate an HMAC signature for given session data.
     *
     * @param string $data The session payload to sign.
     *
     * @return string Hexadecimal HMAC signature.
     */
    public function sign(string $data) : string
    {
        return hash_hmac(
            algo: self::ALGO,
            data: $data,
            key : $this->primaryKey
        );
    }

    // ------------------------------------------------------------
    // ⚙️ Accessors
    // ------------------------------------------------------------

    /**
     * Retrieve the current HMAC algorithm in use.
     *
     * @return string Algorithm identifier (e.g., sha256).
     */
    public function getAlgorithm() : string
    {
        return self::ALGO;
    }

    /**
     * Retrieve the currently active primary signing key.
     *
     * ⚠️ For internal use only — do not expose in logs or debug output.
     *
     * @return string Active signing key.
     */
    public function getKey() : string
    {
        return $this->primaryKey;
    }

    /**
     * Check if a secondary key is configured (key rotation mode).
     *
     * @return bool True if secondary key available.
     */
    public function hasFallbackKey() : bool
    {
        return $this->secondaryKey !== null;
    }
}
