<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\tests\Unit\Cache\Flows\WarmCache;

use Avax\Components\Application\Cache\System\Capabilities\Lifecycle\CachedValues\CachedValueLifecycle;
use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\InMemoryCacheStore;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\StoredCacheRecord;
use Avax\Components\Application\Cache\System\Flows\Lifecycle\WarmCache\WarmCache;
use Avax\Components\Application\Cache\System\Foundation\Time\Duration;
use Avax\Components\Application\Cache\System\Foundation\Time\FrozenClock;
use Avax\Components\Application\Cache\System\Foundation\Time\Timestamp;
use Override;
use PHPUnit\Framework\TestCase;

final class WarmCacheTest extends TestCase
{
    private FrozenClock $frozenClock;

    private InMemoryCacheStore $inMemoryCacheStore;

    public function test_warm_with_callable_loaders() : void
    {
        $warmCache = new WarmCache(store: $this->inMemoryCacheStore, clock: $this->frozenClock);

        $entries = [
            'user:1' => static fn () : string => 'User One',
            'user:2' => static fn () : string => 'User Two',
            'user:3' => static fn () : string => 'User Three',
        ];

        $count = $warmCache->warm(entries: $entries);

        $this->assertEquals(expected: 3, actual: $count);
        $this->assertEquals(expected: 'User One', actual: $this->inMemoryCacheStore->read(key: CacheKey::create(key: 'user:1'), clock: $this->frozenClock)->record->value);
        $this->assertEquals(expected: 'User Two', actual: $this->inMemoryCacheStore->read(key: CacheKey::create(key: 'user:2'), clock: $this->frozenClock)->record->value);
        $this->assertEquals(expected: 'User Three', actual: $this->inMemoryCacheStore->read(key: CacheKey::create(key: 'user:3'), clock: $this->frozenClock)->record->value);
    }

    public function test_warm_with_static_values() : void
    {
        $warmCache = new WarmCache(store: $this->inMemoryCacheStore, clock: $this->frozenClock);

        $entries = [
            'config:theme' => 'dark',
            'config:lang' => 'en',
        ];

        $count = $warmCache->warm(entries: $entries);

        $this->assertEquals(expected: 2, actual: $count);
        $this->assertEquals(expected: 'dark', actual: $this->inMemoryCacheStore->read(key: CacheKey::create(key: 'config:theme'), clock: $this->frozenClock)->record->value);
    }

    public function test_warm_with_custom_ttl() : void
    {
        $warmCache = new WarmCache(store: $this->inMemoryCacheStore, clock: $this->frozenClock);

        $entries = [
            'key_1' => 'value_1',
        ];

        $count = $warmCache->warm(entries: $entries, ttl: 7200);

        $this->assertEquals(expected: 1, actual: $count);
    }

    public function test_warm_returns_correct_count() : void
    {
        $warmCache = new WarmCache(store: $this->inMemoryCacheStore, clock: $this->frozenClock);

        $entries = [
            'key_1' => static fn () : string => 'value_1',
            'key_2' => static fn () : string => 'value_2',
            'key_3' => static fn () : string => 'value_3',
            'key_4' => static fn () : string => 'value_4',
            'key_5' => static fn () : string => 'value_5',
        ];

        $count = $warmCache->warm(entries: $entries);

        $this->assertEquals(expected: 5, actual: $count);
    }

    public function test_warm_overwrites_existing() : void
    {
        $this->inMemoryCacheStore->write(key: CacheKey::create(key: 'existing'), record: new StoredCacheRecord(
            value    : 'old_value',
            lifecycle: CachedValueLifecycle::create(
                           createdAt: $this->frozenClock->now(),
                           expiresAt: $this->frozenClock->now()->add(duration: Duration::ofSeconds(seconds: 3600)),
                           clock    : $this->frozenClock,
                       ),
        ));

        $warmCache = new WarmCache(store: $this->inMemoryCacheStore, clock: $this->frozenClock);

        $entries = [
            'existing' => static fn () : string => 'new_value',
        ];

        $warmCache->warm(entries: $entries);

        $result = $this->inMemoryCacheStore->read(key: CacheKey::create(key: 'existing'), clock: $this->frozenClock);

        $this->assertEquals(expected: 'new_value', actual: $result->record->value);
    }

    #[Override]
    protected function setUp() : void
    {
        parent::setUp();
        $this->frozenClock = new FrozenClock(timestamp: Timestamp::now());
        $this->inMemoryCacheStore = new InMemoryCacheStore(clock: $this->frozenClock, maxEntries: 100);
    }
}
