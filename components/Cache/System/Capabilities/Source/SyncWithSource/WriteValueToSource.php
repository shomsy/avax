<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\Source\SyncWithSource;

use Avax\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;

final readonly class WriteValueToSource
{
    public function __construct(
        private CacheSource $source
    ) {}

    public function write(CacheKey $key, mixed $value) : void
    {
        $sourceKey = CacheSourceKey::create(key: $key->fullKey(), namespace: $key->namespace);
        $this->source->write(key: $sourceKey, value: $value);
    }
}