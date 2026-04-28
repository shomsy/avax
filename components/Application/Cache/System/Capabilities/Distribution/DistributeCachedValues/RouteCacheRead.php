<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Distribution\DistributeCachedValues;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;

final readonly class RouteCacheRead
{
    public function __construct(
        private ChooseCacheNodeForKey $router
    ) {}

    public function route(CacheKey $key) : CacheNode|null
    {
        return $this->router->choose(key: $key);
    }

    public function getPreferredNode(CacheKey $key, array $availableNodes) : CacheNode|null
    {
        $preferred = $this->router->choose(key: $key);

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