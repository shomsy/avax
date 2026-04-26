<?php

declare(strict_types=1);

namespace components\Cache\System\Capabilities\Lifecycle\InvalidateCachedValues;

use components\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheNamespace;

interface InvalidateByNamespace
{
    public function invalidateNamespace(CacheNamespace $namespace) : int;

    public function isNamespaceInvalidated(CacheNamespace $namespace) : bool;
}