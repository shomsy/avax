<?php

declare(strict_types=1);

namespace components\Cache\System\Capabilities\Lifecycle\InvalidateCachedValues;

use components\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;

interface InvalidateByKeys
{
    /**
     * @param iterable<CacheKey> $keys
     */
    public function invalidateMany(iterable $keys) : int;

    /**
     * @param iterable<CacheKey> $keys
     */
    public function areAllInvalidated(iterable $keys) : bool;
}