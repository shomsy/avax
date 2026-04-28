<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\Lifecycle\InvalidateCachedValues;

use Avax\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheVersion;

interface InvalidateByVersion
{
    public function invalidateVersion(CacheVersion $version) : int;

    public function isVersionInvalidated(CacheVersion $version) : bool;
}