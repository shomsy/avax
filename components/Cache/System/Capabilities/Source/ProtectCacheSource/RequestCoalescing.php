<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\Source\ProtectCacheSource;

use Avax\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;

final readonly class RequestCoalescing
{
    public function __construct(
        private CacheLockStore   $lockStore,
        private CacheLockTimeout $timeout = new CacheLockTimeout(seconds: 5)
    ) {}

    public function execute(
        CacheKey      $key,
        callable      $loader,
        callable|null $onStale = null
    ) : mixed
    {
        $lock = new CacheLock(store: $this->lockStore, timeout: $this->timeout);

        if ($lock->acquire(key: $key->fullKey())) {
            try {
                return $loader();
            } finally {
                $lock->release(key: $key->fullKey());
            }
        }

        if ($onStale !== null) {
            return $onStale();
        }

        usleep($this->timeout->inMilliseconds() * 1000);

        return $this->execute(key: $key, loader: $loader, onStale: $onStale);
    }

    public function tryAcquireLock(CacheKey $key) : bool
    {
        $lock = new CacheLock(store: $this->lockStore, timeout: $this->timeout);

        return $lock->acquire(key: $key->fullKey());
    }

    public function releaseLock(CacheKey $key) : void
    {
        $lock = new CacheLock(store: $this->lockStore, timeout: $this->timeout);
        $lock->release(key: $key->fullKey());
    }
}