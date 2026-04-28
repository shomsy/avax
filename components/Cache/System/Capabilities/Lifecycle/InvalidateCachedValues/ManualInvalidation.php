<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\Lifecycle\InvalidateCachedValues;

final readonly class ManualInvalidation implements InvalidationStrategy
{
    public function shouldInvalidate(string $key, string $reason, array $context = []) : bool
    {
        return $reason === 'explicit';
    }

    public function strategyName() : string
    {
        return 'manual';
    }
}