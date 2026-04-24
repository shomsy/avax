<?php

declare(strict_types=1);

namespace Avax\DataLayer\OperateDataLayer;

enum BackupType: string
{
    case FULL         = 'full';
    case INCREMENTAL  = 'incremental';
    case DIFFERENTIAL = 'differential';
    case SNAPSHOT     = 'snapshot';
}

enum BackupStorage: string
{
    case LOCAL  = 'local';
    case REMOTE = 'remote';
    case CLOUD  = 'cloud';
}

final readonly class BackupPolicy
{
    public function __construct(
        public BackupType    $type,
        public BackupStorage $storage,
        public int           $retentionDays,
        public int           $intervalHours,
        public bool          $compressed
    ) {}

    public function describeResponsibility() : string
    {
        return 'records backup policy including type, storage, retention, and schedule.';
    }

    public static function standard() : self
    {
        return new self(
            BackupType::FULL,
            BackupStorage::CLOUD,
            30,
            24,
            true
        );
    }

    public static function aggressive() : self
    {
        return new self(
            BackupType::INCREMENTAL,
            BackupStorage::REMOTE,
            90,
            6,
            true
        );
    }

    public function shouldPerformBackup(float $lastBackup) : bool
    {
        $hoursSinceBackup = (microtime(true) - $lastBackup) / 3600;

        return $hoursSinceBackup >= $this->intervalHours;
    }

    public function toMetadata() : array
    {
        return [
            'type'           => $this->type->value,
            'storage'        => $this->storage->value,
            'retention_days' => $this->retentionDays,
            'interval_hours' => $this->intervalHours,
            'compressed'     => $this->compressed,
        ];
    }
}