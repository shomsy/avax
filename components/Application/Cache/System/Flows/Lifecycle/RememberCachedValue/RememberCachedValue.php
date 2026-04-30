<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Flows\Lifecycle\RememberCachedValue;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStore;
use Avax\Components\Application\Cache\System\Flows\Operations\ReadCachedValue\ReadCachedValue;
use Avax\Components\Application\Cache\System\Flows\Operations\StoreCachedValue\StoreCachedValue;
use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use DateInterval;
use Throwable;

final readonly class RememberCachedValue
{
    public function __construct(
        private CacheStore            $cacheStore,
        private Clock                 $clock,
        private ReadCachedValue|null  $readCachedValue = null,
        private StoreCachedValue|null $storeCachedValue = null,
    ) {}

    public function remember(
        CacheKey $cacheKey,
        int|DateInterval|null $ttl,
        callable $loader,
        mixed    $default = null,
    ) : mixed
    {
        $this->readCachedValue  ??= new ReadCachedValue(store: $this->cacheStore, clock: $this->clock);
        $this->storeCachedValue ??= new StoreCachedValue(store: $this->cacheStore, clock: $this->clock);

        $value = $this->readCachedValue->read(key: $cacheKey, default: null);

        if ($value !== null) {
            return $value;
        }

        try {
            $value = $loader();
        } catch (Throwable) {
            return $default;
        }

        $this->storeCachedValue->store(key: $cacheKey, value: $value, ttl: $ttl);

        return $value;
    }
}
