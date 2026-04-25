<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\DistributeCachedValues;

use Avax\Cache\System\Capabilities\IdentifyCachedValues\CacheKey;

final readonly class ChooseCacheNodeForKey
{
    public function __construct(
        private ConsistentHashRing $ring
    ) {}

    public function choose(CacheKey $key) : ?CacheNode
    {
        return $this->ring->getNodeForKey($key);
    }

    public function chooseForPartition(int $partitionIndex) : ?CacheNode
    {
        return $this->ring->getNodeForPartition($partitionIndex);
    }
}