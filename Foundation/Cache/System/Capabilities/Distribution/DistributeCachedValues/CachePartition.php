<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\Distribution\DistributeCachedValues;

final readonly class CachePartition
{
    public function __construct(
        public int         $index,
        public CacheNodeId $primaryNodeId,
        /** @var array<CacheNodeId> */
        public array       $replicaNodeIds = []
    ) {}

    public function allNodeIds() : array
    {
        return [$this->primaryNodeId, ...$this->replicaNodeIds];
    }

    public function hasNode(CacheNodeId $nodeId) : bool
    {
        return $nodeId->toString() === $this->primaryNodeId->toString()
            || in_array($nodeId->toString(), array_map(fn ($id) => $id->toString(), $this->replicaNodeIds), true);
    }
}