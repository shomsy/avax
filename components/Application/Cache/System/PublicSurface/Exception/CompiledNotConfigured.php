<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\PublicSurface\Exception;

use RuntimeException;

final class CompiledNotConfigured extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(message: 'Compiled cache is not configured. Use CompiledCache::use() or configure compiledCacheDirectory() on CacheServiceProvider.');
    }
}
