<?php

declare(strict_types=1);

namespace Avax\Cache\System\Flows\Protection\ProtectCacheSource;

use Avax\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Cache\System\Capabilities\Source\ProtectCacheSource\CacheLock;
use Avax\Cache\System\Capabilities\Source\ProtectCacheSource\CacheLockStore;
use Avax\Cache\System\Capabilities\Source\ProtectCacheSource\CacheLockWasNotAcquired;

final readonly class StampedeLockGuard
{
    public function __construct(
        private CacheLock $lock,
        private string    $key
    ) {}

    public function release() : void
    {
        $this->lock->release(key: $this->key);
    }

    public function key() : string
    {
        return $this->key;
    }
}

final readonly class AcquireCacheStampedeLock
{
    public function __construct(
        private CacheLockStore $lockStore,
        private int            $waitTimeoutSeconds = 5,
        private int            $lockTtlSeconds = 30
    ) {}

    public function isLocked(string $key) : bool
    {
        $lock = new CacheLock(store: $this->lockStore);

        return $lock->isAcquired(key: $key);
    }

    public function waitForLock(string $key, int $maxWaitSeconds = 5) : bool
    {
        $lock      = new CacheLock(store: $this->lockStore);
        $startTime = hrtime(true);
        $deadline  = $startTime + ($maxWaitSeconds * 1_000_000_000);

        while ( hrtime(true) < $deadline ) {
            if ($lock->acquire(key: $key, ttlSeconds: $this->lockTtlSeconds)) {
                $lock->release(key: $key);

                return true;
            }

            usleep(10_000);
        }

        return false;
    }

    public function acquire(string $key) : StampedeLockGuard
    {
        $lock = new CacheLock(
            store: $this->lockStore
        );

        $startTime = hrtime(true);
        $deadline  = $startTime + ($this->waitTimeoutSeconds * 1_000_000_000);

        while ( hrtime(true) < $deadline ) {
            if ($lock->acquire(key: $key, ttlSeconds: $this->lockTtlSeconds)) {
                return new StampedeLockGuard(lock: $lock, key: $key);
            }

            usleep(10_000);
        }

        throw new CacheLockWasNotAcquired(
            message       : sprintf('Lock for key "%s" could not be acquired after %d seconds', $key, $this->waitTimeoutSeconds),
            key           : CacheKey::create(key: $key),
            timeoutSeconds: $this->waitTimeoutSeconds
        );
    }
}