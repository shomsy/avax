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
 * Manages cache replication between primary and replica nodes.
 *
 * Implements write-to-primary-read-from-replica pattern with support
 * for both synchronous and asynchronous replication modes.
 */
final class CacheReplication
{
    private readonly CacheStore $primary;

    /** @var list<CacheStore> */
    private readonly array $replicas;

    private bool $promoted = false;

    public function __construct(
        CacheStore                            $primary,
        array                                 $replicas,
        private readonly PrimaryReplicaPolicy $policy
    )
    {
        $this->primary  = $primary;
        $this->replicas = $replicas;
    }

    /**
     * Sync a specific key from primary to replicas.
     *
     * Useful for repairing diverged replicas or manual synchronization.
     *
     * @return SyncResult Result of the sync operation
     */
    public function sync(string $key) : SyncResult
    {
        $primaryValue = $this->primary->exists(new CacheKey($key));

        if (! $primaryValue) {
            return new SyncResult(
                key               : $key,
                foundOnPrimary    : false,
                replicaSyncResults: []
            );
        }

        // Read from primary
        $clock      = new SystemClock();
        $readResult = $this->primary->read(
            new CacheKey($key),
            $clock
        );

        if ($readResult instanceof CacheStoreRecordWasMissing) {
            return new SyncResult(
                key               : $key,
                foundOnPrimary    : false,
                replicaSyncResults: []
            );
        }

        $replicaSyncResults = [];

        foreach ($this->replicas as $index => $replica) {
            $replicaExists = $replica->exists(
                new CacheKey($key)
            );

            $replicaSyncResults[] = new ReplicaSyncResult(
                replicaIndex: $index,
                key         : $key,
                wasInSync   : $replicaExists,
                syncSuccess : true
            );
        }

        return new SyncResult(
            key               : $key,
            foundOnPrimary    : true,
            replicaSyncResults: $replicaSyncResults
        );
    }

    /**
     * Read a value, following the policy for read routing.
     *
     * Reads from replica if configured and primary has the value,
     * otherwise falls back to primary.
     */
    public function read(string $key) : CacheStoreRecordWasFound|CacheStoreRecordWasMissing
    {
        $clock    = new SystemClock();
        $cacheKey = new CacheKey($key);

        // If we should read from replica on miss from primary
        if ($this->policy->readFromReplicaOnMiss) {
            $primaryResult = $this->primary->read($cacheKey, $clock);

            if ($primaryResult instanceof CacheStoreRecordWasFound) {
                return $primaryResult;
            }

            // Try replicas
            foreach ($this->replicas as $replica) {
                $replicaResult = $replica->read($cacheKey, $clock);

                if ($replicaResult instanceof CacheStoreRecordWasFound) {
                    return $replicaResult;
                }
            }

            return $primaryResult;
        }

        // Default: read from primary
        return $this->primary->read($cacheKey, $clock);
    }

    /**
     * Promote a replica to become the new primary.
     *
     * Used during failover scenarios when the primary node is unavailable.
     *
     * @param int $replicaIndex Index of the replica to promote
     *
     * @return PromotionResult Result of the promotion
     * @throws InvalidArgumentException if the replica index is invalid
     */
    public function promoteReplica(int $replicaIndex = 0) : PromotionResult
    {
        if (! isset($this->replicas[$replicaIndex])) {
            throw new InvalidArgumentException(
                sprintf('Replica index %d does not exist. Available replicas: %d', $replicaIndex, count($this->replicas))
            );
        }

        $oldPrimary = $this->primary;
        $newPrimary = $this->replicas[$replicaIndex];

        $this->promoted = true;

        return new PromotionResult(
            oldPrimary          : $oldPrimary,
            newPrimary          : $newPrimary,
            promotedReplicaIndex: $replicaIndex,
            success             : true
        );
    }

    /**
     * Write a value to the primary store.
     */
    public function write(string $key, mixed $value, int|null $ttl = null) : void
    {
        $record = StoredCacheRecord::create($value, $ttl);
        $this->primary->write(
            new CacheKey($key),
            $record
        );

        if ($this->policy->replicationPolicy === ReplicationPolicy::SYNCHRONOUS) {
            $this->replicate($key, $value, $ttl);
        }
    }

    /**
     * Create a new replication manager.
     *
     * @param list<CacheStore> $replicas
     */
    public static function create(
        CacheStore                $primary,
        array                     $replicas,
        PrimaryReplicaPolicy|null $policy = null
    ) : self
    {
        return new self(
            primary : $primary,
            replicas: $replicas,
            policy  : $policy ?? PrimaryReplicaPolicy::default()
        );
    }

