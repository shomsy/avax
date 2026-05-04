<?php

declare(strict_types=1);

namespace Avax\Components\Cache\System\Capabilities\Distribution;

use Avax\Components\Cache\System\Capabilities\Distribution\DistributeCachedValues\CacheNodeStatus;
use Avax\Components\Application\DateTime\System\PublicSurface\Clock;
use Avax\Components\Application\DateTime\System\PublicSurface\SystemClock;

/**
 * Health checking for cache nodes.
 *
 * Tracks health status, last check time, and consecutive failures
 * for each node in the distributed cache cluster.
 */
final class CacheNodeHealth
{
    /** @var array<string, NodeHealthRecord> */
    private array $records = [];

    public function __construct(
        private readonly Clock $clock = new SystemClock(),
        private readonly int $failureThreshold = 3,
        private readonly int $recoveryThreshold = 5,
    ) {
    }

    /**
     * Check the health of a specific node.
     *
     * Returns the current health status and updates the last check time.
     */
    public function check(CacheNode $cacheNode): NodeHealthRecord
    {
        $nodeId = $cacheNode->id;
        $now    = $this->clock->now();

        if (! isset($this->records[$nodeId])) {
            $this->records[$nodeId] = new NodeHealthRecord(
                nodeId              : $nodeId,
                cacheNodeStatus     : $cacheNode->cacheNodeStatus,
                lastCheck           : $now,
                consecutiveFailures : 0,
                consecutiveSuccesses: 0,
                lastFailure         : null,
                lastSuccess         : $now,
            );

            return $this->records[$nodeId];
        }

        $record                 = $this->records[$nodeId];
        $this->records[$nodeId] = $record->withLastCheck($now);

        return $this->records[$nodeId];
    }

    /**
     * Mark a node as healthy after a successful health check.
     */
    public function markHealthy(CacheNode $cacheNode): NodeHealthRecord
    {
        $nodeId = $cacheNode->id;
        $now    = $this->clock->now();

        if (! isset($this->records[$nodeId])) {
            $this->records[$nodeId] = new NodeHealthRecord(
                nodeId              : $nodeId,
                cacheNodeStatus     : CacheNodeStatus::HEALTHY,
                lastCheck           : $now,
                consecutiveFailures : 0,
                consecutiveSuccesses: 1,
                lastFailure         : null,
                lastSuccess         : $now,
            );

            return $this->records[$nodeId];
        }

        $record       = $this->records[$nodeId];
        $newSuccesses = $record->consecutiveSuccesses + 1;
        $newStatus    = $record->cacheNodeStatus;

        // If we've recovered enough times, mark as healthy
        if ($newSuccesses >= $this->recoveryThreshold) {
            $newStatus = CacheNodeStatus::HEALTHY;
        }

        $this->records[$nodeId] = new NodeHealthRecord(
            nodeId              : $nodeId,
            cacheNodeStatus     : $newStatus,
            lastCheck           : $now,
            consecutiveFailures : 0,
            consecutiveSuccesses: $newSuccesses,
            lastFailure         : $record->lastFailure,
            lastSuccess         : $now,
        );

        return $this->records[$nodeId];
    }

    /**
     * Mark a node as unhealthy after a failed health check.
     */
    public function markUnhealthy(CacheNode $cacheNode): NodeHealthRecord
    {
        $nodeId = $cacheNode->id;
        $now    = $this->clock->now();

        if (! isset($this->records[$nodeId])) {
            $this->records[$nodeId] = new NodeHealthRecord(
                nodeId              : $nodeId,
                cacheNodeStatus     : CacheNodeStatus::UNHEALTHY,
                lastCheck           : $now,
                consecutiveFailures : 1,
                consecutiveSuccesses: 0,
                lastFailure         : $now,
                lastSuccess         : null,
            );

            return $this->records[$nodeId];
        }

        $record      = $this->records[$nodeId];
        $newFailures = $record->consecutiveFailures + 1;
        $newStatus   = $record->cacheNodeStatus;

        // If we've failed enough times, mark as unhealthy
        if ($newFailures >= $this->failureThreshold) {
            $newStatus = CacheNodeStatus::UNHEALTHY;
        }

        $this->records[$nodeId] = new NodeHealthRecord(
            nodeId              : $nodeId,
            cacheNodeStatus     : $newStatus,
            lastCheck           : $now,
            consecutiveFailures : $newFailures,
            consecutiveSuccesses: 0,
            lastFailure         : $now,
            lastSuccess         : $record->lastSuccess,
        );

        return $this->records[$nodeId];
    }

    /**
     * Get all healthy nodes.
     *
     * @return list<NodeHealthRecord>
     */
    public function getHealthyNodes(): array
    {
        $healthy = [];

        foreach ($this->records as $record) {
            if ($record->cacheNodeStatus === CacheNodeStatus::HEALTHY) {
                $healthy[] = $record;
            }
        }

        return $healthy;
    }

    /**
     * Get all unhealthy nodes.
     *
     * @return list<NodeHealthRecord>
     */
    public function getUnhealthyNodes(): array
    {
        $unhealthy = [];

        foreach ($this->records as $record) {
            if ($record->cacheNodeStatus === CacheNodeStatus::UNHEALTHY) {
                $unhealthy[] = $record;
            }
        }

        return $unhealthy;
    }

    /**
     * Get the health record for a specific node.
     */
    public function getRecord(string $nodeId): ?NodeHealthRecord
    {
        return $this->records[$nodeId] ?? null;
    }

    /**
     * Get all health records.
     *
     * @return array<string, NodeHealthRecord>
     */
    public function getAllRecords(): array
    {
        return $this->records;
    }

    /**
     * Remove a node's health record.
     */
    public function removeRecord(string $nodeId): void
    {
        unset($this->records[$nodeId]);
    }

    /**
     * Reset all health records.
     */
    public function reset(): void
    {
        $this->records = [];
    }

    /**
     * Get the count of tracked nodes.
     */
    public function count(): int
    {
        return count($this->records);
    }
}
