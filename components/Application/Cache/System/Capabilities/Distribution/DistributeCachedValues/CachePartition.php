<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Distribution\DistributeCachedValues;

final readonly class CachePartition
{
    public function __construct(
        public int $index,
        public CacheNodeId $cacheNodeId,
        /** @var array<CacheNodeId> */
        public array $replicaNodeIds = [],
    ) {
    }

    public function allNodeIds(): array
    {
        return [$this->cacheNodeId, ...$this->replicaNodeIds];
    }

    public function hasNode(CacheNodeId $cacheNodeId): bool
    {
        if ($cacheNodeId->toString() === $this->cacheNodeId->toString()) {
            return true;
        }

        return in_array($cacheNodeId->toString(), array_map(static fn ($id): string => $id->toString(), $this->replicaNodeIds), true);
    }
}
