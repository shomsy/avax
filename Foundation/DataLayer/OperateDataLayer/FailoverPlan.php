<?php

declare(strict_types=1);

namespace Avax\DataLayer\OperateDataLayer;

use InvalidArgumentException;

final readonly class FailoverPlan
{
    public function __construct(
        public string $primaryEndpoint,
        public array  $replicaEndpoints,
        public int    $healthCheckIntervalMs,
        public int    $failoverTimeoutMs,
        public bool   $automatic
    )
    {
        if ($this->healthCheckIntervalMs < 100) {
            throw new InvalidArgumentException(message: 'Health check interval must be at least 100ms.');
        }
        if ($this->failoverTimeoutMs < 1000) {
            throw new InvalidArgumentException(message: 'Failover timeout must be at least 1000ms.');
        }
    }

    public function describeResponsibility() : string
    {
        return 'plans failover from primary to replica endpoints.';
    }

    public static function standard() : self
    {
        return new self(
            primaryEndpoint      : 'primary.db',
            replicaEndpoints     : ['replica1.db', 'replica2.db'],
            healthCheckIntervalMs: 5000,
            failoverTimeoutMs    : 30000,
            automatic            : true
        );
    }

    public function shouldFailover(int $consecutiveFailures) : bool
    {
        return $this->automatic && $consecutiveFailures >= 3;
    }

    public function toMetadata() : array
    {
        return [
            'primary_endpoint'         => $this->primaryEndpoint,
            'replica_endpoints'        => $this->replicaEndpoints,
            'health_check_interval_ms' => $this->healthCheckIntervalMs,
            'failover_timeout_ms'      => $this->failoverTimeoutMs,
            'automatic'                => $this->automatic,
        ];
    }
}