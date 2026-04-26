<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\Storage\SizeCachedValues;

use RuntimeException;

final class CacheCapacityWasExceeded extends RuntimeException
{
    public function __construct(
        public readonly int $capacity,
        int|null            $currentCount = null
    )
    {
        parent::__construct(message: sprintf(
                                         'Cache capacity exceeded: %d entries (max: %d). Consider increasing capacity or implementing eviction.',
                                         $currentCount ?? $capacity,
                                         $capacity
                                     ));
    }
}