<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Lifecycle\InvalidateCachedValues;

interface InvalidationStrategy
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function shouldInvalidate(
        string $key,
        string $reason,
        array $context = [],
    ): bool;

    public function strategyName(): string;
}
