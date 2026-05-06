<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Distribution\DistributeCachedValues;

final readonly class CachePartition
{
    public function __construct(
        public int $index,
        public CacheNodeId $cacheNodeId,
        /** @var list<CacheNodeId> */
        public array $replicaNodeIds = [],
    ) {
    }

    /**
     * @return list<CacheNodeId>
     */
    public function allNodeIds(): array
    {
        return [$this->cacheNodeId, ...$this->replicaNodeIds];
    }

    public function hasNode(CacheNodeId $cacheNodeId): bool
    {
        if ($cacheNodeId->toString() === $this->cacheNodeId->toString()) {
            return true;
        }

        return in_array($cacheNodeId->toString(), array_map(static fn (CacheNodeId $cacheNodeId) : string => $cacheNodeId->toString(), $this->replicaNodeIds), true);
    }
}
