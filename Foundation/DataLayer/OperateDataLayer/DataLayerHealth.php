<?php

declare(strict_types=1);

namespace Avax\DataLayer\OperateDataLayer;

enum HealthStatus: string
{
    case HEALTHY   = 'healthy';
    case DEGRADED  = 'degraded';
    case UNHEALTHY = 'unhealthy';
}

final readonly class DataLayerHealth
{
    public function __construct(
        public HealthStatus $status,
        public bool         $primaryAvailable,
        public bool         $replicasAvailable,
        public int          $replicationLagMs,
        public array        $metrics
    ) {}

    public function describeResponsibility() : string
    {
        return 'records data layer health status including primary, replicas, and lag.';
    }

    public static function healthy() : self
    {
        return new self(
            status           : HealthStatus::HEALTHY,
            primaryAvailable : true,
            replicasAvailable: true,
            replicationLagMs : 0,
            metrics          : ['cpu' => 0.3, 'memory' => 0.5, 'disk' => 0.4]
        );
    }

    public function isHealthy() : bool
    {
        return $this->status === HealthStatus::HEALTHY;
    }

    public function toMetadata() : array
    {
        return [
            'status'             => $this->status->value,
            'primary_available'  => $this->primaryAvailable,
            'replicas_available' => $this->replicasAvailable,
            'replication_lag_ms' => $this->replicationLagMs,
            'metrics'            => $this->metrics,
        ];
    }
}