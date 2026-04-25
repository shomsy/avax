<?php

declare(strict_types=1);

namespace Avax\Cache\Tests\Contract\Cache;

use Avax\Cache\System\Capabilities\IdentifyCachedValues\CacheKey;
use Avax\Cache\System\Capabilities\ManageCacheLifecycle\CachedValueLifecycle;
use Avax\Cache\System\Capabilities\StoreCachedValues\CacheStore;
use Avax\Cache\System\Capabilities\StoreCachedValues\CacheStoreRecordWasFound;
use Avax\Cache\System\Capabilities\StoreCachedValues\CacheStoreRecordWasMissing;
use Avax\Cache\System\Capabilities\StoreCachedValues\StoredCacheRecord;
use Avax\Cache\System\Foundation\Time\Clock;
use Avax\Cache\System\Foundation\Time\Duration;
use Avax\Cache\System\Foundation\Time\FrozenClock;
use Avax\Cache\System\Foundation\Time\Timestamp;
use PHPUnit\Framework\TestCase;

abstract class CacheStoreContractTest extends TestCase
{
    public function test_it_returns_missing_when_key_does_not_exist() : void
    {
        $clock = $this->clock();
        $store = $this->createStore($clock);
        $key   = CacheKey::create('nonexistent-key');

        $result = $store->read($key, $clock);

        $this->assertInstanceOf(CacheStoreRecordWasMissing::class, $result);
    }

    protected function clock() : FrozenClock
    {
        return new FrozenClock(Timestamp::now());
    }

    abstract protected function createStore(Clock $clock) : CacheStore;

    public function test_it_returns_stored_value_when_key_exists() : void
    {
        $clock = $this->clock();
        $store = $this->createStore($clock);
        $key   = CacheKey::create('test-key');
        $value = 'test-value';

        $record = new StoredCacheRecord(
            value    : $value,
            lifecycle: CachedValueLifecycle::create(
                           createdAt: $clock->now(),
                           expiresAt: $clock->now()->add(Duration::ofSeconds(3600)),
                           clock    : $clock
                       )
        );

        $store->write($key, $record);
        $result = $store->read($key, $clock);

        $this->assertInstanceOf(CacheStoreRecordWasFound::class, $result);
        $this->assertSame($value, $result->value());
    }

    public function test_it_returns_stored_null_when_null_was_stored() : void
    {
        $clock = $this->clock();
        $store = $this->createStore($clock);
        $key   = CacheKey::create('null-key');

        $record = new StoredCacheRecord(
            value    : null,
            lifecycle: CachedValueLifecycle::create(
                           createdAt: $clock->now(),
                           expiresAt: $clock->now()->add(Duration::ofSeconds(3600)),
                           clock    : $clock
                       )
        );

        $store->write($key, $record);
        $result = $store->read($key, $clock);

        $this->assertInstanceOf(CacheStoreRecordWasFound::class, $result);
        $this->assertNull($result->value());
    }

    public function test_it_expires_value_when_ttl_has_passed() : void
    {
        $clock = $this->clock();
        $store = $this->createStore($clock);
        $key   = CacheKey::create('expired-key');
        $value = 'test-value';

        $record = new StoredCacheRecord(
            value    : $value,
            lifecycle: CachedValueLifecycle::create(
                           createdAt: $clock->now(),
                           expiresAt: $clock->now()->add(Duration::ofSeconds(1)),
                           clock    : $clock
                       )
        );

        $store->write($key, $record);
        $clock->moveForward(Duration::ofSeconds(2));
        $result = $store->read($key, $clock);

        $this->assertInstanceOf(CacheStoreRecordWasMissing::class, $result);
    }

    public function test_it_forgets_value_when_key_exists() : void
    {
        $clock = $this->clock();
        $store = $this->createStore($clock);
        $key   = CacheKey::create('forget-key');
        $value = 'test-value';

        $record = new StoredCacheRecord(
            value    : $value,
            lifecycle: CachedValueLifecycle::create(
                           createdAt: $clock->now(),
                           expiresAt: $clock->now()->add(Duration::ofSeconds(3600)),
                           clock    : $clock
                       )
        );

        $store->write($key, $record);
        $store->forget($key);
        $result = $store->read($key, $clock);

        $this->assertInstanceOf(CacheStoreRecordWasMissing::class, $result);
    }

    public function test_it_clears_all_values() : void
    {
        $clock = $this->clock();
        $store = $this->createStore($clock);

        $record = new StoredCacheRecord(
            value    : 'test-value',
            lifecycle: CachedValueLifecycle::create(
                           createdAt: $clock->now(),
                           expiresAt: $clock->now()->add(Duration::ofSeconds(3600)),
                           clock    : $clock
                       )
        );

        $store->write(CacheKey::create('key1'), $record);
        $store->write(CacheKey::create('key2'), $record);
        $store->write(CacheKey::create('key3'), $record);

        $store->clear();

        $this->assertFalse($store->exists(CacheKey::create('key1')));
        $this->assertFalse($store->exists(CacheKey::create('key2')));
        $this->assertFalse($store->exists(CacheKey::create('key3')));
    }

    public function test_it_exists_returns_true_for_existing_key() : void
    {
        $clock = $this->clock();
        $store = $this->createStore($clock);
        $key   = CacheKey::create('exists-key');

        $record = new StoredCacheRecord(
            value    : 'test-value',
            lifecycle: CachedValueLifecycle::create(
                           createdAt: $clock->now(),
                           expiresAt: $clock->now()->add(Duration::ofSeconds(3600)),
                           clock    : $clock
                       )
        );

        $store->write($key, $record);

        $this->assertTrue($store->exists($key));
    }

    public function test_it_exists_returns_false_for_nonexistent_key() : void
    {
        $clock = $this->clock();
        $store = $this->createStore($clock);
        $key   = CacheKey::create('nonexistent-key');

        $this->assertFalse($store->exists($key));
    }
}