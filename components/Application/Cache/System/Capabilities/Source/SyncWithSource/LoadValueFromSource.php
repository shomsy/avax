<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Source\SyncWithSource;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;

final readonly class LoadValueFromSource
{
    public function __construct(
        private CacheSource $cacheSource,
    ) {}

    public function loadOrFail(CacheKey $cacheKey) : mixed
    {
        $cacheSourceKey = CacheSourceKey::create(namespace: $cacheKey->namespace, cacheKey: $cacheKey->fullKey());

        if (! $this->cacheSource->exists($cacheSourceKey)) {
            throw new CacheSourceFailed(
                message  : sprintf('Source key "%s" does not exist', $cacheKey->fullKey()),
                sourceKey: $cacheSourceKey,
            );
        }

        return $this->cacheSource->load($cacheSourceKey);
    }

    public function load(CacheKey $cacheKey) : mixed
    {
        $cacheSourceKey = CacheSourceKey::create(namespace: $cacheKey->namespace, cacheKey: $cacheKey->fullKey());

        return $this->cacheSource->load($cacheSourceKey);
    }
}
