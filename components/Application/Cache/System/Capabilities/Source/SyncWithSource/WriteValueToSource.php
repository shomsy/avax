<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Source\SyncWithSource;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;

final readonly class WriteValueToSource
{
    public function __construct(private CacheSource $cacheSource) {}

    public function write(CacheKey $cacheKey, mixed $value) : void
    {
        $cacheSourceKey = CacheSourceKey::create(key: $cacheKey->fullKey(), namespace: $cacheKey->namespace);
        $this->cacheSource->write($cacheSourceKey, $value);
    }
}
