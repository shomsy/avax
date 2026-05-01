<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Source\SyncWithSource;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Throwable;

final class DeferredSourceWrite
{
    /** @var array<string, array{key: CacheSourceKey, value: mixed}> */
    private array $queue = [];

    private readonly CacheSource $cacheSource;

    public function __construct(
        CacheSource $source,
    )
    {
        $this->cacheSource = $source;
    }

    public function queueFromCacheKey(CacheKey $key, mixed $value) : void
    {
        $cacheSourceKey = CacheSourceKey::create(key: $key->fullKey(), namespace: $key->namespace);
        $this->queue(key: $cacheSourceKey, value: $value);
    }

    public function queue(CacheSourceKey $key, mixed $value) : void
    {
        $this->queue[$key->fullKey()] = [
            'key'   => $key,
            'value' => $value,
        ];
    }

    public function flush() : int
    {
        $count = count($this->queue);

        foreach ($this->queue as $queuedWrite) {
            try {
                $this->cacheSource->write($queuedWrite['key'], $queuedWrite['value']);
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
