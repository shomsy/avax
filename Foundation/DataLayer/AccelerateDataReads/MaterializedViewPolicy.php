<?php

declare(strict_types=1);

namespace Avax\DataLayer\AccelerateDataReads;

use InvalidArgumentException;

enum MaterializedViewRefreshType: string
{
    case ON_DEMAND   = 'on_demand';
    case SCHEDULED   = 'scheduled';
    case INCREMENTAL = 'incremental';
    case REAL_TIME   = 'real_time';
}

final readonly class MaterializedViewPolicy
{
    public function __construct(
        public MaterializedViewRefreshType $refreshType,
        public int                         $refreshIntervalSeconds,
        public bool                        $fastRefreshEnabled,
        public ?int                        $maxStalenessSeconds
    )
    {
        if ($this->refreshIntervalSeconds < 0) {
            throw new InvalidArgumentException('Refresh interval must be non-negative.');
        }
    }

    public function describeResponsibility() : string
    {
        return 'records materialized view refresh policy including type, interval, and staleness tolerance.';
    }

    public static function scheduled(int $intervalSeconds = 3600) : self
    {
        return new self(MaterializedViewRefreshType::SCHEDULED, $intervalSeconds, true, $intervalSeconds * 2);
    }

    public static function incremental(int $intervalSeconds = 300) : self
    {
        return new self(MaterializedViewRefreshType::INCREMENTAL, $intervalSeconds, true, null);
    }

    public function shouldRefresh(int $lastRefreshTimestamp) : bool
    {
        if ($this->refreshType === MaterializedViewRefreshType::REAL_TIME) {
            return true;
        }
        if ($this->refreshType === MaterializedViewRefreshType::ON_DEMAND) {
            return false;
        }

        $age = time() - $lastRefreshTimestamp;

        return $age >= $this->refreshIntervalSeconds;
    }

    public function toMetadata() : array
    {
        return [
            'refresh_type'             => $this->refreshType->value,
            'refresh_interval_seconds' => $this->refreshIntervalSeconds,
            'fast_refresh_enabled'     => $this->fastRefreshEnabled,
            'max_staleness_seconds'    => $this->maxStalenessSeconds,
        ];
    }
}