<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Distribution;

use Avax\Components\Application\Cache\System\Capabilities\Distribution\DistributeCachedValues\CacheNodeStatus;
use Avax\Components\Application\Cache\System\Foundation\Time\Timestamp;

/**
 * Value object representing the health record of a cache node.
 */
final readonly class NodeHealthRecord
{
    public CacheNodeStatus $cacheNodeStatus;

    public function __construct(
        public string          $nodeId,
        public CacheNodeStatus $cacheNodeStatus,
        public Timestamp       $lastCheck,
        public int             $consecutiveFailures,
        public int             $consecutiveSuccesses,
        public Timestamp|null  $lastFailure,
        public Timestamp|null  $lastSuccess,
    )
    {
        $this->cacheNodeStatus = $cacheNodeStatus;
    }

    /**
     * Create a new record with an updated last check time.
     */
    public function withLastCheck(Timestamp $timestamp) : self
    {
        return new self(
            nodeId              : $this->nodeId,
            status              : $this->cacheNodeStatus,
            lastCheck           : $timestamp,
            consecutiveFailures : $this->consecutiveFailures,
            consecutiveSuccesses: $this->consecutiveSuccesses,
            lastFailure         : $this->lastFailure,
            lastSuccess         : $this->lastSuccess,
        );
    }

    /**
     * Check if the node was checked within the given number of seconds.
     */
    public function wasCheckedWithin(int $seconds) : bool
    {
        $now = Timestamp::now();
        $duration = $now->difference($this->lastCheck);

        return $duration->seconds <= $seconds;
    }

    /**
     * Convert to array representation.
     *
     * @return array{
     *     nodeId: string,
     *     status: string,
     *     lastCheck: int,
     *     consecutiveFailures: int,
     *     consecutiveSuccesses: int,
     *     lastFailure: int|null,
     *     lastSuccess: int|null
     * }
     */
    public function toArray() : array
    {
        return [
            'nodeId'               => $this->nodeId,
            'status' => $this->cacheNodeStatus->value,
            'lastCheck'            => $this->lastCheck->seconds,
            'consecutiveFailures'  => $this->consecutiveFailures,
            'consecutiveSuccesses' => $this->consecutiveSuccesses,
            'lastFailure'          => $this->lastFailure?->seconds,
            'lastSuccess'          => $this->lastSuccess?->seconds,
        ];
    }
}
