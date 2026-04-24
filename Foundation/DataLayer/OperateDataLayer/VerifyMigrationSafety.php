<?php

declare(strict_types=1);

namespace Avax\DataLayer\OperateDataLayer;

use InvalidArgumentException;

enum MigrationSafety: string
{
    case SAFE         = 'safe';
    case DANGEROUS    = 'dangerous';
    case IRREVERSIBLE = 'irreversible';
}

final readonly class VerifyMigrationSafety
{
    public function __construct(
        private int $maxDataSizeMb
    ) {}

    public function describeResponsibility() : string
    {
        return 'verifies migration safety before execution.';
    }

    public function verify(array $migration) : MigrationSafetyResult
    {
        $isDangerous = in_array($migration['type'], ['DROP', 'TRUNCATE', 'ALTER_DROP'], true);
        $dataSize    = $migration['estimated_size_mb'] ?? 0;
        $isLarge     = $dataSize > $this->maxDataSizeMb;

        if ($isDangerous && $isLarge) {
            return new MigrationSafetyResult(
                safe  : false,
                reason: 'Large operation with destructive type',
                checks: ['destructive' => true, 'size_check' => true, 'backup' => false]
            );
        }

        if ($isDangerous) {
            return new MigrationSafetyResult(
                safe  : false,
                reason: 'Destructive operation without safety measures',
                checks: ['destructive' => true, 'size_check' => false, 'backup' => false]
            );
        }

        return new MigrationSafetyResult(
            safe  : true,
            reason: 'Migration appears safe',
            checks: ['destructive' => false, 'size_check' => ! $isLarge, 'backup' => true]
        );
    }

    public function toMetadata() : array
    {
        return ['max_data_size_mb' => $this->maxDataSizeMb];
    }
}

final readonly class MigrationSafetyResult
{
    public function __construct(
        public bool   $safe,
        public string $reason,
        public array  $checks
    ) {}
}