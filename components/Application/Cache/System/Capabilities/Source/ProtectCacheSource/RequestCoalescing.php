<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Source\ProtectCacheSource;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;

final readonly class RequestCoalescing
{
    public function __construct(
        private CacheLockStore   $cacheLockStore,
        private CacheLockTimeout $cacheLockTimeout = new CacheLockTimeout(seconds: 5),
    ) {}

    public function execute(
        CacheKey  $cacheKey,
        callable  $loader,
        ?callable $onStale = null,
    ) : mixed
    {
        $cacheLock = new CacheLock(store: $this->cacheLockStore, timeout: $this->cacheLockTimeout);

        if ($cacheLock->acquire(key: $cacheKey->fullKey())) {
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

        return $this->execute(key: $cacheKey, loader: $loader, onStale: $onStale);
    }

    public function tryAcquireLock(CacheKey $cacheKey) : bool
    {
        $cacheLock = new CacheLock(store: $this->cacheLockStore, timeout: $this->cacheLockTimeout);

        return $cacheLock->acquire(key: $cacheKey->fullKey());
    }

    public function releaseLock(CacheKey $cacheKey) : void
    {
        $cacheLock = new CacheLock(store: $this->cacheLockStore, timeout: $this->cacheLockTimeout);
        $cacheLock->release(key: $cacheKey->fullKey());
    }
}
