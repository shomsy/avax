<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\ManageCacheLifecycle\InvalidationMethods;

use Avax\Cache\System\Capabilities\IdentifyCachedValues\CacheNamespace;

interface InvalidateByNamespace
{
    public function invalidateNamespace(CacheNamespace $namespace) : int;

    public function isNamespaceInvalidated(CacheNamespace $namespace) : bool;
}