<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Source\SyncWithSource;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;

final readonly class WriteValueToSource
{
    public function __construct(
        CacheSource $source,
    )
    {
        $this->cacheSource = $source;
    }

    private CacheSource $cacheSource;

    public function write(CacheKey $key, mixed $value) : void
    {
        $cacheSourceKey = CacheSourceKey::create(key: $key->fullKey(), namespace: $key->namespace);
        $this->cacheSource->write($cacheSourceKey, $value);
    }
}
