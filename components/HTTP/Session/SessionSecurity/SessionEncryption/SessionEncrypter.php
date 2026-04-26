<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\SessionSecurity\SessionEncryption;

use Random\RandomException;

final class SessionEncrypter
{
    private string $key;

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    /**
     * @throws RandomException
     */
    public function encrypt(mixed $value) : string
    {
        $json      = json_encode($value);
        $iv        = random_bytes(16);
        $encrypted = openssl_encrypt($json, 'aes-256-cbc', $this->key, 0, $iv);

        return base64_encode($iv . $encrypted);
    }

    public function decrypt(string $data) : mixed
    {
        $decoded   = base64_decode($data);
        $iv        = substr($decoded, 0, 16);
        $encrypted = substr($decoded, 16);

        $decrypted = openssl_decrypt($encrypted, 'aes-256-cbc', $this->key, 0, $iv);

        return json_decode($decrypted, true);
    }
}