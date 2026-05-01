<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Distribution\ReplicateCachedValues;

/**
 * Policy for primary-replica cache behavior.
 *
 * Controls read routing, replication mode, and failover behavior
 * for distributed cache clusters with primary-replica topology.
 */
final readonly class PrimaryReplicaPolicy
{
    /**
     * @param bool              $readFromReplicaOnMiss  When true, attempt to read from replicas on primary miss
     * @param bool              $asyncReplication       When true, use asynchronous replication (fire-and-forget)
     * @param bool              $syncReplication        When true, use synchronous replication (wait for all replicas)
     * @param bool              $failover               When true, automatically promote a replica on primary failure
     * @param ReplicationPolicy $replicationPolicy      The replication strategy to use
     * @param int               $failoverTimeoutSeconds Maximum time to wait before triggering failover
     * @param int               $maxReplicationRetries  Maximum number of replication retry attempts
     */
    public function __construct(
        public bool $readFromReplicaOnMiss = false,
        public bool $asyncReplication = false,
        public bool $syncReplication = true,
        public bool $failover = false,
        public ReplicationPolicy $replicationPolicy = ReplicationPolicy::SYNCHRONOUS,
        public int $failoverTimeoutSeconds = 30,
        public int $maxReplicationRetries = 3,
    ) {}

    /**
     * Create a policy with default settings.
     *
     * Default: synchronous replication, no read-from-replica on miss, no automatic failover.
     */
    public static function default() : self
    {
        return new self();
    }

    /**
     * Create a policy optimized for read-heavy workloads.
     *
     * Reads from replicas on primary miss, asynchronous replication.
     */
    public static function readHeavy() : self
    {
        return new self(
            readFromReplicaOnMiss: true,
            asyncReplication     : true,
            syncReplication      : false,
            failover             : true,
            replicationPolicy    : ReplicationPolicy::ASYNCHRONOUS,
        );
    }

    /**
     * Create a policy optimized for write-heavy workloads.
     *
     * Synchronous replication, no read-from-replica on miss.
     */
    public static function writeHeavy() : self
    {
        return new self(
            readFromReplicaOnMiss: false,
            asyncReplication     : false,
            syncReplication      : true,
            failover             : false,
            replicationPolicy    : ReplicationPolicy::SYNCHRONOUS,
        );
    }

    /**
     * Create a policy optimized for high availability.
     *
     * Automatic failover, read from replicas, quorum replication.
     */
    public static function highAvailability() : self
    {
        return new self(
            readFromReplicaOnMiss : true,
            asyncReplication      : false,
            syncReplication       : true,
            failover              : true,
            replicationPolicy     : ReplicationPolicy::QUORUM,
            failoverTimeoutSeconds: 15,
            maxReplicationRetries : 5,
        );
    }

    /**
     * Create a policy optimized for eventual consistency.
     *
     * Asynchronous replication, read from replicas, no automatic failover.
     */
    public static function eventualConsistency() : self
    {
        return new self(
            readFromReplicaOnMiss: true,
            asyncReplication     : true,
            syncReplication      : false,
            failover             : false,
            replicationPolicy    : ReplicationPolicy::ASYNCHRONOUS,
        );
    }

    /**
     * Create a policy with strict consistency.
     *
     * Synchronous replication, no read from replicas, no failover.
     */
    public static function strictConsistency() : self
    {
        return new self(
            readFromReplicaOnMiss: false,
            asyncReplication     : false,
            syncReplication      : true,
            failover             : false,
            replicationPolicy    : ReplicationPolicy::SYNCHRONOUS,
        );
    }

    /**
     * Create a policy with automatic failover enabled.
     */
    public function withFailover(int $timeoutSeconds = 30) : self
    {
        return new self(
            readFromReplicaOnMiss : $this->readFromReplicaOnMiss,
            asyncReplication      : $this->asyncReplication,
            syncReplication       : $this->syncReplication,
            failover              : true,
            replicationPolicy     : $this->replicationPolicy,
            failoverTimeoutSeconds: $timeoutSeconds,
            maxReplicationRetries : $this->maxReplicationRetries,
        );
    }

    /**
     * Create a policy with read-from-replica on miss enabled.
     */
    public function withReadFromReplicaOnMiss() : self
    {
        return new self(
            readFromReplicaOnMiss : true,
            asyncReplication      : $this->asyncReplication,
            syncReplication       : $this->syncReplication,
            failover              : $this->failover,
            replicationPolicy     : $this->replicationPolicy,
            failoverTimeoutSeconds: $this->failoverTimeoutSeconds,
            maxReplicationRetries : $this->maxReplicationRetries,
        );
    }

    /**
     * Create a policy with asynchronous replication.
     */
    public function withAsyncReplication() : self
    {
        return new self(
            readFromReplicaOnMiss : $this->readFromReplicaOnMiss,
            asyncReplication      : true,
            syncReplication       : false,
            failover              : $this->failover,
            replicationPolicy     : ReplicationPolicy::ASYNCHRONOUS,
            failoverTimeoutSeconds: $this->failoverTimeoutSeconds,
            maxReplicationRetries : $this->maxReplicationRetries,
        );
    }

    /**
     * Create a policy with synchronous replication.
     */
    public function withSyncReplication() : self
    {
        return new self(
            readFromReplicaOnMiss : $this->readFromReplicaOnMiss,
            asyncReplication      : false,
            syncReplication       : true,
            failover              : $this->failover,
            replicationPolicy     : ReplicationPolicy::SYNCHRONOUS,
            failoverTimeoutSeconds: $this->failoverTimeoutSeconds,
            maxReplicationRetries : $this->maxReplicationRetries,
        );
    }

    /**
     * Create a policy with a specific replication policy.
     */
    public function withReplicationPolicy(ReplicationPolicy $replicationPolicy) : self
    {
        $async = $replicationPolicy === ReplicationPolicy::ASYNCHRONOUS;
        $sync = $replicationPolicy !== ReplicationPolicy::ASYNCHRONOUS;

        return new self(
            readFromReplicaOnMiss : $this->readFromReplicaOnMiss,
            asyncReplication      : $async,
            syncReplication       : $sync,
            failover              : $this->failover,
            replicationPolicy     : $replicationPolicy,
            failoverTimeoutSeconds: $this->failoverTimeoutSeconds,
            maxReplicationRetries : $this->maxReplicationRetries,
        );
    }

    /**
     * Create a policy with custom retry count.
     */
    public function withMaxRetries(int $retries) : self
    {
        return new self(
            readFromReplicaOnMiss : $this->readFromReplicaOnMiss,
            asyncReplication      : $this->asyncReplication,
            syncReplication       : $this->syncReplication,
            failover              : $this->failover,
            replicationPolicy     : $this->replicationPolicy,
            failoverTimeoutSeconds: $this->failoverTimeoutSeconds,
            maxReplicationRetries : $retries,
        );
    }

    /**
     * Convert to array representation.
     *
     * @return array{
     *     readFromReplicaOnMiss: bool,
     *     asyncReplication: bool,
     *     syncReplication: bool,
     *     failover: bool,
     *     replicationPolicy: string,
     *     failoverTimeoutSeconds: int,
     *     maxReplicationRetries: int
     * }
     */
    public function toArray() : array
    {
        return [
            'readFromReplicaOnMiss' => $this->readFromReplicaOnMiss,
            'asyncReplication'      => $this->asyncReplication,
            'syncReplication'       => $this->syncReplication,
            'failover'              => $this->failover,
            'replicationPolicy'     => $this->replicationPolicy->value,
            'failoverTimeoutSeconds' => $this->failoverTimeoutSeconds,
            'maxReplicationRetries' => $this->maxReplicationRetries,
        ];
    }
}
