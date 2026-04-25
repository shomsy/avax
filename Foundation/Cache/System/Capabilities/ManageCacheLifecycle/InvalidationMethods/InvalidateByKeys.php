<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\ManageCacheLifecycle\InvalidationMethods;

use Avax\Cache\System\Capabilities\IdentifyCachedValues\CacheKey;

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