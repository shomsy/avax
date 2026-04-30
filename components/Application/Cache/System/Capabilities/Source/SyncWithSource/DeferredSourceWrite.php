<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Source\SyncWithSource;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Throwable;

final class DeferredSourceWrite
{
    /** @var array<CacheSourceKey, mixed> */
    private array $queue = [];

    public function __construct(
        private readonly CacheSource $cacheSource,
    ) {}

    public function queueFromCacheKey(CacheKey $cacheKey, mixed $value) : void
    {
        $cacheSourceKey = CacheSourceKey::create(key: $cacheKey->fullKey(), namespace: $cacheKey->namespace);
        $this->queue(key: $cacheSourceKey, value: $value);
    }

    public function queue(CacheSourceKey $cacheSourceKey) : void
    {
        $this->queue[$cacheSourceKey->fullKey()] = $cacheSourceKey;
    }

    public function flush() : int
    {
        $count = count($this->queue);

        foreach ($this->queue as $sourceKey) {
            try {
                $this->cacheSource->write(key: $sourceKey, value: null);
            } catch (Throwable) {
            }
        }

        $this->queue = [];

        return $count;
    }

    public function clear() : void
    {
        $this->queue = [];
    }

    public function pendingCount() : int
    {
        return count($this->queue);
    }
}
