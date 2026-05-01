<?php

declare(strict_types=1);

namespace Avax\Components\Security\Secrets\System\Capabilities\Stores;

use Avax\Components\Security\Secrets\System\Capabilities\Encryption\SecretEncrypter;

final readonly class EncryptedSecretStore implements SecretStore
{
    private SecretEncrypter $encrypter;

    public function __construct(
        private SecretStore $inner,
        string              $encryptionKey,
    )
    {
        $this->encrypter = new SecretEncrypter(encryptionKey: $encryptionKey);
    }

    public function get(string $key) : string
    {
        return $this->encrypter->decrypt($this->inner->get($key));
    }

    public function set(string $key, string $value) : void
    {
        $this->inner->set($key, $this->encrypter->encrypt($value));
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
