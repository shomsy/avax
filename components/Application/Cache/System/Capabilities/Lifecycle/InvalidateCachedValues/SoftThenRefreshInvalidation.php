<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Lifecycle\InvalidateCachedValues;

use Override;

final readonly class SoftThenRefreshInvalidation implements InvalidationStrategy
{
    public function __construct(private bool $allowSoft = true, private int $staleRefreshWindowSeconds = 300)
    {
    }

    #[Override]
    public function shouldInvalidate(string $key, string $reason, array $context = []) : bool
    {
        if (! $this->allowSoft) {
            return true;
        }

        $staleAge = $context['stale_age_seconds'] ?? 0;

        return $staleAge > $this->staleRefreshWindowSeconds;
    }

    #[Override]
    public function strategyName() : string
    {
        return 'soft_then_refresh';
    }
}
