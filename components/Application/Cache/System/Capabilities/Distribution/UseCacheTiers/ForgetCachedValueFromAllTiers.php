<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Distribution\UseCacheTiers;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;

final readonly class ForgetCachedValueFromAllTiers
{
    public function __construct(
        private TieredCache $tieredCache,
    ) {
    }

    public function forgetMany(iterable $keys): int
    {
        $count = 0;

        foreach ($keys as $key) {
            $cacheKey = $key instanceof CacheKey ? $key : CacheKey::create(key: $key);
            $this->tieredCache->forget(key: $cacheKey);
            $count++;
        }

        return $count;
    }

    public function forget(CacheKey $cacheKey): void
    {
        $this->tieredCache->forget(key: $cacheKey);
    }
}
