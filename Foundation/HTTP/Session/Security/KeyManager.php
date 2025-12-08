<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Security;

use RuntimeException;

/**
 * KeyManager - Encryption Key Management
 *
 * OWASP ASVS 3.1.1 & 3.1.2 Compliant
 *
 * Manages encryption keys with rotation support.
 * Allows seamless key rotation without invalidating existing sessions.
 *
 * Keys are loaded from environment variables for security.
 *
 * Environment Variables:
 * - SESSION_KEY_ACTIVE: Current encryption key (32 bytes hex)
 * - SESSION_KEY_ROTATED: Comma-separated previous keys (optional)
 *
 * @package Avax\HTTP\Session\Security
 */
final class KeyManager
{
    /**
     * Generate a new random key.
     *
     * Helper method for key generation (use offline).
     *
     * @return string Hex-encoded 32-byte key.
     */
    public static function generateKey() : string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Get all keys (active + rotated).
     *
     * Useful for decryption attempts with multiple keys.
     *
     * @return array<string> All available keys.
     */
    public function getAllKeys() : array
    {
        return array_merge(
            [$this->getActiveKey()],
            $this->getPreviousKeys()
        );
    }

    /**
     * Get the active encryption key.
     *
     * @return string Active key (32 bytes).
     *
     * @throws \RuntimeException If key not configured.
     */
    public function getActiveKey() : string
    {
        $key = getenv('SESSION_KEY_ACTIVE');

        if ($key === false || $key === '') {
            throw new RuntimeException(
                'SESSION_KEY_ACTIVE environment variable not set'
            );
        }

        // Convert from hex to binary
        $binaryKey = hex2bin($key);

        if ($binaryKey === false || strlen($binaryKey) !== 32) {
            throw new RuntimeException(
                'SESSION_KEY_ACTIVE must be 64 hex characters (32 bytes)'
            );
        }

        return $binaryKey;
    }

    /**
     * Get previously rotated keys.
     *
     * Used to decrypt sessions encrypted with old keys.
     *
     * @return array<string> Array of previous keys (32 bytes each).
     */
    public function getPreviousKeys() : array
    {
        $keysString = getenv('SESSION_KEY_ROTATED');

        if ($keysString === false || $keysString === '') {
            return [];
        }

        $hexKeys    = explode(',', $keysString);
        $binaryKeys = [];

        foreach ($hexKeys as $hexKey) {
            $hexKey    = trim($hexKey);
            $binaryKey = hex2bin($hexKey);

            if ($binaryKey !== false && strlen($binaryKey) === 32) {
                $binaryKeys[] = $binaryKey;
            }
        }

        return $binaryKeys;
    }
}
