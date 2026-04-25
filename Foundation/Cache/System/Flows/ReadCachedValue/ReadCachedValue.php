<?php

declare(strict_types=1);

namespace Avax\Cache\System\Flows\ReadCachedValue;

use Avax\Cache\System\Capabilities\IdentifyCachedValues\CacheKey;
use Avax\Cache\System\Capabilities\ObserveCache\CacheMetrics;
use Avax\Cache\System\Capabilities\StoreCachedValues\CacheStore;
use Avax\Cache\System\Capabilities\StoreCachedValues\CacheStoreRecordWasMissing;
use Avax\Cache\System\Foundation\Time\Clock;
use Throwable;

final readonly class ReadCachedValue
{
    public function __construct(
        private CacheStore    $store,
        private Clock         $clock,
        private ?CacheMetrics $metrics = null
    ) {}

    public function read(CacheKey $key, mixed $default = null) : mixed
    {
        $startTime = hrtime(as_integer: true);

        try {
            $result = $this->store->read($key, $this->clock);

            if ($result instanceof CacheStoreRecordWasMissing) {
                $this->recordLatency($startTime, 'miss');
                $this->metrics?->recordMiss();

                return $default;
            }

            if ($result->record->isExpired($this->clock)) {
                $this->recordLatency($startTime, 'miss');
                $this->metrics?->recordMiss();

                return $default;
            }

            $this->recordLatency($startTime, 'hit');
            $this->metrics?->recordHit();

            return $result->value();
        } catch (Throwable $e) {
            $this->metrics?->recordStoreFailure();

            return $default;
        }
    }

    private function recordLatency(int $startTime, string $operation) : void
    {
        if ($this->metrics === null) {
            return;
        }

        $endTime             = hrtime(as_integer: true);
        $latencyMicroseconds = (int) (($endTime - $startTime) / 1000);

        $this->metrics->recordLatency($latencyMicroseconds);
    }
}