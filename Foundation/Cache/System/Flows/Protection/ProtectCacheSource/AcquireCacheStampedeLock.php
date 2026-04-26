<?php

declare(strict_types=1);

namespace Avax\Cache\System\Flows\Protection\ProtectCacheSource;

use Avax\Cache\System\Capabilities\Source\ProtectCacheSource\CacheLock;
use Avax\Cache\System\Capabilities\Source\ProtectCacheSource\CacheLockStore;
use Avax\Cache\System\Capabilities\Source\ProtectCacheSource\CacheLockTimeout;
use Avax\Cache\System\Capabilities\Source\ProtectCacheSource\CacheLockWasNotAcquired;
use Avax\Cache\System\Foundation\Time\Clock;
use Avax\Cache\System\Foundation\Time\SystemClock;

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
    private int $waitTimeoutSeconds;
    private int $lockTtlSeconds;

    public function __construct(
        private CacheLockStore $lockStore,
        private Clock          $clock = new SystemClock(),
        int                    $waitTimeoutSeconds = 5,
        int                    $lockTtlSeconds = 30
    )
    {
        $this->waitTimeoutSeconds = $waitTimeoutSeconds;
        $this->lockTtlSeconds     = $lockTtlSeconds;
    }

    public function isLocked(string $key) : bool
    {
        $lock = new CacheLock(store: $this->lockStore);

        return $lock->isAcquired(key: $key);
    }

    public function waitForLock(string $key, int $maxWaitSeconds = 5) : bool
    {
        $lock     = new CacheLock(store: $this->lockStore, timeout: new CacheLockTimeout(seconds: $maxWaitSeconds));
        $deadline = $this->clock->now()->seconds + $maxWaitSeconds;

        while ( $this->clock->now()->seconds < $deadline ) {
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
            store  : $this->lockStore,
            timeout: new CacheLockTimeout(seconds: $this->waitTimeoutSeconds)
        );

        $acquiredAt = $this->clock->now()->seconds;
        $deadline   = $acquiredAt + $this->waitTimeoutSeconds;

        while ( $this->clock->now()->seconds < $deadline ) {
            if ($lock->acquire(key: $key, ttlSeconds: $this->lockTtlSeconds)) {
                return new StampedeLockGuard(lock: $lock, key: $key);
            }

            $owner = $this->lockStore->getOwner(key: $key);
            if ($owner !== null && $owner->isExpired(clock: $this->clock)) {
                $lock->release(key: $key);
                continue;
            }

            usleep(10_000);
        }

        throw new CacheLockWasNotAcquired(message: $key);
    }
}