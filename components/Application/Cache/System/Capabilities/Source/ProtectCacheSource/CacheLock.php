<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Source\ProtectCacheSource;

final readonly class CacheLock
{
    private string $owner;

    public function __construct(
        private CacheLockStore $store,
        string                 $owner = ''
    )
    {
        $this->owner = $owner === '' ? uniqid(more_entropy: true) : $owner;
    }

    public function acquire(string $key, int $ttlSeconds = 30) : bool
    {
        return $this->store->acquire(key: $key, owner: $this->owner, ttlSeconds: $ttlSeconds);
    }

    public function release(string $key) : void
    {
        $this->store->release(key: $key, owner: $this->owner);
    }

    public function isAcquired(string $key) : bool
    {
        return $this->store->isAcquired(key: $key);
    }

}