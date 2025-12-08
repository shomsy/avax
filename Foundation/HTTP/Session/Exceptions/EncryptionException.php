<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Exceptions;

/**
 * EncryptionException - Encryption/Decryption Errors
 *
 * Thrown when encryption or decryption operations fail.
 * Provides fine-grained error handling for crypto operations.
 *
 * Error Types:
 * - Key missing or invalid
 * - Encryption failed
 * - Decryption failed
 * - Tag verification failed (GCM mode)
 * - Invalid ciphertext format
 *
 * @package Avax\HTTP\Session\Exceptions
 */
class EncryptionException extends \RuntimeException
{
    /**
     * Create exception for missing encryption key.
     *
     * @param string $keyName Key identifier.
     *
     * @return self
     */
    public static function keyMissing(string $keyName = 'default'): self
    {
        return new self(
            "Encryption key '{$keyName}' is missing or not configured. " .
            "Set encryption key in SessionConfig or environment."
        );
    }

    /**
     * Create exception for invalid encryption key.
     *
     * @param string $reason Reason why key is invalid.
     *
     * @return self
     */
    public static function invalidKey(string $reason): self
    {
        return new self("Invalid encryption key: {$reason}");
    }

    /**
     * Create exception for encryption failure.
     *
     * @param string $reason Failure reason.
     *
     * @return self
     */
    public static function encryptionFailed(string $reason = 'unknown'): self
    {
        return new self("Encryption failed: {$reason}");
    }

    /**
     * Create exception for decryption failure.
     *
     * @param string $reason Failure reason.
     *
     * @return self
     */
    public static function decryptionFailed(string $reason = 'unknown'): self
    {
        return new self("Decryption failed: {$reason}");
    }

    /**
     * Create exception for tag verification failure (GCM mode).
     *
     * @return self
     */
    public static function tagVerificationFailed(): self
    {
        return new self(
            "Authentication tag verification failed. " .
            "Data may have been tampered with or corrupted."
        );
    }

    /**
     * Create exception for invalid ciphertext format.
     *
     * @param string $expected Expected format.
     *
     * @return self
     */
    public static function invalidFormat(string $expected): self
    {
        return new self("Invalid ciphertext format. Expected: {$expected}");
    }

    /**
     * Create exception for unsupported cipher.
     *
     * @param string $cipher Cipher name.
     *
     * @return self
     */
    public static function unsupportedCipher(string $cipher): self
    {
        return new self("Unsupported cipher: {$cipher}");
    }

    /**
     * Create exception for key rotation failure.
     *
     * @param string $reason Failure reason.
     *
     * @return self
     */
    public static function keyRotationFailed(string $reason): self
    {
        return new self("Key rotation failed: {$reason}");
    }
}
