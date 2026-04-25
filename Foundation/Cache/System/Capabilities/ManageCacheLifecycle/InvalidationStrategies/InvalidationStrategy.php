<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\ManageCacheLifecycle\InvalidationStrategies;

interface InvalidationStrategy
{
    public function shouldInvalidate(
        string $key,
        string $reason,
        array  $context = []
    ) : bool;

    public function strategyName() : string;
}