<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\UseCacheTiers;

use Avax\Cache\System\Capabilities\IdentifyCachedValues\CacheKey;

final readonly class ForgetCachedValueFromAllTiers
{
    public function __construct(
        private TieredCache $tieredCache
    ) {}

    public function forgetMany(iterable $keys) : int
    {
        $count = 0;

        foreach ($keys as $key) {
            $cacheKey = $key instanceof CacheKey ? $key : CacheKey::create($key);
            $this->tieredCache->forget($cacheKey);
            $count++;
        }

        return $count;
    }

    public function forget(CacheKey $key) : void
    {
        $this->tieredCache->forget($key);
    }
}