<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\Lifecycle\InvalidateCachedValues;

use Avax\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;

interface SoftInvalidateCachedValue
{
    public function markStale(CacheKey $key) : void;

    public function isMarkedStale(CacheKey $key) : bool;
}