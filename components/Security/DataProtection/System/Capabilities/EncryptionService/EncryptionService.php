<?php

declare(strict_types=1);

namespace Avax\Components\Security\DataProtection\System\Capabilities\EncryptionService;

use RuntimeException;

final class EncryptionService
{
    public function __construct(
        private readonly string $cipher = 'aes-256-gcm',
    ) {}

    /**
     * @param string $data
     * @param string $key
     *
     * @return array{ciphertext:string,iv:string,tag?:string}
     */
    public function encrypt(string $data, string $key) : array
    {
        $ivLength = openssl_cipher_iv_length(cipher_algo: $this->cipher);

        if ($ivLength === false) {
            throw new RuntimeException(message: "Unsupported cipher: {$this->cipher}");
        }

        $iv = openssl_random_pseudo_bytes(length: $ivLength);

        $tag        = '';
        $ciphertext = openssl_encrypt(
            data       : $data,
            cipher_algo: $this->cipher,
            passphrase : $key,
            options    : OPENSSL_RAW_DATA,
            iv         : $iv,
            tag        : $tag,
        );

        if ($ciphertext === false) {
            throw new RuntimeException(message: 'Encryption failed');
        }

        $result = [
            'ciphertext' => base64_encode(string: $ciphertext),
            'iv'         => base64_encode(string: $iv),
        ];

        if (! empty($tag)) {
            $result['tag'] = base64_encode(string: $tag);
        }

        return $result;
    }

    /**
     * @param string      $ciphertext
     * @param string      $key
     * @param string      $iv
     * @param string|null $tag
     *
     * @return string
     */
    public function decrypt(string $ciphertext, string $key, string $iv, string|null $tag = null) : string
    {
        $rawCiphertext = base64_decode(string: $ciphertext, strict: true);
        $rawIv         = base64_decode(string: $iv, strict: true);

        if ($rawCiphertext === false || $rawIv === false) {
            throw new RuntimeException(message: 'Invalid base64 data');
        }

        $rawTag = $tag !== null ? base64_decode(string: $tag, strict: true) : null;

        $decrypted = openssl_decrypt(
            data       : $rawCiphertext,
            cipher_algo: $this->cipher,
            passphrase : $key,
            options    : OPENSSL_RAW_DATA,
            iv         : $rawIv,
            tag        : is_string(value: $rawTag) ? $rawTag : '',
        );

        if ($decrypted === false) {
            throw new RuntimeException(message: 'Decryption failed');
        }

        return $decrypted;
    }
}
