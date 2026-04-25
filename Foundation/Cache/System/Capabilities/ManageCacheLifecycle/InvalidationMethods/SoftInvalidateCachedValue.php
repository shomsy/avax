<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\ManageCacheLifecycle\InvalidationMethods;

use Avax\Cache\System\Capabilities\IdentifyCachedValues\CacheKey;

interface SoftInvalidateCachedValue
{
    public function markStale(CacheKey $key) : void;

    public function isMarkedStale(CacheKey $key) : bool;
}