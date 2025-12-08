<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Security;

/**
 * SessionSignature - HMAC-based Session Integrity
 *
 * OWASP ASVS 3.3.3 Compliant
 * 
 * Provides cryptographic signing and verification of session data
 * to prevent tampering attacks.
 * 
 * Uses SHA-256 HMAC for integrity verification.
 * 
 * @package Avax\HTTP\Session\Security
 */
final class SessionSignature
{
    private const ALGO = 'sha256';

    /**
     * Sign session data with HMAC.
     *
     * @param string $data Session data to sign.
     * @param string $key  Secret signing key.
     *
     * @return string HMAC signature (hex).
     */
    public static function sign(string $data, string $key): string
    {
        return hash_hmac(self::ALGO, $data, $key);
    }

    /**
     * Verify HMAC signature.
     *
     * Uses constant-time comparison to prevent timing attacks.
     *
     * @param string $data      Original data.
     * @param string $signature Signature to verify.
     * @param string $key       Secret signing key.
     *
     * @return bool True if signature valid.
     */
    public static function verify(string $data, string $signature, string $key): bool
    {
        $expectedSignature = self::sign($data, $key);
        return hash_equals($expectedSignature, $signature);
    }
}
