<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Distribution\DistributeCachedValues;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;

final readonly class RouteCacheRead
{
    public function __construct(
        private ChooseCacheNodeForKey $chooseCacheNodeForKey,
    ) {}

    public function route(CacheKey $key) : CacheNode|null
    {
        return $this->chooseCacheNodeForKey->choose(key: $key);
    }

    public function getPreferredNode(CacheKey $key, array $availableNodes) : CacheNode|null
    {
        $preferred = $this->chooseCacheNodeForKey->choose(key: $key);

        if ($preferred === null) {
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
