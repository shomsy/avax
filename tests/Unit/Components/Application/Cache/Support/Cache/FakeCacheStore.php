<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Cache\Support\Cache;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStore;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStoreRecordWasFound;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStoreRecordWasMissing;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\StoredCacheRecord;
use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Avax\Components\Application\Cache\System\Foundation\Time\SystemClock;

final class FakeCacheStore implements CacheStore
{
    /** @var array<string, StoredCacheRecord> */
    private array $records = [];

    private Clock $clock;

    public function __construct(?Clock $clock = null)
    {
        $this->clock = $clock ?? new SystemClock;
    }

    public function read(CacheKey $key, Clock $clock) : CacheStoreRecordWasFound|CacheStoreRecordWasMissing
    {
        $fullKey = $key->fullKey();

        if (! isset($this->records[$fullKey])) {
            return new CacheStoreRecordWasMissing(key: $key);
        }

        $record = $this->records[$fullKey];

        if ($record->lifecycle->isExpired(clock: $clock)) {
            unset($this->records[$fullKey]);

            return new CacheStoreRecordWasMissing(key: $key);
        }

        return new CacheStoreRecordWasFound(key: $key, record: $record, clock: $clock);
    }

    public function write(CacheKey $key, StoredCacheRecord $record) : void
    {
        $this->records[$key->fullKey()] = $record;
    }

    public function forget(CacheKey $key) : void
    {
        unset($this->records[$key->fullKey()]);
    }

    public function clear() : void
    {
        $this->records = [];
    }

    public function exists(CacheKey $key) : bool
    {
        $fullKey = $key->fullKey();

        if (! isset($this->records[$fullKey])) {
            return false;
        }

        return ! $this->records[$fullKey]->lifecycle->isExpired(clock: $this->clock);
    }

    public function getRecords() : array
    {
        return $this->records;
    }

    public function setRecords(array $records) : void
    {
        $this->records = $records;
    }

    public function count() : int
    {
        return count($this->records);
    }

    public function containsValue(mixed $value) : bool
    {
        foreach ($this->records as $record) {
            if ($record->value === $value) {
                return true;
            }
        }

        return false;
    }
}
