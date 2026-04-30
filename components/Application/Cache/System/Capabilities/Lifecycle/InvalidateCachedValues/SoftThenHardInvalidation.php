<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Lifecycle\InvalidateCachedValues;

use Override;

final readonly class SoftThenHardInvalidation implements InvalidationStrategy
{
    public function __construct(
        private int $softTtlSeconds = 3600,
        private int $hardTtlSeconds = 86400,
    ) {}

    #[Override]
    public function shouldInvalidate(string $key, string $reason, array $context = []) : bool
    {
        $age = $context['age_seconds'] ?? 0;

        if ($age >= $this->hardTtlSeconds) {
            return true;
        }

        return $age >= $this->softTtlSeconds;
    }

    #[Override]
    public function strategyName() : string
    {
        return 'soft_then_hard';
    }
}
