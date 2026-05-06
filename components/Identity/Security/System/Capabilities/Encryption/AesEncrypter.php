<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Security\System\Capabilities\Encryption;

use RuntimeException;

final readonly class AesEncrypter implements EncrypterInterface
{
    /**
     * @throws RuntimeException if encryption key is invalid
     */
    public function __construct(
        private string $key,
        private string $cipher = 'aes-256-gcm',
    ) {
        if (strlen($key) !== 32) {
            throw new RuntimeException('Encryption key must be 32 bytes for AES-256.');
        }
    }

    /**
     * Encrypt a value using authenticated encryption (AES-256-GCM).
     *
     * @throws RuntimeException if encryption fails
     */
    public function encrypt(mixed $value, EncryptionKey $encryptionKey) : EncryptedPayload
    {
        $ivLength = openssl_cipher_iv_length($this->cipher);

        if ($ivLength === false || $ivLength < 1) {
            throw new RuntimeException('Unsupported encryption cipher.');
        }

        /** @var int<1, max> $ivLength */
        $iv = random_bytes($ivLength);

        $plainText = is_string($value)
            ? $value
            : json_encode($value, JSON_THROW_ON_ERROR);

        $tag = null;

        $cipherText = openssl_encrypt(
            $plainText,
            $this->cipher,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
        );

        if ($cipherText === false || $tag === null) {
            throw new RuntimeException('Encryption failed.');
        }

        return new EncryptedPayload(
            cipherText: $cipherText,
            iv        : $iv,
            tag       : $tag,
        );
    }

    /**
     * Decrypt a value using authenticated encryption (AES-256-GCM).
     *
     * @throws RuntimeException if decryption fails or payload is tampered
     */
    public function decrypt(EncryptedPayload $encryptedPayload, EncryptionKey $encryptionKey) : string
    {
        $plainText = openssl_decrypt(
            $encryptedPayload->cipherText(),
            $this->cipher,
            $this->key,
            OPENSSL_RAW_DATA,
            $encryptedPayload->iv(),
            $encryptedPayload->tag(),
        );

        if ($plainText === false) {
            throw new RuntimeException('Decryption failed or payload was tampered with.');
        }

        return $plainText;
    }

    public function supports(string $cipher) : bool
    {
        return strtolower($cipher) === strtolower($this->cipher);
    }
}
