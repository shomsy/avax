<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Security\System\Capabilities\Encryption;

use Avax\Components\Identity\Security\System\Foundation\Failure\DecryptionFailed;
use Avax\Components\Identity\Security\System\Foundation\Failure\EncryptionFailed;
use function is_string;

/**
 * AES-256-GCM encrypter implementation using OpenSSL.
 *
 * Provides authenticated encryption with associated data (AEAD)
 * via the GCM mode, which ensures both confidentiality and integrity.
 */
final class Encrypter implements EncrypterInterface
{
    private const CIPHER     = 'aes-256-gcm';
    private const IV_LENGTH  = 12; // 96 bits recommended for GCM
    private const TAG_LENGTH = 16; // 128 bits authentication tag

    /**
     * Check if this encrypter supports the given cipher.
     */
    public function supports(string $cipher) : bool
    {
        return $cipher === self::CIPHER;
    }

    /**
     * Encrypt a value using AES-256-GCM.
     *
     * @param mixed         $value The value to encrypt (will be serialized if not string)
     * @param EncryptionKey $key   The encryption key to use
     *
     * @return EncryptedPayload The encrypted payload with cipher text, IV, and auth tag
     * @throws EncryptionFailed if encryption operation fails
     */
    public function encrypt(mixed $value, EncryptionKey $key) : EncryptedPayload
    {
        // Serialize non-string values
        $plaintext = is_string($value) ? $value : serialize($value);

        // Generate random IV (12 bytes for GCM)
        $iv = random_bytes(self::IV_LENGTH);

        // Encrypt with AES-256-GCM
        $cipherText = openssl_encrypt(
            $plaintext,
            self::CIPHER,
            $key->raw(),
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',
            self::TAG_LENGTH,
        );

        if ($cipherText === false) {
            throw new EncryptionFailed('Encryption failed: openssl_encrypt returned false');
        }

        return new EncryptedPayload(
            cipherText: $cipherText,
            iv        : $iv,
            tag       : $tag,
            keyVersion: $key->version(),
        );
    }

    /**
     * Decrypt an EncryptedPayload using AES-256-GCM.
     *
     * Validates the authentication tag to detect tampering.
     *
     * @param EncryptedPayload $payload The encrypted payload to decrypt
     * @param EncryptionKey $key The encryption key to use
     *
     * @return string The decrypted plaintext
     * @throws DecryptionFailed if decryption fails or payload has been tampered with
     */
    public function decrypt(EncryptedPayload $payload, EncryptionKey $key) : string
    {
        // Decrypt with AES-256-GCM, passing the authentication tag
        $plaintext = openssl_decrypt(
            $payload->cipherText(),
            self::CIPHER,
            $key->raw(),
            OPENSSL_RAW_DATA,
            $payload->iv(),
            $payload->tag(),
        );

        if ($plaintext === false) {
            throw new DecryptionFailed(
                'Decryption failed: payload may have been tampered with or wrong key used',
            );
        }

        return $plaintext;
    }
}
