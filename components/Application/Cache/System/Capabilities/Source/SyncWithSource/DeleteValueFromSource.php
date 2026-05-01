<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Source\SyncWithSource;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;

final readonly class DeleteValueFromSource
{
    public function __construct(
        CacheSource $source,
    )
    {
        $this->cacheSource = $source;
    }

    private CacheSource $cacheSource;

    public function deleteMany(iterable $keys) : int
    {
        $count = 0;

        foreach ($keys as $key) {
            $cacheKey = $key instanceof CacheKey ? $key : CacheKey::create(key: $key);
            $this->delete(key: $cacheKey);
            $count++;
        }

        return $count;
    }

    public function delete(CacheKey $key) : void
    {
        $cacheSourceKey = CacheSourceKey::create(key: $key->fullKey(), namespace: $key->namespace);
        $this->cacheSource->delete($cacheSourceKey);
    }
}
