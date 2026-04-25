<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\ProtectCacheSource;

use Avax\Cache\System\Capabilities\IdentifyCachedValues\CacheKey;

final readonly class RequestCoalescing
{
    public function __construct(
        private CacheLockStore   $lockStore,
        private CacheLockTimeout $timeout = new CacheLockTimeout(5)
    ) {}

    public function execute(
        CacheKey  $key,
        callable  $loader,
        ?callable $onStale = null
    ) : mixed
    {
        $lock = new CacheLock($this->lockStore, $this->timeout);

        if ($lock->acquire($key->fullKey())) {
            try {
                return $loader();
            } finally {
                $lock->release($key->fullKey());
            }
        }

        if ($onStale !== null) {
            return $onStale();
        }

        usleep($this->timeout->inMilliseconds() * 1000);

        return $this->execute($key, $loader, $onStale);
    }

    public function tryAcquireLock(CacheKey $key) : bool
    {
        $lock = new CacheLock($this->lockStore, $this->timeout);

        return $lock->acquire($key->fullKey());
    }

    public function releaseLock(CacheKey $key) : void
    {
        $lock = new CacheLock($this->lockStore, $this->timeout);
        $lock->release($key->fullKey());
    }
}