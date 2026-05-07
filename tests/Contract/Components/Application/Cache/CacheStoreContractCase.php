<?php

declare(strict_types=1);

namespace Avax\Tests\Contract\Components\Application\Cache;

use Avax\Components\Application\Cache\System\Capabilities\Lifecycle\CachedValues\CachedValueLifecycle;
use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStore;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStoreRecordWasFound;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStoreRecordWasMissing;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\StoredCacheRecord;
use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Avax\Components\Application\Cache\System\Foundation\Time\Duration;
use Avax\Components\Application\Cache\System\Foundation\Time\FrozenClock;
use Avax\Components\Application\Cache\System\Foundation\Time\Timestamp;
use PHPUnit\Framework\TestCase;

abstract class CacheStoreContractCase extends TestCase
{
    public function test_it_returns_missing_when_key_does_not_exist(): void
    {
        $clock = $this->clock();
        $cacheStore = $this->createStore(clock: $clock);
        $cacheKey = CacheKey::create(key: 'nonexistent-key');

        $result = $cacheStore->read(cacheKey: $cacheKey, clock: $clock);

        $this->assertInstanceOf(expected: CacheStoreRecordWasMissing::class, actual: $result);
    }

    protected function clock(): FrozenClock
    {
        return new FrozenClock(timestamp: Timestamp::now());
    }

    abstract protected function createStore(Clock $clock): CacheStore;

    public function test_it_returns_stored_value_when_key_exists(): void
    {
        $clock = $this->clock();
        $cacheStore = $this->createStore(clock: $clock);
        $cacheKey = CacheKey::create(key: 'test-key');
        $value = 'test-value';

        $storedCacheRecord = new StoredCacheRecord(
            value    : $value,
            cachedValueLifecycle: CachedValueLifecycle::create(
                createdAt: $clock->now(),
                expiresAt: $clock->now()->add(duration: Duration::ofSeconds(seconds: 3600)),
                clock    : $clock,
            ),
        );

        $cacheStore->write(cacheKey: $cacheKey, storedCacheRecord: $storedCacheRecord);
        $result = $cacheStore->read(cacheKey: $cacheKey, clock: $clock);

        $this->assertInstanceOf(expected: CacheStoreRecordWasFound::class, actual: $result);
        $this->assertSame(expected: $value, actual: $result->value());
    }

    public function test_it_returns_stored_null_when_null_was_stored(): void
    {
        $clock = $this->clock();
        $cacheStore = $this->createStore(clock: $clock);
        $cacheKey = CacheKey::create(key: 'null-key');

        $storedCacheRecord = new StoredCacheRecord(
            value    : null,
            cachedValueLifecycle: CachedValueLifecycle::create(
                createdAt: $clock->now(),
                expiresAt: $clock->now()->add(duration: Duration::ofSeconds(seconds: 3600)),
                clock    : $clock,
            ),
        );

        $cacheStore->write(cacheKey: $cacheKey, storedCacheRecord: $storedCacheRecord);
        $result = $cacheStore->read(cacheKey: $cacheKey, clock: $clock);

        $this->assertInstanceOf(expected: CacheStoreRecordWasFound::class, actual: $result);
        $this->assertNull(actual: $result->value());
    }

    public function test_it_expires_value_when_ttl_has_passed(): void
    {
        $clock = $this->clock();
        $cacheStore = $this->createStore(clock: $clock);
        $cacheKey = CacheKey::create(key: 'expired-key');
        $value = 'test-value';

        $storedCacheRecord = new StoredCacheRecord(
            value    : $value,
            cachedValueLifecycle: CachedValueLifecycle::create(
                createdAt: $clock->now(),
                expiresAt: $clock->now()->add(duration: Duration::ofSeconds(seconds: 1)),
                clock    : $clock,
            ),
        );

        $cacheStore->write(cacheKey: $cacheKey, storedCacheRecord: $storedCacheRecord);
        $clock->moveForward(duration: Duration::ofSeconds(seconds: 2));
        $result = $cacheStore->read(cacheKey: $cacheKey, clock: $clock);

        $this->assertInstanceOf(expected: CacheStoreRecordWasMissing::class, actual: $result);
    }

    public function test_it_forgets_value_when_key_exists(): void
    {
        $clock = $this->clock();
        $cacheStore = $this->createStore(clock: $clock);
        $cacheKey = CacheKey::create(key: 'forget-key');
        $value = 'test-value';

        $storedCacheRecord = new StoredCacheRecord(
            value    : $value,
            cachedValueLifecycle: CachedValueLifecycle::create(
                createdAt: $clock->now(),
                expiresAt: $clock->now()->add(duration: Duration::ofSeconds(seconds: 3600)),
                clock    : $clock,
            ),
        );

        $cacheStore->write(cacheKey: $cacheKey, storedCacheRecord: $storedCacheRecord);
        $cacheStore->forget(cacheKey: $cacheKey);

        $result = $cacheStore->read(cacheKey: $cacheKey, clock: $clock);

        $this->assertInstanceOf(expected: CacheStoreRecordWasMissing::class, actual: $result);
    }

    public function test_it_clears_all_values(): void
    {
        $clock = $this->clock();
        $cacheStore = $this->createStore(clock: $clock);

        $storedCacheRecord = new StoredCacheRecord(
            value    : 'test-value',
            cachedValueLifecycle: CachedValueLifecycle::create(
                createdAt: $clock->now(),
                expiresAt: $clock->now()->add(duration: Duration::ofSeconds(seconds: 3600)),
                clock    : $clock,
            ),
        );

        $cacheStore->write(cacheKey: CacheKey::create(key: 'key1'), storedCacheRecord: $storedCacheRecord);
        $cacheStore->write(cacheKey: CacheKey::create(key: 'key2'), storedCacheRecord: $storedCacheRecord);
        $cacheStore->write(cacheKey: CacheKey::create(key: 'key3'), storedCacheRecord: $storedCacheRecord);

        $cacheStore->clear();

        $this->assertFalse(condition: $cacheStore->exists(cacheKey: CacheKey::create(key: 'key1')));
        $this->assertFalse(condition: $cacheStore->exists(cacheKey: CacheKey::create(key: 'key2')));
        $this->assertFalse(condition: $cacheStore->exists(cacheKey: CacheKey::create(key: 'key3')));
    }

    public function test_it_exists_returns_true_for_existing_key(): void
    {
        $clock = $this->clock();
        $cacheStore = $this->createStore(clock: $clock);
        $cacheKey = CacheKey::create(key: 'exists-key');

        $storedCacheRecord = new StoredCacheRecord(
            value    : 'test-value',
            cachedValueLifecycle: CachedValueLifecycle::create(
                createdAt: $clock->now(),
                expiresAt: $clock->now()->add(duration: Duration::ofSeconds(seconds: 3600)),
                clock    : $clock,
            ),
        );

        $cacheStore->write(cacheKey: $cacheKey, storedCacheRecord: $storedCacheRecord);

        $this->assertTrue(condition: $cacheStore->exists(cacheKey: $cacheKey));
    }

    public function test_it_exists_returns_false_for_nonexistent_key(): void
    {
        $clock = $this->clock();
        $cacheStore = $this->createStore(clock: $clock);
        $cacheKey = CacheKey::create(key: 'nonexistent-key');

        $this->assertFalse(condition: $cacheStore->exists(cacheKey: $cacheKey));
    }
}
