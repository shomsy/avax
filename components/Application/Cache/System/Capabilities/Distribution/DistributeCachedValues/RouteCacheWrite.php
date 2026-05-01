<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Distribution\DistributeCachedValues;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;

final readonly class RouteCacheWrite
{
    public function __construct(
        private ChooseCacheNodeForKey $chooseCacheNodeForKey,
    ) {}

    public function routeToPrimary(CacheKey $key) : CacheNode|null
    {
        return $this->chooseCacheNodeForKey->choose(key: $key);
    }

    public function routeToAll(CacheKey $key, int $replicaCount) : array
    {
        return $this->routeToReplicas(key: $key, replicaCount: $replicaCount);
    }

    public function routeToReplicas(CacheKey $key, int $replicaCount = 1) : array
    {
        $primary = $this->chooseCacheNodeForKey->choose(key: $key);

        if ($primary === null) {
            return [];
        }

        return [$primary];
    }
}
