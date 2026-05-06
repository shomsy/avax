<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Source\SyncWithSource;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;

final readonly class DeleteValueFromSource
{
    public function __construct(private CacheSource $cacheSource)
    {
    }

    /**
     * @param iterable<CacheKey|string> $keys
     */
    public function deleteMany(iterable $keys): int
    {
        $count = 0;

        foreach ($keys as $key) {
            $cacheKey = $key instanceof CacheKey ? $key : CacheKey::create(key: $key);
            $this->delete(cacheKey: $cacheKey);
            $count++;
        }

        return $count;
    }

    public function delete(CacheKey $cacheKey): void
    {
        $cacheSourceKey = CacheSourceKey::create(key: $cacheKey->fullKey(), namespace: $cacheKey->namespace);
        $this->cacheSource->delete($cacheSourceKey);
    }
}
