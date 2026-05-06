<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Lifecycle\InvalidateCachedValues;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;

interface SoftInvalidateCachedValue
{
    public function markStale(CacheKey $cacheKey): void;

    public function isMarkedStale(CacheKey $cacheKey): bool;
}
