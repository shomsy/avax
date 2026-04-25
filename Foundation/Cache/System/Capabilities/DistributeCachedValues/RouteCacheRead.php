<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\DistributeCachedValues;

use Avax\Cache\System\Capabilities\IdentifyCachedValues\CacheKey;

final readonly class RouteCacheRead
{
    public function __construct(
        private ChooseCacheNodeForKey $router
    ) {}

    public function route(CacheKey $key) : ?CacheNode
    {
        return $this->router->choose($key);
    }

    public function getPreferredNode(CacheKey $key, array $availableNodes) : ?CacheNode
    {
        $preferred = $this->router->choose($key);

        if ($preferred === null) {
            return $availableNodes[0] ?? null;
        }

        foreach ($availableNodes as $node) {
            if ($node->id->toString() === $preferred->id->toString()) {
                return $node;
            }
        }

        return $availableNodes[0] ?? null;
    }
}