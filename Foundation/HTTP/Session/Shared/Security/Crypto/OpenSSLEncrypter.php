<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Shared\Security\Crypto;

use Avax\HTTP\Session\Shared\Contracts\Security\Encrypter;
use Avax\HTTP\Session\Shared\Exceptions\EncryptionException;

/**
 * OpenSSLEncrypter
 *
 * Standard AES-256-CBC implementation using PHP's OpenSSL extension.
 */
final class OpenSSLEncrypter implements Encrypter
{
    private const string CIPHER = 'AES-256-CBC';

    public function __construct(
        private readonly string $key
    ) {}

    public function encrypt(mixed $value) : string
    {
        $ivLength = openssl_cipher_iv_length(cipher_algo: self::CIPHER);
        $iv       = openssl_random_pseudo_bytes(length: $ivLength);

        $serialized = serialize(value: $value);

        $ciphertext = openssl_encrypt(
            data       : $serialized,
            cipher_algo: self::CIPHER,
            passphrase : $this->key,
            options    : OPENSSL_RAW_DATA,
            iv         : $iv
        );

        if ($ciphertext === false) {
            throw new EncryptionException(message: 'Encryption failed: OpenSSL error');
        }

        // Return combined IV + Ciphertext (base64 encoded)
        return base64_encode(string: $iv . $ciphertext);
    }

    public function decrypt(string $encrypted) : mixed
    {
        $data = base64_decode(string: $encrypted, strict: true);
        if ($data === false) {
            throw new EncryptionException(message: 'Decryption failed: Invalid base64 data');
        }

        $ivLength = openssl_cipher_iv_length(cipher_algo: self::CIPHER);

        if (strlen(string: $data) < $ivLength) {
            throw new EncryptionException(message: 'Decryption failed: Payload too short');
        }

        $iv         = substr(string: $data, offset: 0, length: $ivLength);
        $ciphertext = substr(string: $data, offset: $ivLength);

        $serialized = openssl_decrypt(
            data       : $ciphertext,
            cipher_algo: self::CIPHER,
            passphrase : $this->key,
            options    : OPENSSL_RAW_DATA,
            iv         : $iv
        );

        if ($serialized === false) {
            throw new EncryptionException(message: 'Decryption failed: OpenSSL error');
        }

        // Secure unserialization (allowed classes could be restricted if needed)
        // using allowed_classes: true for now to match broad usage
        return unserialize(data: $serialized);
    }
}
