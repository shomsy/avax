<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Source\SyncWithSource;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;

final readonly class DeleteValueFromSource
{
    public function __construct(
        private CacheSource $source
    ) {}

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
        $sourceKey = CacheSourceKey::create(key: $key->fullKey(), namespace: $key->namespace);
        $this->source->delete(key: $sourceKey);
    }
}