<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Storage\ProtectCachedValues;

final readonly class DecryptCachedValue
{
    public function __construct(
        private EncryptedCache $encryptedCache,
    ) {
    }

    public function decrypt(string $encrypted): mixed
    {
        $decrypted = $this->encryptedCache->decrypt(encrypted: $encrypted);

        return json_decode($decrypted, true, 512, JSON_THROW_ON_ERROR);
    }
}
