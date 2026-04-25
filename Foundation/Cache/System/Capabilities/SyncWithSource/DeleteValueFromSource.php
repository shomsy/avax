<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\SyncWithSource;

use Avax\Cache\System\Capabilities\IdentifyCachedValues\CacheKey;

final readonly class DeleteValueFromSource
{
    public function __construct(
        private CacheSource $source
    ) {}

    public function deleteMany(iterable $keys) : int
    {
        $count = 0;

        foreach ($keys as $key) {
            $cacheKey = $key instanceof CacheKey ? $key : CacheKey::create($key);
            $this->delete($cacheKey);
            $count++;
        }

        return $count;
    }

    public function delete(CacheKey $key) : void
    {
        $sourceKey = CacheSourceKey::create($key->fullKey(), $key->namespace);
        $this->source->delete($sourceKey);
    }
}