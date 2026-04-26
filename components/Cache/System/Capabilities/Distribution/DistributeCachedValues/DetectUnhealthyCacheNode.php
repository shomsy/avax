<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\Distribution\DistributeCachedValues;

final class DetectUnhealthyCacheNode
{
    /** @var array<CacheNodeId, array<int, bool>> */
    private array $failureHistory = [];
    /** @var array<CacheNodeId, int> */
    private array $successCount = [];

    public function __construct(
        private int $failureThreshold = 3,
        private int $recoveryThreshold = 5,
        private int $checkIntervalSeconds = 30,
        array       $failureHistory = [],
        array       $successCount = []
    )
    {
        $this->failureHistory = $failureHistory;
        $this->successCount   = $successCount;
    }

    public function recordFailure(CacheNodeId $nodeId) : CacheNodeStatus
    {
        $nodeIdStr = $nodeId->toString();

        if (! isset($this->failureHistory[$nodeIdStr])) {
            $this->failureHistory[$nodeIdStr] = [];
        }

        $this->failureHistory[$nodeIdStr][] = true;
        $this->successCount[$nodeIdStr]     = 0;

        if (count($this->failureHistory[$nodeIdStr]) >= $this->failureThreshold) {
            return CacheNodeStatus::UNHEALTHY;
        }

        return CacheNodeStatus::HEALTHY;
    }

    public function recordSuccess(CacheNodeId $nodeId) : CacheNodeStatus
    {
        $nodeIdStr = $nodeId->toString();

        if (! isset($this->successCount[$nodeIdStr])) {
            $this->successCount[$nodeIdStr] = 0;
        }

        $this->successCount[$nodeIdStr]++;

        if (isset($this->failureHistory[$nodeIdStr])) {
            array_shift($this->failureHistory[$nodeIdStr]);

            if (count($this->failureHistory[$nodeIdStr]) === 0) {
                unset($this->failureHistory[$nodeIdStr]);
            }
        }

        if ($this->successCount[$nodeIdStr] >= $this->recoveryThreshold) {
            return CacheNodeStatus::HEALTHY;
        }

        return CacheNodeStatus::UNHEALTHY;
    }

    public function getStatus(CacheNodeId $nodeId) : CacheNodeStatus
    {
        $nodeIdStr = $nodeId->toString();

        if (isset($this->failureHistory[$nodeIdStr]) && count($this->failureHistory[$nodeIdStr]) >= $this->failureThreshold) {
            return CacheNodeStatus::UNHEALTHY;
        }

        return CacheNodeStatus::HEALTHY;
    }
}