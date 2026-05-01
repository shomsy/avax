<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Flows\Protection\ProtectCacheSource;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Components\Application\Cache\System\Capabilities\Source\ProtectCacheSource\CacheLock;
use Avax\Components\Application\Cache\System\Capabilities\Source\ProtectCacheSource\CacheLockStore;
use Avax\Components\Application\Cache\System\Capabilities\Source\ProtectCacheSource\CacheLockWasNotAcquired;

final readonly class AcquireCacheStampedeLock
{
    public function __construct(
        private CacheLockStore $cacheLockStore,
        private int $waitTimeoutSeconds = 5,
        private int $lockTtlSeconds = 30,
    ) {
    }

    public function isLocked(string $key): bool
    {
        $cacheLock = new CacheLock(cacheLockStore: $this->cacheLockStore);

        return $cacheLock->isAcquired(key: $key);
    }

    public function waitForLock(string $key, int $maxWaitSeconds = 5): bool
    {
        $cacheLock = new CacheLock(cacheLockStore: $this->cacheLockStore);
        $startTime = hrtime(true);
        $deadline  = $startTime + ($maxWaitSeconds * 1_000_000_000);

        while (hrtime(true) < $deadline) {
            if ($cacheLock->acquire(key: $key, ttlSeconds: $this->lockTtlSeconds)) {
                $cacheLock->release(key: $key);

                return true;
            }

            usleep(10_000);
        }

        return false;
    }

    public function acquire(string $key): StampedeLockGuard
    {
        $cacheLock = new CacheLock(
            cacheLockStore: $this->cacheLockStore,
        );

        $startTime = hrtime(true);
        $deadline  = $startTime + ($this->waitTimeoutSeconds * 1_000_000_000);

        while (hrtime(true) < $deadline) {
            if ($cacheLock->acquire(key: $key, ttlSeconds: $this->lockTtlSeconds)) {
                return new StampedeLockGuard(cacheLock: $cacheLock, key: $key);
            }

            usleep(10_000);
        }

        throw new CacheLockWasNotAcquired(
            message       : sprintf('Lock for key "%s" could not be acquired after %d seconds', $key, $this->waitTimeoutSeconds),
            key           : CacheKey::create(key: $key),
            timeoutSeconds: $this->waitTimeoutSeconds,
        );
    }
}
