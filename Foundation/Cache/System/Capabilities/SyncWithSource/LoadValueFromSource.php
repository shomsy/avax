<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\SyncWithSource;

use Avax\Cache\System\Capabilities\IdentifyCachedValues\CacheKey;

final readonly class LoadValueFromSource
{
    public function __construct(
        private CacheSource $source
    ) {}

    public function loadOrFail(CacheKey $key) : mixed
    {
        $sourceKey = CacheSourceKey::create($key->fullKey(), $key->namespace);

        if (! $this->source->exists($sourceKey)) {
            throw new CacheSourceFailed(
                message  : sprintf('Source key "%s" does not exist', $key->fullKey()),
                sourceKey: $sourceKey
            );
        }

        return $this->source->load($sourceKey);
    }

    public function load(CacheKey $key) : mixed
    {
        $sourceKey = CacheSourceKey::create($key->fullKey(), $key->namespace);

        return $this->source->load($sourceKey);
    }
}