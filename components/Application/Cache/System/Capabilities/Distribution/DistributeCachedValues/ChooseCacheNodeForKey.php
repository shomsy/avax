<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Distribution\DistributeCachedValues;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;

final readonly class ChooseCacheNodeForKey
{
    public function __construct(
        private ConsistentHashRing $consistentHashRing,
    ) {
    }

    public function choose(CacheKey $cacheKey): ?CacheNode
    {
        return $this->consistentHashRing->getNodeForKey(cacheKey: $cacheKey);
    }

    public function chooseForPartition(int $partitionIndex): ?CacheNode
    {
        return $this->consistentHashRing->getNodeForPartition(partitionIndex: $partitionIndex);
    }
}
