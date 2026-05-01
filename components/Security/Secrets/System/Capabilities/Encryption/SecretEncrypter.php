<?php

declare(strict_types=1);

namespace Avax\Components\Secrets\System\Capabilities\Encryption;

use RuntimeException;

final readonly class SecretEncrypter
{
    public function __construct(
        private string $encryptionKey,
    ) {}

    public function encrypt(string $value) : string
    {
        $iv        = random_bytes(16);
        $encrypted = openssl_encrypt(
            data       : $value,
            cipher_algo: 'aes-256-gcm',
            passphrase : $this->encryptionKey,
            options    : 0,
            iv         : $iv,
            tag        : $tag,
        );

        if ($encrypted === false) {
            throw new RuntimeException('Secret encryption failed.');
        }

        return base64_encode($iv . $tag . $encrypted);
    }

    public function decrypt(string $value) : string
    {
        $data = base64_decode($value, strict: true);

        if ($data === false || strlen($data) < 33) {
            throw new RuntimeException('Encrypted secret payload is invalid.');
        }

        $iv        = substr($data, 0, 16);
        $tag       = substr($data, 16, 16);
        $encrypted = substr($data, 32);

        $decrypted = openssl_decrypt(
            data       : $encrypted,
            cipher_algo: 'aes-256-gcm',
            passphrase : $this->encryptionKey,
            options    : 0,
            iv         : $iv,
            tag        : $tag,
        );

        if ($decrypted === false) {
            throw new RuntimeException('Secret decryption failed.');
        }

        return $decrypted;
    }
}
