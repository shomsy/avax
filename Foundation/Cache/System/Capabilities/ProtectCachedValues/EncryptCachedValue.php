<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\ProtectCachedValues;

final readonly class EncryptCachedValue
{
    public function __construct(
        private EncryptedCache $encryptedCache
    ) {}

    public function encrypt(mixed $value) : string
    {
        $serialized = serialize($value);

        return $this->encryptedCache->encrypt($serialized);
    }

    public function decrypt(string $encrypted) : mixed
    {
        $decrypted = $this->encryptedCache->decrypt($encrypted);

        return unserialize($decrypted);
    }
}