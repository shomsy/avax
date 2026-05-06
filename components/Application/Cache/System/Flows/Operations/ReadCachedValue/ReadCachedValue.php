<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Flows\Operations\ReadCachedValue;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Components\Application\Cache\System\Capabilities\Observability\ObserveCache\CacheMetrics;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStore;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStoreRecordWasMissing;
use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Throwable;

final readonly class ReadCachedValue
{
    public function __construct(
        private CacheStore $cacheStore,
        private Clock $clock,
        private ?CacheMetrics $cacheMetrics = null,
    ) {
    }

    public function read(CacheKey $cacheKey, mixed $default = null): mixed
    {
        $startTime = hrtime(true);

        try {
            $result = $this->cacheStore->read(cacheKey: $cacheKey, clock: $this->clock);

            if ($result instanceof CacheStoreRecordWasMissing) {
                $this->recordLatency(startTime: $startTime);
                $this->cacheMetrics?->recordMiss();

                return $default;
            }

            if ($result->storedCacheRecord->isExpired(clock: $this->clock)) {
                $this->recordLatency(startTime: $startTime);
                $this->cacheMetrics?->recordMiss();

                return $default;
            }

            $this->recordLatency(startTime: $startTime);
            $this->cacheMetrics?->recordHit();

            return $result->value();
        } catch (Throwable) {
            $this->cacheMetrics?->recordStoreFailure();

            return $default;
        }
    }

    private function recordLatency(int $startTime): void
    {
        if (! $this->cacheMetrics instanceof CacheMetrics) {
            return;
        }

        $endTime = hrtime(true);
        $latencyMicroseconds = (int) (($endTime - $startTime) / 1000);

        $this->cacheMetrics->recordLatency(microseconds: $latencyMicroseconds);
    }
}
