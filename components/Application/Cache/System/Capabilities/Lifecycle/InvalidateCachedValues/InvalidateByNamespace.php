<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Lifecycle\InvalidateCachedValues;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheNamespace;

interface InvalidateByNamespace
{
    public function invalidateNamespace(CacheNamespace $cacheNamespace) : int;

    public function isNamespaceInvalidated(CacheNamespace $cacheNamespace) : bool;
}
