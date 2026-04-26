<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\Distribution\DistributeCachedValues;

use Avax\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;

final readonly class ChooseCacheNodeForKey
{
    public function __construct(
        private ConsistentHashRing $ring
    ) {}

    public function choose(CacheKey $key) : CacheNode|null
    {
        return $this->ring->getNodeForKey(key: $key);
    }

    public function chooseForPartition(int $partitionIndex) : CacheNode|null
    {
        return $this->ring->getNodeForPartition(partitionIndex: $partitionIndex);
    }
}