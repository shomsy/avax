<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Security\System\Capabilities\Encryption;

use RuntimeException;

final class AesEncrypter implements EncrypterInterface
{
    /**
     * @throws RuntimeException if encryption key is invalid or encryption fails
     */
    public function __construct(
        private readonly string $key,
        private readonly string $cipher = 'aes-256-gcm',
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
    public function encrypt(mixed $value): string
    {
        $ivLen = openssl_cipher_iv_length($this->cipher);
        $iv = random_bytes($ivLen);
        $value = json_encode($value, JSON_THROW_ON_ERROR);

        $ciphertext = openssl_encrypt($value, $this->cipher, $this->key, OPENSSL_RAW_DATA, $iv, $tag);

        if ($ciphertext === false) {
            throw new RuntimeException('Encryption failed.');
        }

        // IV + tag (16 bytes for GCM) + ciphertext
        return base64_encode($iv . $tag . $ciphertext);
    }

    /**
     * Decrypt a value using authenticated encryption (AES-256-GCM).
     *
     * @throws RuntimeException if decryption fails or payload is tampered
     */
    public function decrypt(string $payload): mixed
    {
        $payload = base64_decode($payload, true);
        if ($payload === false) {
            throw new RuntimeException('Invalid base64 payload.');
        }

        $ivLen = openssl_cipher_iv_length($this->cipher);
        $tagLen = 16; // GCM tag length

        if (strlen($payload) < $ivLen + $tagLen) {
            throw new RuntimeException('Invalid encrypted payload.');
        }

        $iv  = substr($payload, 0, $ivLen);
        $tag = substr($payload, $ivLen, $tagLen);
        $ciphertext = substr($payload, $ivLen + $tagLen);

        $decrypted = openssl_decrypt($ciphertext, $this->cipher, $this->key, OPENSSL_RAW_DATA, $iv, $tag);

        if ($decrypted === false) {
            throw new RuntimeException('Decryption failed or payload was tampered with.');
        }

        return json_decode($decrypted, true, 512, JSON_THROW_ON_ERROR);
    }
}
