<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\ManageCacheLifecycle\InvalidationMethods;

use Avax\Cache\System\Capabilities\IdentifyCachedValues\CacheVersion;

interface InvalidateByVersion
{
    public function invalidateVersion(CacheVersion $version) : int;

    public function isVersionInvalidated(CacheVersion $version) : bool;
}