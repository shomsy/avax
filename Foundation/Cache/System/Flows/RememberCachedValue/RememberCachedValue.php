<?php

declare(strict_types=1);

namespace Avax\Cache\System\Flows\RememberCachedValue;

use Avax\Cache\System\Capabilities\IdentifyCachedValues\CacheKey;
use Avax\Cache\System\Capabilities\StoreCachedValues\CacheStore;
use Avax\Cache\System\Flows\ReadCachedValue\ReadCachedValue;
use Avax\Cache\System\Flows\StoreCachedValue\StoreCachedValue;
use Avax\Cache\System\Foundation\Time\Clock;
use DateInterval;
use Throwable;

final readonly class RememberCachedValue
{
    public function __construct(
        private CacheStore        $store,
        private Clock             $clock,
        private ?ReadCachedValue  $reader = null,
        private ?StoreCachedValue $writer = null
    ) {}

    public function remember(
        CacheKey              $key,
        null|int|DateInterval $ttl,
        callable              $loader,
        mixed                 $default = null
    ) : mixed
    {
        $this->reader ??= new ReadCachedValue($this->store, $this->clock);
        $this->writer ??= new StoreCachedValue($this->store, $this->clock);

        $value = $this->reader->read($key, default: null);

        if ($value !== null) {
            return $value;
        }

        try {
            $value = $loader();
        } catch (Throwable $e) {
            return $default;
        }

        $this->writer->store($key, $value, $ttl);

        return $value;
    }
}