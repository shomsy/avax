<?php

declare(strict_types=1);

namespace Avax\Cache\Tests\Support\Cache;

use Avax\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStore;
use Avax\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStoreRecordWasFound;
use Avax\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStoreRecordWasMissing;
use Avax\Cache\System\Capabilities\Storage\StoreCachedValues\StoredCacheRecord;
use Avax\Cache\System\Foundation\Time\Clock;
use Avax\Cache\System\Foundation\Time\SystemClock;
use Random\RandomException;
use RuntimeException;

final class FailingCacheStore implements CacheStore
{
    private Clock $clock;
    private float $failureRate = 1.0;
    private bool  $shouldFail  = false;

    public function __construct(Clock|null $clock = null)
    {
        $this->clock = $clock ?? new SystemClock();
    }

    public function setFailureRate(float $rate) : void
    {
        $this->failureRate = max(0.0, min(1.0, $rate));
    }

    public function forceFailure() : void
    {
        $this->shouldFail = true;
    }

    public function read(CacheKey $key, Clock $clock) : CacheStoreRecordWasFound|CacheStoreRecordWasMissing
    {
        $this->maybeFail();

        return new CacheStoreRecordWasMissing(key: $key);
    }

    /**
     * @throws RandomException
     */
    private function maybeFail() : void
    {
        if ($this->shouldFail || (random_int(0, 100) / 100) < $this->failureRate) {
            throw new RuntimeException(message: 'Simulated cache store failure');
        }
    }

    public function write(CacheKey $key, StoredCacheRecord $record) : void
    {
        $this->maybeFail();
    }

    public function forget(CacheKey $key) : void
    {
        $this->maybeFail();
    }

    public function clear() : void
    {
        $this->maybeFail();
    }

    public function exists(CacheKey $key) : bool
    {
        $this->maybeFail();

        return false;
    }
}