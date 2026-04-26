<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\Source\ProtectCacheSource;

final readonly class CacheLock
{
    public function __construct(
        private CacheLockStore   $store,
        private CacheLockTimeout $timeout = new CacheLockTimeout(seconds: 5)
    ) {}

    public function acquire(string $key, int $ttlSeconds = 30) : bool
    {
        return $this->store->acquire(key: $key, ttlSeconds: $ttlSeconds);
    }

    public function release(string $key) : void
    {
        $this->store->release(key: $key);
    }

    public function isAcquired(string $key) : bool
    {
        return $this->store->isAcquired(key: $key);
    }

    public function withTimeout(CacheLockTimeout $timeout) : self
    {
        return new self(store: $this->store, timeout: $timeout);
    }
}