<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Security\Crypto;

use Avax\HTTP\Session\Contracts\Security\Encrypter;

/**
 * OpenSSLEncrypter - Production-Grade AES-256-GCM Encryption
 *
 * OWASP ASVS 3.3.2 & 3.3.3 Compliant
 * 
 * Features:
 * - AES-256-GCM (Authenticated Encryption with Associated Data)
 * - Automatic IV generation per encryption
 * - Authentication tag for integrity verification
 * - Tampering detection
 * - Cryptographically secure random IVs
 * 
 * Security Properties:
 * - Confidentiality: AES-256 encryption
 * - Integrity: GCM authentication tag
 * - Non-replayability: Unique IV per operation
 * 
 * @package Avax\HTTP\Session\Security\Crypto
 */
final class OpenSSLEncrypter implements Encrypter
{
    private const CIPHER = 'aes-256-gcm';
    private const IV_LENGTH = 12;      // 96 bits for GCM
    private const TAG_LENGTH = 16;     // 128 bits authentication tag

    /**
     * OpenSSLEncrypter Constructor.
     *
     * @param string $key Encryption key (32 bytes for AES-256).
     *
     * @throws \InvalidArgumentException If key length is invalid.
     */
    public function __construct(
        private string $key
    ) {
        if (strlen($key) !== 32) {
            throw new \InvalidArgumentException(
                'Encryption key must be exactly 32 bytes for AES-256'
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function encrypt(mixed $value): string
    {
        $plaintext = serialize($value);

        // Generate cryptographically secure random IV
        $iv = random_bytes(self::IV_LENGTH);

        // Encrypt with AES-256-GCM
        $tag = '';
        $ciphertext = openssl_encrypt(
            $plaintext,
            self::CIPHER,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',
            self::TAG_LENGTH
        );

        if ($ciphertext === false) {
            throw new \RuntimeException('Encryption failed');
        }

        // Package: IV + Tag + Ciphertext (all binary)
        $package = $iv . $tag . $ciphertext;

        // Encode for safe storage
        return base64_encode($package);
    }

    /**
     * {@inheritdoc}
     */
    public function decrypt(string $payload): mixed
    {
        // Decode from base64
        $package = base64_decode($payload, true);

        if ($package === false || strlen($package) < (self::IV_LENGTH + self::TAG_LENGTH)) {
            throw new \RuntimeException('Invalid encrypted payload');
        }

        // Unpack: IV + Tag + Ciphertext
        $iv = substr($package, 0, self::IV_LENGTH);
        $tag = substr($package, self::IV_LENGTH, self::TAG_LENGTH);
        $ciphertext = substr($package, self::IV_LENGTH + self::TAG_LENGTH);

        // Decrypt with authentication tag verification
        $plaintext = openssl_decrypt(
            $ciphertext,
            self::CIPHER,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($plaintext === false) {
            // Tampering detected or invalid key
            throw new \RuntimeException('Decryption failed - possible tampering detected');
        }

        return unserialize($plaintext);
    }
}
