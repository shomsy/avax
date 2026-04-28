<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\Source\SyncWithSource;

use Avax\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;

final readonly class LoadValueFromSource
{
    public function __construct(
        private CacheSource $source
    ) {}

    public function loadOrFail(CacheKey $key) : mixed
    {
        $sourceKey = CacheSourceKey::create(key: $key->fullKey(), namespace: $key->namespace);

        if (! $this->source->exists(key: $sourceKey)) {
            throw new CacheSourceFailed(
                message  : sprintf('Source key "%s" does not exist', $key->fullKey()),
                sourceKey: $sourceKey
            );
        }

        return $this->source->load(key: $sourceKey);
    }

    public function load(CacheKey $key) : mixed
    {
        $sourceKey = CacheSourceKey::create(key: $key->fullKey(), namespace: $key->namespace);

        return $this->source->load(key: $sourceKey);
    }
}