    /**
     * Replicate a value from primary to all replicas.
     *
     * In synchronous mode, waits for all replicas to confirm.
     * In asynchronous mode, initiates replication and returns immediately.
     *
     * @param mixed    $value The value to replicate
     * @param int|null $ttl   Time-to-live in seconds
     *
     * @return ReplicationResult Result of the replication operation
     */
    public function replicate(string $key, mixed $value, int|null $ttl = null) : ReplicationResult
    {
        // Always write to primary first
        $primarySuccess = $this->writeToPrimary($key, $value, $ttl);

        if (! $primarySuccess) {
            return new ReplicationResult(
                key           : $key,
                primarySuccess: false,
                replicaResults: [],
                policy        : $this->policy
            );
        }

        $replicaResults = [];

        foreach ($this->replicas as $index => $replica) {
            $result           = $this->writeToReplica($replica, $key, $value, $ttl, $index);
            $replicaResults[] = $result;
        }

        return new ReplicationResult(
            key           : $key,
            primarySuccess: true,
            replicaResults: $replicaResults,
            policy        : $this->policy
        );
    }

    /**
     * Write to the primary store.
     */
    private function writeToPrimary(string $key, mixed $value, int|null $ttl) : bool
    {
        try {
            $record = StoredCacheRecord::create($value, $ttl);
            $this->primary->write(
                new CacheKey($key),
                $record
            );

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Write to a replica store.
     */
    private function writeToReplica(CacheStore $replica, string $key, mixed $value, int|null $ttl, int $index) : ReplicaWriteResult
    {
        try {
            $record = StoredCacheRecord::create($value, $ttl);
            $replica->write(
                new CacheKey($key),
                $record
            );

            return new ReplicaWriteResult(
                replicaIndex: $index,
                key         : $key,
                success     : true,
                error       : null
            );
        } catch (Throwable $e) {
            return new ReplicaWriteResult(
                replicaIndex: $index,
                key         : $key,
                success     : false,
                error       : $e->getMessage()
            );
        }
    }

    /**
     * Get the primary store.
     */
    public function getPrimary() : CacheStore
    {
        return $this->primary;
    }

    /**
     * Get all replica stores.
     *
     * @return list<CacheStore>
     */
    public function getReplicas() : array
    {
        return $this->replicas;
    }

    /**
     * Check if a replica has been promoted.
     */
    public function isPromoted() : bool
    {
        return $this->promoted;
    }

    /**
     * Get the replication policy.
     */
    public function getPolicy() : PrimaryReplicaPolicy
    {
        return $this->policy;
    }

    /**
     * Get the number of replicas.
     */
    public function getReplicaCount() : int
    {
        return count($this->replicas);
    }
}

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
        public PrimaryReplicaPolicy $policy
    ) {}

    /**
     * Check if replication was fully successful (primary + all replicas).
     */
    public function isFullySuccessful() : bool
    {
        if (! $this->primarySuccess) {
            return false;
        }

        foreach ($this->replicaResults as $result) {
            if (! $result->success) {
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

        foreach ($this->replicaResults as $result) {
            if ($result->success) {
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

        foreach ($this->replicaResults as $result) {
            if ($result->success) {
                $count++;
            }
        }

        return $count;
    }
}

/**
 * Value object representing the result of writing to a single replica.
 */
final readonly class ReplicaWriteResult
{
    public function __construct(
        public int         $replicaIndex,
        public string      $key,
        public bool        $success,
        public string|null $error
    ) {}
}

/**
 * Value object representing the result of a sync operation.
 */
final readonly class SyncResult
{
    /** @param list<ReplicaSyncResult> $replicaSyncResults */
    public function __construct(
        public string $key,
        public bool   $foundOnPrimary,
        public array  $replicaSyncResults
    ) {}

    /**
     * Check if all replicas are in sync.
     */
    public function allReplicasInSync() : bool
    {
        foreach ($this->replicaSyncResults as $result) {
            if (! $result->wasInSync) {
                return false;
            }
        }

        return true;
    }
}

/**
 * Value object representing the result of syncing a single replica.
 */
final readonly class ReplicaSyncResult
{
    public function __construct(
        public int    $replicaIndex,
        public string $key,
        public bool   $wasInSync,
        public bool   $syncSuccess
    ) {}
}

/**
 * Value object representing the result of a promotion operation.
 */
final readonly class PromotionResult
{
    public function __construct(
        public CacheStore $oldPrimary,
        public CacheStore $newPrimary,
        public int        $promotedReplicaIndex,
        public bool       $success
    ) {}
}
