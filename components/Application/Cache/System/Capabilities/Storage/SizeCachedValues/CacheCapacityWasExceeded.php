<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Storage\SizeCachedValues;

use RuntimeException;

final class CacheCapacityWasExceeded extends RuntimeException
{
    public function __construct(
        public readonly int $capacity,
        ?int $currentCount = null,
    ) {
        parent::__construct(message: sprintf(
            'Cache capacity exceeded: %d entries (max: %d). Consider increasing capacity or implementing eviction.',
            $currentCount ?? $capacity,
            $capacity,
        ));
    }
}
