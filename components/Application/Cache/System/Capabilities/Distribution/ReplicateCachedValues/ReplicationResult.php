<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Distribution\ReplicateCachedValues;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStore;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStoreRecordWasFound;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStoreRecordWasMissing;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\StoredCacheRecord;
use Avax\Components\Application\Cache\System\Foundation\Time\SystemClock;
use InvalidArgumentException;
use Throwable;



/**
 * Value object representing the result of a replication operation.
 */
final readonly class ReplicationResult
{
    /** @param list<ReplicaWriteResult> $replicaResults */
    public function __construct(
        public string               $key,
        public bool                 $primarySuccess,
        public array                $replicaResults,
        public PrimaryReplicaPolicy $primaryReplicaPolicy,
    ) {}

    /**
     * Check if replication was fully successful (primary + all replicas).
     */
    public function isFullySuccessful() : bool
    {
        if (! $this->primarySuccess) {
            return false;
        }

        foreach ($this->replicaResults as $replicaResult) {
            if (! $replicaResult->success) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if replication met the quorum requirement.
     */
    public function meetsQuorum() : bool
    {
        if (! $this->primarySuccess) {
            return false;
        }

        $successCount = 1; // Primary counts as 1

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
    public function getSuccessfulReplicaCount() : int
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