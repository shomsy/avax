<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Lifecycle\InvalidateCachedValues;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;

interface InvalidateByKey
{
    public function invalidate(CacheKey $cacheKey) : void;

    public function isInvalidated(CacheKey $cacheKey) : bool;
}
