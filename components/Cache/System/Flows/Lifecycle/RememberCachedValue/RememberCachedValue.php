<?php

declare(strict_types=1);

namespace Avax\Cache\System\Flows\Lifecycle\RememberCachedValue;

use Avax\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStore;
use Avax\Cache\System\Flows\Operations\ReadCachedValue\ReadCachedValue;
use Avax\Cache\System\Flows\Operations\StoreCachedValue\StoreCachedValue;
use Avax\Cache\System\Foundation\Time\Clock;
use DateInterval;
use Throwable;

final readonly class RememberCachedValue
{
    public function __construct(
        private CacheStore            $store,
        private Clock                 $clock,
        private ReadCachedValue|null  $reader = null,
        private StoreCachedValue|null $writer = null
    ) {}

    public function remember(
        CacheKey              $key,
        int|DateInterval|null $ttl,
        callable              $loader,
        mixed                 $default = null
    ) : mixed
    {
        $this->reader ??= new ReadCachedValue(store: $this->store, clock: $this->clock);
        $this->writer ??= new StoreCachedValue(store: $this->store, clock: $this->clock);

        $value = $this->reader->read(key: $key, default: null);

        if ($value !== null) {
            return $value;
        }

        try {
            $value = $loader();
        } catch (Throwable $e) {
            return $default;
        }

        $this->writer->store(key: $key, value: $value, ttl: $ttl);

        return $value;
    }
}