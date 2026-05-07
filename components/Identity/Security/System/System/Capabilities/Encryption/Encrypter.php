<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Security\System\System\Capabilities\Encryption;

use Avax\Components\Identity\Security\System\System\Foundation\Failure\DecryptionFailed;
use Avax\Components\Identity\Security\System\System\Foundation\Failure\EncryptionFailed;
use function is_string;

/**
 * AES-256-GCM encrypter implementation using OpenSSL.
 *
 * Provides authenticated encryption with associated data (AEAD)
 * via the GCM mode, which ensures both confidentiality and integrity.
 */
final class Encrypter implements EncrypterInterface
{
    private const string CIPHER = 'aes-256-gcm';

    private const int IV_LENGTH = 12; // 96 bits recommended for GCM

    private const int TAG_LENGTH = 16; // 128 bits authentication tag

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
     * @param mixed         $value         The value to encrypt (will be serialized if not string)
     * @param EncryptionKey $encryptionKey The encryption key to use
     *
     * @return EncryptedPayload The encrypted payload with cipher text, IV, and auth tag
     *
     * @throws EncryptionFailed if encryption operation fails
     */
    public function encrypt(mixed $value, EncryptionKey $encryptionKey) : EncryptedPayload
    {
        // Serialize to JSON for safe, portable encoding
        $plaintext = is_string($value) ? $value : json_encode(value: $value, flags: JSON_THROW_ON_ERROR);

        // Generate random IV (12 bytes for GCM)
        $iv = random_bytes(self::IV_LENGTH);

        // Encrypt with AES-256-GCM
        $cipherText = openssl_encrypt(
            $plaintext,
            self::CIPHER,
            $encryptionKey->raw(),
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
            keyVersion: $encryptionKey->version(),
        );
    }

    /**
     * Decrypt an EncryptedPayload using AES-256-GCM.
     *
     * Validates the authentication tag to detect tampering.
     *
     * @param EncryptedPayload $encryptedPayload The encrypted payload to decrypt
     * @param EncryptionKey    $encryptionKey    The encryption key to use
     *
     * @return string The decrypted plaintext
     *
     * @throws DecryptionFailed if decryption fails or payload has been tampered with
     */
    public function decrypt(EncryptedPayload $encryptedPayload, EncryptionKey $encryptionKey) : string
    {
        // Decrypt with AES-256-GCM, passing the authentication tag
        $plaintext = openssl_decrypt(
            $encryptedPayload->cipherText(),
            self::CIPHER,
            $encryptionKey->raw(),
            OPENSSL_RAW_DATA,
            $encryptedPayload->iv(),
            $encryptedPayload->tag(),
        );

        if ($plaintext === false) {
            throw new DecryptionFailed(
                'Decryption failed: payload may have been tampered with or wrong key used',
            );
        }

        return $plaintext;
    }
}
