<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\ManageCacheLifecycle\InvalidationStrategies;

final readonly class SoftThenRefreshInvalidation implements InvalidationStrategy
{
    private int $staleRefreshWindowSeconds;

    public function __construct(
        private bool $allowSoft = true,
        int          $staleRefreshWindowSeconds = 300
    )
    {
        $this->staleRefreshWindowSeconds = $staleRefreshWindowSeconds;
    }

    public function shouldInvalidate(string $key, string $reason, array $context = []) : bool
    {
        if (! $this->allowSoft) {
            return true;
        }

        $staleAge = $context['stale_age_seconds'] ?? 0;

        if ($staleAge > $this->staleRefreshWindowSeconds) {
            return true;
        }

        return false;
    }

    public function strategyName() : string
    {
        return 'soft_then_refresh';
    }
}