<?php

declare(strict_types=1);

namespace Avax\Cache\Tests\Unit\Cache\Flows\Lifecycle\WarmCache;

use Avax\Cache\System\Capabilities\Lifecycle\CachedValues\CachedValueLifecycle;
use Avax\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Cache\System\Capabilities\Storage\StoreCachedValues\InMemoryCacheStore;
use Avax\Cache\System\Capabilities\Storage\StoreCachedValues\StoredCacheRecord;
use Avax\Cache\System\Flows\Lifecycle\WarmCache\WarmCache;
use Avax\Cache\System\Foundation\Time\Duration;
use Avax\Cache\System\Foundation\Time\FrozenClock;
use Avax\Cache\System\Foundation\Time\Timestamp;
use PHPUnit\Framework\TestCase;

final class WarmCacheTest extends TestCase
{
    private FrozenClock        $clock;
    private InMemoryCacheStore $store;

    public function test_warm_with_callable_loaders() : void
    {
        $warmCache = new WarmCache(store: $this->store, clock: $this->clock);

        $entries = [
            'user:1' => static fn () => 'User One',
            'user:2' => static fn () => 'User Two',
            'user:3' => static fn () => 'User Three',
        ];

        $count = $warmCache->warm(entries: $entries);

        $this->assertEquals(expected: 3, actual: $count);
        $this->assertEquals(expected: 'User One', actual: $this->store->read(key: CacheKey::create(key: 'user:1'), clock: $this->clock)->record->value);
        $this->assertEquals(expected: 'User Two', actual: $this->store->read(key: CacheKey::create(key: 'user:2'), clock: $this->clock)->record->value);
        $this->assertEquals(expected: 'User Three', actual: $this->store->read(key: CacheKey::create(key: 'user:3'), clock: $this->clock)->record->value);
    }

    public function test_warm_with_static_values() : void
    {
        $warmCache = new WarmCache(store: $this->store, clock: $this->clock);

        $entries = [
            'config:theme' => 'dark',
            'config:lang'  => 'en',
        ];

        $count = $warmCache->warm(entries: $entries);

        $this->assertEquals(expected: 2, actual: $count);
        $this->assertEquals(expected: 'dark', actual: $this->store->read(key: CacheKey::create(key: 'config:theme'), clock: $this->clock)->record->value);
    }

    public function test_warm_with_custom_ttl() : void
    {
        $warmCache = new WarmCache(store: $this->store, clock: $this->clock);

        $entries = [
            'key_1' => 'value_1',
        ];

        $count = $warmCache->warm(entries: $entries, ttl: 7200);

        $this->assertEquals(expected: 1, actual: $count);
    }

    public function test_warm_returns_correct_count() : void
    {
        $warmCache = new WarmCache(store: $this->store, clock: $this->clock);

        $entries = [
            'key_1' => static fn () => 'value_1',
            'key_2' => static fn () => 'value_2',
            'key_3' => static fn () => 'value_3',
            'key_4' => static fn () => 'value_4',
            'key_5' => static fn () => 'value_5',
        ];

        $count = $warmCache->warm(entries: $entries);

        $this->assertEquals(expected: 5, actual: $count);
    }

    public function test_warm_overwrites_existing() : void
    {
        $this->store->write(key: CacheKey::create(key: 'existing'), record: new StoredCacheRecord(
            value    : 'old_value',
            lifecycle: CachedValueLifecycle::create(
                           createdAt: $this->clock->now(),
                           expiresAt: $this->clock->now()->add(duration: Duration::ofSeconds(seconds: 3600)),
                           clock    : $this->clock
                       )
        ));

        $warmCache = new WarmCache(store: $this->store, clock: $this->clock);

        $entries = [
            'existing' => static fn () => 'new_value',
        ];

        $warmCache->warm(entries: $entries);

        $result = $this->store->read(key: CacheKey::create(key: 'existing'), clock: $this->clock);

        $this->assertEquals(expected: 'new_value', actual: $result->record->value);
    }

    protected function setUp() : void
    {
        $this->clock = new FrozenClock(timestamp: Timestamp::now());
        $this->store = new InMemoryCacheStore(clock: $this->clock, maxEntries: 100);
    }
}