<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Storage\ProtectCachedValues;

use Random\RandomException;

final readonly class EncryptedCache
{
    public function __construct(
        private CacheEncryptionKey $cacheEncryptionKey,
        private bool               $enabled = true,
    ) {}

    /**
     * @throws RandomException
     */
    public function encrypt(string $plaintext) : string
    {
        if (! $this->enabled) {
            return $plaintext;
        }

        $iv = random_bytes(openssl_cipher_iv_length('aes-256-gcm'));

        $ciphertext = openssl_encrypt(
            $plaintext,
            'aes-256-gcm',
            $this->cacheEncryptionKey->toString(),
            0,
            $iv,
            $tag,
        );

        return $iv . $tag . $ciphertext;
    }

    public function decrypt(string $encrypted) : string
    {
        if (! $this->enabled) {
            return $encrypted;
        }

        $ivLength   = openssl_cipher_iv_length('aes-256-gcm');
        $iv         = substr($encrypted, 0, $ivLength);
        $tag        = substr($encrypted, $ivLength, 16);
        $ciphertext = substr($encrypted, $ivLength + 16);

        $plaintext = openssl_decrypt(
            $ciphertext,
            'aes-256-gcm',
            $this->cacheEncryptionKey->toString(),
            0,
            $iv,
            $tag,
        );

        if ($plaintext === false) {
            throw new CachePayloadWasTampered(
                message: 'Failed to decrypt cache payload',
            );
        }

        return $plaintext;
    }

    public function isEnabled() : bool
    {
        return $this->enabled;
    }
}
