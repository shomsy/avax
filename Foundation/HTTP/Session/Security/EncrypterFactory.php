<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Security;

use Avax\HTTP\Session\Contracts\Security\Encrypter;
use Avax\HTTP\Session\Security\Crypto\OpenSSLEncrypter;

/**
 * EncrypterFactory - Encrypter with Key Management
 *
 * OWASP ASVS 3.1.3 Compliant
 * 
 * Integrates KeyManager with encryption operations.
 * Supports key rotation by attempting decryption with all known keys.
 * 
 * @package Avax\HTTP\Session\Security
 */
final class EncrypterFactory
{
    private KeyManager $keyManager;

    /**
     * EncrypterFactory Constructor.
     *
     * @param KeyManager|null $keyManager Key manager (optional, creates default).
     */
    public function __construct(?KeyManager $keyManager = null)
    {
        $this->keyManager = $keyManager ?? new KeyManager();
    }

    /**
     * Create encrypter with active key.
     *
     * @return Encrypter Encrypter instance.
     */
    public function create(): Encrypter
    {
        $activeKey = $this->keyManager->getActiveKey();
        return new OpenSSLEncrypter($activeKey);
    }

    /**
     * Encrypt with active key.
     *
     * @param mixed $value Value to encrypt.
     *
     * @return string Encrypted payload.
     */
    public function encrypt(mixed $value): string
    {
        return $this->create()->encrypt($value);
    }

    /**
     * Decrypt with key rotation support.
     *
     * Attempts decryption with all known keys (active + rotated).
     * Enables seamless key rotation.
     *
     * @param string $payload Encrypted payload.
     *
     * @return mixed Decrypted value.
     * 
     * @throws \RuntimeException If decryption fails with all keys.
     */
    public function decrypt(string $payload): mixed
    {
        $allKeys = $this->keyManager->getAllKeys();

        foreach ($allKeys as $key) {
            try {
                $encrypter = new OpenSSLEncrypter($key);
                return $encrypter->decrypt($payload);
            } catch (\Exception $e) {
                // Try next key
                continue;
            }
        }

        // All keys failed
        throw new \RuntimeException(
            'Decryption failed with all known keys - possible tampering or key mismatch'
        );
    }
}
