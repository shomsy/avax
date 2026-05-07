<?php

declare(strict_types=1);

namespace Avax\Components\Security\DataProtection\System\Capabilities\KeyManager;

final class KeyManager
{
    /** @var array<string, string> */
    private array $keys = [];

    public function setKey(string $keyId, string $key) : void
    {
        $this->keys[$keyId] = $key;
    }

    public function getKey(string $keyId) : string|null
    {
        return $this->keys[$keyId] ?? null;
    }

    public function rotateKey(string $oldKeyId, string $newKeyId) : string
    {
        $newKey = $this->generateKey(keyId: $newKeyId);

        return $newKey;
    }

    public function generateKey(string $keyId = 'default') : string
    {
        $key                = sodium_crypto_secretbox_keygen();
        $this->keys[$keyId] = $key;

        return $key;
    }

    public function activeKeyId() : string
    {
        return empty($this->keys) ? 'default' : array_key_last(array: $this->keys);
    }
}
