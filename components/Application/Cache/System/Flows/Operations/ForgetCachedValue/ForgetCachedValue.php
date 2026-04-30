<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Flows\Operations\ForgetCachedValue;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Components\Application\Cache\System\Capabilities\Observability\ObserveCache\CacheMetrics;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStore;
use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Throwable;

final readonly class ForgetCachedValue
{
    public function __construct(
        private CacheStore        $cacheStore,
        private CacheMetrics|null $cacheMetrics = null,
    ) {}

    public function forgetMany(iterable $keys) : int
    {
        $count = 0;

        foreach ($keys as $key) {
            if ($this->forget(key: $key instanceof CacheKey ? $key : CacheKey::create(key: $key))) {
                $count++;
            }
        }

        return $count;
    }

    public function forget(CacheKey $cacheKey) : bool
    {
        $startTime = hrtime(as_integer: true);

        try {
            $this->cacheStore->forget(key: $cacheKey);

            $this->recordLatency(startTime: $startTime);
            $this->cacheMetrics?->recordDelete();

            return true;
        } catch (Throwable) {
            $this->cacheMetrics?->recordStoreFailure();

            return false;
        }
    }

    private function recordLatency(int $startTime) : void
    {
        if ($this->cacheMetrics === null) {
            return;
        }

        $endTime             = hrtime(as_integer: true);
        $latencyMicroseconds = (int) (($endTime - $startTime) / 1000);

        $this->cacheMetrics->recordLatency(microseconds: $latencyMicroseconds);
    }
}
