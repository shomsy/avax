<?php

declare(strict_types=1);

namespace Avax\Components\Cache\System\Capabilities\Lifecycle\InvalidateCachedValues;

use Avax\Components\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheNamespace;

interface InvalidateByNamespace
{
    public function invalidateNamespace(CacheNamespace $namespace) : int;

    public function isNamespaceInvalidated(CacheNamespace $namespace) : bool;
}