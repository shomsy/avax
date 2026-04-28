<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\Lifecycle\InvalidateCachedValues;

interface InvalidationStrategy
{
    public function shouldInvalidate(
        string $key,
        string $reason,
        array  $context = []
    ) : bool;

    public function strategyName() : string;
}