<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Distribution\DistributeCachedValues;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;

final readonly class RouteCacheWrite
{
    public function __construct(
        private ChooseCacheNodeForKey $chooseCacheNodeForKey,
    ) {
    }

    public function routeToPrimary(CacheKey $cacheKey): ?CacheNode
    {
        return $this->chooseCacheNodeForKey->choose(cacheKey: $cacheKey);
    }

    /**
     * @return list<CacheNode>
     */
    public function routeToAll(CacheKey $cacheKey): array
    {
        return $this->routeToReplicas(cacheKey: $cacheKey);
    }

    /**
     * @return list<CacheNode>
     */
    public function routeToReplicas(CacheKey $cacheKey): array
    {
        $primary = $this->chooseCacheNodeForKey->choose(cacheKey: $cacheKey);

        if (! $primary instanceof CacheNode) {
            return [];
        }

        return [$primary];
    }
}
