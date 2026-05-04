<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\tests\Fakes\Cache;

use Avax\Components\Application\DateTime\System\PublicSurface\Clock;
use Avax\Components\Application\DateTime\System\PublicSurface\SystemClock;
use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStore;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStoreRecordWasFound;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStoreRecordWasMissing;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\StoredCacheRecord;
use Override;

final class FakeCacheStore implements CacheStore
{
    /** @var array<string, StoredCacheRecord> */
    private array $records = [];

    public function __construct(private readonly Clock $clock = new SystemClock())
    {
    }

    #[Override]
    public function read(CacheKey $cacheKey, Clock $clock): CacheStoreRecordWasFound|CacheStoreRecordWasMissing
    {
        $fullKey = $cacheKey->fullKey();

        if (!isset($this->records[$fullKey])) {
            return new CacheStoreRecordWasMissing(cacheKey: $cacheKey);
        }

        $record = $this->records[$fullKey];

        if ($record->lifecycle->isExpired(clock: $clock)) {
            unset($this->records[$fullKey]);

            return new CacheStoreRecordWasMissing(cacheKey: $cacheKey);
        }

        return new CacheStoreRecordWasFound(cacheKey: $cacheKey, clock: $clock, record: $record);
    }

    #[Override]
    public function write(CacheKey $cacheKey, StoredCacheRecord $storedCacheRecord): void
    {
        $this->records[$cacheKey->fullKey()] = $storedCacheRecord;
    }

    #[Override]
    public function forget(CacheKey $cacheKey): void
    {
        unset($this->records[$cacheKey->fullKey()]);
    }

    #[Override]
    public function clear(): void
    {
        $this->records = [];
    }

    #[Override]
    public function exists(CacheKey $cacheKey): bool
    {
        $fullKey = $cacheKey->fullKey();

        if (!isset($this->records[$fullKey])) {
            return false;
        }

        return !$this->records[$fullKey]->lifecycle->isExpired(clock: $this->clock);
    }

    public function getRecords(): array
    {
        return $this->records;
    }

    public function setRecords(array $records): void
    {
        $this->records = $records;
    }

    public function count(): int
    {
        return count($this->records);
    }

    public function containsValue(mixed $value): bool
    {
        return array_any($this->records, fn($record): bool => $record->value === $value);
    }
}
