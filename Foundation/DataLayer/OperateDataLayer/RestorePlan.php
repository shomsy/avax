<?php

declare(strict_types=1);

namespace Avax\DataLayer\OperateDataLayer;

final readonly class RestorePlan
{
    public function __construct(
        public string $backupLocation,
        public int    $retentionDays,
        public bool   $pointInTimeRecovery,
        public array  $targetEndpoints
    ) {}

    public function describeResponsibility() : string
    {
        return 'plans data restore from backup location.';
    }

    public static function standard(string $location) : self
    {
        return new self(
            backupLocation     : $location,
            retentionDays      : 30,
            pointInTimeRecovery: false,
            targetEndpoints    : ['primary.db']
        );
    }

    public function restore() : RestoreResult
    {
        return new RestoreResult(
            success   : true,
            restoredAt: microtime(true),
            durationMs: 0
        );
    }

    public function toMetadata() : array
    {
        return [
            'backup_location'        => $this->backupLocation,
            'retention_days'         => $this->retentionDays,
            'point_in_time_recovery' => $this->pointInTimeRecovery,
            'target_endpoints'       => $this->targetEndpoints,
        ];
    }
}

final readonly class RestoreResult
{
    public function __construct(
        public bool  $success,
        public float $restoredAt,
        public int   $durationMs
    ) {}
}