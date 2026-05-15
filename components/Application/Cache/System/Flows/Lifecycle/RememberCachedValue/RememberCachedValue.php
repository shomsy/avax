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
        private CacheStore $cacheStore,
        private Clock $clock,
        private ReadCachedValue  $readCachedValue,
        private StoreCachedValue $storeCachedValue,
    ) {
    }

    public function remember(
        CacheKey $cacheKey,
        int|DateInterval|null $ttl,
        callable $loader,
        mixed $default = null,
    ): mixed {
        $value = $this->readCachedValue->read(cacheKey: $cacheKey);

        if ($value !== null) {
            return $value;
        }

        try {
            $value = $loader();
        } catch (Throwable) {
            return $default;
        }

        $this->storeCachedValue->store(cacheKey: $cacheKey, value: $value, ttl: $ttl);

        return $value;
    }
}
