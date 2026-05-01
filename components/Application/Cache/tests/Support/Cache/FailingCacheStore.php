<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\Tests\Support\Cache;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStore;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStoreRecordWasFound;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStoreRecordWasMissing;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\StoredCacheRecord;
use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Avax\Components\Application\Cache\System\Foundation\Time\SystemClock;
use Override;
use Random\RandomException;
use RuntimeException;

final class FailingCacheStore implements CacheStore
{
    private float $failureRate = 1.0;

    private bool $shouldFail = false;

    public function setFailureRate(float $rate) : void
    {
        $this->failureRate = max(0.0, min(1.0, $rate));
    }

    public function forceFailure() : void
    {
        $this->shouldFail = true;
    }

    #[Override]
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

    #[Override]
    public function write(CacheKey $key, StoredCacheRecord $record) : void
    {
        $this->maybeFail();
    }

    #[Override]
    public function forget(CacheKey $key) : void
    {
        $this->maybeFail();
    }

    #[Override]
    public function clear() : void
    {
        $this->maybeFail();
    }

    #[Override]
    public function exists(CacheKey $key) : bool
    {
        $this->maybeFail();

        return false;
    }
}
