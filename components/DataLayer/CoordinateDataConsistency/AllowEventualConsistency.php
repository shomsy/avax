<?php

declare(strict_types=1);

namespace Avax\DataLayer\CoordinateDataConsistency;

final readonly class AllowEventualConsistency
{
    public function __construct(
        private int $maxStalenessMs
    ) {}

    public function describeResponsibility() : string
    {
        return 'allows eventual consistency with bounded staleness.';
    }

    public function getMaxStaleness() : int
    {
        return $this->maxStalenessMs;
    }

    public function isStale(int $lastUpdateTimestamp, int $currentTimestamp) : bool
    {
        $staleness = ($currentTimestamp - $lastUpdateTimestamp) * 1000;

        return $staleness > $this->maxStalenessMs;
    }

    public function toMetadata() : array
    {
        return ['max_staleness_ms' => $this->maxStalenessMs];
    }
}