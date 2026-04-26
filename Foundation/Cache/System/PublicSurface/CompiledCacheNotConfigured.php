<?php

declare(strict_types=1);

namespace Avax\Cache\System\PublicSurface;

use RuntimeException;

final class CompiledCacheNotConfigured extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Compiled cache is not configured. Use CompiledCache::use() or configure compiledCacheDirectory() on CacheServiceProvider.');
    }
}