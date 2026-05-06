<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Source\ProtectCacheSource;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;

final readonly class RequestCoalescing
{
    public function __construct(
        private CacheLockStore $cacheLockStore,
        private CacheLockTimeout $cacheLockTimeout = new CacheLockTimeout(seconds: 5),
    ) {
    }

    public function execute(
        CacheKey $cacheKey,
        callable $loader,
        ?callable $onStale = null,
    ): mixed {
        $cacheLock = new CacheLock(cacheLockStore: $this->cacheLockStore);

        if ($cacheLock->acquire(key: $cacheKey->fullKey(), ttlSeconds: $this->cacheLockTimeout->seconds)) {
            try {
                return $loader();
            } finally {
                $cacheLock->release(key: $cacheKey->fullKey());
            }
        }

        if ($onStale !== null) {
            return $onStale();
        }

        usleep($this->cacheLockTimeout->inMilliseconds() * 1000);

        return $this->execute(cacheKey: $cacheKey, loader: $loader, onStale: $onStale);
    }

    public function tryAcquireLock(CacheKey $cacheKey): bool
    {
        $cacheLock = new CacheLock(cacheLockStore: $this->cacheLockStore);

        return $cacheLock->acquire(key: $cacheKey->fullKey(), ttlSeconds: $this->cacheLockTimeout->seconds);
    }

    public function releaseLock(CacheKey $cacheKey): void
    {
        $cacheLock = new CacheLock(cacheLockStore: $this->cacheLockStore);
        $cacheLock->release(key: $cacheKey->fullKey());
    }
}
