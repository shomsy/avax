<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Lifecycle\InvalidateCachedValues;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheVersion;

interface InvalidateByVersion
{
    public function invalidateVersion(CacheVersion $cacheVersion): int;

    public function isVersionInvalidated(CacheVersion $cacheVersion): bool;
}
