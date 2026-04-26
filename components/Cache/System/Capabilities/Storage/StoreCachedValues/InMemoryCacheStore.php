<?php

declare(strict_types=1);

namespace components\Cache\System\Capabilities\Storage\StoreCachedValues;

use components\Cache\System\Capabilities\Lifecycle\ReplaceCachedValues\ChooseCachedValueForReplacement;
use components\Cache\System\Capabilities\Lifecycle\ReplaceCachedValues\LeastRecentlyUsedReplacement;
use components\Cache\System\Capabilities\Lifecycle\ReplaceCachedValues\TrackCachedValueAccess;
use components\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use components\Cache\System\Capabilities\Storage\SizeCachedValues\CacheCapacityWasExceeded;
use components\Cache\System\Foundation\Time\Clock;
use components\Cache\System\Foundation\Time\SystemClock;

final class InMemoryCacheStore implements CacheStore
{
    /** @var array<string, StoredCacheRecord> */
    private array $records = [];

    public function __construct(
        private Clock                           $clock = new SystemClock(),
        private int                             $maxEntries = 1000,
        private ChooseCachedValueForReplacement $replacementPolicy = new LeastRecentlyUsedReplacement()
    ) {}

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

        if ($this->replacementPolicy instanceof TrackCachedValueAccess) {
            $this->replacementPolicy->recordAccess(key: $fullKey);
        }

        $updatedLifecycle        = $record->lifecycle->withAccessed(clock: $clock);
        $this->records[$fullKey] = new StoredCacheRecord(
            value         : $record->value,
            lifecycle     : $updatedLifecycle,
            serializedData: $record->serializedData,
            format        : $record->format
        );

        return new CacheStoreRecordWasFound(key: $key, record: $this->records[$fullKey], clock: $clock);
    }

    public function write(CacheKey $key, StoredCacheRecord $record) : void
    {
        $fullKey = $key->fullKey();

        if (isset($this->records[$fullKey])) {
            $this->records[$fullKey] = $record;

            return;
        }

        if (count($this->records) >= $this->maxEntries) {
            $this->evictOne();
        }

        $this->records[$fullKey] = $record;
    }

    private function evictOne() : void
    {
        $entries = [];

        foreach ($this->records as $fullKey => $record) {
            if ($record->lifecycle->isExpired(clock: $this->clock)) {
                unset($this->records[$fullKey]);

                return;
            }

            $entries[$fullKey] = $record->lifecycle;
        }

        if (count($this->records) >= $this->maxEntries) {
            $toEvict = $this->replacementPolicy->choose(entries: $entries);

            if ($toEvict !== null) {
                unset($this->records[$toEvict]);
            } else {
                throw new CacheCapacityWasExceeded(capacity: $this->maxEntries);
            }
        }
    }

    public function forget(CacheKey $key) : void
    {
        $fullKey = $key->fullKey();

        if ($this->replacementPolicy instanceof TrackCachedValueAccess) {
            $this->replacementPolicy->removeKey(key: $fullKey);
        }

        unset($this->records[$fullKey]);
    }

    public function clear() : void
    {
        if ($this->replacementPolicy instanceof TrackCachedValueAccess) {
            $this->replacementPolicy->reset();
        }

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

    public function count() : int
    {
        return count($this->records);
    }

    public function getAllKeys() : array
    {
        return array_keys($this->records);
    }

    public function withCapacity(int $maxEntries) : self
    {
        return new self(
            clock            : $this->clock,
            maxEntries       : $maxEntries,
            replacementPolicy: $this->replacementPolicy
        );
    }

    public function withReplacementPolicy(ChooseCachedValueForReplacement $policy) : self
    {
        return new self(
            clock            : $this->clock,
            maxEntries       : $this->maxEntries,
            replacementPolicy: $policy
        );
    }
}