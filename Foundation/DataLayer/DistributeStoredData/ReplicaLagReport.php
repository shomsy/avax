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

final readonly class ReplicaLagReport
{
    public function __construct(
        public string                 $replicaId,
        public int                    $lagMs,
        public int                    $lagBytes,
        public ReplicationLagSeverity $severity,
        public float                  $timestamp
    )
    {
        if ($this->lagMs < 0) {
            throw new InvalidArgumentException(message: 'Lag cannot be negative.');
        }
    }

    public function describeResponsibility() : string
    {
        return 'reports replica lag in milliseconds, bytes, and severity level.';
    }

    public static function healthy(string $replicaId, int $lagMs) : self
    {
        return new self(
            replicaId: $replicaId,
            lagMs    : $lagMs,
            lagBytes : $lagMs * 1000,
            severity : ReplicationLagSeverity::HEALTHY,
            timestamp: microtime(true)
        );
    }

    public function isHealthy() : bool
    {
        return $this->severity === ReplicationLagSeverity::HEALTHY;
    }

    public function toMetadata() : array
    {
        return [
            'replica_id' => $this->replicaId,
            'lag_ms'     => $this->lagMs,
            'lag_bytes'  => $this->lagBytes,
            'severity'   => $this->severity->value,
            'timestamp'  => $this->timestamp,
        ];
    }
}