<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\DistributeCachedValues;

use Avax\Cache\System\Capabilities\IdentifyCachedValues\CacheKey;

final readonly class RouteCacheWrite
{
    public function __construct(
        private ChooseCacheNodeForKey $router
    ) {}

    public function routeToPrimary(CacheKey $key) : ?CacheNode
    {
        return $this->router->choose($key);
    }

    public function routeToAll(CacheKey $key, int $replicaCount) : array
    {
        return $this->routeToReplicas($key, $replicaCount);
    }

    public function routeToReplicas(CacheKey $key, int $replicaCount) : array
    {
        $primary = $this->router->choose($key);

        if ($primary === null) {
            return [];
        }

        return [$primary];
    }
}