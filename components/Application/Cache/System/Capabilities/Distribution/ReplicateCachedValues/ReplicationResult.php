<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Distribution\ReplicateCachedValues;

/**
 * Value object representing the result of a replication operation.
 */
final readonly class ReplicationResult
{
    /** @param list<ReplicaWriteResult> $replicaResults */
    public function __construct(public string $key, public bool $primarySuccess, public array $replicaResults, public PrimaryReplicaPolicy $primaryReplicaPolicy)
    {
    }

    /**
     * Check if replication was fully successful (primary + all replicas).
     */
    public function isFullySuccessful(): bool
    {
        if (! $this->primarySuccess) {
            return false;
        }

        return array_all($this->replicaResults, fn ($replicaResult) => $replicaResult->success);
    }

    /**
     * Check if replication met the quorum requirement.
     */
    public function meetsQuorum(): bool
    {
        if (! $this->primarySuccess) {
            return false;
        }

        $successCount = 1;

        foreach ($this->replicaResults as $replicaResult) {
            if ($replicaResult->success) {
                $successCount++;
            }
        }

        $totalNodes = 1 + count($this->replicaResults);
        $quorumSize = (int) floor($totalNodes / 2) + 1;

        return $successCount >= $quorumSize;
    }

    /**
     * Get the number of successful replica writes.
     */
    public function getSuccessfulReplicaCount(): int
    {
        $count = 0;

        foreach ($this->replicaResults as $replicaResult) {
            if ($replicaResult->success) {
                $count++;
            }
        }

        return $count;
    }
}
