<?php
declare(strict_types=1);

namespace Avax\Components\Identity\Security\System\Capabilities\Encryption;

use RuntimeException;

final class AesEncrypter implements EncrypterInterface
{
    public function __construct(
        private readonly string $key,
        private readonly string $cipher = 'aes-256-cbc'
    ) {
        if (strlen($key) !== 32) {
            throw new RuntimeException("Encryption key must be 32 bytes for AES-256.");
        }
    }

    public function encrypt(mixed $value): string
    {
        $iv = random_bytes(openssl_cipher_iv_length($this->cipher));
        $value = serialize($value);
        
        $ciphertext = openssl_encrypt($value, $this->cipher, $this->key, 0, $iv);
        
        if ($ciphertext === false) {
            throw new RuntimeException("Encryption failed.");
        }

        return base64_encode($iv . $ciphertext);
    }

    public function decrypt(string $payload): mixed
    {
        $payload = base64_decode($payload);
        $ivLen = openssl_cipher_iv_length($this->cipher);
        
        $iv = substr($payload, 0, $ivLen);
        $ciphertext = substr($payload, $ivLen);
        
        $decrypted = openssl_decrypt($ciphertext, $this->cipher, $this->key, 0, $iv);
        
        if ($decrypted === false) {
            throw new RuntimeException("Decryption failed.");
        }

        return unserialize($decrypted);
    }
}
