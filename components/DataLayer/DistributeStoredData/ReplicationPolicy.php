<?php

declare(strict_types=1);

namespace Avax\DataLayer\DistributeStoredData;

use InvalidArgumentException;

enum ReplicationLagSeverity: string
{
    case HEALTHY  = 'healthy';
    case WARNING  = 'warning';
    case CRITICAL = 'critical';
}

enum ReplicationSyncMode: string
{
    case ASYNC     = 'async';
    case SYNC      = 'sync';
    case SEMI_SYNC = 'semi_sync';
}

enum ReplicaSelectionPolicy: string
{
    case NEAREST     = 'nearest';
    case LOWEST_LAG  = 'lowest_lag';
    case ROUND_ROBIN = 'round_robin';
    case RANDOM      = 'random';
}

final readonly class ReplicationPolicy
{
    public function __construct(
        public ReplicationSyncMode    $syncMode,
        public ReplicaSelectionPolicy $replicaSelection,
        public int                    $minReplicas,
        public int                    $maxReplicas,
        public int                    $replicationTimeoutMs
    )
    {
        if ($this->minReplicas < 1) {
            throw new InvalidArgumentException(message: 'Min replicas must be at least 1.');
        }
        if ($this->maxReplicas < $this->minReplicas) {
            throw new InvalidArgumentException(message: 'Max replicas must be greater than or equal to min replicas.');
        }
    }

    public static function singleAsync() : self
    {
        return new self(
            syncMode            : ReplicationSyncMode::ASYNC,
            replicaSelection    : ReplicaSelectionPolicy::LOWEST_LAG,
            minReplicas         : 1,
            maxReplicas         : 1,
            replicationTimeoutMs: 5000
        );
    }

    public static function multiSync() : self
    {
        return new self(
            syncMode            : ReplicationSyncMode::SEMI_SYNC,
            replicaSelection    : ReplicaSelectionPolicy::LOWEST_LAG,
            minReplicas         : 2,
            maxReplicas         : 3,
            replicationTimeoutMs: 30000
        );
    }

    public function describeResponsibility() : string
    {
        return 'records replication mode, replica selection policy, replica count, and replication timeout.';
    }

    public function toMetadata() : array
    {
        return [
            'sync_mode'              => $this->syncMode->value,
            'replica_selection'      => $this->replicaSelection->value,
            'min_replicas'           => $this->minReplicas,
            'max_replicas'           => $this->maxReplicas,
            'replication_timeout_ms' => $this->replicationTimeoutMs,
        ];
    }
}