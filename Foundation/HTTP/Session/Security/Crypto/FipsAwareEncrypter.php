<?php

declare(strict_types=1);

namespace Foundation\HTTP\Session\Security\Crypto;

use RuntimeException;

/**
 * FipsAwareEncrypter
 *
 * Provides dual-mode encryption supporting AES-256-GCM and AES-256-CBC-HMAC-SHA256.
 * Automatically detects FIPS mode through OPENSSL_FIPS environment variable.
 *
 * @package Foundation\HTTP\Session\Security\Crypto
 */
final class FipsAwareEncrypter
{
    public function __construct(
        private readonly string $key
    ) {}

    public function encrypt(string $plaintext): string
    {
        $fipsMode = getenv('OPENSSL_FIPS') === '1';

        if ($fipsMode) {
            $iv = random_bytes(16);
            $ciphertext = openssl_encrypt($plaintext, 'AES-256-CBC', $this->key, OPENSSL_RAW_DATA, $iv);
            $hmac = hash_hmac('sha256', $ciphertext, $this->key, true);
            return base64_encode($iv . $hmac . $ciphertext);
        }

        $iv = random_bytes(12);
        $tag = '';
        $ciphertext = openssl_encrypt($plaintext, 'aes-256-gcm', $this->key, OPENSSL_RAW_DATA, $iv, $tag);
        return base64_encode($iv . $tag . $ciphertext);
    }

    public function decrypt(string $ciphertext): string
    {
        $decoded = base64_decode($ciphertext, true);

        if (getenv('OPENSSL_FIPS') === '1') {
            $iv = substr($decoded, 0, 16);
            $hmac = substr($decoded, 16, 32);
            $ct = substr($decoded, 48);

            $calcHmac = hash_hmac('sha256', $ct, $this->key, true);
            if (!hash_equals($hmac, $calcHmac)) {
                throw new RuntimeException('HMAC verification failed');
            }

            return openssl_decrypt($ct, 'AES-256-CBC', $this->key, OPENSSL_RAW_DATA, $iv);
        }

        $iv = substr($decoded, 0, 12);
        $tag = substr($decoded, 12, 16);
        $ct = substr($decoded, 28);

        return openssl_decrypt($ct, 'aes-256-gcm', $this->key, OPENSSL_RAW_DATA, $iv, $tag);
    }
}
