<?php

declare(strict_types=1);

namespace Avax\Components\Secrets\System\Capabilities\Stores;

use RuntimeException;

interface SecretStore
{
    public function get(string $key) : string;

    public function set(string $key, string $value) : void;

    public function has(string $key) : bool;

    public function forget(string $key) : void;
}

final class InMemorySecretStore implements SecretStore
{
    /** @var array<string, string> */
    private array $secrets = [];

    public function get(string $key) : string
    {
        return $this->secrets[$key] ?? throw new RuntimeException("Secret '{$key}' not found");
    }

    public function set(string $key, string $value) : void
    {
        $this->secrets[$key] = $value;
    }

    public function has(string $key) : bool
    {
        return isset($this->secrets[$key]);
    }

    public function forget(string $key) : void
    {
        unset($this->secrets[$key]);
    }
}

final class EncryptedSecretStore implements SecretStore
{
    private SecretStore $inner;
    private string      $encryptionKey;

    public function __construct(SecretStore $inner, string $encryptionKey)
    {
        $this->inner         = $inner;
        $this->encryptionKey = $encryptionKey;
    }

    public function get(string $key) : string
    {
        $encrypted = $this->inner->get($key);

        return $this->decrypt($encrypted);
    }

    private function decrypt(string $value) : string
    {
        $data      = base64_decode($value);
        $iv        = substr($data, 0, 16);
        $tag       = substr($data, 16, 16);
        $encrypted = substr($data, 32);

        return openssl_decrypt($encrypted, 'aes-256-gcm', $this->encryptionKey, 0, $iv, $tag);
    }

    public function set(string $key, string $value) : void
    {
        $encrypted = $this->encrypt($value);
        $this->inner->set($key, $encrypted);
    }

    private function encrypt(string $value) : string
    {
        $iv        = random_bytes(16);
        $encrypted = openssl_encrypt($value, 'aes-256-gcm', $this->encryptionKey, 0, $iv, $tag);

        return base64_encode($iv . $tag . $encrypted);
    }

    public function has(string $key) : bool
    {
        return $this->inner->has($key);
    }

    public function forget(string $key) : void
    {
        $this->inner->forget($key);
    }
}