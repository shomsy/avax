<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\ManageCacheLifecycle\InvalidationStrategies;

final readonly class SoftThenHardInvalidation implements InvalidationStrategy
{
    public function __construct(
        private int $softTtlSeconds = 3600,
        private int $hardTtlSeconds = 86400
    ) {}

    public function shouldInvalidate(string $key, string $reason, array $context = []) : bool
    {
        $age = $context['age_seconds'] ?? 0;

        if ($age >= $this->hardTtlSeconds) {
            return true;
        }

        if ($age >= $this->softTtlSeconds) {
            return true;
        }

        return false;
    }

    public function strategyName() : string
    {
        return 'soft_then_hard';
    }
}