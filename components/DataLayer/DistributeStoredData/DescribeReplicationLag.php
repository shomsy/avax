<?php

declare(strict_types=1);

namespace Avax\DataLayer\DistributeStoredData;

final readonly class DescribeReplicationLag
{
    public function __construct(
        public int  $warningThresholdMs,
        public int  $criticalThresholdMs,
        public bool $autoFailoverEnabled
    ) {}

    public static function standard() : self
    {
        return new self(
            warningThresholdMs : 1000,
            criticalThresholdMs: 5000,
            autoFailoverEnabled: true
        );
    }

    public function describeResponsibility() : string
    {
        return 'describes replication lag thresholds for warnings, critical alerts, and auto-failover triggers.';
    }

    public function evaluateLag(int $lagMs) : ReplicationLagSeverity
    {
        if ($lagMs >= $this->criticalThresholdMs) {
            return ReplicationLagSeverity::CRITICAL;
        }
        if ($lagMs >= $this->warningThresholdMs) {
            return ReplicationLagSeverity::WARNING;
        }

        return ReplicationLagSeverity::HEALTHY;
    }

    public function shouldFailover(int $lagMs) : bool
    {
        return $this->autoFailoverEnabled && $lagMs >= $this->criticalThresholdMs;
    }

    public function toMetadata() : array
    {
        return [
            'warning_threshold_ms'  => $this->warningThresholdMs,
            'critical_threshold_ms' => $this->criticalThresholdMs,
            'auto_failover_enabled' => $this->autoFailoverEnabled,
        ];
    }
}