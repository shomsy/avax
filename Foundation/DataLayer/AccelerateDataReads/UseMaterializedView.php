<?php

declare(strict_types=1);

namespace Avax\DataLayer\AccelerateDataReads;

final readonly class UseMaterializedView
{
    public function __construct(
        private MaterializedViewPolicy $policy
    ) {}

    public function describeResponsibility() : string
    {
        return 'determines whether to use a materialized view for query acceleration.';
    }

    public function shouldUse(array $queryContext, array $availableViews) : UseMaterializedViewResult|null
    {
        $queryFingerprint = $queryContext['fingerprint'] ?? '';
        $targetTable      = $queryContext['target_table'] ?? '';

        $matchingView = null;
        foreach ($availableViews as $view) {
            if ($view['source_table'] === $targetTable && $view['fingerprint'] === $queryFingerprint) {
                $matchingView = $view;
                break;
            }
        }

        if ($matchingView === null) {
            return null;
        }

        $lastRefresh = $matchingView['last_refresh'] ?? 0;
        $isStale = $this->isStale(lastRefreshTimestamp: $lastRefresh);

        return new UseMaterializedViewResult(
            viewName        : $matchingView['name'],
            shouldUse       : ! $isStale,
            stale           : $isStale,
            stalenessSeconds: $isStale ? time() - $lastRefresh : 0
        );
    }

    private function isStale(int $lastRefreshTimestamp) : bool
    {
        if ($lastRefreshTimestamp === 0) {
            return true;
        }

        $staleness    = time() - $lastRefreshTimestamp;
        $maxStaleness = $this->policy->maxStalenessSeconds ?? PHP_INT_MAX;

        return $staleness > $maxStaleness;
    }

    public function toMetadata() : array
    {
        return ['policy' => $this->policy->toMetadata()];
    }
}

final readonly class UseMaterializedViewResult
{
    public function __construct(
        public string $viewName,
        public bool   $shouldUse,
        public bool   $stale,
        public int    $stalenessSeconds
    ) {}
}