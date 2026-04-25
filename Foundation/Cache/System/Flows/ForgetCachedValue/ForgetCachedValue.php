<?php

declare(strict_types=1);

namespace Avax\Cache\System\Flows\ForgetCachedValue;

use Avax\Cache\System\Capabilities\IdentifyCachedValues\CacheKey;
use Avax\Cache\System\Capabilities\ObserveCache\CacheMetrics;
use Avax\Cache\System\Capabilities\StoreCachedValues\CacheStore;
use Avax\Cache\System\Foundation\Time\Clock;
use Throwable;

final readonly class ForgetCachedValue
{
    public function __construct(
        private CacheStore    $store,
        private Clock         $clock,
        private ?CacheMetrics $metrics = null
    ) {}

    public function forgetMany(iterable $keys) : int
    {
        $count = 0;

        foreach ($keys as $key) {
            if ($this->forget($key instanceof CacheKey ? $key : CacheKey::create($key))) {
                $count++;
            }
        }

        return $count;
    }

    public function forget(CacheKey $key) : bool
    {
        $startTime = hrtime(as_integer: true);

        try {
            $this->store->forget($key);

            $this->recordLatency($startTime);
            $this->metrics?->recordDelete();

            return true;
        } catch (Throwable $e) {
            $this->metrics?->recordStoreFailure();

            return false;
        }
    }

    private function recordLatency(int $startTime) : void
    {
        if ($this->metrics === null) {
            return;
        }

        $endTime             = hrtime(as_integer: true);
        $latencyMicroseconds = (int) (($endTime - $startTime) / 1000);

        $this->metrics->recordLatency($latencyMicroseconds);
    }
}