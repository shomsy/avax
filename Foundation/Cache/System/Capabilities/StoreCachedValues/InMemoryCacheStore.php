<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\StoreCachedValues;

use Avax\Cache\System\Capabilities\IdentifyCachedValues\CacheKey;
use Avax\Cache\System\Foundation\Time\Clock;
use Avax\Cache\System\Foundation\Time\SystemClock;

final class InMemoryCacheStore implements CacheStore
{
    /** @var array<string, StoredCacheRecord> */
    private array $records = [];

    public function __construct(
        private Clock $clock = new SystemClock()
    ) {}

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

        $updatedLifecycle        = $record->lifecycle->withAccessed($clock);
        $this->records[$fullKey] = new StoredCacheRecord(
            value         : $record->value,
            lifecycle     : $updatedLifecycle,
            serializedData: $record->serializedData,
            format        : $record->format
        );

        return new CacheStoreRecordWasFound($key, $this->records[$fullKey], $clock);
    }

    public function write(CacheKey $key, StoredCacheRecord $record) : void
    {
        $fullKey                 = $key->fullKey();
        $this->records[$fullKey] = $record;
    }

    public function forget(CacheKey $key) : void
    {
        $fullKey = $key->fullKey();
        unset($this->records[$fullKey]);
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

    public function count() : int
    {
        return count($this->records);
    }

    public function getAllKeys() : array
    {
        return array_keys($this->records);
    }
}