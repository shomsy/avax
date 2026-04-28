<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\Distribution\DistributeCachedValues;

use Avax\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;

final readonly class RouteCacheWrite
{
    public function __construct(
        private ChooseCacheNodeForKey $router
    ) {}

    public function routeToPrimary(CacheKey $key) : CacheNode|null
    {
        return $this->router->choose(key: $key);
    }

    public function routeToAll(CacheKey $key, int $replicaCount) : array
    {
        return $this->routeToReplicas(key: $key, replicaCount: $replicaCount);
    }

    public function routeToReplicas(CacheKey $key, int $replicaCount) : array
    {
        $primary = $this->router->choose(key: $key);

        if ($primary === null) {
            return [];
        }

        return [$primary];
    }
}