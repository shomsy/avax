<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\SyncWithSource;

use Avax\Cache\System\Capabilities\IdentifyCachedValues\CacheKey;

final readonly class WriteValueToSource
{
    public function __construct(
        private CacheSource $source
    ) {}

    public function write(CacheKey $key, mixed $value) : void
    {
        $sourceKey = CacheSourceKey::create($key->fullKey(), $key->namespace);
        $this->source->write($sourceKey, $value);
    }
}