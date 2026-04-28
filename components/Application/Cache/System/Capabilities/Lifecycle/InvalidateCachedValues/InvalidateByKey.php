<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\Lifecycle\InvalidateCachedValues;

use Avax\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;

interface InvalidateByKey
{
    public function invalidate(CacheKey $key) : void;

    public function isInvalidated(CacheKey $key) : bool;
}