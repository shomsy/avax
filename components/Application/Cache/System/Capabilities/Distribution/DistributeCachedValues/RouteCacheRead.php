<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Distribution\DistributeCachedValues;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;

final readonly class RouteCacheRead
{
    public function __construct(
        private ChooseCacheNodeForKey $chooseCacheNodeForKey,
    ) {}

    public function route(CacheKey $cacheKey) : CacheNode|null
    {
        return $this->chooseCacheNodeForKey->choose(key: $cacheKey);
    }

    public function getPreferredNode(CacheKey $cacheKey, array $availableNodes) : CacheNode|null
    {
        $preferred = $this->chooseCacheNodeForKey->choose(key: $cacheKey);

        if (! $preferred instanceof CacheNode) {
            return $availableNodes[0] ?? null;
        }

        foreach ($availableNodes as $availableNode) {
            if ($availableNode->id->toString() === $preferred->id->toString()) {
                return $availableNode;
            }
        }

        return $availableNodes[0] ?? null;
    }
}
