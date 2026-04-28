<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\Distribution\DistributeCachedValues;

use Avax\Cache\System\Capabilities\Observability\IdentifyCachedValues\CachePartitionKey;
use Stringable;

final readonly class CacheNode implements Stringable
{
    public function __construct(
        public CacheNodeId     $id,
        public CacheNodeStatus $status = CacheNodeStatus::HEALTHY,
        public float           $weight = 1.0
    ) {}

    public static function create(string $id, CacheNodeStatus $status = CacheNodeStatus::HEALTHY) : self
    {
        return new self(
            id    : CacheNodeId::from(id: $id),
            status: $status
        );
    }

    public function isHealthy() : bool
    {
        return $this->status === CacheNodeStatus::HEALTHY;
    }

    public function withStatus(CacheNodeStatus $status) : self
    {
        return new self(id: $this->id, status: $status, weight: $this->weight);
    }

    public function withWeight(float $weight) : self
    {
        return new self(id: $this->id, status: $this->status, weight: $weight);
    }

    public function __toString() : string
    {
        return $this->id->toString();
    }
}