<?php

declare(strict_types=1);

namespace Avax\Components\Security\System\Secrets\Capabilities\Stores;

use Avax\Components\Security\System\Secrets\Capabilities\Encryption\SecretEncrypter;

final readonly class EncryptedSecretStore implements SecretStore
{
    private SecretEncrypter $secretEncrypter;

    public function __construct(
        private SecretStore $secretStore,
        string              $encryptionKey,
    )
    {
        $this->secretEncrypter = new SecretEncrypter(encryptionKey: $encryptionKey);
    }

    public function get(string $key) : string
    {
        return $this->secretEncrypter->decrypt($this->secretStore->get($key));
    }

    public function set(string $key, string $value) : void
    {
        $this->secretStore->set($key, $this->secretEncrypter->encrypt($value));
    }

    public function has(string $key) : bool
    {
        return $this->secretStore->has($key);
    }

    public function forget(string $key) : void
    {
        $this->secretStore->forget($key);
    }
}
