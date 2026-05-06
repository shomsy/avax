<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Source\SyncWithSource\SourceSyncPolicies;

enum SourceSyncPolicy: string
{
    case NO_SYNC = 'no_sync';
    case WRITE_THROUGH = 'write_through';
    case WRITE_AROUND = 'write_around';
    case WRITE_BEHIND = 'write_behind';
    case CACHE_ASIDE = 'cache_aside';

    public function writesToCache(): bool
    {
        return match ($this) {
            self::NO_SYNC => false,
            self::WRITE_THROUGH => true,
            self::WRITE_AROUND => false,
            self::WRITE_BEHIND => true,
            self::CACHE_ASIDE => false,
        };
    }

    public function writesToSource(): bool
    {
        return match ($this) {
            self::NO_SYNC => false,
            self::WRITE_THROUGH => true,
            self::WRITE_AROUND => true,
            self::WRITE_BEHIND => false,
            self::CACHE_ASIDE => false,
        };
    }

    public function isAsync(): bool
    {
        return match ($this) {
            self::WRITE_BEHIND => true,
            default => false,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::NO_SYNC => 'No Sync',
            self::WRITE_THROUGH => 'Write-Through',
            self::WRITE_AROUND => 'Write-Around',
            self::WRITE_BEHIND => 'Write-Behind',
            self::CACHE_ASIDE => 'Cache-Aside',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::NO_SYNC => 'No synchronization. Cache and source are independent.',
            self::WRITE_THROUGH => 'Synchronous writes to both cache and source. Consistent but slower.',
            self::WRITE_AROUND => 'Writes directly to source, invalidates cache. Good for read-heavy workloads.',
            self::WRITE_BEHIND => 'Asynchronous writes to source. Fast but potential data loss risk.',
            self::CACHE_ASIDE => 'Application manages cache explicitly. Cache is populated on read miss.',
        };
    }
}
