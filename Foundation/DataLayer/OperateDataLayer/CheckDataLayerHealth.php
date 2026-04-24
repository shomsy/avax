<?php

declare(strict_types=1);

namespace Avax\DataLayer\OperateDataLayer;

final readonly class CheckDataLayerHealth
{
    public function __construct(
        private DataLayerHealth $health
    ) {}

    public function describeResponsibility() : string
    {
        return 'checks data layer health including primary, replicas, replication, and storage.';
    }

    public function check() : HealthCheckResult
    {
        $isHealthy = $this->health->isHealthy();
        $status    = $isHealthy ? 'healthy' : 'unhealthy';

        return new HealthCheckResult(
            status : $status,
            details: $this->health->toMetadata()
        );
    }

    public function toMetadata() : array
    {
        return $this->health->toMetadata();
    }
}

final readonly class HealthCheckResult
{
    public function __construct(
        public string $status,
        public array  $details
    ) {}
}