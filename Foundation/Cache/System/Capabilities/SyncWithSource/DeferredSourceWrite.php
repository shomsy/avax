<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\SyncWithSource;

use Avax\Cache\System\Capabilities\IdentifyCachedValues\CacheKey;
use Throwable;

final class DeferredSourceWrite
{
    /** @var array<CacheSourceKey, mixed> */
    private array $queue = [];

    public function __construct(
        private CacheSource $source
    ) {}

    public function queueFromCacheKey(CacheKey $key, mixed $value) : void
    {
        $sourceKey = CacheSourceKey::create($key->fullKey(), $key->namespace);
        $this->queue($sourceKey, $value);
    }

    public function queue(CacheSourceKey $key, mixed $value) : void
    {
        $this->queue[$key->fullKey()] = $key;
    }

    public function flush() : int
    {
        $count = count($this->queue);

        foreach ($this->queue as $sourceKey) {
            try {
                $this->source->write($sourceKey, null);
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