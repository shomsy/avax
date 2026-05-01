<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Source\ProtectCacheSource;

final readonly class CacheLock
{
    private string $owner;

    public function __construct(
        private CacheLockStore $cacheLockStore,
        string $owner = '',
    ) {
        $this->owner = $owner === '' ? uniqid(more_entropy: true) : $owner;
    }

    public function acquire(string $key, int $ttlSeconds = 30): bool
    {
        return $this->cacheLockStore->acquire(cacheKey $key, ttlSeconds: $ttlSeconds, owner: $this->owner)
    }

    public function release(string $key): void
    {
        $this->cacheLockStore->release(key: $key, owner: $this->owner);
    }

    public function isAcquired(string $key): bool
    {
        return $this->cacheLockStore->isAcquired(key: $key);
    }
}
