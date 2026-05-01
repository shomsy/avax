<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Distribution\DistributeCachedValues;

final class DetectUnhealthyCacheNode
{
    public function __construct(
        private readonly int $failureThreshold = 3,
        private readonly int $recoveryThreshold = 5,
        /** @var array<string, array<int, bool>> */
        private array $failureHistory = [],
        /** @var array<string, int> */
        private array $successCount = [],
    ) {
    }

    public function recordFailure(CacheNodeId $cacheNodeId) : CacheNodeStatus
    {
        $nodeIdStr = $cacheNodeId->toString();

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

    public function recordSuccess(CacheNodeId $cacheNodeId) : CacheNodeStatus
    {
        $nodeIdStr = $cacheNodeId->toString();

        if (! isset($this->successCount[$nodeIdStr])) {
            $this->successCount[$nodeIdStr] = 0;
        }

        $this->successCount[$nodeIdStr]++;

        if (isset($this->failureHistory[$nodeIdStr])) {
            array_shift($this->failureHistory[$nodeIdStr]);

            if ($this->failureHistory[$nodeIdStr] === []) {
                unset($this->failureHistory[$nodeIdStr]);
            }
        }

        if ($this->successCount[$nodeIdStr] >= $this->recoveryThreshold) {
            return CacheNodeStatus::HEALTHY;
        }

        return CacheNodeStatus::UNHEALTHY;
    }

    public function getStatus(CacheNodeId $cacheNodeId) : CacheNodeStatus
    {
        $nodeIdStr = $cacheNodeId->toString();

        if (isset($this->failureHistory[$nodeIdStr]) && count($this->failureHistory[$nodeIdStr]) >= $this->failureThreshold) {
            return CacheNodeStatus::UNHEALTHY;
        }

        return CacheNodeStatus::HEALTHY;
    }
}
