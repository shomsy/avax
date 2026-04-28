<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Lifecycle\InvalidateCachedValues;

final readonly class TtlOnlyInvalidation implements InvalidationStrategy
{
    public function shouldInvalidate(string $key, string $reason, array $context = []) : bool
    {
        return $reason === 'ttl_expired';
    }

    public function strategyName() : string
    {
        return 'ttl_only';
    }
}