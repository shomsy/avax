<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Distribution\DistributeCachedValues;

use Override;
use Stringable;

final readonly class CacheNode implements Stringable
{
    public CacheNodeId $id;

    public CacheNodeStatus $status;

    public function __construct(
        public CacheNodeId     $cacheNodeId,
        public CacheNodeStatus $cacheNodeStatus = CacheNodeStatus::HEALTHY,
        public float           $weight = 1.0,
    )
    {
        $this->id     = $cacheNodeId;
        $this->status = $cacheNodeStatus;
    }

    public static function create(string $id, CacheNodeStatus $status = CacheNodeStatus::HEALTHY) : self
    {
        return new self(
            cacheNodeId    : CacheNodeId::from(id: $id),
            cacheNodeStatus: $status,
        );
    }

    public function isHealthy() : bool
    {
        return $this->cacheNodeStatus === CacheNodeStatus::HEALTHY;
    }

    public function withStatus(CacheNodeStatus $status) : self
    {
        return new self(cacheNodeId: $this->cacheNodeId, cacheNodeStatus: $status, weight: $this->weight);
    }

    public function withWeight(float $weight) : self
    {
        return new self(cacheNodeId: $this->cacheNodeId, cacheNodeStatus: $this->cacheNodeStatus, weight: $weight);
    }

    #[Override]
    public function __toString() : string
    {
        return (string) $this->cacheNodeId->toString();
    }
}
