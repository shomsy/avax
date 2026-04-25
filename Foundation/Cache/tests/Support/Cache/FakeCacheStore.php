<?php

declare(strict_types=1);

namespace Avax\Cache\Tests\Support\Cache;

use Avax\Cache\System\Capabilities\IdentifyCachedValues\CacheKey;
use Avax\Cache\System\Capabilities\StoreCachedValues\CacheStore;
use Avax\Cache\System\Capabilities\StoreCachedValues\CacheStoreRecordWasFound;
use Avax\Cache\System\Capabilities\StoreCachedValues\CacheStoreRecordWasMissing;
use Avax\Cache\System\Capabilities\StoreCachedValues\StoredCacheRecord;
use Avax\Cache\System\Foundation\Time\Clock;
use Avax\Cache\System\Foundation\Time\SystemClock;

final class FakeCacheStore implements CacheStore
{
    /** @var array<string, StoredCacheRecord> */
    private array $records = [];

    private Clock $clock;

    public function __construct(?Clock $clock = null)
    {
        $this->clock = $clock ?? new SystemClock();
    }

    public function read(CacheKey $key, Clock $clock) : CacheStoreRecordWasFound|CacheStoreRecordWasMissing
    {
        $fullKey = $key->fullKey();

        if (! isset($this->records[$fullKey])) {
            return new CacheStoreRecordWasMissing($key);
        }

        $record = $this->records[$fullKey];

        if ($record->lifecycle->isExpired($clock)) {
            unset($this->records[$fullKey]);

            return new CacheStoreRecordWasMissing($key);
        }

        return new CacheStoreRecordWasFound($key, $record, $clock);
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

        return ! $this->records[$fullKey]->lifecycle->isExpired($this->clock);
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