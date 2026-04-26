<?php

declare(strict_types=1);

namespace Avax\DataLayer\DescribeStorageBehavior;

enum WriteAheadLogPurpose: string
{
    case RECOVERY    = 'recovery';
    case REPLICATION = 'replication';
    case AUDIT       = 'audit';
}

final readonly class DescribeWriteAheadLog
{
    public function __construct(
        public WriteAheadLogPurpose $purpose,
        public int                  $retentionDays,
        public bool                 $compressionEnabled,
        public bool                 $encrypted
    ) {}

    public static function forRecovery(int $retentionDays = 7) : self
    {
        return new self(purpose: WriteAheadLogPurpose::RECOVERY, retentionDays: $retentionDays, compressionEnabled: false, encrypted: false);
    }

    public static function forReplication(int $retentionDays = 30) : self
    {
        return new self(purpose: WriteAheadLogPurpose::REPLICATION, retentionDays: $retentionDays, compressionEnabled: true, encrypted: true);
    }

    public static function forAudit(int $retentionDays = 365) : self
    {
        return new self(purpose: WriteAheadLogPurpose::AUDIT, retentionDays: $retentionDays, compressionEnabled: true, encrypted: true);
    }

    public function describeResponsibility() : string
    {
        return 'describes write-ahead log purpose, retention, compression, and encryption settings.';
    }

    public function toMetadata() : array
    {
        return [
            'purpose'             => $this->purpose->value,
            'retention_days'      => $this->retentionDays,
            'compression_enabled' => $this->compressionEnabled,
            'encrypted'           => $this->encrypted,
        ];
    }
}